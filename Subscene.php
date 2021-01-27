<?php

/**
 * Subscene-API-PHP
 * By NimaH79
 * http://NimaH79.ir.
 */
class Subscene
{
    private static $base_url = 'https://subscene.com';

    public static function search(string $title): array
    {
        $page = self::curl_post(
            self::$base_url.'/subtitles/searchbytitle',
            [
                'query' => $title,
            ]
        );
        $titles = self::xpathQuery("//ul/li/div[@class='title']/a/text()", $page);
        $urls = self::xpathQuery("//ul/li/div[@class='title']/a/@href", $page);
        $results = [];
        for ($i = 0; $i < count($titles); $i++) {
            $results[] = [
                'title' => $titles[$i]->nodeValue,
                'url'   => self::$base_url.$urls[$i]->nodeValue,
            ];
        }

        return $results;
    }

    public static function getSubtitles(string $url, array $languages = []): array
    {
        $cookie = null;
        if (!empty($languages)) {
            $cookie = 'LanguageFilter='.implode(',', $languages);
        }
        $page = self::curl_get_contents($url, $cookie);
        $result = [];
        foreach ([
            'title' => '//h2/text()',
            'year' => "//li[strong[contains(text(), 'Year')]]/text()[last()]",
            'poster' => "//img[@alt='Poster']/@src",
            'imdb' => "//a[@target='_blank' and @class='imdb']/@href",
        ] as $part => $query) {
            ${$part} = self::xpathQuery($query, $page);
            if (count(${$part}) > 0) {
                $result[$part] = trim(${$part}[0]->nodeValue);
            }
        }
        $subtitle_nodes = self::xpathQuery("//tr[td[@class='a5']]", $page);
        $titles = self::xpathQuery('//tr/td/a/span[2]/text()', $page);
        $languages = self::xpathQuery('//tr/td/a/span[1]/text()', $page);
        $authors_names = self::xpathQuery("//td[@class='a5']/a/text()", $page);
        $authors_urls = self::xpathQuery("//td[@class='a5']/a/@href", $page);
        $comments = self::xpathQuery("//td[@class='a6']/div/text()", $page);
        $urls = self::xpathQuery('//tr/td[1]/a/@href', $page);
        $subtitles = [];
        foreach ($subtitle_nodes as $subtitle_node) {
            $subtitle_node_html = $subtitle_node->ownerDocument->saveHTML($subtitle_node);
            $title = trim(self::xpathQuery('//td/a/span[2]/text()', $subtitle_node_html)[0]->nodeValue);
            $language = trim(self::xpathQuery('//td/a/span[1]/text()', $subtitle_node_html)[0]->nodeValue);
            $author_name = self::xpathEvaluate("boolean(//td[@class='a5']/a/text())", $subtitle_node_html) ? trim(self::xpathQuery("//td[@class='a5']/a/text()", $subtitle_node_html)[0]->nodeValue) : trim(self::xpathQuery("//td[@class='a5']/text()", $subtitle_node_html)[0]->nodeValue);
            $author_url = $author_name = self::xpathEvaluate("boolean(//td[@class='a5']/a/@href)", $subtitle_node_html) ? (self::$base_url.trim(self::xpathQuery("//td[@class='a5']/a/@href", $subtitle_node_html)[0]->nodeValue)) : 'n/A';
            $author = [
                'name' => $author_name,
                'url'  => $author_url,
            ];
            $comment = trim(self::xpathQuery("//td[@class='a6']/div/text()", $subtitle_node_html)[0]->nodeValue);
            $url = self::$base_url.trim(self::xpathQuery('//tr/td[1]/a/@href', $subtitle_node_html)[0]->nodeValue);
            $subtitles[] = compact('title', 'language', 'author', 'comment', 'url');
        }
        $result['subtitles'] = $subtitles;

        return $result;
    }

    public static function getSubtitleInfo(string $url): array
    {
        $page = self::curl_get_contents($url);
        $result = [];
        $url = self::xpathQuery("//a[@id='downloadButton']/@href", $page);
        if (count($url) < 1) {
            return false;
        }
        foreach ([
            'title' => "//span[@itemprop='name']",
            'poster' => "//img[@alt='Poster']/@src",
            'author' => "//li[@class='author']/a/text()",
            'comment' => "//div[@class='comment']",
            'imdb' => "//a[@class='imdb']/@href",
        ] as $part => $query) {
            ${$part} = self::xpathQuery($query, $page);
            if (count(${$part}) > 0) {
                $result[$part] = trim(${$part}[0]->nodeValue);
            }
        }
        $preview = self::xpathQuery("//div[@id='preview']/p", $page);
        $result['preview'] = $preview[0]->ownerDocument->saveHTML($preview[0]);
        $info = self::xpathQuery("//li[@class='release']/div", $page);
        if (count($info) > 0) {
            $info_text = '';
            for ($i = 0; $i < count($info); $i++) {
                $info_text .= trim($info[$i]->nodeValue)."\n";
            }
            $result['info'] = $info_text;
        }
        $details = self::xpathQuery("//div[@id='details']/ul/li", $page);
        if (count($details) > 0) {
            $details_text = '';
            for ($i = 0; $i < count($details); $i++) {
                $details_text .= trim(str_replace(["\n", "\r", "\t"], '', $details[$i]->nodeValue))."\n";
            }
            $result['details'] = $details_text;
        }
        $result['download_url'] = self::$base_url.$url[0]->nodeValue;

        return $result;
    }

    public static function getHome(): array
    {
        $page = self::curl_get_contents(self::$base_url);
        $result = [
            'popular'    => [],
            'popular_tv' => [],
            'recent'     => [],
        ];

        // Popular subtitles
        $titles = self::xpathQuery("//div[@class='popular-films']/div[@class='box'][1]//div[@class='title']/a[1]/text()", $page);
        $posters = self::xpathQuery("//div[@class='popular-films']/div[@class='box'][1]//div[@class='poster']/img/@src", $page);
        $imdbs = self::xpathQuery("//div[@class='popular-films']/div[@class='box'][1]//div[@class='title']/a[2]/@href", $page);
        $urls = self::xpathQuery("//div[@class='popular-films']/div[@class='box'][1]//div[@class='title']/a[1]/@href", $page);
        for ($i = 0; $i < count($titles); $i++) {
            $item = [
                'title'  => trim($titles[$i]->nodeValue),
                'poster' => $posters[$i]->nodeValue,
                'url'    => self::$base_url.$urls[$i]->nodeValue,
            ];
            if (!empty($imdbs[$i])) {
                $item['poseter'] = $posters[$i]->nodeValue;
            }
            $result['popular'][] = $item;
        }

        // Popular tv subtitles
        $titles = self::xpathQuery("//div[@class='popular-films']/div[@class='box'][2]//div[@class='title']/a[1]/text()", $page);
        $posters = self::xpathQuery("//div[@class='popular-films']/div[@class='box'][2]//div[@class='poster']/img/@src", $page);
        $imdbs = self::xpathQuery("//div[@class='popular-films']/div[@class='box'][2]//div[@class='title']/a[2]/@href", $page);
        $urls = self::xpathQuery("//div[@class='popular-films']/div[@class='box'][2]//div[@class='title']/a[1]/@href", $page);
        for ($i = 0; $i < count($titles); $i++) {
            $item = [
                'title'  => trim($titles[$i]->nodeValue),
                'poster' => $posters[$i]->nodeValue,
                'url'    => self::$base_url.$urls[$i]->nodeValue,
            ];
            if (!empty($imdbs[$i])) {
                $item['poseter'] = $posters[$i]->nodeValue;
            }
            $result['popular_tv'][] = $item;
        }

        // Recent subtitles
        $titles = self::xpathQuery("//div[@class='recent-subtitles']//li/div/a/text()[last()]", $page);
        $urls = self::xpathQuery("//div[@class='recent-subtitles']//li/div/a/@href", $page);
        $contributors_names = self::xpathQuery("//div[@class='recent-subtitles']//li/address/a/text()", $page);
        $contributors_urls = self::xpathQuery("//div[@class='recent-subtitles']//li/address/a/@href", $page);
        for ($i = 0; $i < count($titles); $i++) {
            $result['recent'][] = [
                'title'       => trim($titles[$i]->nodeValue),
                'contributor' => [
                    'name' => trim($contributors_names[$i]->nodeValue),
                    'url'  => self::$base_url.trim($contributors_urls[$i]->nodeValue),
                ],
                'url' => self::$base_url.$urls[$i]->nodeValue,
            ];
        }

        return $result;
    }

    public static function getDownload(string $url, string $filename): void
    {
        $data = self::curl_get_contents($url);
        $file_name = $filename;
        header('Content-Type: application/zip');
        header("Content-Disposition: attachment; filename=$file_name");
        header('Content-Length: '.strlen($data));
        echo $data;
    }

    private static function curl_get_contents(string $url, ?string $cookie = null): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Safari/537.36',
        ]);
        if (!is_null($cookie)) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    private static function curl_post(string $url, ?array $parameters = null): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $parameters,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Safari/537.36',
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    private static function xpathQuery(string $query, string $html): DOMNodeList
    {
        $libxml_use_internal_errors = libxml_use_internal_errors(true);
        if (empty($query) || empty($html)) {
            return new DOMNodeList();
        }
        $xpath = self::htmlToDomXPath($html);
        $results = $xpath->query($query);
        libxml_use_internal_errors($libxml_use_internal_errors);

        return $results;
    }

    private static function xpathEvaluate(string $expression, string $html)
    {
        $libxml_use_internal_errors = libxml_use_internal_errors(true);
        if (empty($expression) || empty($html)) {
            return false;
        }
        $xpath = self::htmlToDomXPath($html);
        $result = $xpath->evaluate($expression);
        libxml_use_internal_errors($libxml_use_internal_errors);

        return $result;
    }

    private static function htmlToDomXPath(string $html): DomXPath
    {
        $dom = new DomDocument();
        $dom->loadHTML("<?xml encoding='utf-8'?>$html");
        $xpath = new DomXPath($dom);

        return $xpath;
    }
}

<?php

/*
 * Subscene-API-PHP
 * By NimaH79
 * http://NimaH79.ir
 */

libxml_use_internal_errors(true);

class Subscene
{
    private $username;
    private $password;
    private $cookie_file;
    private $languages;

    private $base_url = 'https://subscene.com';

    public function __construct($username, $password, $languages = [46], $cookie_file = __DIR__.'/cookies.txt')
    { // 46 is for Persian
        foreach ([
            'username',
            'password',
            'cookie_file',
            'languages',
        ] as $var) {
            $this->{$var} = ${$var};
        }
    }

    public function search($title, $exit_on_bad_request = false)
    {
        $page = $this->curl_post($this->base_url.'/subtitles/searchbytitle', ['query' => $title]);
        if (!$this->isLoggedIn($page)) {
            if ($exit_on_bad_request) {
                return false;
            }
            $this->login($this->username, $this->password);

            return $this->search($title, true);
        }
        $titles = $this->xpathQuery('//ul/li/div[@class="title"]/a/text()', $page);
        $urls = $this->xpathQuery('//ul/li/div[@class="title"]/a/@href', $page);
        $results = [];
        for ($i = 0; $i < $titles->length; $i++) {
            $results[] = ['title' => $titles[$i]->nodeValue, 'url' => $this->base_url.$urls[$i]->nodeValue];
        }

        return $results;
    }

    public function getSubtitles($url, $exit_on_bad_request = false)
    {
        $page = $this->curl_get_contents($url);
        if (!$this->isLoggedIn($page)) {
            if ($exit_on_bad_request) {
                return false;
            }
            $this->login($this->username, $this->password);

            return $this->getSubtitles($url, true);
        }
        $result = [];
        foreach ([
            'title' => '//h2/text()',
            'year' => '//li[strong[contains(text(), "Year")]]/text()[last()]',
            'poster' => '//img[@alt="Poster"]/@src',
            'imdb' => '//a[@class="imdb"]/@href',
        ] as $part => $query) {
            ${$part} = $this->xpathQuery($query, $page);
            if (${$part}->length > 0) {
                $result[$part] = trim(${$part}[0]->nodeValue);
            }
        }
        $titles = $this->xpathQuery('//tr/td/a/span[2]/text()', $page);
        $languages = $this->xpathQuery('//tr/td/a/span[1]/text()', $page);
        $authors_names = $this->xpathQuery('//td[@class="a5"]/a/text()', $page);
        $authors_urls = $this->xpathQuery('//td[@class="a5"]/a/@href', $page);
        $comments = $this->xpathQuery('//td[@class="a6"]/div/text()', $page);
        $urls = $this->xpathQuery('//tr/td[1]/a/@href', $page);
        $subtitles = [];
        for ($i = 0; $i < $titles->length; $i++) {
            $subtitles[] = ['title' => trim($titles[$i]->nodeValue), 'language' => trim($languages[$i]->nodeValue), 'author' => ['name' => trim($authors_names[$i]->nodeValue), 'url' => trim($authors_urls[$i]->nodeValue)], 'comment' => trim($comments[$i]->nodeValue), 'url' => $this->base_url.$urls[$i]->nodeValue];
        }
        $result['subtitles'] = $subtitles;

        return $result;
    }

    public function getSubtitleInfo($url, $exit_on_bad_request = false)
    {
        $page = $this->curl_get_contents($url);
        if (!$this->isLoggedIn($page)) {
            if ($exit_on_bad_request) {
                return false;
            }
            $this->login($this->username, $this->password);

            return $this->getSubtitleInfo($url, true);
        }
        $result = [];
        $url = $this->xpathQuery('//a[@id="downloadButton"]/@href', $page);
        if ($url->length < 1) {
            return false;
        }
        foreach ([
            'title' => '//span[@itemprop="name"]',
            'poster' => '//img[@alt="Poster"]/@src',
            'author' => '//li[@class="author"]/a/text()',
            'comment' => '//div[@class="comment"]',
            'imdb' => '//a[@class="imdb"]/@href',
        ] as $part => $query) {
            ${$part} = $this->xpathQuery($query, $page);
            if (${$part}->length > 0) {
                $result[$part] = trim(${$part}[0]->nodeValue);
            }
        }
        $dom = new DomDocument();
        $dom->loadHTML($page);
        $xpath = new DomXPath($dom);
        $preview = $xpath->query('//div[@id="preview"]/p');
        $result['preview'] = $preview[0]->ownerDocument->saveHTML($preview[0]);
        $info = $this->xpathQuery('//li[@class="release"]/div', $page);
        if ($info->length > 0) {
            $info_text = '';
            for ($i = 0; $i < $info->length; $i++) {
                $info_text .= trim($info[$i]->nodeValue)."\n";
            }
            $result['info'] = $info_text;
        }
        $details = $this->xpathQuery('//div[@id="details"]/ul/li', $page);
        if ($details->length > 0) {
            $details_text = '';
            for ($i = 0; $i < $details->length; $i++) {
                $details_text .= trim(str_replace(["\n", "\r", "\t"], '', $details[$i]->nodeValue))."\n";
            }
            $result['details'] = $details_text;
        }
        $result['url'] = $this->base_url.$url[0]->nodeValue;

        return $result;
    }

    public function getHome()
    {
        $page = $this->curl_get_contents($this->base_url);
        $result = ['popular' => [], 'popular_tv' => [], 'recent' => []];

        // Popular subtitles
        $titles = $this->xpathQuery('//div[@class="popular-films"]/div[@class="box"][1]//div[@class="title"]/a[1]/text()', $page);
        $posters = $this->xpathQuery('//div[@class="popular-films"]/div[@class="box"][1]//div[@class="poster"]/img/@src', $page);
        $imdbs = $this->xpathQuery('//div[@class="popular-films"]/div[@class="box"][1]//div[@class="title"]/a[2]/@href', $page);
        $urls = $this->xpathQuery('//div[@class="popular-films"]/div[@class="box"][1]//div[@class="title"]/a[1]/@href', $page);
        for ($i = 0; $i < $titles->length; $i++) {
            $item = ['title' => trim($titles[$i]->nodeValue), 'poster' => $posters[$i]->nodeValue, 'url' => $this->base_url.$urls[$i]->nodeValue];
            if (!empty($imdbs[$i])) {
                $item['poseter'] = $posters[$i]->nodeValue;
            }
            $result['popular'][] = $item;
        }

        // Popular tv subtitles
        $titles = $this->xpathQuery('//div[@class="popular-films"]/div[@class="box"][2]//div[@class="title"]/a[1]/text()', $page);
        $posters = $this->xpathQuery('//div[@class="popular-films"]/div[@class="box"][2]//div[@class="poster"]/img/@src', $page);
        $imdbs = $this->xpathQuery('//div[@class="popular-films"]/div[@class="box"][2]//div[@class="title"]/a[2]/@href', $page);
        $urls = $this->xpathQuery('//div[@class="popular-films"]/div[@class="box"][2]//div[@class="title"]/a[1]/@href', $page);
        for ($i = 0; $i < $titles->length; $i++) {
            $item = ['title' => trim($titles[$i]->nodeValue), 'poster' => $posters[$i]->nodeValue, 'url' => $this->base_url.$urls[$i]->nodeValue];
            if (!empty($imdbs[$i])) {
                $item['poseter'] = $posters[$i]->nodeValue;
            }
            $result['popular_tv'][] = $item;
        }

        // Recent subtitles
        $titles = $this->xpathQuery('//div[@class="recent-subtitles"]//li/div/a/text()[last()]', $page);
        $urls = $this->xpathQuery('//div[@class="recent-subtitles"]//li/div/a/@href', $page);
        $contributors_names = $this->xpathQuery('//div[@class="recent-subtitles"]//li/address/a/text()', $page);
        $contributors_urls = $this->xpathQuery('//div[@class="recent-subtitles"]//li/address/a/@href', $page);
        for ($i = 0; $i < $titles->length; $i++) {
            $result['recent'][] = ['title' => trim($titles[$i]->nodeValue), 'contributor' => ['name' => trim($contributors_names[$i]->nodeValue), 'url' => $this->base_url.trim($contributors_urls[$i]->nodeValue)], 'url' => $this->base_url.$urls[$i]->nodeValue];
        }

        return $result;
    }

    public function getDownload($url, $filename) {
        $data = $this->curl_get_contents($url);
        $file_name = $filename;
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename='.$file_name);
        header('Content-Length: '.strlen($data));
        die($data);
    }

    public function setLanguages($languages = [])
    {
        if (empty($languages)) {
            $languages = $this->languages;
        }
        $parameters = 'ReturnUrl=';
        foreach ($languages as $language) {
            $parameters .= '&SelectedIds='.$language;
        }
        $parameters .= '&HearingImpaired=2&ForeignOnly=false';
        $this->curl_post('https://u.subscene.com/filter', $parameters);
    }

    public function login($username, $password)
    {
        $login_info = $this->curl_get_contents($this->base_url.'/account/login');
        $login_info = $this->xpathQuery('//script[@id="modelJson"]', $login_info);
        if ($login_info->length < 1) {
            return false;
        }
        $login_info = json_decode(htmlspecialchars_decode(trim($login_info[0]->nodeValue)), true);
        $form_info = $this->curl_post('https://identity.jeded.com'.$login_info['loginUrl'], http_build_query(['idsrv.xsrf' => $login_info['antiForgery']['value'], 'username' => $username, 'password' => $password, 'rememberMe' => 'true']));
        $form = [];
        foreach (['id_token', 'access_token', 'token_type', 'expires_in', 'scope', 'state', 'session_state'] as $key) {
            ${$key} = $this->xpathQuery('//input[@name="'.$key.'"]/@value', $form_info);
            if (${$key}->length < 1) {
                return false;
            }
            ${$key} = ${$key}[0]->nodeValue;
            $form[$key] = ${$key};
        }
        $this->curl_post($this->base_url, http_build_query($form));
        $this->setLanguages();

        return true;
    }

    private function curl_get_contents($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    private function curl_post($url, $parameters = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    private function xpathQuery($query, $html)
    {
        if (empty($query) || empty($html)) {
            return false;
        }
        $dom = new DomDocument();
        $dom->loadHTML($html);
        $xpath = new DomXPath($dom);
        $results = $xpath->query($query);

        return $results;
    }

    private function isLoggedIn($html)
    {
        return strpos($html, 'logout') !== false;
    }
}

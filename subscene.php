<?php

/*
 * Subscene-API-PHP
 * By NimaH79
 * https://NimaH79.ir
 */

libxml_use_internal_errors(true);

class subscene
{
    private static $base_url = 'https://subscene.com';
    private static $default_language = 'farsi_persian';

    public static function search($title)
    {
        $page = self::curl_get_contents(self::$base_url.'/subtitles/title?q='.urlencode($title));
        $titles = self::xpathQuery('//ul/li/div[@class=\'title\']/a/text()', $page);
        $urls = self::xpathQuery('//ul/li/div[@class=\'title\']/a/@href', $page);
        $results = [];
        for ($i = 0; $i < count($titles); $i++) {
            $results[] = ['title' => $titles[$i]->nodeValue, 'url' => self::$base_url.$urls[$i]->nodeValue];
        }

        return $results;
    }

    public static function getSubtitles($url, $language = '')
    {
        if (empty($language)) {
            $language = self::$default_language;
        }
        $page = self::curl_get_contents($url.'/'.$language);
        $titles = self::xpathQuery('//tr/td/a/span[2]/text()', $page);
        $urls = self::xpathQuery('//tr/td/a/@href', $page);
        $results = [];
        for ($i = 0; $i < count($titles); $i++) {
            $results[] = ['title' => trim($titles[$i]->nodeValue), 'url' => self::$base_url.$urls[$i]->nodeValue];
        }

        return $results;
    }

    public static function getDownloadUrl($url)
    {
        $page = self::curl_get_contents($url);
        $url = self::xpathQuery('//a[@id=\'downloadButton\']/@href', $page);
        if (count($url) == 0) {
            return false;
        }

        return self::$base_url.$url[0]->nodeValue;
    }

    private static function curl_get_contents($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    private static function xpathQuery($query, $html)
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
}

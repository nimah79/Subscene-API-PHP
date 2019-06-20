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

    private $base_url = 'https://subscene.com';
    private $default_language = 'farsi_persian';

    public function __construct($username, $password, $cookie_file = __DIR__.'/cookies.txt') {
        $this->username = $username;
        $this->password = $password;
        $this->cookie_file = $cookie_file;
    }

    public function search($title, $exit_on_bad_request = false)
    {
        $page = $this->curl_post($this->base_url.'/subtitles/searchbytitle', ['query' => $title]);
        if($this->isBadRequest($page)) {
            if($exit_on_bad_request) {
                return false;
            }
            $this->login($this->username, $this->password);
            return $this->search($title, true);
        }
        $titles = $this->xpathQuery('//ul/li/div[@class=\'title\']/a/text()', $page);
        $urls = $this->xpathQuery('//ul/li/div[@class=\'title\']/a/@href', $page);
        $results = [];
        for ($i = 0; $i < $titles->length; $i++) {
            $results[] = ['title' => $titles[$i]->nodeValue, 'url' => $this->base_url.$urls[$i]->nodeValue];
        }

        return $results;
    }

    public function getSubtitles($url, $language = '', $exit_on_bad_request = false)
    {
        if (empty($language)) {
            $language = $this->default_language;
        }
        $page = $this->curl_get_contents($url.'/'.$language);
        if($this->isBadRequest($page)) {
            if($exit_on_bad_request) {
                return false;
            }
            $this->login($this->username, $this->password);
            return $this->getSubtitles($url, $language, true);
        }
        $titles = $this->xpathQuery('//tr/td/a/span[2]/text()', $page);
        $urls = $this->xpathQuery('//tr/td/a/@href', $page);
        $results = [];
        for ($i = 0; $i < $titles->length; $i++) {
            $results[] = ['title' => trim($titles[$i]->nodeValue), 'url' => $this->base_url.$urls[$i]->nodeValue];
        }

        return $results;
    }

    public function getDownloadUrl($url, $exit_on_bad_request = false)
    {
        $page = $this->curl_get_contents($url);
        if($this->isBadRequest($page)) {
            if($exit_on_bad_request) {
                return false;
            }
            $this->login($this->username, $this->password);
            return $this->getDownloadUrl($url, true);
        }
        $url = $this->xpathQuery('//a[@id=\'downloadButton\']/@href', $page);
        if ($url->length < 1) {
            return false;
        }

        return $this->base_url.$url[0]->nodeValue;
    }

    public function login($username, $password)
    {
        $login_info = $this->curl_get_contents($this->base_url.'/account/login');
        $login_info = $this->xpathQuery('//script[@id="modelJson"]', $login_info);
        if($login_info->length < 1) {
            return false;
        }
        $login_info = json_decode(htmlspecialchars_decode(trim($login_info[0]->nodeValue)), true);
        $form_info = $this->curl_post('https://identity.jeded.com'.$login_info['loginUrl'], http_build_query(['idsrv.xsrf' => $login_info['antiForgery']['value'], 'username' => $username, 'password' => $password, 'rememberMe' => 'true']));
        $form = [];
        foreach(['id_token', 'access_token', 'token_type', 'expires_in', 'scope', 'state', 'session_state'] as $key) {
            ${$key} = $this->xpathQuery('//input[@name="'.$key.'"]/@value', $form_info);
            if(${$key}->length < 1) {
                return false;
            }
            ${$key} = ${$key}[0]->nodeValue;
            $form[$key] = ${$key};
        }
        $this->curl_post($this->base_url, http_build_query($form));

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

    private function isBadRequest($html)
    {
        return strpos($html, 'Bad request') !== false;
    }

}

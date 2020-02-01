<?php

use PHPUnit\Framework\TestCase;

final class SubsceneTest extends TestCase
{
    protected $cookie_file_path = __DIR__.'/../cookies.txt';

    protected function clean()
    {
        if (file_exists($this->cookie_file_path)) {
            unlink($this->cookie_file_path);
        }
    }

    public function testSearch()
    {
        require_once __DIR__.'/../Subscene.php';
        $subscene = new Subscene('ntrolly79', '10111379');
        $this->assertNotEmpty($subscene->search('Fast Five'));
        $this->clean();
    }

    public function testGetSubtitles()
    {
        require_once __DIR__.'/../Subscene.php';
        $subscene = new Subscene('ntrolly79', '10111379');
        $this->assertNotEmpty($subscene->getSubtitles('https://subscene.com/subtitles/fast-five-fast-and-furious-5-the-rio-heist'));
        $this->clean();
    }

    public function testGetSubtitleInfo()
    {
        require_once __DIR__.'/../Subscene.php';
        $subscene = new Subscene('ntrolly79', '10111379');
        $this->assertNotEmpty($subscene->getSubtitleInfo('https://subscene.com/subtitles/fast-five-fast-and-furious-5-the-rio-heist/farsi_persian/1108695'));
        $this->clean();
    }
}

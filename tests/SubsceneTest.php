<?php

use PHPUnit\Framework\TestCase;

final class SubsceneTest extends TestCase
{
    public function testSearch()
    {
        require_once __DIR__.'/../Subscene.php';
        $subscene = new Subscene();
        $this->assertNotEmpty($subscene->search('Fast Five'));
    }

    public function testGetSubtitles()
    {
        require_once __DIR__.'/../Subscene.php';
        $subscene = new Subscene();
        $this->assertNotEmpty($subscene->getSubtitles('https://subscene.com/subtitles/fast-five-fast-and-furious-5-the-rio-heist'));
    }

    public function testGetSubtitleInfo()
    {
        require_once __DIR__.'/../Subscene.php';
        $subscene = new Subscene();
        $this->assertNotEmpty($subscene->getSubtitleInfo('https://subscene.com/subtitles/fast-five-fast-and-furious-5-the-rio-heist/farsi_persian/1108695'));
    }
}

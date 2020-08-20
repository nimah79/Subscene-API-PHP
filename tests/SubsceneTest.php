<?php

use PHPUnit\Framework\TestCase;

final class SubsceneTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__.'/../Subscene.php';
    }

    public function testSearch()
    {
        $this->assertNotEmpty(Subscene::search('Fast Five'));
    }

    public function testGetSubtitles()
    {
        $this->assertNotEmpty(Subscene::getSubtitles('https://subscene.com/subtitles/fast-five-fast-and-furious-5-the-rio-heist'));
    }

    public function testGetSubtitlesCustomLanguage()
    {
        $subtitles = Subscene::getSubtitles('https://subscene.com/subtitles/fast-five-fast-and-furious-5-the-rio-heist', [46]);
        $invalid_language_found = false;
        foreach ($subtitles['subtitles'] as $subtitle) {
            if ($subtitle['language'] != 'Farsi/Persian') {
                $invalid_language_found = true;
                break;
            }
        }
        $this->assertFalse($invalid_language_found);
    }

    public function testGetSubtitleInfo()
    {
        $this->assertNotEmpty(Subscene::getSubtitleInfo('https://subscene.com/subtitles/fast-five-fast-and-furious-5-the-rio-heist/farsi_persian/1108695'));
    }
}

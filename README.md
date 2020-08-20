# Subscene-API-PHP

Unofficial API for Subscene subtitle service, written in PHP

## Usage

Just include `Subscene.php` to your project and use it:

```php
<?php

require_once __DIR__ . "/Subscene.php";

$movies = Subscene::search("Fast Five");
// Array
// (
//     [0] => Array
//         (
//             [title] => Fast Five - How We Roll (Fast Five) [Album- iDon] (2009)
//             [url] => https://subscene.com/subtitles/fast-five-how-we-roll-fast-five-album-idon
//         )
// 
//     [1] => Array
//         (
//             [title] => Fast Five (Fast & Furious 5: The Rio Heist) (2011)
//             [url] => https://subscene.com/subtitles/fast-five-fast-and-furious-5-the-rio-heist
//         )
// 
//     [2] => Array
//         (
//             [title] => How We Roll Fast Five Remix (2011)
//             [url] => https://subscene.com/subtitles/how-we-roll-fast-five-remix
//         )
// 
//     [3] => Array
//         (
//             [title] => Fast Five (Fast & Furious 5: The Rio Heist) (2011)
//             [url] => https://subscene.com/subtitles/fast-five-fast-and-furious-5-the-rio-heist
//         )
// )

$subtitles = Subscene::getSubtitles("https://subscene.com/subtitles/fast-five-fast-and-furious-5-the-rio-heist");
// Array
// (
//     [title] => Fast Five (Fast & Furious 5: The Rio Heist)
//     [year] => 2011
//     [poster] => https://i.jeded.com/i/fast-five-fast-and-furious-5-the-rio-heist.154-8128.jpg
//     [imdb] => https://www.imdb.com/title/tt1596343
//     [subtitles] => Array
//         (
//             [0] => Array
//                 (
//                     [title] => Fast.Five.2011.EXTENDED.720p.BluRay.DTS.x264-DON
//                     [language] => Farsi/Persian
//                     [author] => Array
//                         (
//                             [name] => msasanmh
//                             [url] => https://subscene.com/u/797826
//                         )
// 
//                     [comment] => برای تمامی نسخه‌های بلوری و غیر اکستندد ---ترجمه جدید سال 2015 
//                     [url] => https://subscene.com/subtitles/fast-five-fast-and-furious-5-the-rio-heist/farsi_persian/1108695
//                 )
// 
//             [1] => Array
//                 (
//                     [title] => Fast.Five.2011.BluRay.720p.DTS.x264-CHD
//                     [language] => Farsi/Persian
//                     [author] => Array
//                         (
//                             [name] => msasanmh
//                             [url] => https://subscene.com/u/797826
//                         )
// 
//                     [comment] => برای تمامی نسخه‌های بلوری و غیر اکستندد ---ترجمه جدید سال 2015 
//                     [url] => https://subscene.com/subtitles/fast-five-fast-and-furious-5-the-rio-heist/farsi_persian/1108695
//                 )
// )

$subtitle_info = Subscene::getSubtitleInfo("https://subscene.com/subtitles/fast-five-fast-and-furious-5-the-rio-heist/farsi_persian/1108695");
// Array
// (
//     [title] => Fast Five (Fast & Furious 5: The Rio Heist)
//     [poster] => https://i.jeded.com/i/fast-five-fast-and-furious-5-the-rio-heist.154-8128.jpg
//     [author] => msasanmh
//     [comment] => برای تمامی نسخه‌های بلوری و غیر اکستندد ---
// ترجمه جدید سال 2015
//     [imdb] => https://www.imdb.com/title/tt1596343
//     [preview] => <p>
//                                                                 1<br>00:00:35,702 --&gt; 00:00:38,128<br>JUDGE: Dominic Toretto.<br><br>2<br>00:00:38,455 --&gt; 00:00:42,499<br>You are // hereby sentenced<br>to serve 25 years to life<br><br>3<br>00:00:42,500 --&gt; 00:00:46,295<br>at the Lompoc Maximum Security<br>Prison system<br><br>4<br>00:00:46,296 --&gt; 00:00:50,098<br>&lt;i&gt;without the possibility<br>of early parole.&lt;/i&gt;<br><br>5<br>00:01:35,845 --&gt; 00:01:37,645<br>(TIRES SQUEALING)<br><br>6<br>
//                                                         </p>
//     [info] => Fast.Five.2011.720p.BluRay.x264.YIFY
// Fast.Five.2011.1080p.BluRay.x264.YIFY
// Fast.Five.2011.720p.BluRay.x264.DTS-WiKi
// Fast.Five.2011.1080p.BluRay.x264.DTS-WiKi
// Fast.Five.2011.BluRay.720p.DTS.x264-CHD
// Fast.Five.2011.BluRay.1080p.DTS.x264-CHD
// Fast.Five.2011.EXTENDED.720p.BluRay.DTS.x264-DON
// Fast.Five.2011.BluRay.1080p.DTS.DUAL.x264
// Fast and Furious 5 Fast Five 2011 BDRip 500MB x264 AAC-DiDee
// Fast Five 2011 720p BDRip XviD AC3-ViSiON
// 
//     [details] => Online:5/1/2015 9:34 AM 
// Hearing Impaired:No
// Foreign parts:Yes
// Framerate:23.976
// Files:2 (72,727bytes)
// Production type:Translated a subtitle
// Release type:Blu-ray
// ---------------------------------------
// Rated:10/10 from15 users
// Voted as Good by:15 users
// Downloads:2,438
// 
//     [download_url] => https://subscene.com/subtitles/farsi_persian-text/WqoLpe1QAjx6piQHWXmhrAM3cg2siBVAHTCqvpqNOvuT4VkzRdV0daps-FEMwk__g1FFHwEbjQ9G23wsaVEeYnk2VAVS3x2ExEMA-z7JdEGvZHH-sYVYDMyfsiTKaj_50
// )
```

# Subscene-API-PHP
Unofficial API for Subscene subtitle service, written in PHP

## Required parameters:
`movie`

## Usage
Just include `subscene.php` to your project and use it:
```
<?php

require_once __DIR__.'/subscene.php';

$movies = SubScene::search('Fast Five');
// [
//     {
//         "title": "Fast Five (Fast & Furious 5: The Rio Heist) (2011)",
//         "url": "https:\/\/subscene.com\/subtitles\/fast-five-fast-and-furious-5-the-rio-heist"
//     },
//     {
//         "title": "How We Roll Fast Five Remix (2011)",
//         "url": "https:\/\/subscene.com\/subtitles\/how-we-roll-fast-five-remix"
//     }
// ]

$subtitles = SubScene::getSubtitles('https://subscene.com/subtitles/fast-five-fast-and-furious-5-the-rio-heist');
// [
//     {
//         "title": "Fast.Five.2011.EXTENDED.720p.BluRay.DTS.x264-DON",
//         "url": "https:\/\/subscene.com\/subtitles\/fast-five-fast-and-furious-5-the-rio-heist\/farsi_persian\/1108695"
//     },
//     {
//         "title": "Fast.Five.2011.BluRay.720p.DTS.x264-CHD",
//         "url": "https:\/\/subscene.com\/u\/797826"
//     }
// ]

$subtitle_download_url = SubScene::getDownloadUrl('https://subscene.com/subtitles/fast-five-fast-and-furious-5-the-rio-heist/farsi_persian/1108695');
// https://subscene.com/subtitles/farsi_persian-text/PIxzwhys19Sl8f872x-2ol4Zr96hb0lN4PuI4D-23aYGRdtKfXt5qXFtQYMcHR6oG1_JfEXwz3MgoFlax0afv4riF__a8h7kk6TfYTbVLndu7og4QnuYWIG97_RPJwcR0
```

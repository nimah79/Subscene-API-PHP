<?php

/*
 * Subscene REST API
 * By NimaH79
 * http://nimatv.ir
 */

header('Content-Type: application/json');
if(isset($_GET["movie"])) {
  $search = file_get_contents("https://subscene.com/subtitles/title?q=".urlencode($_GET["movie"])."&l=farsi_persian");
  if(preg_match('/<div class="title">.*?<a href="(.*?)">.*?<\/a>.*?<\/div>/s', $search, $title)) {
    $list = file_get_contents("https://subscene.com".$title[1]."/farsi_persian");
    if(preg_match('/<td class="a1">.*?<a href="(.*?)">.*?<span class="l r positive-icon">/s', $list, $sub)) {
      $page = file_get_contents("https://subscene.com".$sub[1]);
      if(preg_match('/<div class="download">.*?<a href="(.*?)" rel="nofollow" onclick="DownloadSubtitle\(this\)" id="downloadButton" class="button positive">.*?Download Farsi/s', $page, $download)) {
        preg_match('/<title>(.*?)<\/title>/', str_replace("Subscene - Subtitles for ", "", $list), $name);
        preg_match('/<img src="(.*?)"/', $list, $poster);
        exit(json_encode(["title" => htmlspecialchars_decode(str_replace("&#39;", "'", $name[1])), "poster" => $poster[1], "download" => "https://subscene.com".$download[1]]));
      }
      exit('{"error":"not found"}');
    }
    exit('{"error":"not found"}');
  }
  exit('{"error":"not found"}');
}
exit('{"error":"parameter movie is required"}');
?>

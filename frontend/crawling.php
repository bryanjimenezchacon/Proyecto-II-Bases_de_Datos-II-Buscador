<?php

$allUrls = array();
$crawlDoc = array();
$media = array("mp3", "mp4", "mov", "flv", "wmv", "avi", "png", "gif", "jpg", "bmp", "svg", "ico", "jpeg", "css", "css?ver", "js", "js?ver", ".", "min.css?ver", "php");
$media_imp = implode('|', $media);
$textTag = array("~(<tt>(.*?)</tt>)~","~(<i>(.*?)</i>)~","~(<b>(.*?)</b>)~","~(<big>(.*?)</big>)~","~(<small>(.*?)</small>)~","~(<em>(.*?)</em>)~","~(<strong>(.*?)</strong>)~","~(<dfn>(.*?)</dfn>)~","~(<samp>(.*?)</samp>)~","~(<kbd>(.*?)</kbd>)~","~(<var>(.*?)</var>)~","~(<cite>(.*?)</cite>)~","~(<abbr>(.*?)</abbr>)~","~(<acronym>(.*?)</acronym>)~","~(<sub>(.*?)</sub>)~","~(<sup>(.*?)</sup>)~","~(<span>(.*?)</span>)~","~(<bdo>(.*?)</bdo>)~","~(<div>(.*?)</div>)~","~(<a>(.*?)</a>)~","~(<p>(.*?)</p>)~","~(<h1>(.*?)</h1>)~","~(<h2>(.*?)</h2>)~","~(<h3>(.*?)</h3>)~","~(<h4>(.*?)</h4>)~","~(<h5>(.*?)</h5>)~","~(<h6>(.*?)</h6>)~","~(<pre>(.*?)</pre>)~","~(<q>(.*?)</q>)~","~(<ins>(.*?)</ins>)~","~(<del>(.*?)</del>)~","~(<dt>(.*?)</dt>)~","~(<dd>(.*?)</dd>)~","~(<li>(.*?)</li>)~","~(<label>(.*?)</label>)~","~(<legend>(.*?)</legend>)~","~(<button>(.*?)</button>)~","~(<caption>(.*?)</caption>)~","~(<td>(.*?)</td>)~","~(<th>(.*?)</th>)~","~(<title>(.*?)</title>)~");
$tagsDoc = array("~(<h1>(.*?)</h1>)~","~(<h2>(.*?)</h2>)~","~(<h3>(.*?)</h3>)~","~(<h4>(.*?)</h4>)~","~(<h5>(.*?)</h5>)~","~(<h6>(.*?)</h6>)~","~(<strong>(.*?)</strong>)~");

function crawlPage($url, $file, $depth=2){
    global $allUrls;
    global $media_imp;
    $crawledURLs = array();    
    $content = file_get_contents($url);
    
    fwrite($file, "/-/".$url."\n");
    fwrite($file, "/+/".get_content($content));
    fwrite($file, "/*/".get_tags($content));
    fwrite($file, "/·/".page_title($content));
     
    preg_match_all("/((?:http|https):\/\/(?:www\.)*(?:[a-zA-Z0-9_\-]{1,15}\.+[a-zA-Z0-9_]{1,}){1,}(?:[a-zA-Z0-9_\/\.\-\?\&\:\%\,\!\;]*))/", $content, $matches);
    $linksOnPage = $matches[0];
    
    foreach ($linksOnPage as $link) {
        if(!preg_match('/^.*\.('.$media_imp.')$/i', $link) && parse_url($link, PHP_URL_HOST) != "https://i.ytimg.com"){
            $contentLink = file_get_contents($link);
            fwrite($file, "/-/".$link."\n");
            fwrite($file, "/+/".get_content($contentLink));
            fwrite($file, "/*/".get_tags($contentLink));
            fwrite($file, "/·/".page_title($contentLink));
        }    
        if ($depth != 0){
            crawlPage($link, $file, $depth-1);
        }
    }
    return;
}


//Gets the title
function page_title($content) {
    //echo "title<br/>";
    $res = preg_match("/<title>(.*)<\/title>/siU", $content, $title_matches);
    if (!$res) 
        return "No title\n";
    // Clean up title: remove EOL's and excessive whitespace.
    $title = preg_replace('/\s+/', ' ', $title_matches[1]);
    $title = trim($title);
    $title = $title."\n";
    return $title;
}

function get_content($content){
    global $textTag;
    $allContent = "";
    foreach ($textTag as $delimiter) {
        preg_match_all($delimiter, $content, $matches);
        foreach ($matches[2] as $blah) {
            $blah = preg_replace('/ class=".*?"/', '', $blah);
            $blah = preg_replace('/ href=".*?"/', '', $blah);
            $blah = strip_tags($blah);
            $allContent = $allContent." ".$blah;   
        }
    }
    $allContent = $allContent."\n";
    return $allContent;
}

function get_tags($content){
    $tags = "";
    global $tagsDoc;
    foreach ($tagsDoc as $tag){
        preg_match_all($tag, $content, $matches);
        foreach ($matches[2] as $tag) {
            $tags = $tags." ";
        }
    }
    $tags = $tags."\n";
    return $tags;    
}

///// Run //////////
$input = isset($_POST['Urls'])?$_POST['Urls']:"";
$urls = explode("\n", str_replace("\r","",$input));
$inputLinks = array();
$error = 0;
foreach ($urls as $link) {
  if(preg_match("/((?:http|https):\/\/(?:www\.)*(?:[a-zA-Z0-9_\-]{1,15}\.+[a-zA-Z0-9_]{1,}){1,}(?:[a-zA-Z0-9_\/\.\-\?\&\:\%\,\!\;]*))/", $link)){
    array_push($inputLinks, $link);
  }
  $error = $error + 1;
}
if ( count($inputLinks) == 0){
  echo "<script>window.alert('There is no valid urls to crawl');</script>";
}
$crawlerfile = fopen("crawler", "w");
foreach($inputLinks as $links){
    crawlPage($links, $crawlerfile);
}
fclose($crawlerfile);


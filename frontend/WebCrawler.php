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

///// TEST ZONE //////////
$crawlerfile = fopen("crawler3", "w");
$websites = array("https://reddit.com/","http://www.extremetechcr.com","http://stackoverflow.com/questions/3149682/php-strings-remove-a-html-tag-with-a-specific-class-including-its-contents","http://www.youtube.com/", "http://es.wikipedia.org/wiki/Portada", "http://intel.com","http://www.tec.ac.cr","http://google.com", "http://www.upsocl.com/viajes/40-cosas-que-todo-el-mundo-debe-hacer-en-nueva-york/");
foreach($websites as $links){
    crawlPage($links, $crawlerfile);
}
fclose($crawlerfile);
//crawlPage("http://stackoverflow.com/questions/3149682/php-strings-remove-a-html-tag-with-a-specific-class-including-its-contents");
//crawlPage("http://www.youtube.com/");
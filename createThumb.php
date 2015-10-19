<?php
require_once "lib/Thumbnail.php";
require_once "lib/PreviewGenerationException.php";



$allowedRes = array(
    'scale' => array(
        "100x100",
        "10x100"
    ),
    'crop' => array(
        "100x100"
    )
);


$matches = array();

if (preg_match("#^/thumbnails/#", $_SERVER['REQUEST_URI'])) {
    preg_match("#^/thumbnails/(crop|scale)/([0-9]+x[0-9]+)/#u", $_SERVER['REQUEST_URI'], $matches);
    
    if (!in_array($matches[2], $allowedRes[$matches[1]])) {
        header("HTTP/1.0 404 Not Found");
        die("Incorrect resolution");
    }
    
    
    try {
        $thumb = new Thumbnail($_SERVER['REQUEST_URI']);
        
    }
    catch (PreviewGenerationException $e) {
        echo $e->errorMessage();
        header("HTTP/1.0 500 Internal Server Error");
    }
    
    $thumb->showThumbnail();
}

else{
    header("HTTP/1.0 404 Not Found");
    die("Incorrect url");
}

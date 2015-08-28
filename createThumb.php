<?php
require_once "lib/Thumbnail.php";

$allowedRes = array(
	'scale'=>array("100x100"),
	'crop' => array("100x100")
	);


	$matches = array();
    preg_match("#^/thumbnails/(crop|scale)/([0-9]+x[0-9]+)/#u", $_SERVER['REQUEST_URI'], $matches);

    if(!in_array($matches[2], $allowedRes[$matches[1]])){
    	header("HTTP/1.0 404 Not Found");
    }

$thumb = new Thumbnail($_SERVER['REQUEST_URI']);
if($thumb->createThumbnail()){
    $thumb->showThumbnail();
}
else{
    header("HTTP/1.1 500 Internal Error");
    die("File doesn't exist");
}
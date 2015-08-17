<?php
require_once "lib/Thumbnail.php";

$thumb = new Thumbnail($_SERVER['REQUEST_URI']);
if($thumb->createThumbnail()){
    $thumb->showThumbnail();
}
else{
    header("HTTP/1.1 500 Internal Error");
    die("File doesn't exist");
}
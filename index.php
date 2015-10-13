<?php
include "lib/Thumbnail.php";

$dir    = opendir("images");
$images = array();
while (false !== ($fname = readdir($dir))) {
    if ($fname !== "." && $fname !== "..") {
        
        
        $size = getimagesize("images/$fname");
        if ($size[2] >= 1 && $size[3] <= 3) {
            $images[] = $fname;
        }
    }
}


include "templates/main.html";
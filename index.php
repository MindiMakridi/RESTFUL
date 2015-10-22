<?php
include "lib/Thumbnail.php";
$allowedFormats = array(IMAGETYPE_GIF, IMAGETYPE_PNG, IMAGETYPE_JPEG);
$dir    = opendir("images");
$images = array();
while (false !== ($fname = readdir($dir))) {
    if ($fname !== "." && $fname !== "..") {
        
        
        if($size = getimagesize("images/$fname")){
          $format = $size[2];

          if (in_array($format, $allowedFormats)) {
              $images[] = $fname;
          }
      }
    }
}


include "templates/main.html";
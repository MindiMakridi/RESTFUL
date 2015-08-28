<?php
include "lib/Thumbnail.php";

$dir = opendir("images");

$test = getimagesize("images/327242.jpg");
var_dump($test);


include "templates/main.html";
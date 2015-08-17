<?php
if (isset($_FILES['picture'])) {
    
    
    $extension = getimagesize($_FILES['picture']['tmp_name']);
    switch ($extension[2]) {
        case 1:
            $extension = "gif";
            break;
        case 2:
            $extension = "jpg";
            break;
        case 3:
            $extension = "png";
            break;
        default:
            $extension = NULL;
    }
    if ($extension == NULL) {
        die("Не верный формат изображения");
    }
    
    $image = file_get_contents($_FILES['picture']['tmp_name']);
    $dir   = opendir("images");
    
    while (false != ($fname = readdir($dir))) {
        if (preg_match("/jpg|png|gif/", $fname)) {
            $tmpImage = file_get_contents("images/$fname");
            if ($image == $tmpImage) {
                die("Picture already exist");
                exit();
            }
        }
    }
    
    do {
        $random   = mt_rand(100000, 999999);
        $fileName = "$random." . $extension;
    } while (file_exists("images/$fileName"));
    
    
    
    
    
    if (move_uploaded_file($_FILES['picture']['tmp_name'], "images/$fileName")) {
        echo "Файл загружен";
    } else {
        echo "Произошла ошибка";
    }
}
include "templates/upload.html";
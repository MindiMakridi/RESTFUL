<?php
$message = "";
if (isset($_FILES['picture'])) {
    
    
    $extension = getimagesize($_FILES['picture']['tmp_name']);
    switch ($extension[2]) {
        case IMAGETYPE_GIF:
            $extension = "gif";
            break;
        case IMAGETYPE_JPEG:
            $extension = "jpg";
            break;
        case IMAGETYPE_PNG:
            $extension = "png";
            break;
        default:
            $extension = NULL;
    }
    if ($extension == NULL) {
        die("Не верный формат изображения");
    }
    
  
    $i = 0;
    do {
        $random   = mt_rand(100000, 999999);
        $fileName = "$random." . $extension;
        $i++;
    } while (file_exists("images/$fileName" && $i<=20));
    
    
    
    
    
    if (move_uploaded_file($_FILES['picture']['tmp_name'], "images/$fileName")) {
        $message = "Файл загружен";
    } else {
        $message = "Произошла ошибка";
    }
}
include "templates/upload.html";
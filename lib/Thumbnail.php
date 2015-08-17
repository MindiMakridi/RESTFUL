<?php

class Thumbnail{
    protected $fileName;
    protected $thumbPath;
    protected $url;
    protected $thumbWidth;
    protected $thumbHeight;
    protected $extension;
    protected $mode;


    public function __construct($url){
        $this->thumbPath = mb_substr($url, 1);
        $this->url = explode("/", mb_substr($url, 12));
        $this->fileName = $this->getFileName();
        $this->thumbWidth = $this->getThumbWidth();
        $this->thumbHeight = $this->getThumbHeight();
        $this->extension = $this->getExtension();
        $this->mode = $this->getMode();
    }

    public function getFileName(){
        $fileName = $this->url[2];
        return $fileName;
    } 

    public function getThumbWidth(){
        
        $resolution = explode('x', $this->url[1]);
        $width = $resolution[0];
        return $width;
    }

    public function getThumbHeight(){
        
        $resolution = explode('x', $this->url[1]);
        $height = $resolution[1];
        return $height;
    }

    public function getExtension(){
        $size = getimagesize("images/$this->fileName");
        switch($size[2]){
            case 1: return "gif";
                    break;
            case 2: return "jpeg";
                    break;
            case 3: return "png";
                    break;                
        }
    }

    public function getMode(){
        $mode = $this->url[0];
        return $mode;
    }



    public function createThumbnail(){
        if(!file_exists("images/$this->fileName")){
            return false;
        }
        
        $imgCreateFunc = "imagecreatefrom".$this->extension;
        $imgFunc = "image".$this->extension;
        $image = $imgCreateFunc("images/$this->fileName");
        imagealphablending($image, true);

        $width = imagesx($image);
        $height = imagesy($image);

        if($width<$this->thumbWidth && $height<$this->thumbHeight){
            $imgFunc($image, $this->thumbPath);
            return true; 
        }

        if($this->mode=="scale"){
            $x = 0;
            $y = 0;

                if($width>$height){
                $newWidth = $this->thumbWidth;
                $newHeight = floor($height*($this->thumbWidth/$width));
                }
                elseif($height>$width){
                $newHeight = $this->thumbHeight;
                $newWidth = floor($width*($this->thumbHeight/$height));
                }
                elseif ($height==$width) {
                    $newWidth = $this->$thumbWidth;
                    $newHeight = $this->thumbHeight;
                }
    }

        elseif ($this->mode=="crop") {
            $newWidth = $this->thumbWidth;
            $newHeight = $this->thumbHeight;
            if($width>$height){
                $tmpHeight = $this->thumbHeight;
                $tmpWidth = floor($width*($this->thumbHeight/$height));
                $x = floor($tmpWidth/2-$this->thumbWidth/2);
                $y = 0;
            }
            elseif ($width<$height) {
                $tmpWidth = $this->thumbWidth;
                $tmpHeight = floor($height*($this->thumbWidth/$width));
                $y = floor($tmpHeight/2-$this->thumbHeight/2);
                $x = 0;
            }
            elseif ($width==$height) {
                $tmpWidth = $newWidth;
                $tmpHeight = $newHeight;
                $x = 0;
                $y = 0;
            }
            $tmpImage = imagecreatetruecolor($tmpWidth, $tmpHeight);
            imagealphablending($tmpImage, false);
            imagesavealpha($tmpImage, true);
            imagecopyresampled($tmpImage, $image, 0, 0, 0, 0, $tmpWidth, $tmpHeight, $width, $height);
            $image = $tmpImage;
            $width = $this->thumbWidth;
            $height = $this->thumbHeight;


        }

        $tmpImage = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($tmpImage, false);
        imagesavealpha($tmpImage, true);
        imagecopyresampled($tmpImage, $image, 0, 0, $x, $y, $newWidth, $newHeight, $width, $height);
        $imgFunc($tmpImage, $this->thumbPath);
        return true;
        
    }

    public function showThumbnail(){
        if($this->extension == "jpeg"){
            $image = imagecreatefromjpeg($this->thumbPath);
            header('Content-Type: image/jpg');
            imagejpeg($image);
        }

        else{
        $imageCreateFunc="imagecreatefrom".$this->extension;
        $imageFunc="image".$this->extension;
        $image = $imageCreateFunc($this->thumbPath);
        header("Content-Type: image/$this->extension");
        $imageFunc($image);
        }
    }


    public static function link($fileName, $maxWidth, $maxHeight = NULL, $mode = "scale"){
        if(!preg_match("/scale|crop/", $mode)){
         throw new Exception("Incorrect mode");
         
        }
       
        if($maxHeight==NULL){
            $maxHeight = $maxWidth;
        }
        $link = "/thumbnails/$mode/{$maxWidth}x$maxHeight/$fileName";
        return $link;
    }





}
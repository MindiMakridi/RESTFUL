<?php

class Thumbnail{
    protected $fileName;
    protected $thumbPath;
    protected $thumbWidth;
    protected $thumbHeight;
    protected $extension;
    protected $mode;
    protected $mime;


    public function __construct($url){
        $url = parse_url($url);
        $url = $url['path'];
        $this->thumbPath = ltrim($url, "/");
        $match = array();
        preg_match("#^/thumbnails/(crop|scale)/([0-9]+)x([0-9]+)/(.+)$#u", $url, $match);
        $this->mode = $match[1];
        $this->thumbWidth = $match[2];
        $this->thumbHeight = $match[3];
        $this->fileName = $match[4];
        $this->extension = $this->getExtension();
        $this->mime = $this->getMime();
        
    }

 

   
    

   protected function getExtension(){
        $size = getimagesize("images/$this->fileName");
        if($size == false){
            throw new Exception("Файл не является изображением");
            
        }
        switch($size[2]){
            case IMAGETYPE_GIF: return "gif";
                    break;
            case IMAGETYPE_JPEG: return "jpeg";
                    break;
            case IMAGETYPE_PNG: return "png";
                    break;                
        }
    }

    protected function getMime(){
        $size = getimagesize("images/$this->fileName");
        if($size == false){
            throw new Exception("Файл не является изображением");
            
        }
        return $size['mime'];
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


        if($width>$height){
            $scale = $this->thumbWidth/$width;
            $cropHeight = $this->thumbHeight;
            $cropWidth = floor($width*($this->thumbHeight/$height));
        }
        else{
            $scale = $this->thumbHeight/$height;
            $cropWidth = $this->thumbWidth;
            $cropHeight = floor($height*($this->thumbWidth/$width));
        }


        if($this->mode=="scale"){
            if($scale>1){
                return false;
            }
            $x = 0;
            $y = 0;
            $newWidth = floor($width*$scale);
            $newHeight = floor($height*$scale);
    }

        elseif ($this->mode=="crop") {
            $newWidth = $this->thumbWidth;
            $newHeight = $this->thumbHeight;
            $y = floor($height/2-$this->thumbHeight/2);
            $x = floor($width/2-$this->thumbWidth/2);
            
            if ($width==$height) {
                $cropWidth = $newWidth;
                $cropHeight = $newHeight;
                
            }
           
            $width = $cropWidth;
            $height = $cropHeight;


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
            header("Content-Type: $this->mime");
            imagejpeg($image);
        }

        else{
        $imageCreateFunc="imagecreatefrom".$this->extension;
        $imageFunc="image".$this->extension;
        $image = $imageCreateFunc($this->thumbPath);
        header("Content-Type: $this->mime");
        $imageFunc($image);
        }
    }


    public static function link($fileName, $maxWidth, $maxHeight = NULL, $mode = "scale"){
        if(!preg_match("/^scale|crop$/u", $mode)){
         throw new Exception("Incorrect mode");
         
        }
       
        if($maxHeight==NULL){
            $maxHeight = $maxWidth;
        }
        $link = "/thumbnails/$mode/{$maxWidth}x$maxHeight/$fileName";
        return $link;
    }





}
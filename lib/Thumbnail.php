<?php

class Thumbnail
{
    const MODE_SCALE = "scale";
    const MODE_CROP = "crop";
    protected $fileName;
    protected $thumbPath;
    protected $srcImagePath;
    protected $thumbWidth;
    protected $thumbHeight;
    protected $extension;
    protected $mode;
    protected $mime;
    protected $imgCreateFunc;
    protected $imgFunc;
    
    
    public function __construct($url)
    {
        $url = parse_url($url, PHP_URL_PATH);
        
        $this->thumbPath = ltrim($url, "/");
        $match           = array();
        preg_match("#^/thumbnails/(crop|scale)/([0-9]+)x([0-9]+)/(.+)$#u", $url, $match);
        $this->mode         = $match[1];
        $this->thumbWidth   = $match[2];
        $this->thumbHeight  = $match[3];
        $this->fileName     = $match[4];
        $this->srcImagePath = "images/$this->fileName";
        $this->extension    = $this->getExtension();
        $this->mime         = $this->getMime();
        
    }
    
    
    
    
    
    
    protected function getExtension()
    {
        $size = getimagesize($this->srcImagePath);
        
        switch ($size[2]) {
            case IMAGETYPE_GIF:
                $this->imgCreateFunc = "imagecreatefromgif";
                $this->imgFunc       = "imagegif";
                return "gif";
                break;
            case IMAGETYPE_JPEG:
                $this->imgCreateFunc = "imagecreatefromjpeg";
                $this->imgFunc       = "imagejpeg";
                return "jpeg";
                break;
            case IMAGETYPE_PNG:
                $this->imgCreateFunc = "imagecreatefrompng";
                $this->imgFunc       = "imagepng";
                return "png";
                break;
            default:
                throw new PreviewGenerationException("Incorrect file extension");
                
        }
    }
    
    protected function getMime()
    {
        $size = getimagesize($this->srcImagePath);
        if ($size == false) {
            throw new Exception("Файл не является изображением");
            
        }
        return $size['mime'];
    }
    
    
    
    
    
    public function createThumbnail()
    {
        if (!file_exists($this->srcImagePath)) {
            return false;
        }
        
        
        
        $image = call_user_func($this->imgCreateFunc, $this->srcImagePath);
        imagealphablending($image, true);
        
        $width  = imagesx($image);
        $height = imagesy($image);
        
        if ($width < $this->thumbWidth && $height < $this->thumbHeight) {
            call_user_func($this->imgFunc, $image, $this->thumbPath);
            return true;
        }
        
        
        
        
        
        if ($this->mode == "scale") {
            $scaleX = $this->thumbWidth / $width;
            $scaleY = $this->thumbHeight / $height;
            $scale  = min($scaleX, $scaleY);
            if ($scale > 1) {
                $scale = 1;
            }
            
            $x         = 0;
            $y         = 0;
            $newWidth  = floor($width * $scale);
            $newHeight = floor($height * $scale);
        }
        
        elseif ($this->mode == "crop") {
            
            
            $newWidth  = $this->thumbWidth;
            $newHeight = $this->thumbHeight;
            
            
            $cropWidth  = $height * $this->thumbWidth / $this->thumbHeight;
            $cropHeight = $width * $this->thumbHeight / $this->thumbWidth;
            if ($cropWidth > $width) {
                $cropWidth = $width;
                $cropY     = ($height - $cropHeight) / 2;
                $cropX     = 0;
            } else {
                $cropHeight = $height;
                $cropX      = ($width - $cropWidth) / 2;
                $cropY      = 0;
            }
            
            $width  = $cropWidth;
            $height = $cropHeight;
            
            $x = $cropX;
            $y = $cropY;
            
            
        }
        
        $tmpImage = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($tmpImage, false);
        imagesavealpha($tmpImage, true);
        imagecopyresampled($tmpImage, $image, 0, 0, $x, $y, $newWidth, $newHeight, $width, $height);
        call_user_func($this->imgFunc, $tmpImage, $this->thumbPath);
        return true;
        
    }
    
    public function showThumbnail()
    {
        
        $this->createThumbnail();
        $image = call_user_func($this->imgCreateFunc, $this->thumbPath);
        
        header("Content-Type: $this->mime");
        call_user_func($this->imgFunc, $image);
        
        
    }
    
    
    public static function link($fileName, $maxWidth, $maxHeight = NULL, $mode = "scale")
    {
        if (!preg_match("/^scale|crop$/u", $mode)) {
            throw new Exception("Incorrect mode");
            
        }
        
        if ($maxHeight == NULL) {
            $maxHeight = $maxWidth;
        }
        $link = "/thumbnails/$mode/{$maxWidth}x$maxHeight/$fileName";
        return $link;
    }
    
    
    
    
    
}
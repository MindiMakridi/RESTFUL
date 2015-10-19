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
        $this->imageSize    = getimagesize($this->srcImagePath);
        if ($this->imageSize == false) {
            throw new Exception("Файл не является изображением");
            
        }
        $this->extension     = $this->getExtension();
        $this->mime          = $this->getMime();
        $this->imgFunc       = "image" . $this->extension;
        $this->imgCreateFunc = "imagecreatefrom" . $this->extension;
        
    }
    
    
    
    
    
    
    protected function getExtension()
    {
        $size = $this->imageSize;
        
        switch ($size[2]) {
            case IMAGETYPE_GIF:
                return "gif";
            
            case IMAGETYPE_JPEG:
                return "jpeg";
            
            case IMAGETYPE_PNG:
                return "png";
            
            default:
                throw new PreviewGenerationException("Incorrect file extension");
                
        }
    }
    
    protected function getMime()
    {
        $size = $this->imageSize;
        
        return $size['mime'];
    }
    
    
    
    
    
    public function createThumbnail()
    {
        if (!file_exists($this->srcImagePath)) {
            throw new PreviewGenerationException("File doesn't exist");
        }
        
        
        
        $image = call_user_func($this->imgCreateFunc, $this->srcImagePath);
        imagealphablending($image, true);
        
        $width  = imagesx($image);
        $height = imagesy($image);
        
        if ($width < $this->thumbWidth && $height < $this->thumbHeight) {
            call_user_func($this->imgFunc, $image, $this->thumbPath);
            return true;
        }
        
        
        
        
        
        if ($this->mode == self::MODE_SCALE) {
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
        
        elseif ($this->mode == self::MODE_CROP) {
            
            
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
        $image = file_get_contents($this->thumbPath);
        
        
        header("Content-Type: $this->mime");
        echo $image;
        
        
    }
    
    
    public static function link($fileName, $maxWidth, $maxHeight = NULL, $mode = self::MODE_SCALE)
    {
        if ($mode != self::MODE_SCALE && $mode != self::MODE_CROP) {
            throw new Exception("Incorrect mode");
            
        }
        
        if ($maxHeight == NULL) {
            $maxHeight = $maxWidth;
        }
        $link = "/thumbnails/$mode/{$maxWidth}x$maxHeight/$fileName";
        return $link;
    }
    
    
    
    
    
}
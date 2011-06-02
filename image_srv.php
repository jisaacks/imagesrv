<?php

class ImageSrv
{
    private $image;
    private $original_width;
    private $original_height;
    private $original_type;

    // default colors for background and padding
    private $r = 255;
    private $g = 255;
    private $b = 255;

    function load($image_file) 
    {
        $this->image = $this->createImageFromFile($image_file);
    }

    function setBackground($r, $g, $b)
    {
        $this->r = (int) $r;
        $this->g = (int) $g;
        $this->b = (int) $b;
    }

    function createImageFromFile($image_file)
    {
        $image_info = getimagesize($image_file);

        $this->original_width = $image_info[0];
        $this->original_height = $image_info[1];
        $this->original_type = $image_info[2];

        if( $this->original_type == IMAGETYPE_JPEG ) 
        {
            return imagecreatefromjpeg($image_file);
        } 
        else if( $this->original_type == IMAGETYPE_GIF ) 
        {
            return imagecreatefromgif($image_file);
        } 
        else if( $this->original_type == IMAGETYPE_PNG ) 
        {
            /*
             * create image and preserve transpancy
             * this keeps png files from having a black backgroung
             * when you don't resize them.
             * pngs converted to jpgs still have a black background
             * not sure how to make it a different color
             */
            $im = imagecreatefrompng($image_file);
            imagealphablending($im, false);
            imagesavealpha($im, true);

            return $im;
        }	
    }

    function setSize($width = NULL,$height = NULL) 
    {
        if($width == NULL && $height == NULL)
        {
            return;	
        }

        $ratio = $this->original_width / $this->original_height;
        $new_ratio = $width / $height;

        if($width == NULL)
        {
            $width = $height*$ratio;
            if($width > $this->original_width)
            {
                $width = $this->original_width;	
            }
        }
        else if($height == NULL)
        {
            $height = $width/$ratio;
            if($height > $this->original_height)
            {
                $height = $this->original_height;	
            }
        }


        // if width is greater than original width 
        // set to original width and pad the difference
        if($width < $this->original_width)
        {
            $w = $width;
        }
        else
        {
            $w = $this->original_width;
        }
        // if height is greater than original height 
        // set to original height and pad the difference
        if($height < $this->original_height)
        {
            $h = $height;
        }
        else
        {
            $h = $this->original_height;
        }
        // check if new deminsions are same aspect ratio
        // if not, fix aspect ratio and pad the difference
        if( $w / $h != $ratio )
        {
            if($width > $w && $height > $h)
            {
                //no scaling	
            }
            else if($width > $w)
            {
                $w = $h*$ratio;
            }
            else if($height > $h)
            {
                $h = $w/$ratio;
            }
            else if($ratio < $new_ratio)
            {
                $w = $h*$ratio;
            }
            else
            {
                $h = $w/$ratio;
            }
        }

        // resize image with correct aspect ratio and use padding to meet required size
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, $this->r, $this->g, $this->b, 127);
        imagefill($image, 0, 0, $transparent);
        imagecopyresampled($image, $this->image, ($width - $w) / 2 , ($height - $h) / 2, 0, 0, $w, $h, $this->original_width, $this->original_height);

        $this->image = $image;

        // reset original width and height to the new width and height
        $this->original_width = $width;
        $this->original_height = $height;
    }

    function output($type=NULL) 
    {
        if($type == NULL)
        {
            $type = $this->original_type;	
        }

        if( $type == IMAGETYPE_JPEG ) 
        {
            header('Content-Type: image/jpeg');
            imagejpeg($this->image);
        } 
        else if( $type == IMAGETYPE_GIF ) 
        {
            header('Content-Type: image/gif');
            imagegif($this->image);         
        } 
        else if( $type == IMAGETYPE_PNG ) 
        {
            header('Content-Type: image/png');
            imagepng($this->image);
        }   
    }
	
}

?>
<?php

class JWImaging
{
    private $img_input;
    private $img_output;
    private $img_src;
    private $quality = 80;
    private $x_input;
    private $y_input;
    private $x_output;
    private $y_output;

    public function set_img( $img )
    {
        $this->format = $ext;
        $this->img_input = ImageCreateFromJPEG($img);
        $this->img_src = $img;
        $this->x_input = imagesx($this->img_input);
        $this->y_input = imagesy($this->img_input);
    }

    public function set_size( $size = 120 )
    {
        $this->x_output = $size;
        $this->y_output = ($this->x_output / $this->x_input) * $this->y_input;
    }

    public function set_quality( $quality = 60 )
    {
        if(is_int($quality)) {
            $this->quality = $quality;
        }
    }

    public function save_img($path)
    {
        $this->img_output = ImageCreateTrueColor($this->x_output, $this->y_output);
        ImageCopyResampled($this->img_output, $this->img_input, 0, 0, 0, 0, $this->x_output, $this->y_output, $this->x_input, $this->y_input);

        imageJPEG($this->img_output, $path, $this->quality); 
    }

    public function clear_cache()
    {
        @ImageDestroy($this->img_input);
        @ImageDestroy($this->img_output);
    }
}

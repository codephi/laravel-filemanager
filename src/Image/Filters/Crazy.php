<?php

namespace Wiidoo\FileManager\Image\Filters;

use Intervention\Image\Filters\FilterInterface;

class Crazy implements FilterInterface
{

    public $contrast = 65;

    public $colorize;

    public function __construct($contrast = false)
    {
        if ($contrast) {
            $this->contrast = $contrast;
        }

        $this->colorize = [random_int(0, 100), random_int(0, 100), random_int(0, 100)];
    }


    public function applyFilter(\Intervention\Image\Image $image)
    {
        $image->contrast($this->contrast);;
        $image->colorize($this->colorize[0], $this->colorize[1], $this->colorize[2]);

        return $image;
    }
}
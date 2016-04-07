<?php

namespace Wiidoo\FileManager\Image\Filters;

use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\ImageManagerStatic as IImage;

class Fit implements FilterInterface
{

    public $width;

    public $height;

    public function __construct($width = null, $height = false)
    {
        $this->width = $width;
        $this->height = ($height !== false) ? $height : $width;
    }

    /**
     * Applies filter effects to given image
     *
     * @param  \Intervention\Image\Image $image
     * @return \Intervention\Image\Image
     */
    public function applyFilter(\Intervention\Image\Image $image)
    {
        $image->fit($this->width, $this->height, function ($constraint) {
            $constraint->upsize();
        });

        return $image;
    }
}
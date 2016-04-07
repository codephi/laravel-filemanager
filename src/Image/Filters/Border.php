<?php

namespace Wiidoo\FileManager\Image\Filters;

use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\ImageManagerStatic as IImage;

class Border implements FilterInterface
{

    public $width;

    public $height;

    public $color = null;

    public function __construct($size = null, $color = null)
    {
        if (is_array($size)) {
            $this->width = $size[0] * 4;
            $this->height = (isset($size[1]) ? $size[1] : $size[0]) * 4;
        } elseif (is_int($size)) {
            $size = $size * 10;
            $this->width = $size;
            $this->height = $size;
        }

        if ($color) {
            $this->color = $color;
        }
    }

    /**
     * Applies filter effects to given image
     *
     * @param  \Intervention\Image\Image $image
     * @return \Intervention\Image\Image
     */
    public function applyFilter(\Intervention\Image\Image $image)
    {
        $image->resizeCanvas($this->width, $this->height, 'center', true, $this->color);

        return $image;
    }
}
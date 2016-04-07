<?php

namespace Wiidoo\FileManager\Image\Filters;

use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\ImageManagerStatic as IImage;

class Canvas implements FilterInterface
{

    public $width;

    public $height;

    public $position = 'center';

    public $color = null;

    public function __construct($width = null, $height = false, $color = null, $position = false)
    {
        $this->width = $width;
        $this->height = ($height !== false) ? $height : $width;

        if ($position) {
            $this->position = $position;
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
        $image->resize($this->width, $this->height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $image->resizeCanvas($this->width, $this->height, $this->position, false, $this->color);

        return $image;
    }
}
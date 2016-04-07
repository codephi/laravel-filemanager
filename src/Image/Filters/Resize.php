<?php
/**
 * Created by PhpStorm.
 * User: philippe
 * Date: 31/03/16
 * Time: 16:06
 */

namespace Wiidoo\FileManager\Image\Filters;

use Intervention\Image\Filters\FilterInterface;

class Resize implements FilterInterface
{

    public $width;

    public $height;

    public function __construct($width = null, $height = false)
    {
        $this->width = $width;
        $this->height = ($height !== false) ? $height : $width;
    }

    public function applyFilter(\Intervention\Image\Image $image)
    {
        $image->resize($this->width, $this->height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        return $image;
    }

}
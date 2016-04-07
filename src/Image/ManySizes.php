<?php
/**
 * Created by PhpStorm.
 * User: philippe
 * Date: 01/04/16
 * Time: 14:43
 */

namespace Wiidoo\FileManager\Image;


use Wiidoo\FileManager\Image;

class ManySizes extends Image
{

    public function resize($sizes, $dir = 'resize')
    {
        return $this->manySizes($sizes, 'Resize', $dir);
    }

    public function fit($sizes, $dir = 'fit')
    {
        return $this->manySizes($sizes, 'Fit', $dir);
    }

    public function canvas($sizes, $dir = 'canvas')
    {
        return $this->manySizes($sizes, 'Canvas', $dir);
    }

}
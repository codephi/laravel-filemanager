<?php
/**
 * Created by PhpStorm.
 * User: philippe
 * Date: 24/03/16
 * Time: 09:47
 */

namespace Wiidoo\FileManager;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

use Intervention\Image\ImageManagerStatic as IImage;

class Image extends Upload
{

    private $local = false;

    private $original = null;

    public $mode = 0777;

    public $quality = 80;

    private $newImages = null;

    public $saveOriginal = false;

    public $sizes = [
        'favicon' => [16, 16],
        'icon' => [64, 64],
        'icon_h' => [null, 64],
        'icon_w' => [64, null],
        'thumb' => [256, 256],
        'thumb_h' => [null, 256],
        'thumb_w' => [256, null],
        'medium' => [800, 800],
        'medium_h' => [null, 800],
        'medium_w' => [800, null],
        'large' => [1200, 1200],
        'large_h' => [null, 1200],
        'large_w' => [1200, null],
        'xlarge' => [1980, 1980],
        'xlarge_h' => [null, 1980],
        'xlarge_w' => [1980, null]
    ];

    public $cache = [
        'filter' => [
            'filters' => [],
            'complement' => null
        ],
        'manySizes' => []
    ];

    private $active = [
        'filter' => false,
        'make' => false
    ];

    /**
     * Image constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);

        parent::mergeConfig('wiidoo.filemanager.image');
    }

    public function __call($name, $arguments)
    {
        parent::__call($name, $arguments);

        if (!parent::validatePropertyChange($name) and !parent::searchNegatedName($name)) {
            $this->filter([$name => $arguments]);
        }

        return $this;
    }

    public function __toString()
    {
        $images = $this->image();

        if (is_object($images)) {
            return isset($images->basename) ? $images->basename : ($images->getFilename() ?: '');
        }

        if (is_array($images)) {
            $filenames = [];

            do {
                $image = current($images);
                $filenames[] = $image->basename;
            } while (next($images));

            return implode(', ', $filenames);
        }

        return '';
    }

    /**
     * @return string
     */


    public function file($key = null, $default = null)
    {
        if ($this->request->file($key, $default)) {
            parent::file($key, $default);
        } elseif (File::exists($this->basePath . '/' . $key)) {
            $this->file = $this->basePath . '/' . $key;
            $this->local = true;
        } elseif (File::exists($key)) {
            $this->file = $key;
            $this->local = true;
        }

        return $this;
    }

    public function manySizes($sizes, $filter = 'Resize', $dir = '')
    {
        $this->cache['manySizes'][] =
            [
                'sizes' => $sizes,
                'filter' => $filter,
                'dir' => $dir
            ];

        return $this;
    }

    public function filter($filters, $complement = null)
    {
        $this->active['filter'] = true;

        if (is_string($filters)) {
            $filters = [$filters];
        }

        $this->cache['filter']['filters'] = array_merge($this->cache['filter']['filters'], $filters);
        $this->cache['filter']['complement'] = $complement;

        return $this;
    }

    public function make($callback = null)
    {
        $this->active['make'] = true;
        $this->cache['make'] = $callback;

        return $this;
    }

    /*********************************************************
     * Renderização
     * *******************************************************/

    protected function applyMake($callback = true)
    {
        $make = IImage::make(is_object($this->file) ? $this->file->getRealPath() : $this->file);

        if ($callback && isset($this->cache['make']) && is_callable($this->cache['make'])) {
            $make = $this->cache['make']($make);
        }

        return $make;
    }

    protected function applyFilterAllImages()
    {
        if (!$this->active['filter']) {
            return;
        }

        $i = 0;
        do {
            $this->newImages[$i] = $this->applyFilter($this->newImages[$i]);
            $i++;
        } while (isset($this->newImages[$i]));
    }

    protected function applyFilter($image)
    {
        if (isset($this->cache['filter']['filters'])) {
            foreach ($this->cache['filter']['filters'] as $key => $value) {

                if (is_string($key)) {
                    $arguments = $value;
                    $filter = $key;
                } else {
                    $filter = $value;
                }

                $this->execFilter($image, $filter, $arguments);
            }

            if (is_callable($this->cache['filter']['complement'])) {
                $image = $this->cache['filter']['complement']($image);
            }
        }

        return $image;
    }

    protected function execFilter($image, $filter, $arguments)
    {
        $filter = __NAMESPACE__ . '\Image\Filters\\' . $filter;

        if (class_exists($filter)) {
            $image->filter(isset($arguments) ? new $filter(...$arguments) : new $filter());
        }

        return $image;
    }

    protected function applyManySizes()
    {
        if (!isset($this->cache['manySizes'][0])) {
            return;
        }

        do {
            $manySizes = current($this->cache['manySizes']);

            $dir = $manySizes['dir'];
            $sizes = $manySizes['sizes'];
            $filter = $manySizes['filter'];

            if (is_array($sizes)) {
                if ((is_null($sizes[0]) or is_int($sizes[0])) and ($sizes[0] or $sizes[1])) {
                    $this->execManySizes($sizes, $dir . '/' . ($sizes[0] ?: $sizes[1]), $filter);
                } elseif (gettype($sizes[0]) == 'string') {
                    foreach ($sizes as $value) {
                        if (isset($this->sizes[$value])) {
                            $this->execManySizes($this->sizes[$value], $dir . '/' . $value, $filter);
                        }
                    }
                }
            } else {
                if ($sizes == 'all') {
                    foreach ($this->sizes as $key => $value) {
                        $this->execManySizes($value, $dir . '/' . $key, $filter);
                    }
                } elseif (isset($this->sizes[$sizes])) {
                    $this->execManySizes($this->sizes[$sizes], $dir . '/' . $sizes, $filter);
                }
            }
        } while (next($this->cache['manySizes']));
    }

    protected function execManySizes($size, $dir, $filter)
    {
        $newImage = $this->applyMake();
        $newImage = $this->applyFilter($newImage);
        if (is_string($filter)) {
            $newImage = $this->execFilter($newImage, $filter, $size);
        } elseif (is_callable($filter)) {
            $newImage = $filter($newImage, $size);
        }
        $newImage->dir = $dir;
        $this->newImages[] = $newImage;
    }

    protected function execData()
    {
        if ($this->original) {
            $image = $this->original;
            $data = [
                'name' => $this->name,
                'filename' => $image->filename,
                'mime' => $image->mime,
                'dirname' => $image->dirname,
                'basename' => $image->basename,
                'realPath' => $image->dirname . '/' . $this->name,
                'extension' => $image->extension,
                'dir' => $this->getOnlyDir($image),
                'variations' => []
            ];
        }

        if (isset($this->images[1])) {
            if (!isset($data['variations'])) {
                $data = ['variations' => []];
            }

            do {
                $image = current($this->images);

                $data['variations'][] = [
                    'name' => $this->name,
                    'filename' => $image->filename,
                    'mime' => $image->mime,
                    'dirname' => $image->dirname,
                    'basename' => $image->basename,
                    'realPath' => $image->dirname . '/' . $this->name,
                    'extension' => $image->extension,
                    'dir' => $this->getOnlyDir($image)
                ];

            } while (next($this->images));
        } elseif (isset($this->images[0])) {
            $image = $this->images[0];

            if (isset($image->filename)) {
                $item = [
                    'name' => $this->name,
                    'filename' => $image->filename,
                    'mime' => $image->mime,
                    'dirname' => $image->dirname,
                    'basename' => $image->basename,
                    'realPath' => $image->dirname . '/' . $this->name,
                    'extension' => $image->extension,
                    'dir' => $this->getOnlyDir($image),
                    'variations' => []
                ];
            } else {
                $item = [
                    'name' => $this->name,
                    'filename' => $image->getFileName(),
                    'mime' => mime_content_type($image->getPathName()),
                    'dirname' => str_replace($this->getFileName(), '', $image->getPathName()),
                    'basename' => $this->dir,
                    'realPath' => $image->getPathName(),
                    'extension' => pathinfo($image->getFileName())['extension'],
                    'dir' => $this->getOnlyDir()
                ];
            }

            if (isset($data)) {
                if (isset($item['variations'])) {
                    unset($item['variations']);
                }

                $data['variations'][] = $item;
            } else {
                $data = $item;
            }

        }

        return $data;
    }

    public function exec($return = true)
    {
        if ($this->exec) {
            if ($return) {
                return $this;
            } else {
                return;
            }
        }

        $this->exec = true;

        /*
         * Define File
         */
        if (!$this->local) {
            parent::execFile();
        } elseif (!$this->name) {
            $this->name = pathinfo($this->file)['basename'];
        } elseif ($this->cache['ext']) {
            if (!$this->local) {
                $this->name .= '.' . $this->file->getClientOriginalExtension();
            } else {
                $this->name .= '.' . pathinfo($this->file)['extension'];
            }
        }

        /*
         * Define Dir
         */
        $this->reguleDir();

        /*
         * Salva original
         * */
        if ($this->saveOriginal) {
            $this->original = $this->applyMake(false)->save($this->pathOfTheEditedImage(), $this->quality);
        }

        $this->applyManySizes();

        if (!isset($this->cache['manySizes'][0])) {
            if ($this->local or $this->active['filter']) {
                if (!isset($this->newImages[0])) {
                    $this->newImages[] = $this->applyMake();
                }
            }

            if (!$this->active['filter'] and $this->active['make']) {

                $this->newImages[] = $this->applyMake();
            }

            $this->applyFilterAllImages();
        }


        if ($return) {
            return $this;
        }
    }

    public function save()
    {
        $this->exec(false);

        if (isset($this->newImages[0])) {
            do {
                $image = current($this->newImages);

                if (isset($image->dir)) {
                    $this->images[] = $image->save($this->pathOfTheEditedImage($image->dir), $this->quality);
                } else {
                    if ($this->local and $this->overwrite) {
                        $this->images[] = $image->save($this->file, $this->quality);
                    } else {
                        $this->images[] = $image->save($this->pathOfTheEditedImage(), $this->quality);
                    }
                }

            } while (next($this->newImages));

        } else {
            $this->images[] = $this->file->move($this->dir, $this->name);
        }

        return $this;
    }

    public function image($key = false)
    {
        if (isset($this->images[1])) {
            return ($key !== false) ? $this->images : $this->image[$key];
        } elseif (isset($this->images[0])) {
            return $this->images[0];
        } else {
            return null;
        }
    }

    public function contentType($type = null)
    {
        if (!$type) {
            $type = 'image/' . pathinfo($this->name)['extension'] ?: 'png';
        }

        header('Content-Type: ' . $type);
        return $this;
    }

    public function encode()
    {
        $image = $this->image(0);

        if (!$image) {
            return '';
        }

        return $image->encode(pathinfo($this->name)['extension'] ?: 'png', 100);
    }

    private function getOnlyDir($image = false)
    {
        $dir = str_replace($this->basePath . '/', '', $this->dir);
        return str_replace($this->basePath, '', $dir) . (($image and isset($image->dir)) ? '/' . $image->dir : '');
    }


    public function data($type = 'all')
    {
        $data = $this->execData();

        switch ($type) {
            case 'all':
                return $data;
                break;
            case 'simple':
                return [
                    'name' => $data['name'],
                    'dir' => $data['dir'],
                    'realPath' => $data['realPath']
                ];
                break;
        }
    }


    public function success()
    {
        return [
            'success' => true,
            'data' => $this->data('simple')
        ];
    }


    /*********************************************************
     * Feramentas e rotinas
     * *******************************************************/

    /**
     * @param null $dir
     * @return bool
     */
    protected function pathOfTheEditedImage($dir = '')
    {
        $dir = $this->dir . '/' . $dir;

        if (!File::exists($dir)) {
            File::makeDirectory($dir, $this->mode, true);
        }

        if (!$this->overwrite) {

            $file = pathinfo($this->name);
            $name = $file['filename'];
            $ext = isset($file['extension']) ? '.' . $file['extension'] : '';
            $suffix = '';

            $i = 0;
            while (File::exists($dir . '/' . $name . $suffix . $ext)) {
                $suffix = str_replace('%number%', ++$i, $this->suffix);
            }

            return $dir . '/' . $name . $suffix . $ext;
        }

        return $dir . '/' . $this->name;

    }


}
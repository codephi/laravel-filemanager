<?php
/**
 * Created by PhpStorm.
 * User: philippe
 * Date: 24/03/16
 * Time: 09:47
 */

namespace Wiidoo\FileManager;

use Wiidoo\Support\FluentInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Log;

class Upload extends FluentInterface
{
    public $request;

    public $file;

    public $name = null;

    public $dir = null;

    /*
     * Implement the suffix using% number%, example, to "filename-2.jpg" use '-% number%'
     */
    public $suffix = '(%number%)';

    public $overwrite = false;

    public $basePath = null;

    protected $useRelativePath = true;

    public $forceCreateDir = true;

    public $cache = [
        'prepare' => null
    ];

    public $exec = false;

    /**
     * Upload constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        parent::mergeConfig('wiidoo.filemanager.upload');

        $this->request = $request;
    }

    /**
     * @param $use
     * @return $this
     */
    public function relativePath($use = null)
    {
        $this->useRelativePath = $use;

        return $this;
    }

    /**
     * @param null $key
     * @param null $default
     * @return $this
     */
    public function file($key = null, $default = null)
    {
        $this->cache['file'] = compact('key', 'default');

        return $this;
    }

    /**
     * @param $directory
     * @param bool $forceCreate
     * @return $this
     */
    public function dir($directory, $forceCreate = true)
    {
        $this->dir = $directory;

        $this->forceCreateDir = $forceCreate;

        return $this;
    }

    public function path($directory, $forceCreate = true)
    {
        $this->dir($directory, $forceCreate);

        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function name($name, $ext = true)
    {
        $this->name = $name;
        $this->cache['ext'] = $ext;

        return $this;
    }

    /**
     * @return $this
     */
    public function unique()
    {
        $this->overwrite = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function overwrite()
    {
        $this->overwrite = true;

        return $this;
    }

    /**
     * Renderização
     * */

    protected function execFile()
    {
        $file = $this->cache['file'];

        $this->file = $this->request->file($file['key'], $file['default']);

        if (!$this->name) {
            $this->name = $this->file->getClientOriginalName();
        } elseif ($this->cache['ext']) {
            $this->name .= '.' . $this->file->getClientOriginalExtension();
        }
    }

    /**
     * @param $callback
     * @return $this
     */
    protected function prepare($callback)
    {
        $this->cache['prepare'] = $callback;

        return $this;
    }

    /**
     *
     */
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

        $this->execFile();
        $this->reguleDir();

        if(is_callable($this->cache['prepare'])){
           $this->file = $this->cache['prepare']($this->file);
        }

        if ($return) {
            return $this;
        }
    }

    /**
     * @return null|$this
     */
    public function save()
    {
        $this->exec();

        return $this->file->move($this->dir, $this->name);
    }


    /**
     * Rotinas e ferramentas
     * */

    /**
     * @return bool
     */
    protected function reguleDir()
    {
        if ($this->dir) {
            $this->dir = $this->getBaseDir('/') . $this->dir;
        } elseif ($this->getBaseDir()) {
            $this->dir = $this->getBaseDir();
        } else {
            Log::error("FileManager | Directory not specified, can not move the file");
            return false;
        }

        if (!$this->overwrite) {
            $this->addSuffixNumberIfNameExists();
        }

        if ($this->forceCreateDir !== false) {
            $this->forceCreateFolder();
        }

        return true;
    }

    /**
     * The name says it all
     */
    protected function addSuffixNumberIfNameExists()
    {
        $i = 0;
        $file = pathinfo($this->name);
        $name = $file['filename'];
        $ext = isset($file['extension']) ? '.' . $file['extension'] : '';
        $suffix = '';

        while (File::exists($this->dir . '/' . $name . $suffix . $ext)) {
            $suffix = str_replace('%number%', ++$i, $this->suffix);
        }

        $this->name = $name . $suffix . $ext;
    }

    /**
     * @param int $mode
     * @param bool $recursive
     * @return $this
     */
    public function forceCreateFolder($mode = 0777, $recursive = true)
    {
        if (!File::exists($this->dir)) {
            File::makeDirectory($this->dir, $mode, $recursive);
        }

        return $this;
    }

    /**
     * @param string $suffix
     * @return string
     */
    public function getBaseDir($suffix = '')
    {
        if ($this->useRelativePath)
            return $this->basePath . $suffix;
        else
            return '';
    }

}
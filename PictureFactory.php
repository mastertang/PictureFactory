<?php

namespace PictureFactory;

use PictureFactory\driver\Gd;
use PictureFactory\driver\Gif;

/**
 * Class PictureFactory
 * @package PictureFactory
 */
class PictureFactory
{
    /**
     * @var Gif null gif客户端
     */
    public $gifIntance = null;

    /**
     * @var Gd null gd客户端
     */
    public $gdInstance = null;

    /**
     * 创建gif客户端
     *
     * @return Gif
     */
    public function gif()
    {
        if ($this->gifIntance instanceof Gif) {
            return $this->gifIntance;
        }
        $this->gifIntance = new Gif();
        return $this->gifIntance;
    }

    /**
     * 创建gd客户端
     *
     * @return Gd
     */
    public function gd()
    {
        if ($this->gdInstance instanceof Gd) {
            return $this->gdInstance;
        }
        $this->gdInstance = new Gd();
        return $this->gdInstance;
    }
}
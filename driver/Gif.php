<?php

namespace PictureFactory\driver;

use PictureFactory\driver\gifLib\GifEncoder;

/**
 * Class Gif
 * @package PictureFactory\driver
 */
class Gif
{
    /**
     * @var int 延时
     */
    protected $delayTime = 100;

    /**
     * @var int 处理方法
     */
    protected $disposalMethod = 0;

    /**
     * @var int 图片偏移
     */
    protected $offset = 0;

    /**
     * @var array 透明颜色
     */
    protected $transparentColor = [254, 254, 254];

    /**
     * @var string 临时图片路径
     */
    protected $tempDir = "";

    /**
     * @var int 循环次数
     */
    protected $loop = 1;

    /**
     * @var string 保存路径
     */
    protected $savePath = "";

    /**
     * @var array 图片帧路径集
     */
    protected $pictureSet = [];

    /**
     * @var array gif尺寸
     */
    protected $gifSize = [];

    /**
     * @var string 错误信息
     */
    public $errorMessage = "";

    /**
     * 设置处理方法
     *
     * @param $method
     * @return $this
     */
    public function disposalMethod($method)
    {
        $this->disposalMethod = $method;
        return $this;
    }

    /**
     * 设置gif尺寸
     *
     * @param $width
     * @param $height
     * @return $this
     */
    public function gifSize($width, $height)
    {
        if ($width > 0 && $height > 0) {
            $this->gifSize = [$width, $height];
        }
        return $this;
    }

    /**
     * 设置gif保存路径
     *
     * @param $savePath
     * @return $this
     */
    public function savePath($savePath)
    {
        $this->savePath = $savePath;
        return $this;
    }

    /**
     * 设置图片帧路径集
     *
     * @param $pictureSet
     * @return $this
     */
    public function pictureSet($pictureSet)
    {
        foreach ($pictureSet as $key => $path) {
            if (!is_file($path)) {
                unset($pictureSet[$key]);
            }
        }
        $this->pictureSet = $pictureSet;
        return $this;
    }

    /**
     * 设置延时
     *
     * @param $time
     * @return $this
     */
    public function delayTime($time)
    {
        $this->delayTime = $time;
        return $this;
    }

    /**
     * 设置偏移
     *
     * @param $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * 设置透明颜色
     *
     * @param $r
     * @param $g
     * @param $b
     * @return $this
     */
    public function transparentColor($r, $g, $b)
    {
        if ($r <= 255 && $r >= 0 &&
            $g <= 255 && $g >= 0 &&
            $b <= 255 && $b >= 0) {
            $this->transparentColor = [$r, $g, $b];
        }
        return $this;
    }

    /**
     * 设置临时文件夹
     *
     * @param $dir
     * @return $this
     */
    public function tempDir($dir)
    {
        if (is_dir($dir)) {
            $this->tempDir = $dir;
        }
        return $this;
    }

    /**
     * 设置循环次数
     *
     * @param $loop
     * @return $this
     */
    public function loop($loop)
    {
        $this->loop = $loop;
        return $this;
    }

    /**
     * 开始生成
     *
     * @return bool
     */
    public function gifStart()
    {
        $tempPicPath = [];
        $index       = 0;
        $firstWidth  = $this->gifSize[0];
        $firstHeight = $this->gifSize[1];
        $scaleWidth  = 0;
        $scaleHeight = 0;
        foreach ($this->pictureSet as $picture) {
            $pictureInfo = getimagesize($picture);//获取图片信息
            if (empty($pictureInfo)) {
                continue;
            }
            $pictureSource = $this->createImage($picture, $pictureInfo['mime']);
            if (!is_resource($pictureSource)) continue;

            $this->gifFit($pictureSource, $index, $firstWidth, $firstHeight, $scaleWidth, $scaleHeight);

            $newCanvasSource = NULL;
            $this->createCanvas($newCanvasSource, $firstWidth, $firstHeight);
            $result = imagecopyresampled(
                $newCanvasSource,
                $pictureSource,
                0, 0, 0, 0,
                $scaleWidth,
                $scaleHeight,
                $pictureInfo[0],
                $pictureInfo[1]);
            if (empty($result)) {
                $index++;
                continue;
            }
            $pictureTempPath = $this->tempDir . DIRECTORY_SEPARATOR . uniqid() . '.gif';
            $tempPicPath[]   = $pictureTempPath;
            imagegif($newCanvasSource, $pictureTempPath);
            imagedestroy($newCanvasSource);
            imagedestroy($pictureSource);
            $pictureInfo     = NULL;
            $pictureSource   = NULL;
            $newCanvasSource = NULL;
            $pictureTempPath = NULL;
            $index++;
        }
        if (empty($tempPicPath)) {
            $this->errorMessage = "所有素材文件都错误";
            return false;
        }
        return $this->start($tempPicPath, $savePath);
    }

    /**
     * 处理图片尺寸
     *
     * @param $image
     * @param $index
     * @param $firstWidth
     * @param $firstHeight
     * @param $scaleWidth
     * @param $scaleHeight
     */
    private function gifFit(
        &$image,
        $index,
        &$firstWidth,
        &$firstHeight,
        &$scaleWidth,
        &$scaleHeight
    )
    {
        $width  = imagesx($image);
        $height = imagesy($image);
        if ($index == 0 && $firstWidth == 0 && $firstWidth == 0) {
            $firstWidth  = $width;
            $firstHeight = $height;
        }
        $scaleWidth  = 0;
        $scaleHeight = 0;
        if ($width > $firstWidth) {
            $num       = $firstWidth / $width;
            $numHeight = (int)($height * $num);
            if ($numHeight <= $firstHeight) {
                $scaleWidth  = $firstWidth;
                $scaleHeight = $numHeight;
            }
        }
        if ($height > $firstHeight) {
            $num      = $firstHeight / $height;
            $numWidth = (int)($width * $num);
            if ($numWidth <= $firstWidth) {
                $scaleWidth  = $numWidth;
                $scaleHeight = $firstHeight;
            }
        }
        if ($scaleWidth == 0 && $scaleHeight == 0) {
            $scaleWidth  = $width;
            $scaleHeight = $height;
        }
    }

    /**
     * 开始处理
     *
     * @param $tempPicPath
     * @param $savePath
     * @return bool
     */
    private function start(&$tempPicPath, &$savePath)
    {
        try {
            $gif = new GifEncoder(
                $tempPicPath,
                $this->delayTime,
                $this->loop,
                $this->disposalMethod, //构造函数传入参数初始化
                $this->offset,
                $this->transparentColor);
            $gif->encodeStart();          //开始进行合成
            $file = fopen($savePath, 'w');//把二进制数据写入文件
            fwrite($file, $gif->getAnimation());
            fclose($file);
        } catch (\Exception $e) {
            return false;
        }
        foreach ($tempPicPath as $picture) {
            unlink($picture);
        }
        return true;
    }

    /**
     * 创建canvas
     *
     * @param $canvas
     * @param $width
     * @param $height
     */
    private function createCanvas(&$canvas, $width, $height)
    {
        $canvas = imagecreatetruecolor($width, $height);   //创建真彩色画布
        $color  = imagecolorallocate(
            $canvas,
            $this->transparentColor[0], //设置透明颜色值
            $this->transparentColor[1],
            $this->transparentColor[2]);
        imagecolortransparent($canvas, $color);
        imagefill($canvas, 0, 0, $color);
    }

    /**
     * 创建图片资源
     *
     * @param $imagePath
     * @param $fileMime
     * @return null|resource
     */
    private function createImage($imagePath, $fileMime)
    {
        $image = NULL;
        switch (strtolower($fileMime)) {
            case 'image/jpg':
            case 'image/jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($imagePath);
                break;
            case 'image/bmp':
                $image = imagecreatefromwbmp($imagePath);
                break;
            default:
                break;
        }
        return $image;
    }
}
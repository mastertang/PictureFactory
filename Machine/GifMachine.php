<?php
namespace PictureFactory\Machine;

use PictureFactory\Exception\PictureException;
use PictureFactory\Machine\GifMachineLib\GifEncoder;

class GifMachine
{
    const mDelayTimeDefault = 100;//默认延时
    const mOffest = 0;//默认图片偏移量
    private $gifSavePath = '';//gif保存路径
    private $pictureTempPath = '';//临时文件的保存路径
    private $delayTime = [];//每张图片的延时时间
    private $transparentColor = [];//透明颜色rgb
    private $loopFlag = 1;//循环次数，默认1
    private $disposalMethod = 0;//处置方法，默认0
    private $offest = [];//每张图片的偏移量
    private $picturePath = [];//每张图片的文件路径

    private $config = [];
    private $defaultConfig = [
        'transparent_color' => [254, 254, 254],
        'disposal_method' => 0,
        'delay' => 1,
        'loop' => 1,
        'offset' => 0
    ];

    public function __construct()
    {
        $configPath = '../Config/GifConfig.php';
        if (is_file($configPath))
            $this->config = include $configPath;
        $this->config = array_merge($this->defaultConfig, $this->config);
        ParamsHandler::handleStart([
            'delay' => ['set|int|min:0', $this->config['delay']],
            'transparent_color' => ['set|arr|color', $this->config['transparent_color']],
            'loop' => ['set|int|min:1', $this->config['loop']],
            'disposal_method' => ['set|int|min:0', $this->config['disposal_method']],
            'offset' => ['set', $this->config['offset']]
        ]);
    }

    public function gifStart($picturePath, $tempPath, $savePath)
    {
        if (!empty($tempPath) && !is_dir($tempPath)) {
            if (!mkdir($tempPath, 0775))
                throw new PictureException('临时文件保存路径创建失败');
        } else
            $tempPath = './';
        $tempPicPath = [];
        foreach ($picturePath as $picture) {
            $pictureInfo = getimagesize($picture);//获取图片信息
            $width = $pictureInfo[0];
            $height = $pictureInfo[1];
            $pictureSource = $this->createImage($picturePath, $pictureInfo['mime']);  //获取图片资源
            $newCanvasSource = NULL;
            $this->createCanvas($newCanvasSource, $width, $height);
            $result = imagecopyresampled(
                $newCanvasSource,
                $pictureSource,
                0, 0, 0, 0,
                $width,
                $height,
                $width,
                $height);
            if (empty($result)) continue;
            $pictureTempPath = $tempPath . uniqid() . '.gif';               //设置保存图片的路径和名字
            $tempPicPath[] = $pictureTempPath;

            imagegif($newCanvasSource, $pictureTempPath);                   //保存图片
            imagedestroy($newCanvasSource);
            imagedestroy($pictureSource);
            $pictureInfo = NULL;
            $pictureSource = NULL;
            $newCanvasSource = NULL;
            $pictureTempPath = NULL;
            $width = 0;
            $height = 0;
        }

        try {
            $gif = new GifEncoder(
                $tempPicPath,
                $this->mDelayTime,
                $this->mloopFlag,
                $this->mDisposalMethod, //构造函数传入参数初始化
                $this->mTransparentColor[0],
                $this->mTransparentColor[1],
                $this->mTransparentColor[2],
                $this->mOffest);
            $result = $gif->encodeStart();          //开始进行合成
            if (!$result) throw new PictureException('合成错误');
            $file = fopen($savePath, 'w');//把二进制数据写入文件
            fwrite($file, $gif->GetAnimation());
            fclose($file);
        } catch (\Exception $e) {
            return false;
        }

        foreach ($tempPicPath as $picture) {
            unlink($picture);
        }
        return true;
    }

    private function createCanvas(&$canvas, $width, $height)
    {
        $canvas = imagecreatetruecolor($width, $height);   //创建真彩色画布
        $color = imagecolorallocate(
            $canvas,
            $this->config['transparent_color'][0], //设置透明颜色值
            $this->config['transparent_color'][0],
            $this->config['transparent_color'][0]);
        imagecolortransparent($canvas, $color);
        imagefill($canvas, 0, 0, $color);
    }

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
<?php
namespace PictureFactory\Machine;

use PictureFactory\Exception\PictureException;
use PictureFactory\Machine\GifMachineLib\GifEncoder;

class GifMachine
{
    const mDelayTimeDefault = 100;  //默认延时
    const mOffest = 0;              //默认图片偏移量
    private $config = [];           //配置
    private $defaultConfig = [
        'transparent_color' => [254, 254, 254], //透明颜色
        'disposal_method' => 0,                 //处理方法
        'temp_path' => './',                    //临时文件路径
        'delay' => 1,                           //gif延时间隔
        'loop' => 1,                            //循环
        'offset' => 0                           //偏移量
    ];

    public function __construct()
    {
        $configPath = __DIR__ . '/../Config/GifConfig.php';
        if (is_file($configPath))
            $this->config = include $configPath;
        $this->config = array_merge($this->defaultConfig, $this->config);
        ParamsHandler::handleStart([
            'transparent_color' => ['set|arr|color', $this->config['transparent_color']],
            'disposal_method' => ['set|int|min:0', $this->config['disposal_method']],
            'delay' => ['set|min:0', $this->config['delay']],
            'loop' => ['set|int|min:0', $this->config['loop']],
            'offset' => ['set|min:0', $this->config['offset']]
        ]);
        if (empty($this->config['temp_path'])) $this->config['temp_path'] = './';
        if (!is_dir($this->config['temp_path'])) {
            $mkresult = mkdir($this->config['temp_path'], 0775);
            if (!$mkresult) throw new PictureException('临时文件夹创建失败');
        }
    }

    private function checkStartParams(&$picturePath, &$savePath, &$tempPath, &$saveName)
    {
        if (!is_array($picturePath))
            $picturePath = [$picturePath];
        if (empty($savePath)) {
            throw new PictureException('保存路径不能为空');
        } else {
            $info = explode('.', basename($savePath));
            if (empty($info[0]) ||
                empty($info[1]) ||
                strtolower($info[1]) != 'gif'
            ) {
                throw new PictureException('保存路径格式错误');
            }
            $saveName = $info[0];
        }
        if (!empty($tempPath) && !is_dir($tempPath)) {
            if (!mkdir($tempPath, 0775))
                throw new PictureException('临时文件保存路径创建失败');
        } else {
            $tempPath = $this->config['temp_path'];
        }
    }

    public function gifStart($picturePath, $savePath, $tempPath = '')
    {
        $saveName = NULL;
        $this->checkStartParams($picturePath, $savePath, $tempPath, $saveName);
        $tempPicPath = [];
        foreach ($picturePath as $picture) {
            $pictureInfo = getimagesize($picture);//获取图片信息
            $pictureSource = $this->createImage($picture, $pictureInfo['mime']);
            if (!is_resource($pictureSource)) continue;
            $newCanvasSource = NULL;
            $this->createCanvas($newCanvasSource, $pictureInfo[0], $pictureInfo[1]);
            $result = imagecopyresampled(
                $newCanvasSource,
                $pictureSource,
                0, 0, 0, 0,
                $pictureInfo[0],
                $pictureInfo[1],
                $pictureInfo[0],
                $pictureInfo[1]);
            if (empty($result)) continue;
            $pictureTempPath = $tempPath . DIRECTORY_SEPARATOR . $saveName . uniqid() . '.gif';
            $tempPicPath[] = $pictureTempPath;
            imagegif($newCanvasSource, $pictureTempPath);
            imagedestroy($newCanvasSource);
            imagedestroy($pictureSource);
            $pictureInfo = NULL;
            $pictureSource = NULL;
            $newCanvasSource = NULL;
            $pictureTempPath = NULL;
        }
        if (empty($tempPicPath)) throw new PictureException('所有素材文件都错误');
        return $this->start($tempPicPath, $savePath);
    }

    private function start(&$tempPicPath, &$savePath)
    {
        try {
            $gif = new GifEncoder(
                $tempPicPath,
                $this->config['delay'],
                $this->config['loop'],
                $this->config['disposal_method'], //构造函数传入参数初始化
                $this->config['offset'],
                $this->config['transparent_color']);
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
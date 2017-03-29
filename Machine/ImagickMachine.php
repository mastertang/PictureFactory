<?php
namespace PictureFactory\Machine;

use PictureFactory\Exception\PictureException;
use PictureFactory\PictureInterface\PictureInterface;

class ImagickMachine implements PictureInterface
{
    const RETURN_PATH = 1;
    const RETURN_STRING = 2;
    private $nowConfig = [];
    private $defaultConfig = [
        'transparent_color' => [254, 254, 254],
        'font_path' => './',
        'font_color' => [255, 255, 255],
        'font_size' => 15,
    ];

    public function __construct($config = NULL)
    {
        if (!extension_loaded('imagemagick'))
            throw new PictureException('未安装ImageMagick扩展');
        $configArray = [];
        if (is_string($config) && is_file($config)) {
            $configArray = include $config;
            $this->nowConfig = array($this->defaultConfig, $configArray);
        } elseif (is_array($configArray))
            $configArray = $config;
        $configArray = array($this->defaultConfig, $configArray);
        $checkResult = ParamsHandler::handleStart(
            [
                'transparent_color' => 'color',
                'font_path' => 'string|path',
                'font_color' => 'color',
                'font_size' => 'int|min:1'
            ], $configArray);
        $lastArray = [];
        !$checkResult['transparent_color'] or $lastArray['transparent_color'] = [254, 254, 254];
        !$checkResult['font_path'] or $lastArray['font_path'] = './';
        !$checkResult['font_color'] or $lastArray['font_color'] = [255, 255, 255];
        !$checkResult['font_size'] or $lastArray['font_size'] = 15;
        $this->nowConfig = array_merge($configArray, $lastArray);
    }

    private function returnHandler(\Imagick &$imageick, $savePath, $type)
    {
        if ($type == self::RETURN_PATH) {
            $name = '';
            $suffix = '';
            $saveInfo = MachineHandler::getPictureNameAndsuffix($savePath, $name, $suffix);
            if (!$this->supportPicture($suffix)) throw new PictureException('不支持此保存文件的类型');
            if (empty($name)) throw new PictureException('文件保存名字不能为空');
            $imageick->writeImage($savePath);
            return $savePath;
        } elseif ($type == self::RETURN_STRING) {
            return $imageick->__toString();
        }
    }

    private function originPictureHander(\Imagick &$imageick, $originPicture)
    {
        $name = '';
        $suffix = '';
        $info = [];
        if (is_file($originPicture)) {
            MachineHandler::getPictureNameAndsuffix($originPicture, $name, $suffix);
            if (!$this->supportPicture($suffix))
                throw new PictureException('当前不支持此文件类型');
            $imageick->readImage($originPicture);
            $info = $imageick->getImagePage();
        } elseif (is_string($originPicture)) {
            $imageick->readImageBlob($originPicture);
            $info = $imageick->getImagePage();
            $suffix = $imageick->getFormat();
            if (!$this->supportPicture($suffix))
                throw new PictureException('当前不支持此文件类型');
        } else
            throw new PictureException('当前不支持此类型文件');
        $info['suffix'] = $suffix;
        $info['name'] = $name;
        return $info;
    }

    public function changeConfig($params = NULL)
    {
        // TODO: Implement changeConfig() method.
    }

    public function composition(
        $backPicture,
        $frontPicture,
        $position,
        $savePath = NULL,
        $returnType = self::RETURN_PATH
    )
    {
        // TODO: Implement composition() method.
        $backImageick = new \Imagick();
        $frontImageick = new \Imagick();
        $backInfo = $this->originPictureHander($backImageick, $backPicture);
        $frontInfo = $this->originPictureHander($frontImageick, $frontPicture);
        $checkResult = ParamsHandler::handleStart(
            [
                'back_info' => 'set|arr',
                'front_info' => 'set|arr',
                'position_x' => 'set|int|min:0',
                'position_y' => 'set|int|min:0'
            ],
            [
                'back_info' => $backInfo,
                'front_info' => $frontInfo,
                'position_x' => $position[0],
                'position_y' => $position[1]
            ]
        );
        if (!$checkResult['back_info'] || !$checkResult['front_info'])
            throw new PictureException('解析图片错误');
        if (!$checkResult['position_x']) $position[0] = 0;
        if (!$checkResult['position_y']) $position[1] = 0;
        if ($backInfo['suffix'] == 'gif') {
            foreach ($backImageick as $frame) {
                $frame->compositeImage(
                    $frontImageick,
                    $frontImageick->getImageCompose(),
                    $position[0],
                    $position[1]);
            }
        } else
            $backImageick->compositeImage(
                $frontImageick,
                $frontImageick->getImageCompose(),
                $position[0],
                $position[1]);
        return $this->returnHandler($backImageick, $savePath, $returnType);
    }

    public function cut(
        $originPicture,
        $cutSize,
        $position,
        $savePath = NULL,
        $returnType = self::RETURN_PATH
    )
    {
        // TODO: Implement cut() method.
        $imageick = new \Imagick();
        $info = $this->originPictureHander($imageick, $originPicture);
        $checkResult = ParamsHandler::handleStart(
            [
                'origin_width' => 'set|int|min:1',
                'origin_height' => 'set|int|min:1',
                'cut_width' => 'set|int|min:0',
                'cut_height' => 'set|int|min:0',
                'position_x' => 'set|int|min:0',
                'position_y' => 'set|int|min:0'
            ],
            [
                'origin_width' => $info['width'],
                'origin_height' => $info['height'],
                'cut_width' => $cutSize[0],
                'cut_height' => $cutSize[1],
                'position_x' => $position[0],
                'position_y' => $position[1]
            ]
        );
        if (!$checkResult['origin_width'] || !$checkResult['origin_height'])
            throw new PictureException('解析图片错误');
        if (!$checkResult['cut_width']) $size[0] = $info['width'];
        if (!$checkResult['cut_height']) $size[1] = $info['height'];
        if (!$checkResult['position_x']) $position[0] = 0;
        if (!$checkResult['position_y']) $position[1] = 0;
        if ($info['suffix'] == 'gif') {
            foreach ($imageick as $frame) {
                $frame->cropImage($cutSize[0], $cutSize[1], $position[0], $position[1]);
            }
        } else
            $imageick->cropImage($cutSize[0], $cutSize[1], $position[0], $position[1]);
        return $this->returnHandler($imageick, $savePath, $returnType);
    }

    public function scale(
        $originPicture,
        $size,
        $savePath = NULL,
        $returnType = self::RETURN_PATH
    )
    {
        $imageick = new \Imagick();
        $info = $this->originPictureHander($imageick, $originPicture);
        $checkResult = ParamsHandler::handleStart(
            [
                'origin_width' => 'set|int|min:1',
                'origin_height' => 'set|int|min:1',
                'scale_width' => 'set|int|min:1',
                'scale_height' => 'set|int|min:1'
            ],
            [
                'origin_width' => $info['width'],
                'origin_height' => $info['height'],
                'scale_width' => $size[0],
                'scale_height' => $size[1]
            ]
        );
        if (!$checkResult['origin_width'] || !$checkResult['origin_height'])
            throw new PictureException('解析图片错误');
        if (!$checkResult['scale_width']) $size[0] = $info['width'];
        if (!$checkResult['scale_height']) $size[1] = $info['height'];
        if ($info['suffix'] == 'gif') {
            foreach ($imageick as $frame) {
                $frame->scaleImage($size[0], $size[1]);
            }
        } else
            $imageick->scaleImage($size[0], $size[1]);
        return $this->returnHandler($imageick, $savePath, $returnType);
    }

    public function rotate(
        $originPicture,
        $angle,
        $savePath = NULL,
        $returnType = self::RETURN_PATH
    )
    {
        $imageick = new \Imagick();
        $info = $this->originPictureHander($imageick, $originPicture);
        $checkResult = ParamsHandler::handleStart(
            [
                'origin_width' => 'set|int|min:1',
                'origin_height' => 'set|int|min:1',
                'angle' => 'set|int'
            ],
            [
                'origin_width' => $info['width'],
                'origin_height' => $info['height'],
                'angle' => $angle
            ]
        );
        if (!$checkResult['origin_width'] || !$checkResult['origin_height'])
            throw new PictureException('解析图片错误');
        if (!$checkResult['angle']) $angle = 0;
        $color = '#eeeeee';
        if ($info['suffix'] == 'gif') {
            foreach ($imageick as $frame) {
                $frame->rotateImage($color, $angle);
            }
        } else
            $imageick->rotateImage($color, $angle);
        return $this->returnHandler($imageick, $savePath, $returnType);
    }

    public function text(
        $originPicture,
        $position,
        $string,
        $angle = 0,
        $savePath = NULL,
        $fontSize = NULL,
        $fontFile = NULL,
        $fontColor = NULL,
        $returnType = self::RETURN_PATH)
    {
        // TODO: Implement text() method.
        if (empty($string))
            throw new PictureException('要写入的字符串不能为空');
        $imageick = new \Imagick();
        $info = $this->originPictureHander($imageick, $originPicture);
        $checkResult = ParamsHandler::handleStart(
            [
                'origin_width' => 'set|int|min:1',
                'origin_height' => 'set|int|min:1',
                'angle' => 'set|int',
                'font_size' => 'int|min:1',
                'font_file' => 'file',
                'font_color' => 'string'
            ],
            [
                'origin_width' => $info['width'],
                'origin_height' => $info['height'],
                'angle' => $angle,
                'font_size' => $fontSize,
                'font_file' => $fontFile,
                'font_color' => $fontColor
            ]
        );
        if (!$checkResult['origin_width'] || !$checkResult['origin_height'])
            throw new PictureException('解析图片错误');
        if (!$checkResult['angle']) $angle = 0;
        if (!$checkResult['font_size']) $fontSize = $this->nowConfig['font_size'];
        if (!$checkResult['font_file']) $fontFile = $this->nowConfig['font_file'];
        if (!$checkResult['font_color']) $fontColor = $this->nowConfig['font_color'];
        $imageDraw = new \ImagickDraw();
        $imageDraw->setFont($fontFile);
        $imageDraw->setFontSize($fontSize);
        if ($info['suffix'] == 'gif') {
            foreach ($imageick as $frame) {
                $frame->annotateImage($imageDraw, $position[0], $position[1], $angle, $string);
            }
        } else
            $imageick->annotateImage($imageDraw, $position[0], $position[1], $angle, $string);
        return $this->returnHandler($imageick, $savePath, $returnType);
    }

    private function supportPicture($type)
    {
        $result = false;
        switch ($type) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'bmp';
                $result = true;
                break;
            default:
                break;
        }
        return $result;
    }
}
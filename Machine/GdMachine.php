<?php
namespace PictureFactory\Machine;

use PictureFactory\Exception\PictureException;
use PictureFactory\PictureInterface\PictureInterface;

class GdMachine implements PictureInterface
{
    private $nowConfig = NULL;
    private $defaultConfig = [
        'save_path' => './',
        'auto_name' => true,
        'transparent_color' => [254, 254, 254],
        'font_path' => './',
        'font_color' => [255, 255, 255],
        'font_size' => 15,
    ];

    public function __construct($config = NULL)
    {
        if (!extension_loaded('gd') && !extension_loaded('gd2'))
            throw new PictureException('未安装gd扩展');
        $configArray = [];
        if (is_string($config) && is_file($config)) {
            $configArray = include $config;
            $this->nowConfig = array($this->defaultConfig, $configArray);
        } elseif (is_array($configArray))
            $configArray = $config;
        $configArray = array($this->defaultConfig, $configArray);
        $checkResult = ParamsHandler::handleStart(
            [
                'save_path' => 'string|path',
                'auto_name' => 'bool',
                'transparent_color' => 'color',
                'font_path' => 'string|path',
                'font_color' => 'color',
                'font_size' => 'int|min:1'
            ], $configArray);
        $lastArray = [];
        !$checkResult['save_path'] or $lastArray['save_path'] = './';
        !$checkResult['auto_name'] or $lastArray['auto_name'] = true;
        !$checkResult['transparent_color'] or $lastArray['transparent_color'] = [254, 254, 254];
        !$checkResult['font_path'] or $lastArray['font_path'] = './';
        !$checkResult['font_color'] or $lastArray['font_color'] = [255, 255, 255];
        !$checkResult['font_size'] or $lastArray['font_size'] = 15;
        $this->nowConfig = array_merge($configArray, $lastArray);
    }

    public function getPictureInfo($string)
    {
        $name = '';
        $suffix = '';
        $hight = 0;
        $width = 0;
        $type = 0;
        if (!empty($string)) {
            $info = NULL;
            if (is_file($string)) {
                $explorArray = explode('.', basename($string));
                $name = $explorArray[0];
                $suffix = strtolower($explorArray[1]);
                $info = getimagesize($string);
            }
            !is_resource($string) or $info = getimagesize($string);
            !is_string($string) or $info = getimagesizefromstring($string);
            if ($info === false)
                throw new PictureException('图片路径错误');
            if ($info[0] <= 0 || $info[1] <= 0 || $info[2] < 1 || $info[2] > 16)
                throw new PictureException('不是图片类型文件');
            $width = $info[0];
            $hight = $info[1];
            $type = $this->getPicType($info[2]);
        }
        return [
            'name' => $name,
            'suffix' => $suffix,
            'hight' => $hight,
            'width' => $width,
            'type' => $type
        ];
    }

    public function getPicType($type)
    {
        $string = '';
        switch ($type) {
            case 1:
                $string = 'gif';
                break;
            case 2:
                $string = 'jpg';
                break;
            case 3:
                $string = 'png';
                break;
            case 4:
                $string = 'swf';
                break;
            case 5:
                $string = 'psd';
                break;
            case 6:
                $string = 'bmp';
                break;
            case 7:
                $string = 'tiff';
                break;
            case 8:
                $string = 'tiff';
                break;
            case 9:
                $string = 'jpc';
                break;
            case 10:
                $string = 'jp2';
                break;
            case 11:
                $string = 'jpx';
                break;
            case 12:
                $string = 'jb2';
                break;
            case 13:
                $string = 'swc';
                break;
            case 14:
                $string = 'iff';
                break;
            case 15:
                $string = 'wbmp';
                break;
            case 16:
                $string = 'xbm';
                break;
            default:
                break;
        }
        return $string;
    }

    public function supportType($suffix)
    {
        $result = false;
        switch ($suffix) {
            case 'gif':
            case 'jpg':
            case 'png':
            case 'swf':
            case 'psd':
            case 'bmp':
            case 'tiff':
            case 'jpc':
            case 'jp2':
            case 'jpx':
            case 'jb2':
            case 'swc':
            case 'iff':
            case 'wbmp':
            case 'xbm':
                $result = true;
                break;
            default:
                break;
        }
        return $result;
    }

    public function handlerSavePath($savePath, $originSuffix)
    {
        if (empty($savePath))
            $savePath = $this->nowConfig['save_path'];
        $saveSuffix = '';
        $saveName = '';
        $saveArray = explode('.', basename($savePath));
        $saveSuffix = $saveArray[1];
        $saveName = $saveArray[0];
        if (empty($saveSuffix) && empty($saveName))
            throw new PictureException('保存路径不合法');
        if (!empty($saveSuffix) && !$this->supportType($saveSuffix))
            throw new PictureException('不支持的保存文件的后缀');
        !empty($saveSuffix) or $saveSuffix = empty($originSuffix) ? 'jpg' : $saveSuffix;
        !empty($saveName) or $saveName = uniqid() . date('YmdHiss', time());
        $savePath .= $saveName . '.' . $saveSuffix;
        return [
            'suffix' => $saveSuffix,
            'save_path' => $savePath
        ];
    }

    private function getPicRes($pictureType, $image)
    {
        $resource = null;
        switch ($pictureType) {
            case 'jpeg':
                $resource = imagecreatefromjpeg($image);
                break;
            case 'png':
                $resource = imagecreatefrompng($image);
                break;
            case 'gif':
                $resource = imagecreatefromgif($image);
                break;
            case 'bmp':
                $resource = imagecreatefromwbmp($image);
                break;
            default:
                break;
        }
        return $resource;
    }

    public function scale($originPicture, $size, $savePath)
    {
        $originInfo = $this->getPictureInfo($originPicture);
        $saveInfo = $this->handlerSavePath($savePath, $originInfo['suffix']);
        $originResult = $this->getPicRes($originInfo['type'], $originPicture);
        $originWidth = $originInfo['width'];
        $originHight = $originInfo['hight'];
        $scaleWidth = 0;
        $scaleHight = 0;
        if (is_array($size)) {
            if (!is_int($size[0]) || !is_int($size[1]) || $size[0] < 1 || $size[1] < 1)
                throw new PictureException('scale 的伸缩尺寸为数组时数值格式错误');
            $scaleWidth = $size[0];
            $scaleHight = $size[1];
        } elseif (is_float($size) && $size > 0) {
            $scaleWidth = (int)($size * $originWidth);
            $scaleHight = (int)($size * $originHight);
        } else
            throw new PictureException('scale 的size参数输入错误');

        $saveRes = imagecreatetruecolor($scaleHight, $scaleWidth);
        imagealphablending($saveRes, true);
        imagesavealpha($saveRes, true);
        $alphaColour = imagecolorallocatealpha(
            $saveRes,
            $this->nowConfig['transparent_color'][0],
            $this->nowConfig['transparent_color'][1],
            $this->nowConfig['transparent_color'][2],
            127);
        imagefill($saveRes, 0, 0, $alphaColour);
        $result = imagecopyresampled($saveRes,)

    }

    public function composition($backPicture, $frontPicture, $savePath, $position)
    {

    }

    public function cut($originPicture, $savePath, $cutSize, $poition)
    {
    }

    public function rotate($originPicture, $savePath, $angle)
    {
    }

    public function text($originPicture, $savePath, $position, $angle, $string, $fontSize = NULL, $fontFile = NULL, $fontColor = NULL)
    {
    }

    public function changeConfig($params = NULL)
    {
    }

    private function getImageInfo()
    {

    }


}
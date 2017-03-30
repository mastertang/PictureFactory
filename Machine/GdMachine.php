<?php
namespace PictureFactory\Machine;

use PictureFactory\Exception\PictureException;
use PictureFactory\PictureFactory;
use PictureFactory\PictureInterface\PictureInterface;

class GdMachine implements PictureInterface
{
    private $nowConfig = NULL;
    private $defaultConfig = [
        'transparent_color' => [254, 254, 254],
        'font_path' => './',
        'font_color' => [255, 255, 255],
        'font_size' => 15,
    ];

    public function __construct()
    {
        if (!extension_loaded('gd') && !extension_loaded('gd2'))
            throw new PictureException('未安装gd扩展');
        $configPath = '../Config/GdConfig';
        if (is_file($configPath)) {
            $this->nowConfig = include $configPath;
        } else {
            $this->nowConfig = $this->defaultConfig;
        }
        $this->nowConfig = array_merge($this->defaultConfig, $this->nowConfig);
        $checkResult = ParamsHandler::handleStart(
            [
                'transparent_color' => ['color', $this->nowConfig['transparent_color']],
                'font_path' => ['string|path', $this->nowConfig['font_path']],
                'font_color' => ['color', $this->nowConfig['font_color']],
                'font_size' => ['int|min:1', $this->nowConfig['font_size']]
            ]);
        !$checkResult['transparent_color'] or $this->nowConfig['transparent_color'] = [254, 254, 254];
        !$checkResult['font_path'] or $this->nowConfig['font_path'] = './';
        !$checkResult['font_color'] or $this->nowConfig['font_color'] = [255, 255, 255];
        !$checkResult['font_size'] or $this->nowConfig['font_size'] = 15;
    }

    public function thumbnailImage(
        $originPicture,
        $scaleSize,
        $picQuality = -1,
        $savePath = NULL,
        $returnType = 1,
        $transparentColor = []
    )
    {
        return $this->scale(
            $originPicture,
            $scaleSize,
            $savePath,
            $picQuality,
            $returnType,
            $transparentColor
        );
    }

    public function makeGif($images, $savePath)
    {
        return true;
    }

    public function scale(
        $originPicture,
        $size,
        $savePath = NULL,
        $quality = -1,
        $returnType = 1,
        $transparentColor = []
    )
    {
        $originPicInfo = $this->getPictureInfo($originPicture);
        $originRes = $this->getPicRes($originPicInfo['type'], $originPicture);
        $scaleWidth = 0;
        $scaleHight = 0;
        $this->checkSize($size, $scaleWidth, $scaleHight);
        $saveRes = $this->createEmptyImage(
            $scaleWidth,
            $scaleHight,
            empty($transparentColor) ? $this->nowConfig['transparent_color'] : $transparentColor
        );
        imagecopyresampled(
            $saveRes,
            $originRes,
            0, 0, 0, 0,
            $scaleWidth,
            $scaleHight,
            $originPicInfo['width'],
            $originPicInfo['height']
        );
        return $this->returnHanler($returnType, $saveRes, $savePath, $quality);
    }

    public function composition(
        $backPicture,
        $frontPicture,
        $position,
        $savePath = NULL,
        $quality = -1,
        $returnType = 1
    )
    {
        $backInfo = $this->getPictureInfo($backPicture);
        $frontInfo = $this->getPictureInfo($frontPicture);
        $backRes = $this->getPicRes($backInfo['suffix'], $backPicture);
        $frontRes = $this->getPicRes($frontInfo['suffix'], $frontPicture);
        $x = 0;
        $y = 0;
        if (is_array($position)) {
            if (is_int($position[0]) && $position[0] >= 0
                &&
                is_int($position[1] && $position[1] >= 0)
            ) {
                $x = $position[0] >= $backInfo['width'] ? $x : $position[0];
                $y = $position[1] >= $backInfo['height'] ? $y : $position[1];
            }
        }
        imagecopyresampled(
            $backRes,
            $frontRes,
            $x, $y, 0, 0,
            $backInfo['width'],
            $backInfo['height'],
            $frontInfo['width'],
            $frontInfo['height']
        );
        return $this->returnHanler($returnType, $backRes, $savePath, $quality);
    }

    public function cut(
        $originPicture,
        $cutSize,
        $poition,
        $savePath = NULL,
        $quality = -1,
        $returnType = 1,
        $transparentColor = []
    )
    {
        $originPicInfo = $this->getPictureInfo($originPicture);
        $originRes = $this->getPicRes($originPicInfo['type'], $originPicture);
        $cutWidth = $cutSize[0];
        $cutHeight = $cutSize[1];
        $positionX = $poition[0];
        $positionY = $poition[1];
        $cutWidth =
            ($cutSize[0] + $positionX) > $originPicInfo['width'] ?
                $originPicture['width'] - $positionX :
                $cutSize[0];
        $cutHeight =
            ($cutSize[1] + $positionY) > $originPicInfo['height'] ?
                $originPicture['height'] - $positionY :
                $cutSize[1];
        $saveRes = $this->createEmptyImage(
            $cutWidth,
            $cutHeight,
            empty($transparentColor) ?
                $this->nowConfig['transparent_color'] :
                $transparentColor
        );
        imagecopyresampled(
            $saveRes,
            $originRes,
            0, 0, $positionX, $positionY,
            $cutWidth,
            $cutHeight,
            $originPicInfo['width'],
            $originPicInfo['height']);
        return $this->returnHanler($returnType, $saveRes, $savePath, $quality);
    }

    public function rotate(
        $originPicture,
        $angle,
        $savePath = NULL,
        $quality = -1,
        $returnType = 1,
        $transparentColor = []
    )
    {
        $originPicInfo = $this->getPictureInfo($originPicture);
        $originRes = $this->getPicRes($originPicInfo['type'], $originPicture);
        if (!is_int($angle)) throw new PictureException('角度值不合法');
        $rotateSize = $this->getRotateSize($originPicInfo['width'], $originPicInfo['height'], $angle);
        $saveRes = $this->createEmptyImage(
            $rotateSize['width'],
            $rotateSize['height'],
            empty($transparentColor) ? $this->nowConfig['transparent_color'] : $transparentColor
        );
        imagecopyresampled(
            $saveRes,
            $originRes,
            0, 0, 0, 0,
            $rotateSize['width'],
            $rotateSize['height'],
            $originPicInfo['width'],
            $originPicInfo['height']);
        $alphaColour = imagecolorallocatealpha(
            $saveRes,
            $transparentColor[0],
            $transparentColor[1],
            $transparentColor[2],
            127);
        $resultRes = imagerotate($saveRes, $angle, $alphaColour, 0);
        return $this->returnHanler($returnType, $resultRes, $savePath, $quality);
    }

    public function text(
        $originPicture,
        $position,
        $string,
        $angle = 0,
        $savePath = NULL,
        $fontFile = NULL,
        $fontSize = NULL,
        $fontColor = NULL,
        $quality = -1,
        $returnType = 1
    )
    {
        if (empty($string)) throw new PictureException('添加的字符串不能为空');
        if (empty($fontSize)) $fontSize = $this->nowConfig['font_size'];
        if (empty($fontFile)) $fontFile = $this->nowConfig['font_path'];
        if (empty($fontColor)) $fontColor = $this->nowConfig['font_color'];
        ParamsHandler::handleStart(
            [
                'angle' => ['set|int', $angle],
                'font_size' => ['set|int|min:1', $fontSize],
                'font_file' => ['set|file', $fontFile],
                'font_color' => ['set|color', $fontColor],
                'position' => ['set|arr|position', $position]
            ]);
        $originPicInfo = $this->getPictureInfo($originPicture);
        $originRes = $this->getPicRes($originPicInfo['type'], $originPicture);
        $textColor = imagecolorallocate(
            $originRes,
            $fontColor[0],
            $fontColor[1],
            $fontColor[2]);
        $result = imagettftext(
            $originRes,
            $fontSize,
            $angle,
            $position[0],
            $position[1],
            $textColor,
            $fontFile,
            $string);
        return $this->returnHanler($returnType, $originRes, $savePath, $quality);
    }

    public function changeConfig($params = NULL)
    {
        if (!empty($params)) {
            $roler = [];
            $data = [];
            if (!empty($params['transparent_color'])) {
                $roler['transparent_color'] = ['color',$params['transparent_color']];
            }
            if (!empty($params['font_path'])) {
                $roler['font_path'] = ['file',$params['font_path']];
            }
            if (!empty($params['font_color'])) {
                $roler['font_color'] = ['color',$params['font_color']];
            }
            if (!empty($params['font_size'])) {
                $roler['font_size'] = ['int|min:1',$params['font_size']];
            }
            try {
                ParamsHandler::handleStart($roler);
                if (!$data['transparent_color']) unset($data['transparent_color']);
                if (!$data['font_path']) unset($data['font_path']);
                if (!$data['font_color']) unset($data['font_color']);
                if (!$data['font_size']) unset($data['font_size']);
                array_merge($this->nowConfig, $data);
            }catch (\Exception $e){
                return true;
            }
        }
        return true;
    }

    private function getPictureInfo($string)
    {
        $result = [];
        if (!empty($string)) {
            if (is_file($string)) {
                $suffix = NULL;
                MachineHandler::getPictureNameAndsuffix($string, $result['name'], $suffix);
                $info = getimagesize($string);
                if ($info === false)
                    throw new PictureException('图片路径错误');
                if ($info[0] <= 0 ||
                    $info[1] <= 0 ||
                    $info[2] < 1 ||
                    $info[2] > 16
                )
                    throw new PictureException('不是图片类型文件');
                $result['width'] = $info[0];
                $result['height'] = $info[1];
                $result['suffix'] = $this->getPicType($info[2]);
                if (empty($result['suffix']))
                    throw new PictureException('不支持当前文件类型');
            } else if (is_resource($string)) {
                $result = [
                    'width' => imagesx($string),
                    'height' => imagesy($string),
                ];
            }
        }
        return $result;
    }

    private function getSavePathInfo($string)
    {
        if (!empty($string)) {
            $name = NULL;
            $suffix = NULL;
            MachineHandler::getPictureNameAndsuffix($string, $name, $suffix);
            switch ($suffix) {
                case 'jpg':
                case 'gif':
                case  'jpeg':
                case 'pnd':
                case 'bmp':
                    break;
                default:
                    throw new PictureException('不支持当前保存类型');
                    break;
            }
            return [
                'name' => $name,
                'suffix' => $suffix
            ];
        } else throw new PictureException('保存路径不能为空');
    }

    private function getPicType($type)
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

    private function getPicRes($pictureType, $image)
    {
        $resource = null;
        if (is_resource($image))
            $resource = $image;
        else {
            switch ($pictureType) {
                case 'jpg':
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
        }
        return $resource;
    }

    private function createEmptyImage($width, $height, $transparentColor)
    {
        $res = imagecreatetruecolor($width, $height);
        imagealphablending($res, true);
        imagesavealpha($res, true);
        $alphaColour = imagecolorallocatealpha(
            $res,
            $transparentColor[0],
            $transparentColor[1],
            $transparentColor[2],
            127);
        imagefill($res, 0, 0, $alphaColour);
        return $res;
    }

    private function savePicture($type, &$res, $savePath, $quality = 100)
    {
        if ($type == 'jpg' || $type == 'jpeg') {
            if ($quality < 0 || $quality > 100) $quality = 100;
            imagejpeg($res, $savePath, $quality);
        } else if ($type == 'bmg')
            image2wbmp($res, $savePath);
        else if ($type == 'gif')
            imagegif($res, $savePath);
        else {
            $quality = ($quality >= 0 && $quality <= 9) ? (int)$quality : 9;
            imagepng($res, $savePath, $quality);
        }
        imagedestroy($res);
    }

    private function checkSize($size, &$width, &$height)
    {
        if (is_array($size)) {
            if (!is_int($size[0]) ||
                !is_int($size[1]) ||
                $size[0] < 1 ||
                $size[1] < 1
            )
                throw new PictureException('scale 的伸缩尺寸为数组时数值格式错误');
            $width = $size[0];
            $height = $size[1];
        } elseif (is_float($size) && $size > 0) {
            $width = (int)($size * $width);
            $height = (int)($size * $height);
        } else throw new PictureException('scale 的size参数输入错误');
    }

    private function getRotateSize($originWidth, $originHeight, $angle)
    {
        $halfWidth = $originWidth / 2;
        $halfHeight = $originHeight / 2;
        $point = [
            [-$halfWidth, $halfHeight],
            [$halfWidth, $halfHeight],
            [$halfWidth, -$halfHeight],
            [-$halfWidth, -$halfHeight]
        ];
        $cos = cos(deg2rad($angle));
        $sin = sin(deg2rad($angle));
        $newpoint[] = [
            (int)($cos * $point[0][0] + $sin * $point[0][1]),
            (int)($cos * $point[0][1] - $sin * $point[0][0])
        ];
        $newpoint[] = [
            (int)($cos * $point[1][0] + $sin * $point[1][1]),
            (int)($cos * $point[1][1] - $sin * $point[1][0])
        ];
        $newpoint[] = [
            (int)($cos * $point[2][0] + $sin * $point[2][1]),
            (int)($cos * $point[2][1] - $sin * $point[2][0])
        ];
        $newpoint[] = [
            (int)($cos * $point[3][0] + $sin * $point[3][1]),
            (int)($cos * $point[3][1] - $sin * $point[3][0])
        ];
        $maxSmallX = 0;
        $maxSmallY = 0;
        $maxBigX = 0;
        $maxBigY = 0;
        foreach ($newpoint as $point) {
            if ($point[0] < $maxSmallX)
                $maxSmallX = $point[0];
            if ($point[0] > $maxBigX)
                $maxBigX = $point[0];
            if ($point[1] < $maxSmallY)
                $maxSmallY = $point[1];
            if ($point[1] < $maxBigY)
                $maxBigY = $point[1];
        }
        return [
            'width' => abs($maxSmallX) + abs($maxBigX),
            'height' => abs($maxSmallY) + abs($maxBigY)
        ];
    }

    private function returnHanler($returnType, $res, $savePath, $quality)
    {
        if ($returnType == PictureFactory::RETURN_RES) {
            return $res;
        } elseif ($returnType == PictureFactory::RETURN_IMG_STRING) {
            $saveInfo = $saveInfo = $this->getSavePathInfo($savePath);
            $this->savePicture($saveInfo['suffix'], $res, $savePath, $quality);
            return file_get_contents($savePath);
        } elseif ($returnType == PictureFactory::RETURN_PATH) {
            $saveInfo = $saveInfo = $this->getSavePathInfo($savePath);
            $this->savePicture($saveInfo['suffix'], $res, $savePath, $quality);
            return $savePath;
        }
    }
}
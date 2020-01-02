<?php

namespace PictureFactory\driver;

/**
 * Class Gd
 * @package PictureFactory\driver
 */
class Gd
{
    /**
     * @var array 透明色
     */
    protected $transparentColor = [254, 254, 254];

    /**
     * @var string 字体文件地址
     */
    protected $fontPath = "";

    /**
     * @var array 字体颜色
     */
    protected $fontColor = [255, 255, 255];

    /**
     * @var int 字体字号
     */
    protected $fontSize = 12;

    /**
     * @var string 源图片路径
     */
    protected $srcPicturePath = "";

    /**
     * @var string 保存路径
     */
    protected $savePicturePath = "";

    /**
     * @var array 处理尺寸
     */
    protected $directSize = [0, 0];

    /**
     * @var int 保存质量
     */
    protected $saveQuality = 100;

    /**
     * @var string 图片内容
     */
    protected $imageContent = "";

    /**
     * @var string 前背景图片路径
     */
    protected $frontPicturePath = "";

    /**
     * @var string 后背景图片路径
     */
    protected $backPicturePath = "";

    /**
     * @var array 图片合成XY位置
     */
    protected $position = [0, 0];

    /**
     * @var string 图片资源
     */
    protected $imageRes = "";

    /**
     * @var string 文字
     */
    protected $text = "";

    /**
     * @var int 旋转角度
     */
    protected $rotateAngle = 0;

    /**
     * @var bool 删除保存的图片
     */
    protected $deleteSavePicture = false;

    /**
     * @var array 图片背景颜色
     */
    protected $imageBgColor = [255, 255, 255];

    /**
     * @var int 躁点数量
     */
    protected $noiseCount = 0;

    /**
     * @var array 躁点颜色集合
     */
    protected $noiseColorSet = [];

    /**
     * @var array 图片的尺寸
     */
    protected $imageSize = [];

    /**
     * @var string 错误信息
     */
    protected $errorMessage = "";

    /**
     * 图片尺寸
     *
     * @param $width
     * @param $height
     * @return $this
     */
    public function imageSize($width, $height)
    {
        if ($width > 0 && $height > 0) {
            $this->imageSize = [$width, $height];
        }
        return $this;
    }

    /**
     * 早点颜色集合
     *
     * @param $set
     * @return $this
     */
    public function noiseColorSet($set)
    {
        foreach ($set as $key => $value) {
            if ($value[0] <= 255 && $value[0] >= 0 &&
                $value[1] <= 255 && $value[1] >= 0 &&
                $value[2] <= 255 && $value[2] >= 0) {
            } else {
                unset($value[$key]);
            }
        }
        $this->noiseColorSet = $set;
        return $this;
    }

    /**
     * 设置图片背景颜色
     *
     * @param $r
     * @param $g
     * @param $b
     * @return $this
     */
    public function imageBgColor($r, $g, $b)
    {
        if ($r <= 255 && $r >= 0 &&
            $g <= 255 && $g >= 0 &&
            $b <= 255 && $b >= 0) {
            $this->imageBgColor = [$r, $g, $b];
        }
        return $this;
    }

    /**
     * 实则躁点数量
     *
     * @param $count
     */
    public function noiseCount($count)
    {
        $this->noiseCount = $count;
    }

    /**
     * 设置前背景图片路径
     *
     * @param $picturePath
     * @return $this
     */
    public function frontPicturePath($picturePath)
    {
        if (is_file($picturePath) || is_resource($picturePath)) {
            $this->frontPicturePath = $picturePath;
        }
        return $this;
    }

    /**
     * 设置文字
     *
     * @param $text
     * @return $this
     */
    public function text($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * 设置旋转的角度
     *
     * @param $angle
     * @return $this
     */
    public function rotateAngle($angle)
    {
        $this->rotateAngle = $angle;
        return $this;
    }

    /**
     * 设置图片合成XY位置
     *
     * @param $x
     * @param $y
     * @return $this
     */
    public function position($x, $y)
    {
        $this->position = [(int)$x, (int)$y];
        return $this;
    }

    /**
     * 设置后背景图片路径
     *
     * @param $picturePath
     * @return $this
     */
    public function backPicturePath($picturePath)
    {
        if (is_file($picturePath) || is_resource($picturePath)) {
            $this->backPicturePath = $picturePath;
        }
        return $this;
    }

    /**
     * 删除保存的图片
     *
     * @param $delete
     * @return $this
     */
    public function deleteSavePicture($delete)
    {
        $this->deleteSavePicture = $delete;
        return $this;
    }

    /**
     * 设置源图片路径
     *
     * @param $filePath
     * @return $this
     */
    public function srcPicturePath($filePath)
    {
        if (is_file($filePath) || is_resource($filePath)) {
            $this->srcPicturePath = $filePath;
        }
        return $this;
    }

    /**
     * 设置保存图片路径
     *
     * @param $filePath
     * @return $this
     */
    public function savePicturePath($filePath)
    {
        $this->savePicturePath = $filePath;
        return $this;
    }

    /**
     * 设置目标尺寸
     *
     * @param $size
     * @return $this
     */
    public function directSize($size)
    {
        if (is_array($size)) {
            if (!is_int($size[0]) ||
                !is_int($size[1]) ||
                $size[0] < 1 ||
                $size[1] < 1
            ) {
                return $this;
            }
        } elseif ((is_float($size) || is_int($size)) && $size > 0) {

        } else {
            return $this;
        }
        $this->directSize = $size;
        return $this;
    }

    /**
     * 设置保存的质量
     *
     * @param $quality
     * @return $this
     */
    public function saveQuality($quality)
    {
        if (is_numeric($quality)) {
            $this->saveQuality = (int)$quality;
        }
        return $this;
    }

    /**
     * 设置透明色数值
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
     * 字体文件路径
     *
     * @param $fontPath
     * @return $this
     */
    public function fontPath($fontPath)
    {
        if (is_file($fontPath)) {
            $this->fontPath = $fontPath;
        }
        return $this;
    }

    /**
     * 设置字体颜色
     *
     * @param $r
     * @param $g
     * @param $b
     * @return $this
     */
    public function fontColor($r, $g, $b)
    {
        if ($r <= 255 && $r >= 0 &&
            $g <= 255 && $g >= 0 &&
            $b <= 255 && $b >= 0) {
            $this->fontColor = [$r, $g, $b];
        }
        return $this;
    }

    /**
     * 设置字体字号
     *
     * @param $fontSize
     * @return $this
     */
    public function fontSize($fontSize)
    {
        if ($fontSize >= 12) {
            $this->fontSize = $fontSize;
        }
        return $this;
    }

    /**
     * 获取处理后的图片内容
     *
     * @return string
     */
    public function imageContent()
    {
        return $this->imageContent;
    }

    /**
     * 获取处理后的图片资源
     *
     * @return string
     */
    public function imageRes()
    {
        return $this->imageRes;
    }

    /**
     * 生成缩略图
     *
     * @return bool|string
     */
    public function thumbnailImage()
    {
        return $this->scale();
    }

    /**
     * 生成gif
     *
     * @param $images
     * @param $savePath
     * @param array $gifSize
     * @param string $tempPath
     * @param array $transparentColor
     * @return bool
     */
    public function makeGif($images, $savePath, $gifSize = [], $tempPath = './', $transparentColor = [])
    {
        $gif    = new Gif();
        $result = $gif->gifStart($images, $savePath, $gifSize, $tempPath, $transparentColor);
        return $result;
    }

    /**
     * 图片缩放
     *
     * @return bool|string
     */
    public function scale()
    {
        if (!$this->getPictureInfo(
            $this->srcPicturePath,
            $fileName,
            $suffix,
            $width,
            $height
        )) {
            return false;
        }
        $originRes = $this->getPictureRes($suffix, $this->srcPicturePath);
        if (empty($originRes)) {
            return false;
        }
        if (!$this->makeDirectSize(
            $this->directSize,
            $width,
            $height,
            $scaleWidth,
            $scaleHight)
        ) {
            return false;
        };
        $saveRes = $this->createEmptyImage(
            $scaleWidth,
            $scaleHight,
            $this->transparentColor
        );
        imagecopyresampled(
            $saveRes,
            $originRes,
            0, 0, 0, 0,
            $scaleWidth,
            $scaleHight,
            $width,
            $height
        );
        $this->imageRes = $saveRes;
        return $this->saveHandler($saveRes);
    }

    /**
     * 图片合成
     *
     * @return bool|string
     */
    public function composition()
    {
        if (!$this->getPictureInfo(
            $this->backPicturePath,
            $backName,
            $backSuffix,
            $backWidth,
            $backHeight
        )) {
            return false;
        };
        if (!$this->getPictureInfo(
            $this->frontPicturePath,
            $frontName,
            $frontSuffix,
            $frontWidth,
            $frontHeight
        )) {
            return false;
        };
        $frontRes = $this->getPictureRes($frontSuffix, $this->frontPicturePath);
        if (empty($frontRes)) {
            return false;
        }
        $x = 0;
        $y = 0;
        if (is_array($this->position)) {
            if (is_int($this->position[0]) && $this->position[0] >= 0 &&
                is_int($this->position[1]) && $this->position[1] >= 0
            ) {
                $x = $this->position[0] >= $backWidth ? $x : $this->position[0];
                $y = $this->position[1] >= $backHeight ? $y : $this->position[1];
            }
        }
        $backRes = $this->getPictureRes($backSuffix, $this->backPicturePath);
        if (empty($frontRes)) {
            return false;
        }
        $restWidth  = $backWidth - $x;
        $restHeight = $backHeight - $y;
        $restWidth  = $restWidth > $frontWidth ? $frontWidth : $restWidth;
        $restHeight = $restHeight > $frontHeight ? $frontHeight : $restHeight;
        imagecopy(
            $backRes, $frontRes,
            $x, $y, 0, 0, $restWidth, $restHeight
        );
        if (in_array($backSuffix, ["png", "bmp"])) {
            $self   = new self();
            $result = $self->srcPicturePath($backRes)
                ->directSize([$backWidth, $backHeight])
                ->transparentColor(
                    $this->transparentColor[0],
                    $this->transparentColor[1],
                    $this->transparentColor[2]
                )
                ->saveQuality(9)
                ->scale();
            if ($result === false) {
                $this->errorMessage = $self->errorMessage;
                return false;
            }
            $backRes = $self->imageRes();
            unset($self);
        }
        $this->imageRes = $backRes;
        return $this->saveHandler($backRes);
    }

    /**
     * 图片裁剪
     *
     * @return bool|string
     */
    public function cut()
    {
        if (!$this->getPictureInfo(
            $this->srcPicturePath,
            $pictureName,
            $suffix,
            $width,
            $height
        )) {
            return false;
        }
        $originRes = $this->getPictureRes($suffix, $this->srcPicturePath);
        if (empty($originRes)) {
            return false;
        }
        $positionX = $this->position[0];
        $positionY = $this->position[1];
        $cutWidth  = ($this->directSize[0] + $positionX) > $width ? $width - $positionX : $this->directSize[0];
        $cutHeight = ($this->directSize[1] + $positionY) > $height ? $height - $positionY : $this->directSize[1];
        if ($cutHeight <= 0 || $cutWidth <= 0) {
            $this->errorMessage = "截取尺寸错误!";
            return false;
        }
        $saveRes = $this->createEmptyImage($cutWidth, $cutHeight, $this->transparentColor);
        imagecopy(
            $saveRes,
            $originRes,
            0, 0,
            $positionX,
            $positionY,
            $cutWidth,
            $cutHeight
        );
        return $this->saveHandler($saveRes);
    }

    /**
     * 图片旋转
     *
     * @return bool|string
     */
    public function rotate()
    {
        if (!$this->getPictureInfo(
            $this->srcPicturePath,
            $pictureName,
            $suffix,
            $width,
            $height
        )) {
            return false;
        }
        $originRes = $this->getPictureRes($suffix, $this->srcPicturePath);
        if (empty($originRes)) {
            return false;
        }
        if (!is_int($this->rotateAngle)) {
            $this->errorMessage = "角度值不合法";
            return false;
        }
        $this->getRotateSize($width, $height, $this->rotateAngle, $directWidth, $directHeight);
        $saveRes = $this->createEmptyImage(
            $directWidth,
            $directHeight,
            $this->transparentColor
        );
        imagecopy(
            $saveRes,
            $originRes,
            0, 0, 0, 0,
            $width,
            $height);
        $alphaColour    = imagecolorallocatealpha(
            $saveRes,
            $this->transparentColor[0],
            $this->transparentColor[1],
            $this->transparentColor[2],
            127);
        $resultRes      = imagerotate($originRes, $this->rotateAngle, $alphaColour, 0);
        $this->imageRes = $resultRes;
        return $this->saveHandler($resultRes);
    }

    /**
     * 文字生成图片
     *
     * @return bool|string
     */
    public function drawText()
    {
        if (!($this->getPictureInfo(
            $this->srcPicturePath,
            $pictureName,
            $suffix,
            $width,
            $height
        ))) {
            return false;
        }
        $originRes = $this->getPictureRes($suffix, $this->srcPicturePath);
        if (empty($originRes)) {
            return false;
        }
        $textColor = imagecolorallocate(
            $originRes,
            $this->fontColor[0],
            $this->fontColor[1],
            $this->fontColor[2]);
        imagettftext(
            $originRes,
            $this->fontSize,
            $this->rotateAngle,
            $this->position[0],
            $this->position[1],
            $textColor,
            $this->fontPath,
            $this->text);
        if (in_array($suffix, ["png", "bmp"])) {
            $self   = new self();
            $result = $self->srcPicturePath($originRes)
                ->directSize([$width, $height])
                ->transparentColor(
                    $this->transparentColor[0],
                    $this->transparentColor[1],
                    $this->transparentColor[2]
                )
                ->saveQuality(9)
                ->scale();
            if ($result === false) {
                $this->errorMessage = $self->errorMessage;
                return false;
            }
            $originRes = $self->imageRes();
            unset($self);
        }
        return $this->saveHandler($originRes);
    }

    /**
     * 生成验证码
     *
     * @return bool|string
     */
    public function makeIdentifyCodePicture()
    {

        $image = imagecreate($this->imageSize[0], $this->imageSize[1]);
        imagefill(
            $image,
            0, 0,
            imagecolorallocate(
                $image,
                $this->imageBgColor[0],
                $this->imageBgColor[1],
                $this->imageBgColor[2]
            )
        );
        $self   = new self();
        $result = $self->srcPicturePath($image)
            ->position($this->position[0], $this->position[1])
            ->text($this->text)
            ->fontPath($this->fontPath)
            ->fontSize($this->fontSize)
            ->rotateAngle($this->rotateAngle)
            ->fontColor($this->fontColor[0], $this->fontColor[1], $this->fontColor[2])
            ->drawText();
        if ($result === false) {
            $this->errorMessage = $self->errorMessage;
            return false;
        }
        $colorSize = sizeof($this->noiseColorSet);
        $tempColor = NULL;
        for ($i = 0; $i < $this->noiseCount; $i++) {
            $color     = $this->noiseColorSet[rand(0, $colorSize - 1)];
            $point1_x  = rand(0, $this->imageSize[0]);
            $point1_y  = rand(0, $this->imageSize[1]);
            $point2_x  = rand(1, 16) == 1 ? $point1_x + rand(1, 4) : $point1_x - rand(1, 4);
            $point2_y  = rand(1, 16) == 1 ? $point1_y + rand(1, 4) : $point1_y - rand(1, 4);
            $tempColor = imagecolorallocate($image, $color[0], $color[1], $color[2]);
            imageline(
                $image,
                $point1_x,
                $point1_y,
                $point2_x,
                $point2_y,
                $tempColor
            );
        }
        for ($i = 0; $i < $this->noiseCount; $i++) {
            $color     = $this->noiseColorSet[rand(0, $colorSize - 1)];
            $tempColor = imagecolorallocate($image, $color[0], $color[1], $color[2]);
            imagesetpixel(
                $image,
                rand(0, $this->imageSize[0]),
                rand(0, $this->imageSize[1]),
                $tempColor
            );
        }
        return $this->saveHandler($image);
    }

    /**
     * 获取图片的信息
     *
     * @param $filePath
     * @param $fileName
     * @param $suffix
     * @param $width
     * @param $height
     * @return bool
     */
    private function getPictureInfo($filePath, &$fileName, &$suffix, &$width, &$height)
    {
        if (!empty($filePath)) {
            if (is_file($filePath)) {
                $suffix = NULL;
                CommonHandler::getPictureNameAndsuffix($filePath, $fileName, $suffix);
                $info = getimagesize($filePath);
                if ($info === false ||
                    !is_array($info) ||
                    $info[0] <= 0 ||
                    $info[1] <= 0 ||
                    $info[2] < 1 ||
                    $info[2] > 16) {
                    $this->errorMessage = "图片文件错误,无法识别";
                    return false;
                }
                $width  = $info[0];
                $height = $info[1];
                $suffix = $this->getPictureType($info[2]);
                if (empty($suffix)) {
                    $this->errorMessage = "图片类型不支持, Type: " . $info[2];
                    return false;
                }
            } else if (is_resource($filePath)) {
                $width    = imagesx($filePath);
                $height   = imagesy($filePath);
                $fileName = "";
                $suffix   = "";
                $width    = 0;
                $height   = 0;
            } else {
                $this->errorMessage = "图片不存在";
                return false;
            }
        } else {
            $this->errorMessage = "图片路径参数空";
        }
        return true;
    }

    /**
     * 获取保存图片的信息
     *
     * @param $filePath
     * @param $name
     * @param $suffix
     * @return bool
     */
    private function getSavePathInfo($filePath, $name, $suffix)
    {
        if (!empty($filePath)) {
            $name   = null;
            $suffix = null;
            CommonHandler::getPictureNameAndsuffix($filePath, $name, $suffix);
            switch ($suffix) {
                case 'jpg':
                case 'gif':
                case  'jpeg':
                case 'png':
                case 'bmp':
                    break;
                default:
                    $suffix             = null;
                    $this->errorMessage = "不支持当前保存的图片类型!";
                    break;
            }
            return true;
        } else {
            $this->errorMessage = "图片保存路径为空!";
            return false;
        }
    }

    /**
     * 获取图片的类型
     *
     * @param $type
     * @return string
     */
    private function getPictureType($type)
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

    /**
     * 获取图片的资源
     *
     * @param $pictureType
     * @param $image
     * @return bool|resource
     */
    private function getPictureRes($pictureType, $image)
    {
        $resource = false;
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
                    $this->errorMessage = "不支持此图片类型，Type: " . $pictureType;
                    break;
            }
        }
        return $resource;
    }

    /**
     * 创建图片res
     *
     * @param $width
     * @param $height
     * @param $transparentColor
     * @return resource
     */
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

    /**
     * 保存图片
     *
     * @param $type
     * @param $res
     * @param $savePath
     * @param int $quality
     */
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

    /**
     * 处理目标尺寸
     *
     * @param $size
     * @param $srcWidth
     * @param $srcHeight
     * @param $scaleWidth
     * @param $scaleHeight
     * @return bool
     */
    private function makeDirectSize($size, $srcWidth, $srcHeight, &$scaleWidth, &$scaleHeight)
    {
        if (is_array($size)) {
            $width  = $size[0];
            $height = $size[1];
        } elseif ((is_float($size) || is_int($size)) && $size > 0) {
            $scaleWidth  = (int)($size * $srcWidth);
            $scaleHeight = (int)($size * $srcHeight);
        } else {
            $this->errorMessage = "目标尺寸格式错误!";
            return false;
        }
        return true;
    }

    /**
     * 计算旋转后画布的长宽
     *
     * @param $originWidth
     * @param $originHeight
     * @param $angle
     * @param $directWidth
     * @param $directHeight
     * @return bool
     */
    private function getRotateSize($originWidth, $originHeight, $angle, &$directWidth, &$directHeight)
    {
        $halfWidth  = $originWidth / 2;
        $halfHeight = $originHeight / 2;
        $point      = [
            [-$halfWidth, $halfHeight],
            [$halfWidth, $halfHeight],
            [$halfWidth, -$halfHeight],
            [-$halfWidth, -$halfHeight]
        ];
        $cos        = cos(deg2rad($angle));
        $sin        = sin(deg2rad($angle));
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
        $maxSmallX  = 0;
        $maxSmallY  = 0;
        $maxBigX    = 0;
        $maxBigY    = 0;
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

        $directWidth  = abs($maxSmallX) + abs($maxBigX);
        $directHeight = abs($maxSmallY) + abs($maxBigY);
        return true;
    }

    /**
     * 保存出来后的图片
     *
     * @param $res
     * @return bool|string
     */
    private function saveHandler($res)
    {
        $pictureName = "";
        $suffix      = "";
        $result      = $this->getSavePathInfo($this->savePicturePath, $pictureName, $suffix);
        if ($result === false) {
            return false;
        }
        $this->savePicture($suffix, $res, $this->savePicturePath, $this->saveQuality);
        if (!is_file($this->savePicturePath)) {
            $this->errorMessage = "保存图片失败!";
            return false;
        }
        $this->imageContent = file_get_contents($this->savePicturePath);
        if ($this->deleteSavePicture) {
            unlink($this->savePicturePath);
        }
        return true;
    }

    private function checkReturnType($returnType)
    {
        if ($returnType != 1 && $returnType != 2 && $returnType != 3)
            throw new PictureException("不支持当前输入的返回类型{$returnType}");
    }
}
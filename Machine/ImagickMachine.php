<?php
namespace PictureFactory\Machine;

use PictureFactory\Exception\PictureException;
use PictureFactory\PictureFactory;
use PictureFactory\PictureInterface\PictureInterface;

class ImagickMachine implements PictureInterface
{
    private $nowConfig = [];
    private $defaultConfig = [
        'transparent_color' => [254, 254, 254],
        'font_path' => './',
        'font_color' => [255, 255, 255],
        'font_size' => 15,
    ];

    public function __construct()
    {
        if (!extension_loaded('imagick'))
            throw new PictureException('未安装ImageMagick扩展');
        $configPath = __DIR__ . '/../Config/ImageickConfig.php';
        if (is_file($configPath)) {
            $this->nowConfig = include $configPath;
        } else $this->nowConfig = $configPath;
        $this->nowConfig = array_merge($this->defaultConfig, $this->nowConfig);
        $checkResult = ParamsHandler::handleStart(
            [
                'font_path' => ['string|path', $this->nowConfig['font_path']],
                'font_size' => ['int|min:1', $this->nowConfig['font_size']]
            ]);
        !$checkResult['transparent_color'] or $this->nowConfig['transparent_color'] = [254, 254, 254];
        !$checkResult['font_path'] or $this->nowConfig['font_path'] = './';
        !$checkResult['font_color'] or $this->nowConfig['font_color'] = [255, 255, 255];
        !$checkResult['font_size'] or $this->nowConfig['font_size'] = 15;
    }

    public function changeConfig($params = NULL)
    {
        if (!empty($params)) {
            $roler = [];
            $data = [];
            if (!empty($params['transparent_color'])) {
                $roler['transparent_color'] = ['color', $params['transparent_color']];
            }
            if (!empty($params['font_path'])) {
                $roler['font_path'] = ['file', $params['font_path']];
            }
            if (!empty($params['font_color'])) {
                $roler['font_color'] = ['color', $params['font_color']];
            }
            if (!empty($params['font_size'])) {
                $roler['font_size'] = ['int|min:1', $params['font_size']];
            }
            try {
                ParamsHandler::handleStart($roler);
                if (!$data['transparent_color']) unset($data['transparent_color']);
                if (!$data['font_path']) unset($data['font_path']);
                if (!$data['font_color']) unset($data['font_color']);
                if (!$data['font_size']) unset($data['font_size']);
                array_merge($this->nowConfig, $data);
            } catch (\Exception $e) {
                return true;
            }
        }
        return true;
    }

    public function thumbnailImage(
        $originPicture,
        $scaleSize,
        $savePath = NULL,
        $quality = -1,
        $returnType = 1
    )
    {
        $imageick = new \Imagick();
        $info = $this->originPictureHander($imageick, $originPicture);
        ParamsHandler::handleStart(
            [
                'origin_width' => ['set|int|min:1', $info['width']],
                'origin_height' => ['set|int|min:1', $info['height']],
                'scale_size' => ['set|arr', $scaleSize]
            ]);
        if ($info['suffix'] == 'gif') {
            foreach ($imageick as $frame) {
                $frame->thumbnailImage($scaleSize[0], $scaleSize[1]);
            }
        } else
            $imageick->thumbnailImage($scaleSize[0], $scaleSize[1]);
        return $this->returnHandler($imageick, $savePath, $returnType, $quality);
    }

    public function makeGif($images, $savePath, $delay = 100, $dispose = 2)
    {
        ParamsHandler::handleStart(
            [
                'images' => ['set', $images],
                'path' => ['set', $images],
                'delay' => ['set|int|min:0', $delay],
                'dispose' => ['set|int|min:0', $dispose]
            ]);
        $imageick = NULL;
        if (is_array($images)) {
            $imageick = new \Imagick();
            $tempick = NULL;
            $firstWidth = 0;
            $firstHeight = 0;
            $i = 0;
            foreach ($images as $image) {
                $tempick = new \Imagick();
                if (is_file($image))
                    $tempick->readImage($image);
                elseif (is_string($image))
                    $tempick->readImageBlob($image);
                $this->gifFit($tempick, $i, $firstWidth, $firstHeight);
                $imageick->addImage($tempick);
                $imageick->setImageDelay(100);
                $imageick->setImageDispose(2);
                $i++;
            }
        } elseif (is_string($images)) {
            $imageick = new \Imagick($images);
        } else
            throw new PictureException('图片参数格式错误');
        $imageick->writeImages($savePath, true);
        return $savePath;
    }

    private function gifFit(\Imagick &$image, $index, &$firstWidth, &$firstHeight)
    {
        $info = $image->getImagePage();
        if ($index == 0) {
            $firstWidth = $info['width'];
            $firstHeight = $info['height'];
        }
        $scaleWidth = 0;
        $scaleHeight = 0;
        if ($info['width'] > $firstWidth) {
            $num = $firstWidth / $info['width'];
            $numHeight = (int)($info['height'] * $num);
            if ($numHeight <= $firstHeight) {
                $scaleWidth = $firstWidth;
                $scaleHeight = $numHeight;
            }
        }
        if ($info['height'] > $firstHeight) {
            $num = $firstHeight / $info['height'];
            $numWidth = (int)($info['width'] * $num);
            if ($numWidth <= $firstWidth) {
                $scaleWidth = $numWidth;
                $scaleHeight = $firstHeight;
            }
        }
        if ($scaleWidth != 0 && $scaleHeight != 0) {
            $image->scaleImage($scaleWidth, $scaleHeight);
        }
    }

    public function scale(
        $originPicture,
        $size,
        $savePath = NULL,
        $quality = -1,
        $returnType = 1
    )
    {
        $imageick = new \Imagick();
        $info = $this->originPictureHander($imageick, $originPicture);
        ParamsHandler::handleStart(
            [
                'origin_width' => ['set|int|min:1', $info['width']],
                'origin_height' => ['set|int|min:1', $info['height']],
                'scale_size' => ['set|arr', $size]
            ]);
        if ($info['suffix'] == 'gif') {
            foreach ($imageick as $frame) {
                $frame->scaleImage($size[0], $size[1]);
            }
        } else
            $imageick->scaleImage($size[0], $size[1]);
        return $this->returnHandler($imageick, $savePath, $returnType, $quality);
    }

    public function cut(
        $originPicture,
        $cutSize,
        $position,
        $savePath = NULL,
        $quality = -1,
        $returnType = 1
    )
    {
        $imageick = new \Imagick();
        $info = $this->originPictureHander($imageick, $originPicture);
        ParamsHandler::handleStart(
            [
                'origin_width' => ['set|int|min:1', $info['width']],
                'origin_height' => ['set|int|min:1', $info['height']],
                'cut_size' => ['set|arr|min:0', $cutSize],
                'position' => ['set|arr|position|min:0', $position],
            ]);
        if ($position[0] >= $info['width'] || $position[1] >= $info['height'])
            throw new PictureException('开始截取的位置超出图片范围');
        if (($cutSize[0] + $position[0]) > $info['width'])
            $cutSize[0] = $info['width'] - $position[0];
        if (($cutSize[1] + $position[1]) > $info['height'])
            $cutSize[1] = $info['height'] - $position[1];
        if ($info['suffix'] == 'gif') {
            foreach ($imageick as $frame) {
                $frame->cropImage($cutSize[0], $cutSize[1], $position[0], $position[1]);
            }
        } else
            $imageick->cropImage($cutSize[0], $cutSize[1], $position[0], $position[1]);
        return $this->returnHandler($imageick, $savePath, $returnType, $quality);
    }

    public function rotate(
        $originPicture,
        $angle,
        $savePath = NULL,
        $quality = -1,
        $transparentColor = [],
        $returnType = 1
    )
    {
        $imageick = new \Imagick();
        $info = $this->originPictureHander($imageick, $originPicture);
        ParamsHandler::handleStart(
            [
                'origin_width' => ['set|int|min:1', $info['width']],
                'origin_height' => ['set|int|min:1', $info['height']],
                'angle' => ['set|int', $angle]
            ]);
        $color = $this->makeColor($transparentColor);
        if ($info['suffix'] == 'gif') {
            foreach ($imageick as $frame) {
                $frame->rotateImage($color, $angle);
            }
        } else {
            $expore = explode('.', basename($savePath));
            if ($expore[1] == 'png') $color = new \ImagickPixel('none');
            $imageick->rotateImage($color, $angle);
        }
        return $this->returnHandler($imageick, $savePath, $returnType, $quality);
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
        $backImageick = new \Imagick();
        $frontImageick = new \Imagick();
        $backInfo = $this->originPictureHander($backImageick, $backPicture);
        $frontInfo = $this->originPictureHander($frontImageick, $frontPicture);
        ParamsHandler::handleStart(
            [
                'back_info' => ['set|arr', $backInfo],
                'front_info' => ['set|arr', $frontInfo],
                'position' => ['set|arr|min:0', $position],
            ]);
        if ($backInfo['suffix'] == 'gif') {
            foreach ($backImageick as $frame) {
                $frame->compositeImage(
                    $frontImageick,
                    $frontImageick->getImageCompose(),
                    $position[0],
                    $position[1]);
            }
        } else {
            $compositeType = \Imagick::COMPOSITE_DEFAULT;
            $saveSuffix = explode('.', basename($savePath));
            if ($saveSuffix[1] != "png") $compositeType = \Imagick::COMPOSITE_ATOP;
            $backImageick->compositeImage(
                $frontImageick,
                $compositeType,
                $position[0],
                $position[1]);
        }
        return $this->returnHandler($backImageick, $savePath, $returnType, $quality);
    }

    public function makeIdentifyCodePicture(
        $code,
        $savePath,
        $params = [],
        $quality = -1,
        $returnType = 1
    )
    {
        $defaultCofing = [
            'size' => [100, 100],
            'position' => [20, 30],
            'noise_count' => rand(50, 90),
            'bg_color' => [255, 255, 255],
            'code_color' => [0, 0, 0],
            'color' => [
                [255, 0, 0],
                [0, 255, 0],
                [0, 0, 255],
                [0, 0, 0],
                [0, 255, 255],
                [255, 255, 0],
                [255, 0, 255]
            ],
            'font_size' => rand(13, 18),
            'font_path' => './Attrl.ttl'
        ];
        if (!empty($params)) $defaultCofing = array_merge($defaultCofing, $params);
        ParamsHandler::handleStart([
            'code' => ['set|string', $code],
            'position' => ['set|arr|position', $defaultCofing['position']],
            'noise_count' => ['set|int|min:1', $defaultCofing['noise_count']],
            'color' => ['set|arr', $defaultCofing['color']],
            'bg_color' => ['set|arr', $defaultCofing['bg_color']],
            'code_color' => ['set|arr', $defaultCofing['code_color']],
            'font_size' => ['set|int|min:1', $defaultCofing['font_size']],
            'font_path' => ['set|file', $defaultCofing['font_path']]
        ]);
        $imageIck = new \Imagick();
        $pixel = new \ImagickPixel($this->makeColor($defaultCofing['bg_color']));
        $imageIck->newImage($defaultCofing['size'][0], $defaultCofing['size'][1], $pixel);
        $draw = new \ImagickDraw();
        $draw->setFont($defaultCofing['font_path']);
        $draw->setFontSize($defaultCofing['font_size']);
        $draw->setFillColor(new \ImagickPixel($this->makeColor($defaultCofing['code_color'])));
        $imageIck->annotateImage(
            $draw,
            $defaultCofing['position'][0],
            $defaultCofing['position'][1],
            rand(0, 45),
            $code);
        $colorSize = sizeof($defaultCofing['color']);
        for ($i = 0; $i < $defaultCofing['noise_count']; $i++) {
            $draw->setFillColor(new \ImagickPixel($this->makeColor($defaultCofing['color'][rand(0, $colorSize - 1)])));
            $draw->setFontSize(rand(10, 60));
            $imageIck->annotateImage(
                $draw,
                rand(0, $defaultCofing['size'][0]),
                rand(0, $defaultCofing['size'][1]),
                0,
                '.');
        }
        return $this->returnHandler($imageIck, $savePath, $returnType, $quality);
    }

    public function text(
        $originPicture,
        $position,
        $string,
        $angle = 0,
        $savePath = NULL,
        $quality = -1,
        $returnType = 1,
        $fontSize = NULL,
        $fontFile = NULL,
        $fontColor = NULL
    )
    {
        if (empty($string))
            throw new PictureException('要写入的字符串不能为空');
        $imageick = new \Imagick();
        $info = $this->originPictureHander($imageick, $originPicture);
        ParamsHandler::handleStart(
            [
                'origin_width' => ['set|int|min:1', $info['width']],
                'origin_height' => ['set|int|min:1', $info['height']],
                'angle' => ['set|int', $angle],
            ]);
        if ($fontSize == NULL) $fontSize = $this->nowConfig['font_size'];
        if ($fontFile == NULL) $fontFile = $this->nowConfig['font_file'];
        if ($fontColor == NULL) $fontColor = $this->nowConfig['font_color'];
        $imageDraw = new \ImagickDraw();
        $imageDraw->setFont($fontFile);
        $imageDraw->setFontSize($fontSize);
        $imageDraw->setFillColor(new \ImagickPixel($this->makeColor($fontColor)));
        if ($info['suffix'] == 'gif') {
            foreach ($imageick as $frame) {
                $frame->annotateImage($imageDraw, $position[0], $position[1], $angle, $string);
            }
        } else
            $imageick->annotateImage($imageDraw, $position[0], $position[1], $angle, $string);
        return $this->returnHandler($imageick, $savePath, $returnType, $quality);
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

    private function makeColor($color)
    {
        if (empty($color))
            $color = $this->nowConfig['transparent_color'];
        if (is_array($color)) {
            if (!is_int($color[0]) ||
                !is_int($color[1]) ||
                !is_int($color[2]) ||
                $color[0] < 0 ||
                $color[0] > 255 ||
                $color[1] < 0 ||
                $color[1] > 255 ||
                $color[2] < 0 ||
                $color[2] > 255
            )
                $color = [254, 254, 254];
            $red = dechex($color[0]);
            $green = dechex($color[1]);
            $blue = dechex($color[2]);
            if (strlen($red) < 2) $red = '0' . $red;
            if (strlen($green) < 2) $green = '0' . $green;
            if (strlen($blue) < 2) $blue = '0' . $blue;
            $color = '#' . $red . $green . $blue;
        } else {
            if (strlen($color) < 4 || strlen($color) > 4 || $color{0} != '#') {
                $color = '#eee';
            } else {
                $color = strtolower($color);
                $splitColor = str_split($color, 1);
                unset($splitColor[0]);
                foreach ($splitColor as $lettler) {
                    if ((ord($lettler) >= 97 && ord($lettler) <= 102) ||
                        (ord($lettler) >= 48 && ord($lettler) <= 57)
                    ) {
                    } else {
                        $color = '#eee';
                        break;
                    }
                }
            }
        }
        return $color;
    }

    private function returnHandler(\Imagick &$imageick, $savePath, $type, $quality = 100)
    {
        if ($type == PictureFactory::RETURN_PATH) {
            $name = '';
            $suffix = '';
            MachineHandler::getPictureNameAndsuffix($savePath, $name, $suffix);
            if (!$this->supportPicture($suffix)) throw new PictureException('不支持此保存文件的类型');
            if (empty($name)) throw new PictureException('文件保存名字不能为空');
            $quality = $quality < 1 ? 100 : $quality;
            $imageick->setImageCompressionQuality($quality);
            $explor = explode('.', basename($savePath));
            if ($explor[1] == 'gif')
                $imageick->writeImages($savePath, true);
            else
                $imageick->writeImage($savePath);
            return $savePath;
        } elseif ($type == PictureFactory::RETURN_IMG_STRING) {
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
            try {
                $imageick->readImageBlob($originPicture);
                $info = $imageick->getImagePage();
                $suffix = $imageick->getFormat();
                if (!$this->supportPicture($suffix))
                    throw new PictureException('当前不支持此文件类型');
            } catch (\Exception $e) {
                throw new PictureException('图片字符串错误');
            }
        } else {
            throw new PictureException('当前不支持此类型文件');
        }
        $info['suffix'] = $suffix;
        $info['name'] = $name;
        return $info;
    }
}
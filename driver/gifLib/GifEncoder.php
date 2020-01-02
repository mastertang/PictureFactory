<?php
/*
 [gif图片具体Byte结构]
 BYTE   7 6 5 4 3 2 1 0 BIT
 1				G
 2				I
 3				F
 4				8
 5				9
 6				a

 7    	  逻辑屏幕宽度
 8	      逻辑屏幕宽度
 9	  		  高度
 10           高度
 11       m cr s pixel
 12         背景色
 13         宽高比

 14   		  r
 15   		  g
 16   		  b
             ...

 22   	   扩展块标识
 23        扩展标签
 24          块大小
 25      保留 处置方法 i t
 26   	 	 延时
 27   		 延时
 28   	  透明色索引
 29   	    块终结

 30   	   图像标识
 31   		x偏移
 32   		x偏移
 33   		y偏移
 34   		y偏移
 35   		图宽
 36   		图高
 37     m i s r pixel

 38   		r
 39   		g
 40   		b
 		   ...

 52  	 LZW编码长度
     		...
     	  数据块
     		...

 80  	 gif结尾
 */

namespace PictureFactory\driver\gifLib;

/**
 * Class GifEncoder
 * @package PictureFactory\driver\gifLib
 */
Class GifEncoder
{
    private $disposalMethod = 2;//处置方法 0 1 2 3 四种
    private $gifHeader      = "GIF89a";
    private $delayTime      = [];
    private $picture        = [];
    private $offest         = [];
    private $color          = -1;
    private $loop           = 0;
    private $SIG            = 0;

    private $newGifDinary = NULL;

    /**
     * GifEncoder constructor.
     * @param $resource
     * @param $delayTime
     * @param $loopFlag
     * @param $disposalMethod
     * @param $offest
     * @param $color
     */
    function __construct(
        &$resource,
        $delayTime,
        $loopFlag,
        $disposalMethod,
        $offest,
        $color
    )
    {
        $resourceSize = sizeof($resource);
        if (!is_array($offest)) $offest = [$offest];
        if (!is_array($delayTime)) $delayTime = [$delayTime];
        for ($i = 0; $i < $resourceSize; $i++) {
            if (!isset($offest[$i])) $offest[$i] = $offest[0];
            if (!isset($delayTime[$i])) $delayTime[$i] = $delayTime[0];
        }
        $loopFlag > -1 or $loopFlag = 0;//循环播放次数，$_loopFlag大于-1为$_loopFlag,小于则为0
        (is_int($disposalMethod) && $disposalMethod >= 0 && $disposalMethod <= 3) or $disposalMethod = 0;//处置方法的值为0，1，2，3
        (is_int($color[0]) && $color[0] >= 1 && $color[0] <= 255) or $color[0] = 254;
        (is_int($color[1]) && $color[1] >= 1 && $color[1] <= 255) or $color[1] = 254;
        (is_int($color[2]) && $color[2] >= 1 && $color[2] <= 255) or $color[2] = 254;
        $this->color          = chr($color[0]) . chr($color[1]) . chr($color[2]);
        $this->disposalMethod = $disposalMethod;
        $this->loop           = $loopFlag;
        $this->delayTime      = $delayTime;
        $this->offest         = $offest;
        $this->picture        = $resource;
        unset($offest);
        unset($delayTime);
    }

    /**
     *
     */
    public function encodeStart()
    {
        $pictureSize        = sizeof($this->picture);
        $newGifHead         = $this->gifHeader;
        $firstPictureBinary = NULL;
        $globalColorSize    = 0;
        $globalColorTable   = '';
        $globalColorPixel   = 0;
        GifEncoder::addHeader(//创建文件头部
            $firstPictureBinary,
            $newGifHead,
            $globalColorSize,
            $globalColorTable,
            $globalColorPixel
        );//添加头部
        for ($i = 0; $i < $pictureSize; $i++) {//添加头像信息
            GifEncoder::addFrames(
                $i,
                $globalColorSize,
                $globalColorTable,
                $newGifHead,
                $globalColorPixel
            );
        }
        GifEncoder::addEndBlock($newGifHead);//添加结尾
        $this->newGifDinary = $newGifHead;
    }

    /**
     * @param $firstPictureBinary
     * @param $newGifHead
     * @param $globalColorSize
     * @param $globalColorTable
     * @param $globalColorPixel
     */
    function addHeader(
        &$firstPictureBinary,
        &$newGifHead,
        &$globalColorSize,
        &$globalColorTable,
        &$globalColorPixel
    )
    {
        $firstPictureBinary = fread(fopen($this->picture[0], "rb"), filesize($this->picture[0]));
        $globalColorPixel   = ord($firstPictureBinary{10}) & 0x07;
        $globalColorSize    = 2 << $globalColorPixel;//获取全局颜色数目
        $globalColorTable   = substr($firstPictureBinary, 13, 3 * $globalColorSize);//获取全局颜色列表
        $cmap               = 0;
        if (ord($firstPictureBinary{10}) & 0x80) {//检查是否有全局颜色列表
            $cmap       = 3 * (2 << (ord($firstPictureBinary{10}) & 0x07));
            $newGifHead .= substr($firstPictureBinary, 6, 7);//添加逻辑屏幕标识块
            $newGifHead .= substr($firstPictureBinary, 13, $cmap);//添加全局颜色列表
            $newGifHead .= "!\377\13NETSCAPE2.0\3\1" . GifEncoder::gifWord($this->loop) . "\0";//添加扩展块
        }
    }

    /**
     * @param $index
     * @param $globalColorSize
     * @param $globalColorTable
     * @param $gifHeader
     * @param $globalColorPixel
     * @return null
     */
    function addFrames(
        $index,
        &$globalColorSize,
        &$globalColorTable,
        &$gifHeader,
        &$globalColorPixel
    )
    {
        $pictureHandler = fopen($this->picture[$index], 'rb');
        $pictureBuffer  = fread($pictureHandler, filesize($this->picture[$index]));
        fclose($pictureHandler);
        $headInfo = substr($pictureBuffer, 0, 6);
        if ($headInfo != "GIF87a" && $headInfo != "GIF89a")
            return NULL;//检查文件的头信息的前6byte是否为GIF87a或GIF89a，不是就代表不是gif文件
        $localColorPixel             = ord($pictureBuffer{10}) & 0x07;
        $localColorSize              = 2 << $localColorPixel;//获取局部颜色数目
        $localColorTable             = substr($pictureBuffer, 13, 3 * $localColorSize); //获取此图片的颜色数目
        $localColorTableEndPostition = 13 + 3 * $localColorSize;//找到每个图片颜色列表后的第一个位置
        $localColorTableLength       = strlen($pictureBuffer) - $localColorTableEndPostition - 1;//获取图像出去头文件，屏幕标识块，颜色列表后的内容的长度
        $localPictureContent         = substr($pictureBuffer, $localColorTableEndPostition, $localColorTableLength);//获取图像内容
        $localColorTableFlag         = ord($pictureBuffer{10}) & 0x80;
        /* 设置扩展块，！为标识符，F9为扩展标签，04块大小固定值
            ，chr( ( $this->DIS << 2 ) + 0）设置处置方法
            ,chr ( ( $_delayTime >> 0 ) & 0xFF ) . chr ( ( $_delayTime >> 8 ) & 0xFF )设置延时时间
            ,\x0颜色索引
           ，\x0块终结
        */
        $this->makeTransparentColor(
            $index,
            $localExtensionBlock,
            $localColorTableFlag,
            $localColorTable);
        $localImageData = '';
        switch ($localPictureContent{0}) {
            case "!":    //如果一开始就是扩展块就进行相应的截取
                $localImageData      = substr($localPictureContent, 8, 10);
                $localPictureContent = substr($localPictureContent, 18, strlen($localPictureContent) - 18);
                break;
            case ",":    //如果一开始是图像标识块
                $localImageData      = substr($localPictureContent, 0, 10);
                $localPictureContent = substr($localPictureContent, 10, strlen($localPictureContent) - 10);
                break;
            default:
                break;
        }
        $this->colorTable(
            $index,
            $gifHeader,
            $localImageData,
            $localPictureContent,
            $localColorTableFlag,
            $localExtensionBlock,
            $localColorSize,
            $localColorTable,
            $localColorPixel,
            $globalColorSize,
            $globalColorPixel,
            $globalColorTable
        );
    }

    /**
     * @param $index
     * @param $gifHeader
     * @param $localImageData
     * @param $localPictureContent
     * @param $localColorTableFlag
     * @param $localExtensionBlock
     * @param $localColorSize
     * @param $localColorTable
     * @param $localColorPixel
     * @param $globalColorSize
     * @param $globalColorPixel
     * @param $globalColorTable
     */
    private function colorTable(
        &$index,
        &$gifHeader,
        &$localImageData,
        &$localPictureContent,
        &$localColorTableFlag,
        &$localExtensionBlock,
        &$localColorSize,
        &$localColorTable,
        &$localColorPixel,
        &$globalColorSize,
        &$globalColorPixel,
        &$globalColorTable
    )
    {
        //如果有颜色列表，且 图像不为空
        if ($localColorTableFlag) {
            $lengthResult = $globalColorSize == $localColorSize ? true : false;
            $tableResult  = GifEncoder::colorTableBlockCompare($globalColorTable, $localColorTable, $globalColorSize);
            if ($lengthResult && $tableResult) {
                $gifHeader .= $localExtensionBlock . $localImageData . $localPictureContent;
            } else if (!$lengthResult || ($lengthResult && !$tableResult)) {
                if ($this->SIG == 1) {//设置图像标识符的x，y方向偏移
                    $localImageData{1} = chr($this->offest[$index][0] & 0xFF);
                    $localImageData{2} = chr(($this->offest[$index][0] & 0xFF00) >> 8);
                    $localImageData{3} = chr($this->offest[$index][1] & 0xFF);
                    $localImageData{4} = chr(($this->offest[$index][1] & 0xFF00) >> 8);
                }
                $byte = ord($localImageData{9});//图像标识块的第10个byte(m i s r pixel)
                $byte |= 0x80;//将m置为1
                $byte &= 0xF8;//将pixel置为000
                if (!$lengthResult)
                    $byte |= $globalColorPixel;//设置pixel为全局颜色列表的pixel值
                else
                    $byte |= $localColorPixel;//设置pixel为局部颜色列表的pixel值
                $localImageData{9} = chr($byte);
                $gifHeader         .= $localExtensionBlock . $localImageData . $localColorTable . $localPictureContent;
            }
        } else {//没有颜色列表就直接添加了
            $gifHeader .= $localExtensionBlock . $localImageData . $localPictureContent;
        }
    }

    /**
     * @param $index
     * @param $localExtensionBlock
     * @param $localColorTableFlag
     * @param $localColorTable
     */
    private function makeTransparentColor(
        &$index,
        &$localExtensionBlock,
        &$localColorTableFlag,
        &$localColorTable
    )
    {
        $char1               = $this->disposalMethod << 2;
        $char2               = chr(($this->delayTime[$index] >> 0) & 0xFF);
        $char3               = chr(($this->delayTime[$index] >> 8) & 0xFF);
        $localExtensionBlock = "!\xF9\x04" . chr($char1 + 0) . $char2 . $char3 . "\x0\x0";
        if (isset($this->color) && $localColorTableFlag) {//如果有透明颜色就设置拓展块的透明颜色索引,$this->mColor为透明颜色的索引值
            $localSpilt = str_split($localColorTable, 3);
            $spiltSize  = sizeof($localSpilt);
            for ($i = 0; $i < $spiltSize; $i++) {
                if ($localSpilt[$i] == $this->color) {
                    $localExtensionBlock = "!\xF9\x04" . chr(($char1 + 1)) . $char2 . $char3 . chr($i) . "\x0";
                    break;
                }
            }
        }
    }

    /**
     * @param $gifHeader
     */
    private function addEndBlock(&$gifHeader)
    {
        $gifHeader .= ";";
    }

    /**
     * @param $globalColorTableBlock
     * @param $localColorTableBlock
     * @param $length
     * @return bool
     */
    private function colorTableBlockCompare(
        $globalColorTableBlock,
        $localColorTableBlock,
        $length
    )
    {
        $gbSplit = str_split($globalColorTableBlock, 3);
        $loSplit = str_split($localColorTableBlock, 3);
        $result  = true;
        for ($i = 0; $i < $length; $i++) {
            if ($gbSplit[$i] != $loSplit[$i]) {
                $result = false;
                break;
            }
        }
        unset($gbSplit);
        unset($loSplit);
        return $result;
    }

    /**
     * @return null
     */
    public function getAnimation()
    {
        return $this->newGifDinary;
    }

    /**
     * @param $_int
     * @return string
     */
    private function gifWord($_int)
    {
        return (chr($_int & 0xFF) . chr(($_int >> 8) & 0xFF));
    }
}
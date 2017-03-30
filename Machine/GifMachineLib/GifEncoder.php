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
namespace PictureFactory\Machine\GifMachineLib;

Class GifEncoder
{
    private $gifHeader = "GIF89a";
    private $mVersion = "GIF Encode V1.0";
    private $picture = [];
    private $Offest = [];
    private $SIG = 0;
    private $loop = 0;
    private $disposalMethod = 2;//处置方法 0 1 2 3 四种
    private $color = -1;
    private $mImage = -1;
    private $delayTime = [];

    /**
     * [构造函数]
     * @param [array]  $_resource            [文件路径]
     * @param [array]  $_delayTime            [延时时间]
     * @param [String] $_loopFlag            [循环次数]
     * @param [String] $_disposalMethod        [处置方法]
     * @param [String] $_redColor            [红颜色值]
     * @param [String] $_greenColor            [绿颜色值]
     * @param [String] $_blueColor            [蓝颜色值]
     * @param [array]  $_offest                [图片偏移]
     */
    function __construct(&$resource, &$delayTime, &$loopFlag, &$disposalMethod, &$offest, &$color)
    {
        $this->picture = $resource;
        $resourceSize = sizeof($this->picture);
        if (is_array($_offest) && count($_offest) > 1) {            //检查图片位移变量是否是数组且不空
            $this->SIG = 1;
            $this->mPictureOffest = $_offest;
        }
        $this->mDelayTime = $_delayTime;
        $this->mLoopPlay = $_loopFlag === false ? false : (($_loopFlag > -1) ? $_loopFlag : 0);                //检查是否循环播放，false为false,$_loopFlag大于-1为$_loopFlag,小于则为0
        $this->mDisposalMethod = ($_disposalMethod > -1) ? (($_disposalMethod < 3) ? $_disposalMethod : 3) : 2;    //处置方法的值为0，1，2，3
        $this->mColor = ($_redColor > -1 && $_greenColor > -1 && $_blueColor > -1) ?                                    //颜色的值都必须大于1，
            ($_redColor | ($_greenColor << 8) | ($_blueColor << 16)) : -1;

        for ($i = 0; $i < count($_resource); $i++) {
            if (file_exists($_resource[$i])) {    //$_resource装的是文件路径
                $this->mPictureBuffer[] = fread(fopen($_resource [$i], "rb"), filesize($_resource [$i]));
            } else {                                //不然就处理下一张
                continue;
            }

            //检查文件的头信息的前6byte是否为GIF87a或GIF89a，不是就代表不是gif文件
            if (substr($this->mPictureBuffer[$i], 0, 6) != "GIF87a" && substr($this->mPictureBuffer[$i], 0, 6) != "GIF89a") {
                $this->mErrorIndex = empty($this->mErrorIndex) ? $this->mErrorIndex : 'ERRO1';
            }

            //检查是否有扩展块，$j的开始值为全局颜色列表的后一个byte位置，13代表GIF头信息6byte+逻辑屏幕块7byte，
            //$this->BUF[$i]{10}代表第$i张图片的第10个byte，就是逻辑屏幕块的第5byte（m cr s pixel），& 0x07就是将高5位(m cr s)都置零,获得pixel的值，因为pixel代表有多少个颜色，因为每个颜色有rgb三个颜色排列而成，所以乘以3
            $position = 13 + 3 * (2 << (ord($this->mPictureBuffer[$i]{10}) & 0x07));
            for ($j = $position, $k = TRUE; $k; $j++) {
                switch ($this->mPictureBuffer[$i]{$j}) {
                    case "!":    //扩展块的标识，1byte，固定我0x21，00100001，ASCII：33，对应字符：！
                        if ((substr($this->mPictureBuffer[$i], ($j + 3), 8)) == "NETSCAPE") {
                            $this->mErrorIndex = empty($this->mErrorIndex) ? $this->mErrorIndex : 'ERRO3';
                        }
                        break;
                    case ",":    //图像信息块标识，1byte，固定0x2C，00101100，ASCII：44,字符:,
                        $k = FALSE;
                        break;
                }
            }
        }
    }

    /**
     * [开始编码]
     */
    public function encodeStart()
    {
        if (!empty($this->mErrorIndex)) {
            return $this->mErrorInfo[$this->mErrorIndex];
        }

        try {
            GifEncoder::addHeader();                                        //添加头部
            for ($i = 0; $i < count($this->mPictureBuffer); $i++) {        //添加头像信息
                GifEncoder::addFrames($i, $this->mDelayTime[$i]);
            }
            GifEncoder::addEndBlock();                                        //添加结尾
        } catch (Exception $e) {
            return $this->mErrorInfo['ERRO4'];
        }

        return $this->mSucessInfo['SUC00'];
    }

    /**
     * [为gif添加头部信息]
     */
    function addHeader()
    {
        $cmap = 0;
        if (ord($this->mPictureBuffer[0]{10}) & 0x80) {                            //检查是否有全局颜色列表
            $cmap = 3 * (2 << (ord($this->mPictureBuffer[0]{10}) & 0x07));
            $this->mGifHeader .= substr($this->mPictureBuffer[0], 6, 7);            //添加逻辑屏幕标识块
            $this->mGifHeader .= substr($this->mPictureBuffer[0], 13, $cmap);        //添加全局颜色列表
            if ($this->mLoopPlay !== false)                                            //如果是循环播放
            {
                $this->mGifHeader .= "!\377\13NETSCAPE2.0\3\1" . GifEncoder::gifWord($this->mLoopPlay) . "\0";        //添加扩展块
            }
        }
    }

    /**
     * [添加图片信息内容]
     * @param [int]        $_pictureIndex    [图像数组的索引值]
     * @param [array]    $_delayTime        [此图片的显示延时时间]
     */
    function addFrames($_pictureIndex, $_delayTime)
    {
        $globalColorPixel = ord($this->mPictureBuffer[0]{10}) & 0x07;
        $localColorPixel = ord($this->mPictureBuffer[$_pictureIndex]{10}) & 0x07;
        $globalColorSize = 2 << $globalColorPixel;                                                       //获取全局颜色数目
        $localColorSize = 2 << $localColorPixel;                                                        //获取局部颜色数目

        $globalColorTable = substr($this->mPictureBuffer[0], 13, 3 * $globalColorSize);             //获取全局颜色列表
        $localColorTable = substr($this->mPictureBuffer[$_pictureIndex], 13, 3 * $localColorSize); //获取此图片的颜色数目

        $localColorTableEndPostition = 13 + 3 * $localColorSize;                                           //找到每个图片颜色列表后的第一个位置
        $localColorTableLength = strlen($this->mPictureBuffer[$_pictureIndex]) - $localColorTableEndPostition - 1;                        //获取图像出去头文件，屏幕标识块，颜色列表后的内容的长度
        $localPictureContent = substr($this->mPictureBuffer[$_pictureIndex], $localColorTableEndPostition, $localColorTableLength);   //获取图像内容

        $localColorTableFlag = ord($this->mPictureBuffer[$_pictureIndex]{10}) & 0x80;
        /* 设置扩展块，！为标识符，F9为扩展标签，04块大小固定值
            ，chr( ( $this->DIS << 2 ) + 0）设置处置方法
            ,chr ( ( $_delayTime >> 0 ) & 0xFF ) . chr ( ( $_delayTime >> 8 ) & 0xFF )设置延时时间
            ,\x0颜色索引
           ，\x0块终结
        */
        $localExtensionBlock = "!\xF9\x04" . chr(($this->mDisposalMethod << 2) + 0) .
            chr(($_delayTime >> 0) & 0xFF) . chr(($_delayTime >> 8) & 0xFF) . "\x0\x0";

        if ($this->mColor > -1 && $localColorTableFlag) {//如果有透明颜色就设置拓展块的透明颜色索引,$this->mColor为透明颜色的索引值
            $colorShift16 = ($this->mColor >> 16) & 0xFF;
            $colorShift8 = ($this->mColor >> 8) & 0xFF;
            $colorShift0 = ($this->mColor >> 0) & 0xFF;
            for ($j = 0; $j < $localColorSize; $j++) {
                if (
                    ord($localColorTable{3 * $j + 0}) == $colorShift16 &&
                    ord($localColorTable{3 * $j + 1}) == $colorShift8 &&
                    ord($localColorTable{3 * $j + 2}) == $colorShift0
                ) {
                    $localExtensionBlock = "!\xF9\x04" . chr(($this->mDisposalMethod << 2) + 1) .
                        chr(($_delayTime >> 0) & 0xFF) . chr(($_delayTime >> 8) & 0xFF) . chr($j) . "\x0";
                    break;
                }
            }
        }
        switch ($localPictureContent{0}) {
            case "!":    //如果一开始就是扩展块就进行相应的截取
                $localImageData = substr($localPictureContent, 8, 10);
                $localPictureContent = substr($localPictureContent, 18, strlen($localPictureContent) - 18);
                break;
            case ",":    //如果一开始是图像标识块
                $localImageData = substr($localPictureContent, 0, 10);
                $localPictureContent = substr($localPictureContent, 10, strlen($localPictureContent) - 10);
                break;
        }

        //如果有颜色列表，且 图像不为空
        if ($localColorTableFlag && $this->mImage > -1) {
            if ($globalColorSize == $localColorSize) {//如果全局颜色列表长度 == 局部颜色列表长度

                if (GifEncoder::colorTableBlockCompare($globalColorTable, $localColorTable, $globalColorSize)) {//如果全局颜色列表等于局部颜色列表
                    $this->mGifHeader .= ($localExtensionBlock . $localImageData . $localPictureContent);
                } else {
                    if ($this->SIG == 1) {//设置图像标识符的x，y方向偏移
                        $localImageData{1} = chr($this->mPictureOffest[$_pictureIndex][0] & 0xFF);
                        $localImageData{2} = chr(($this->mPictureOffest[$_pictureIndex][0] & 0xFF00) >> 8);
                        $localImageData{3} = chr($this->mPictureOffest[$_pictureIndex][1] & 0xFF);
                        $localImageData{4} = chr(($this->mPictureOffest[$_pictureIndex][1] & 0xFF00) >> 8);
                    }
                    $byte = ord($localImageData{9});    //图像标识块的第10个byte(m i s r pixel)
                    $byte |= 0x80;                        //将m置为1
                    $byte &= 0xF8;                        //将pixel置为000
                    $byte |= $globalColorPixel;              //设置pixel为全局颜色列表的pixel值
                    $localImageData{9} = chr($byte);
                    $this->mGifHeader .= ($localExtensionBlock . $localImageData . $localColorTable . $localPictureContent);
                }
            } else {                        //如果全局颜色列表长度不等于局部颜色列表
                if ($this->SIG == 1) {//设置图像标识符的x，y方向偏移
                    $localImageData{1} = chr($this->mPictureOffest[$_pictureIndex][0] & 0xFF);
                    $localImageData{2} = chr(($this->mPictureOffest[$_pictureIndex][0] & 0xFF00) >> 8);
                    $localImageData{3} = chr($this->mPictureOffest[$_pictureIndex][1] & 0xFF);
                    $localImageData{4} = chr(($this->mPictureOffest[$_pictureIndex][1] & 0xFF00) >> 8);
                }
                $byte = ord($localImageData{9});    //图像标识块的第10个byte(m i s r pixel)
                $byte |= 0x80;                        //将m置为1
                $byte &= 0xF8;                        //将pixel置为000
                $byte |= $localColorPixel;//设置pixel为局部颜色列表的pixel值
                $localImageData{9} = chr($byte);
                $this->mGifHeader .= ($localExtensionBlock . $localImageData . $localColorTable . $localPictureContent);
            }
        } else {//没有颜色列表就直接添加了
            $this->mGifHeader .= ($localExtensionBlock . $localImageData . $localPictureContent);
        }
        $this->mImage = 1;
    }

    /**
     * [添加结束块]
     */
    function addEndBlock()
    {
        $this->mGifHeader .= ";";
    }

    /**
     * [合成颜色列表]
     * @param  [String] $_globalColorTableBlock        [全局颜色列表二进制]
     * @param  [String] $_localColorTableBlock        [局部颜色列表二进制]
     * @param  [int]    $_length                    [颜色数量长度]
     * @return [int]                                [1代表合成成功，0代表失败]
     */
    function colorTableBlockCompare($_globalColorTableBlock, $_localColorTableBlock, $_length)
    {
        for ($i = 0; $i < $_length; $i++) {
            if (//如果全局颜色列表每一个rgb都等于局部颜色列表
                $_globalColorTableBlock{3 * $i + 0} != $_localColorTableBlock{3 * $i + 0} ||
                $_globalColorTableBlock{3 * $i + 1} != $_localColorTableBlock{3 * $i + 1} ||
                $_globalColorTableBlock{3 * $i + 2} != $_localColorTableBlock{3 * $i + 2}
            ) {
                return (0);
            }
        }
        return (1);
    }

    /**
     * [根据参数设置gif字符]
     * @param  [int]     $_int [数字]
     * @return [string]        [字符]
     */
    function gifWord($_int)
    {
        return (chr($_int & 0xFF) . chr(($_int >> 8) & 0xFF));
    }

    /**
     * [返回gif图片信息]
     * @return [string] [gif二进制内容]
     */
    function getAnimation()
    {
        return ($this->mGifHeader);
    }
}
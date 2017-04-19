## 图片处理工厂

#### 说明
* 该包包括图片的缩放,裁剪,旋转,合成,缩略图生成,验证码图片生成,gif图片生成,人脸识别功能。
* 使用了GD库,imagemagick扩展,face_detector扩展，你可以选择使用且安装GD，imagemagick其中的一个扩展。
  而face_detector扩展用于人脸识别功能，且此扩展是本人开发。详细请到:https://github.com/mastertang/Facedetector

#### 内容
##### 异常
* 包异常类文件是PictureException.php , 继承\Exception基类
* 此包对于大多错误，参数格式要求错误等问题都会以抛异常处理，请做好try catch工作，方便调试代码
    
##### 接口
######  PictureFactory类
* factoryRun($machineName = NULL) 工程运行
````
    $machineName = 默认调用GD库，输入Gd调用GD库，输入Imagick调用ImageMageick库
    返回 库类实例
````    
* swiftMachine($machineName = NULL) 切换工作机器
````
    $machineName = 默认调用GD库，输入Gd调用GD库，输入Imagick调用ImageMageick库
    返回 库类实例
````  
* faceDetector($xmlPath, $pictureData) 静态函数,人脸识别
```` 
   $xmlPath = xml文件路径
   $pictureData = 图片数据或路径
   返回 数组
   (具体详细到face_detector库链接中的说明中了解) 
````
* instance() 返回创建的工作机器实例

###### 函数返回说明
````
    每个函数都有可选的returnType设置：
    其值为： RETURN_PATH = 1
            RETURN_RES = 2
            RETURN_IMG_STRING = 3
    其默认值为1 , 1表示返回保存的路径 , 2表示返回图片资源 , 3表示返回图片二进制数据        
````

###### GD
调用方式 PictureFactory->instance()->func(...)
返回类型有三种 保存路径,资源,图片数据  
* scale(  
      $originPicture,$size,$savePath = NULL,  
      $quality = -1, $returnType = 1)
````
    说明 : 图片缩放
    $originPicture = 图片数据,可以是图片路径，也可以是图片资源resource
    $size = 缩放大小,可以是int型 0.5，即缩放为原来尺寸的1/2;也可以是数组[100,150]，第一个参数是宽，第二参数是高
    $savePath = 图片保存路径，根据returnType选择是否输入,如果选择返回资源，可以不输入
    $quality = 图片保存质量,jpg的值范围1-100,png值范围0-9
    $returnType = 返回数据类型
    $transparentColor = 透明颜色值,对于png可能需要设置此值,例子: 数组:[r,g,b]= [0-255,0-255,0-255],默认[254,254,254]
````
* thumbnailImage(   
               $originPicture,$scaleSize,$picQuality = -1,  
               $savePath = NULL,$returnType = 1,$transparentColor = [])
````
    说明 : 生成图片缩略图
    $originPicture = 图片数据,可以是图片路径，也可以是图片资源resource
    $scaleSize = 缩放大小,可以是int型 0.5，即缩放为原来尺寸的1/2;也可以是数组[100,150]，第一个参数是宽，第二参数是高
    $savePath = 图片保存路径，根据returnType选择是否输入,如果选择返回资源，可以不输入
    $picQuality = 图片保存质量,jpg的值范围1-100,png值范围0-9
    $returnType = 返回数据类型
    $transparentColor = 透明颜色值,对于png可能需要设置此值,例子: 数组:[r,g,b]= [0-255,0-255,0-255],默认[254,254,254]
````   
* makeGif($images, $savePath , $tempPath='')
````
    说明 : 生成gif图片
    $images = 数组,gif图片素材,元素可以是资源resource,也可以是图片路径
    $savePath = gif图片保存路径
    $tempPath = 临时文件存储路径
````
* composition(    
            $backPicture,$frontPicture,$position,    
            $savePath = NULL,$quality = -1,$returnType = 1) 
````
    说明 : 合成图片
    $backPicture = 背景图片,图片数据,可以是图片路径，也可以是图片资源resource
    $frontPicture = 需合成图片,图片数据,可以是图片路径，也可以是图片资源resource
    $position = 合成图片的位置,例 : [x,y] = [100,100] 
    $savePath = 图片保存路径，根据returnType选择是否输入,如果选择返回资源，可以不输入
    $quality = 图片保存质量,jpg的值范围1-100,png值范围0-9
    $returnType = 返回数据类型
````
* cut(    
    $originPicture,$cutSize,$poition,$savePath = NULL,    
    $quality = -1,$returnType = 1,$transparentColor = [])
````
    说明 : 裁剪图片
    $originPicture = 图片数据,可以是图片路径，也可以是图片资源resource
    $cutSize = 需要裁剪的尺寸,例 : [宽,高] = [200,200]
    $position = 裁剪的开始位置,例 : [x,y] = [100,100] 
    $savePath = 图片保存路径，根据returnType选择是否输入,如果选择返回资源，可以不输入
    $quality = 图片保存质量,jpg的值范围1-100,png值范围0-9
    $returnType = 返回数据类型
    $transparentColor = 透明颜色值,对于png可能需要设置此值,例子: 数组:[r,g,b]= [0-255,0-255,0-255],默认[254,254,254]
````
*  rotate(    
        $originPicture,$angle,$savePath = NULL,$quality = -1,    
        $returnType = 1,$transparentColor = [])
````
    说明 : 旋转图片
    $originPicture = 图片数据,可以是图片路径，也可以是图片资源resource
    $angle = 旋转的角度,可以是正负数,例 90度 = 90
    $savePath = 图片保存路径，根据returnType选择是否输入,如果选择返回资源，可以不输入
    $quality = 图片保存质量,jpg的值范围1-100,png值范围0-9
    $returnType = 返回数据类型
    $transparentColor = 透明颜色值,对于png可能需要设置此值,例子: 数组:[r,g,b]= [0-255,0-255,0-255],默认[254,254,254]
````
* text(    
     $originPicture,$position,$string,$angle = 0,    
     $savePath = NULL,$fontFile = NULL,$fontSize = NULL,    
     $fontColor = NULL,$quality = -1,$returnType = 1)
````
    说明 : 在图片上添加文字
    $originPicture = 图片数据,可以是图片路径，也可以是图片资源resource
    $position = 文字开始位置,例 [x,y] = [100,100]
    $string = 要添加的字符串
    $angle = 文字角度,可以是正负数,例 90度 = 90
    $savePath = 图片保存路径，根据returnType选择是否输入,如果选择返回资源，可以不输入
    $fontFile = 字体路径
    $fontSize = 字体大小,>=1
    $fontColor = 字体颜色,例子: 数组:[r,g,b]= [0-255,0-255,0-255]
    $quality = 图片保存质量,jpg的值范围1-100,png值范围0-9
    $returnType = 返回数据类型
````
* makeIdentifyCodePicture(    
        $code,$savePath,$params = [],    
        $returnType = 1,$quality = 100)
````
    说明 : 生成验证码图片
    $code = 验证码
    $position = 文字开始位置,例 [x,y] = [100,100]
    $savePath = 图片保存路径，根据returnType选择是否输入,如果选择返回资源，可以不输入
    $quality = 图片保存质量,jpg的值范围1-100,png值范围0-9
    $returnType = 返回数据类型
    $params = 数组,一些验证码配置,例 : [
        'size' => [100, 100],   (验证码图片的尺寸)
        'position' => [20, 30],   (验证码文字的开始位置)
        'noise_count' => rand(10, 20),   (图片干扰点的数量)
        'color' => [    (干扰点需要用到的颜色集合,随机用到这些颜色)
            [255, 0, 0],
            [0, 255, 0],
            [0, 0, 255],
            [0, 0, 0],
            [0, 255, 255],
            [255, 255, 0],
             255, 0, 255]],
        'font_size' => rand(13, 18),    (文字大小)
        'font_path' => './Attrl.ttl'    (文字文件路径)
    ]
````        
###### ImageMagick
调用方式 PictureFactory->instance()->func(...)   
返回类型有两种 保存路径,图片数据    
* scale(  
      $originPicture,$size,$savePath = NULL,  
      $quality = -1, $returnType = 1)
````
    说明 : 图片缩放
    $originPicture = 图片数据,可以是图片路径，也可以是图片数据
    $size = 缩放尺寸,数组[100,150]，第一个参数是宽，第二参数是高
    $savePath = 图片保存路径，根据returnType选择是否输入,如果选择返回数据，可以不输入
    $quality = 图片保存质量,jpg的值范围1-100,png值范围0-9
    $returnType = 返回数据类型
````
* thumbnailImage(   
               $originPicture,$scaleSize,      
               $quality = -1,$savePath = NULL,$returnType = 1)
````
    说明 : 生成图片缩略图
    $originPicture = 图片数据,可以是图片路径，也可以是图片数据
    $scaleSize = 缩放大小,可以是int型 0.5，即缩放为原来尺寸的1/2;也可以是数组[100,150]，第一个参数是宽，第二参数是高
    $savePath = 图片保存路径，根据returnType选择是否输入,如果选择返回数据，可以不输入
    $quality = 图片保存质量,jpg的值范围1-100,png值范围0-9
    $returnType = 返回数据类型
````   
* makeGif($images, $savePath , $delay = 100 , $dispose = 2)
````
    说明 : 生成gif图片
    $images = 数组,gif图片素材,元素可以是资源resource,也可以是图片路径
    $savePath = gif图片保存路径
    $delay = 图片播放间隔
    $dispose = 图片处理方式
````
* composition(    
            $backPicture,$frontPicture,$position,    
            $savePath = NULL,$quality = -1,$returnType = 1) 
````
    说明 : 合成图片
    $backPicture = 背景图片,图片数据,可以是图片路径，也可以是图片数据
    $frontPicture = 需合成图片,图片数据,可以是图片路径，也可以是图片数据
    $position = 合成图片的位置,例 : [x,y] = [100,100] 
    $savePath = 图片保存路径，根据returnType选择是否输入,如果选择返回数据，可以不输入
    $quality = 图片保存质量,jpg的值范围1-100,png值范围0-9
    $returnType = 返回数据类型
````
* cut(    
    $originPicture,$cutSize,$poition,$savePath = NULL,    
    $quality = -1,$returnType = 1,$transparentColor = [])
````
    说明 : 裁剪图片
    $originPicture = 图片数据,可以是图片路径，也可以是图片数据
    $cutSize = 需要裁剪的尺寸,例 : [宽,高] = [200,200]
    $position = 裁剪的开始位置,例 : [x,y] = [100,100] 
    $savePath = 图片保存路径，根据returnType选择是否输入,如果选择返回数据，可以不输入
    $quality = 图片保存质量,jpg的值范围1-100,png值范围0-9
    $returnType = 返回数据类型
````
*  rotate(    
        $originPicture,$angle,$savePath = NULL,$quality = -1,    
        $returnType = 1,$transparentColor = [])
````
    说明 : 旋转图片
    $originPicture = 图片数据,可以是图片路径，也可以是图片数据
    $angle = 旋转的角度,可以是正负数,例 90度 = 90
    $savePath = 图片保存路径，根据returnType选择是否输入,如果选择返回数据，可以不输入
    $quality = 图片保存质量,jpg的值范围1-100,png值范围0-9
    $transparentColor = 透明颜色值,对于png可能需要设置此值,例子: 数组:[r,g,b]= [0-255,0-255,0-255],默认[254,254,254]
    $returnType = 返回数据类型
````
* text(    
     $originPicture,$position,$string,$angle = 0,    
     $savePath = NULL,,$quality = -1,$returnType = 1,        
     $fontFile = NULL,$fontSize = NULL,$fontColor = NULL)
````
    说明 : 在图片上添加文字
    $originPicture = 图片数据,可以是图片路径，也可以是图片数据
    $position = 文字开始位置,例 [x,y] = [100,100]
    $string = 要添加的字符串
    $angle = 文字角度,可以是正负数,例 90度 = 90
    $savePath = 图片保存路径，根据returnType选择是否输入,如果选择返回数据，可以不输入
    $fontFile = 字体路径
    $fontSize = 字体大小,>=1
    $fontColor = 字体颜色,例子: 数组:[r,g,b]= [0-255,0-255,0-255]
    $quality = 图片保存质量,jpg的值范围1-100,png值范围0-9
    $returnType = 返回数据类型
````
* makeIdentifyCodePicture(    
        $code,$savePath,$params = [],    
        $quality = 1,$returnType = 1)
````
    说明 : 生成验证码图片
    $code = 验证码
    $position = 文字开始位置,例 [x,y] = [100,100]
    $savePath = 图片保存路径，根据returnType选择是否输入,如果选择返回资源，可以不输入
    $quality = 图片保存质量,jpg的值范围1-100,png值范围0-9
    $returnType = 返回数据类型
    $params = 数组,一些验证码配置,例 : [
        'size' => [100, 100],   (验证码图片的尺寸)
        'position' => [20, 30],   (验证码文字的开始位置)
        'noise_count' => rand(10, 20),   (图片干扰点的数量)
        'color' => [    (干扰点需要用到的颜色集合,随机用到这些颜色)
            [255, 0, 0],
            [0, 255, 0],
            [0, 0, 255],
            [0, 0, 0],
            [0, 255, 255],
            [255, 255, 0],
             255, 0, 255]],
        'font_size' => rand(13, 18),    (文字大小)
        'font_path' => './Attrl.ttl'    (文字文件路径)
    ]
````     
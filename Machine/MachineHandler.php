<?php
namespace PictureFactory\Machine;

use PictureFactory\Exception\PictureException;

class MachineHandler
{
    public static function getPictureNameAndsuffix($string, &$name, &$suffix)
    {
        $explorArray = explode('.', basename($string));
        if (empty($explorArray[0]) || empty($explorArray[1]))
            throw new PictureException('不合法的图片路径');
        $name = $explorArray[0];
        $suffix = strtolower($explorArray[1]);
    }

    public static function makeAutoName($suffix, $saveDir)
    {
        if (!is_dir($saveDir)) {
            $result = mkdir($saveDir, 0775);
            if (!$result)
                throw new PictureException('保存路径错误');
        }
        $name = '';
        while (true) {
            $name = uniqid() . date('YmdHiss', time()) . $suffix;
            if (!is_file($saveDir . '/' . $name))
                break;
        }
        return $name;
    }

}
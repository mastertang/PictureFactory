<?php

namespace PictureFactory\driver;

/**
 * Class CommonHandler
 * @package PictureFactory\driver
 */
class CommonHandler
{
    /**
     * 获取图片的名字和后缀
     *
     * @param $string
     * @param $name
     * @param $suffix
     * @return bool
     */
    public static function getPictureNameAndsuffix($string, &$name, &$suffix)
    {
        $explorArray = explode('.', basename($string));
        if (empty($explorArray[0]) || empty($explorArray[1])) {
            return false;
        }
        $name   = $explorArray[0];
        $suffix = strtolower($explorArray[1]);
    }

}
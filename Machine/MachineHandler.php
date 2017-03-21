<?php
namespace PictureFactory\Machine;
class MachineHandler
{
    public static function getPictureNameAndsuffix($string)
    {
        $explorArray = explode('.', basename($string));
        $name = $explorArray[0];
        $suffix = strtolower($explorArray[1]);
    }
}
<?php
namespace PictureFactory\Machine;

use PictureFactory\Exception\PictureException;

class ParamsHandler
{

    public static function handleStart($rule)
    {
        $ruleArray = [];
        $result = [];
        foreach ($rule as $key => $value) {
            $ruleArray = explode('|', $value[0]);
            foreach ($ruleArray as $value2) {
                $result[$key] = self::center($value2, $value[1]);
                if (!$result) {
                    break;
                }
            }
        }
        return $result;
    }

    private static function center($value, $data)
    {
        $valueArray = explode(':', $value);
        $func = $valueArray[0];
        var_dump($data);
        return self::$func($valueArray[1], $data);
    }

    private static function path($value, $data)
    {
        if (!is_file($data) && !is_dir($data))
            throw new PictureException('不是路径字符串');
        return true;
    }

    private static function dir($value, $data)
    {
        if (!is_dir($data))
            throw new PictureException('不是文件夹');
        return true;
    }

    private static function file($value, $data)
    {
        if (!is_file($data))
            throw new PictureException('不是文件');
        return true;
    }

    private static function set($value, $data)
    {
        if (isset($data) &&
            !empty($data) &&
            $data !== '' &&
            $data != NULL
        )
            return true;
        elseif ($data == 0) {
            return true;
        } else
            throw new PictureException('此参数不能为空');
    }

    private static function bool($value, $data)
    {
        if (!is_bool($data))
            throw new PictureException('此参数不是布尔类型');
        return true;
    }

    private static function string($value, $data)
    {
        if (!is_string($data))
            throw new PictureException('此类型不是字符串类型');
        return true;
    }

    private static function color($value, $data)
    {
        $result = true;
        $rgb = [];
        if (is_string($data)) {
            if (strlen($data) != 7 || $data{0} != '#')
                $result = false;
            $rgb[] = hexdec($data{1} . $data{2});
            $rgb[] = hexdec($data{3} . $data{4});
            $rgb[] = hexdec($data{5} . $data{6});
            $data = $rgb;
        }
        if (is_array($data)) {
            if (!is_int($data[0]) || !is_int($data[1]) || !is_int($data[2]))
                $result = false;
            if ($data[0] < 0 || $data[1] < 0 || $data[2] < 0)
                $result = false;
            if ($data[0] > 255 || $data[1] > 255 || $data[2] > 255)
                $result = false;
        } else
            $result = false;
        if (!$result)
            throw new PictureException('此参数不符合color类型');
        return true;
    }

    private static function max($max, $data)
    {
        if (is_array($max)) {
            foreach ($max as $value) {
                $max = (int)$value;
                if (!((int)$data <= $max))
                    throw new PictureException('此参数超出最大值设定范围');
            }
        } else {
            $max = (int)$max;
            if (!((int)$data <= $max))
                throw new PictureException('此参数超出最大值设定范围');
        }
        return true;
    }

    private static function min($min, $data)
    {
        if (is_array($min)) {
            foreach ($min as $value) {
                $min = (int)$value;
                if (!((int)$data >= $min))
                    throw new PictureException('此参数超出最小值设定范围');
            }
        } else {
            $min = (int)$min;
            if (!((int)$data >= $min))
                throw new PictureException('此参数超出最小值设定范围');
        }
        return true;
    }

    private static function int($value, $data)
    {
        if (!is_int($data))
            throw new PictureException('此参数不是整形类型');
        return true;
    }

    private static function arr($value, $data)
    {
        if (!is_array($data))
            throw new PictureException('此参数不是array类型');
        return true;
    }

    private static function position($value, $data)
    {
        if (!is_array($data) ||
            sizeof($data) < 2 ||
            !is_int($data[0]) ||
            !is_int($data[1]) ||
            $data[0] < 0 ||
            $data[1] < 0
        )
            throw new PictureException('此参数不符合position格式要求');
        return true;
    }
}
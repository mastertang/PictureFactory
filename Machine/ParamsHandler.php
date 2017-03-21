<?php
namespace PictureFactory\Machine;
class ParamsHandler
{

    public static function handleStart($rule, $data)
    {
        $ruleArray = [];
        $result = [];
        foreach ($rule as $key => $value) {
            $ruleArray = explode('|', $value);
            foreach ($ruleArray as $value2) {
                $result[$key] = true;
                $result = self::center($value2, $data[$key]);
                if (!$result) {
                    $result[$key] = false;
                    break;
                }
            }
        }
        return $result;
    }

    private static function path($data)
    {
        return (is_dir($data) || is_file($data)) ? true : false;
    }

    private static function set($data)
    {
        return (isset($data) && !empty($data) && $data !== '' && $data != NULL) ? true : false;
    }

    private static function bool($data)
    {
        return is_bool($data);
    }

    private static function string($data)
    {
        return is_string($data);
    }

    private static function color($data)
    {
        $result = true;
        $rgb = [];
        if (is_string($data)) {
            if (strlen($data) != 7)
                $result = false;
            if ($data{0} != '#')
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
        return $result;
    }

    private static function max($max, $data)
    {
        $max = (int)$max;
        return ((int)$data <= $max) ? true : false;
    }

    private static function min($min, $data)
    {
        $min = (int)$min;
        return ((int)$data >= $min) ? true : false;
    }

    private static function int($data)
    {
        return is_int($data);
    }

    private static function position($data)
    {
        if (!is_array($data))
            return false;
        if (sizeof($data) < 2)
            return false;
        if (!is_int($data[0]) || !is_int($data[1]))
            return false;
        return false;
    }
}
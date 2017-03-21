<?php
namespace PictureFactory;

use PictureFactory\Exception\PictureException;

class PictureFactory
{
    private $machineInstance = NULL;

    public function factoryRun($machineName = NULL, $config = NULL)
    {
        if (empty($machineName))
            $machineName = 'Gd';
        $machineName = ucfirst($machineName);
        $machineClass = "{$machineName}Machine";
        $machinePath = __DIR__ . "/Machine/{$machineClass}.php";
        if (!file_exists($machinePath))
            throw new PictureException("不存在{$machineName}文件");
        require $machinePath;
        if (!class_exists($machineClass))
            throw new PictureException("不存在{$machineClass}类");
        $this->machineInstance = new $machineClass($config);
    }

    public function scale($originPicture, $size, $savePath)
    {
        return $this->machineInstance->scale($originPicture, $size, $savePath);
    }

    public function rotate($originPicture, $savePath, $angle)
    {
        return $this->machineInstance->rotate($originPicture, $savePath, $angle);
    }

    public function cut($originPicture, $savePath, $cutSize, $poition)
    {
        return $this->machineInstance->cut($originPicture, $savePath, $cutSize, $poition);
    }

    public function text($originPicture, $savePath, $position, $angle, $string, $fontSize = NULL, $fontFile = NULL, $fontColor = NULL)
    {
        return $this->machineInstance->text($originPicture, $savePath, $position, $angle, $string, $fontSize, $fontFile, $fontColor);
    }

    public function composition($backPicture, $frontPicture, $savePath, $position)
    {
        return $this->machineInstance->composition($backPicture, $frontPicture, $savePath, $position);
    }

    public function changeConfig($params = NULL)
    {
        return $this->machineInstance->changeConfig($params);
    }

    public function swiftMachine($machineName = NULL)
    {

    }
}
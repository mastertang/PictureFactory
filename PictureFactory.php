<?php
namespace PictureFactory;

use PictureFactory\Exception\PictureException;

class PictureFactory
{
    const RETURN_PATH = 1;
    const RETURN_RES = 2;
    const RETURN_IMG_STRING = 3;
    private $machineInstance = NULL;

    public function factoryRun($machineName = NULL)
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
        $this->machineInstance = new $machineClass();
    }

    public function swiftMachine($machineName = NULL)
    {
        if (!empty($machineName)) {
            $machineName = ucfirst($machineName);
            $machineClass = "{$machineName}Machine";
            $machinePath = __DIR__ . "/Machine/{$machineClass}.php";
            if (!file_exists($machinePath))
                throw new PictureException("不存在{$machineName}文件");
            require $machinePath;
            if (!class_exists($machineClass))
                throw new PictureException("不存在{$machineClass}类");
            $this->machineInstance = new $machineClass();
        }
        return true;
    }

    public function instance()
    {
        return $this->machineInstance;
    }
}
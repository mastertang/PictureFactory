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
        return $this->machineInstance;
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
        return $this->machineInstance;
    }

    public static function faceDetector($xmlPath, $pictureData)
    {
        if (!extension_loaded('face_detector')) {
            throw new PictureException('未安装face_detector扩展');
        }
        $data = face_detector($xmlPath, $pictureData);
        return json_decode($data);
    }

    public function instance()
    {
        return $this->machineInstance;
    }
}
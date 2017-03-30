<?php
namespace PictureFactory\PictureInterface;
interface PictureInterface
{
    public function scale($originPicture, $size);

    public function rotate($originPicture, $angle);

    public function cut($originPicture, $cutSize, $poition);

    public function text($originPicture, $position,$string, $angle);

    public function composition($backPicture, $frontPicture, $savePath, $position);

    public function thumbnailImage($originPicture,$scaleSize);

    public function makeGif($images,$savePath);

    public function changeConfig($params = NULL);
}
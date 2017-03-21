<?php
namespace PictureFactory\PictureInterface;
interface PictureInterface
{
    public function scale($originPicture, $size, $savePath);

    public function rotate($originPicture, $savePath, $angle);

    public function cut($originPicture, $savePath, $cutSize, $poition);

    public function text($originPicture, $savePath, $position, $angle, $string, $fontSize = NULL, $fontFile = NULL, $fontColor = NULL);

    public function composition($backPicture, $frontPicture, $savePath, $position);

    public function changeConfig($params = NULL);
}
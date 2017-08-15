<?php

namespace G4\Image;

use Intervention\Image\ImageManager;
use G4\Image\Consts;

class Process
{

    /**
     * @var  \Intervention\Image\ImageManager
     */
    private $imageManager;

    public function __construct($driver = 'gd')
    {
        $this->imageManager =  new ImageManager(['driver' => $driver]);
    }

    /**
     * @param \Intervention\Image\Image $image
     * @param integer $width
     * @param integer $height
     * @param integer $x
     * @param integer $y
     * @return \Intervention\Image\Image
     */
    public function crop($image, $width, $height, $x, $y)
    {
        return $image->crop($width, $height, $x, $y);
    }

    /**
     * @param \Intervention\Image\Image $image
     * @return resource
     */
    public function getImageResource($image)
    {
        return $image->getCore();
    }

    /**
     * @param \Intervention\Image\Image $image
     * @param integer $dstW
     * @param integer $dstH
     * @param mixed $background
     * @return \Intervention\Image\Image
     */
    public function imageCopyResampled($image, $dstW, $dstH, $background)
    {
        $imgBgd = $this->imageCreateTrueColor($dstW, $dstH, $background);
        $image->fit(intval($dstW), intval($dstH));
        return $this->imageSetTile($imgBgd, $image);
    }

    /**
     * @param integer $width
     * @param integer $height
     * @param mixed $background
     * @return \Intervention\Image\Image
     */
    public function imageCreateTrueColor($width, $height, $background = null)
    {
        return $this->imageManager->canvas($width, $height, $background);
    }

    /**
     * @param mixed $image
     * @param integer $x1
     * @param integer $x2
     * @param integer $y1
     * @param integer $y2
     * @return \Intervention\Image\Image
     */
    public function imageFilledRectangle($image, $x1, $y1, $x2, $y2)
    {
        return $this->make($image)->rectangle($x1, $y1, $x2, $y2,  function ($draw) {
            $draw->background(Consts::TRANSPARENT_WHITE_COLOR);
        });
    }

    /**
     * Returns string or boolean
     * @param \Intervention\Image\Image $image
     * @param string $filename
     * @return mixed
     */
    public function imageGif($image, $filename = null)
    {
        $img = $image->encode('gif');
        return $this->getEncodedImage($img, $filename);
    }

    /**
     * Returns string or boolean
     * @param \Intervention\Image\Image $image
     * @param string $filename
     * @param integer $quality
     * @return mixed
     */
    public function imageJpeg($image, $filename = null, $quality = null)
    {
        $img = $image->encode('jpg', $quality);
        return $this->getEncodedImage($img, $filename);
    }

    /**
     * Returns string or boolean
     * @param \Intervention\Image\Image $image
     * @param string $filename
     * @param integer $quality
     * @return mixed
     */
    public function imagePng($image, $filename = null, $quality = null)
    {
        $img = $image->encode('png', $quality);
        return $this->getEncodedImage($img, $filename);
    }

    /**
     * @param \Intervention\Image\Image $image
     * @param mixed $tile
     * @return \Intervention\Image\Image
     */
    public function imageSetTile($image, $tile)
    {
        return $image->insert($tile);

    }

    /**
     * @param mixed $image
     * @return \Intervention\Image\Image
     */
    public function make($image)
    {
        return $this->imageManager->make($image);
    }

    /**
     * @param \Intervention\Image\Image $image
     * @param integer $width
     * @param integer $height
     * @return \Intervention\Image\Image
     */
    public function resize($image, $width, $height)
    {
        return $image->resize($width, $height);
    }

    /**
     * @param \Intervention\Image\Image $image
     * @param float $angle
     * @param string $bgdColor
     * @return \Intervention\Image\Image
     */
    public function rotate($image, $angle, $bgdColor)
    {
        return $image->rotate($angle, $bgdColor);
    }

    /**
     * Returns string or boolean
     * @param \Intervention\Image\Image $img
     * @param string $filename
     * @return mixed
     */
    private function getEncodedImage(\Intervention\Image\Image $img, $filename)
    {
        if ($filename != null) {
            return $this->saveImage($img, $filename);
        }
        return $img->getEncoded();
    }

    /**
     * @param \Intervention\Image\Image $image
     * @param string $filename
     * @return boolean
     */
    private function saveImage($image, $filename)
    {
        try {
            $image->save($filename);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

}
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
     * @param mixed $image
     * @param integer $width
     * @param integer $height
     * @param integer $x
     * @param integer $y
     * @return \Intervention\Image\Image
     */
    public function crop($image, $width, $height, $x, $y)
    {
        return $this->imageManager->make($image)->crop($width, $height, $x, $y);
    }

    /**
     * @param mixed $image
     * @return integer
     */
    public function getHeight($image)
    {
        return $this->imageManager->make($image)->height();
    }

    /**
     * @param mixed $image
     * @return resource
     */
    public function getImageResource($image)
    {
        $img = $this->imageManager->make($image);
        return $img->getCore();
    }

    /**
     * @param mixed $image
     * @return integer
     */
    public function getWidth($image)
    {
        return $this->imageManager->make($image)->width();
    }

    /**
     * @param mixed $image
     * @param integer $dstW
     * @param integer $dstH
     * @param mixed $background
     * @return \Intervention\Image\Image
     */
    public function imageCopyResampled($image, $dstW, $dstH, $background)
    {
        $imgBgd = $this->imageCreateTrueColor($dstW, $dstH, $background);
        $resizedImg = $this->imageManager->make($image);
        $resizedImg->fit(intval($dstW), intval($dstH));
        return $this->imageSetTile($imgBgd, $resizedImg);
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
        return $this->imageManager->make($image)->rectangle($x1, $y1, $x2, $y2,  function ($draw) {
            $draw->background(Consts::TRANSPARENT_WHITE_COLOR);
        });
    }

    /**
     * Returns string or boolean
     * @param mixed $image
     * @param string $filename
     * @return mixed
     */
    public function imageGif($image, $filename = null)
    {
        $img = $this->imageManager->make($image)->encode('gif');
        return $this->getEncodedImage($img, $filename);
    }

    /**
     * Returns string or boolean
     * @param mixed $image
     * @param string $filename
     * @param integer $quality
     * @return mixed
     */
    public function imageJpeg($image, $filename = null, $quality = null)
    {
        $img = $this->imageManager->make($image)->encode('jpg', $quality);
        return $this->getEncodedImage($img, $filename);
    }

    /**
     * Returns string or boolean
     * @param mixed $image
     * @param string $filename
     * @param integer $quality
     * @return mixed
     */
    public function imagePng($image, $filename = null, $quality = null)
    {
        $img = $this->imageManager->make($image)->encode('png', $quality);
        return $this->getEncodedImage($img, $filename);
    }

    /**
     * @param mixed $image
     * @param mixed $tile
     * @return \Intervention\Image\Image
     */
    public function imageSetTile($image, $tile)
    {
        return $this->imageManager->make($image)->insert($tile);

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
     * @param mixed $img
     * @param integer $width
     * @param integer $height
     * @return \Intervention\Image\Image
     */
    public function resize($img, $width, $height)
    {
        return $this->imageManager->make($img)->resize($width, $height);
    }

    /**
     * @param mixed $image
     * @param float $angle
     * @param string $bgdColor
     * @return \Intervention\Image\Image
     */
    public function rotate($image, $angle, $bgdColor)
    {
        return $this->imageManager->make($image)->rotate($angle, $bgdColor);
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
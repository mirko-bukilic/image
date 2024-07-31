<?php
namespace G4\Image;

class Image
{
    const SEPARATOR_COMMA = ',';

    /**
     * @var string
     */
    private $base64Encoded;

    /**
     * @var integer
     */
    private $height;

    /**
     * @var \G4\Image\Path
     */
    private $imagePath;

    /**
     * @var \G4\Image\Process
     */
    private $imageProcess;

    /**
     * @var resource
     */
    private $imageResource;

    /**
     * @var string
     */
    private $mimeType;

    /**
     * @var integer
     */
    private $width;

    /**
     * @var string
     */
    private $photoId;

    /**
     * @var \Intervention\Image\Image
     */
    private $resampledImage;

    /**
     * @var \Intervention\Image\Image
     */
    private $resizedImage;

    /**
     * @var \G4\Storage\Storage
     */
    private $storage;

    /**
     * @var int
     */
    private $orientation;

    /**
     * @var string
     */
    private $base64Header;

    public function __construct(\G4\Storage\Storage $storage = null, \G4\Image\StorageConfig $storageConfig = null, $photoId = null, $mimeType = null, $driver = Consts::DEFAULT_DRIVER)
    {
        $this->storage = $storage;
        $this->photoId = $photoId;
        $this->mimeType = $mimeType;
        $this->imagePath = new \G4\Image\Path($storageConfig, $this->photoId, $this->mimeType);
        $this->imageProcess = new \G4\Image\Process($driver);
        $this->orientation = 0;
    }

    /**
     * @return \G4\Image\Image
     * @throws \Exception
     */
    public function createImageResourceFromBase64Encoded($extractExifData = false)
    {
        if ( $this->base64Encoded === null ) {
            throw new \Exception('Missing base64 encoded image.', Consts::HTTP_CODE_415);
        }

        if ( ! $this->allowedImageHeader() ) {
            throw new \Exception('Image has unavailable MIME type.', Consts::HTTP_CODE_415);
        }

        $this->setBase64Header();

        $this->base64Encoded = str_replace(Consts::getAllowedHeaders(), '', $this->base64Encoded);

        $this->base64Encoded = str_replace(' ', '+', $this->base64Encoded);

        $decoded = base64_decode( $this->base64Encoded );

        $this->checkIfImageIsProperlyDecoded($decoded);

        if ($extractExifData) {
            $this->extractExifDataAndSetOrientation();
        }

        $f = finfo_open();

        $this->setMimeType( finfo_buffer($f, $decoded, FILEINFO_MIME_TYPE) );

        $this->setImageResource( $decoded );

        $source = $this->getCreatedImageResource($decoded);

        if ($source === false) {
            throw new \Exception('Cannot create image from post data.', Consts::HTTP_CODE_400);
        }
        $image = $this->imageProcess->make($source);

        $this->setWidth($image->getWidth());
        $this->setHeight($image->getHeight());

        return $this;
    }

    /**
     * @param integer $width
     * @param integer $height
     * @param integer $x
     * @param integer $y
     * @return \G4\Image\Image
     * @throws \Exception
     */
    public function crop($width, $height, $x, $y)
    {
        $srcImg = $this->getImageSource();

        if ($srcImg == null) {
            throw new \Exception('Source image not found.', Consts::HTTP_CODE_403);
        }
        $img = $this->imageProcess->make($srcImg);
        $cropImg = $this->imageProcess->crop($img, $width, $height, $x, $y);

        $this->outputImage($this->getCreatedImageResource($cropImg), $this->imagePath->getOriginalPath());

        return $this;
    }

    /**
     * @return string
     */
    public function getBase64Encoded()
    {
        return $this->base64Encoded;
    }

    /**
     * @return string
     */
    public function getBase64EncodedSourceImage()
    {
        $imageSource = $this->getImageResourceFromPath($this->imagePath->getSourcePath(), 'src');
        $img = $this->imageProcess->make($imageSource);
        switch ($this->getMimeType())
        {
            case Consts::IMAGE_JPEG:
                $img = $this->imageProcess->imageJpeg($img, null, Consts::QUALITY_JPEG);
                break;
            case Consts::IMAGE_GIF:
                $img = $this->imageProcess->imageGif($img);
                break;
            case Consts::IMAGE_PNG:
                $img = $this->imageProcess->imagePng($img, null, Consts::QUALITY_PNG);
                break;
        }

        return base64_encode($img);
    }

    /**
     * @param mixed $image
     * @return resource
     */
    public function getCreatedImageResource($image)
    {
        $img = $this->imageProcess->make($image);
        return $this->imageProcess->getImageResource($img);
    }

    /**
     * @return integer
     */
    public function getHeight()
    {
        if (!isset($this->height)) {
            $img = $this->imageProcess->make($this->getImageResource());
            $this->height = $img->height();
        }

        return $this->height;
    }

    /**
     * @return resource
     */
    public function getImageOriginal()
    {
        return $this->getImageResourceFromPath($this->imagePath->getOriginalPath(), 'org');
    }

    /**
     * @return \G4\Image\Path
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * @return resource
     */
    public function getImageResource()
    {
        return $this->imageResource;
    }

    /**
     * @return resource
     */
    public function getImageSource()
    {
        return $this->getImageResourceFromPath($this->imagePath->getSourcePath(), 'src');
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return \Intervention\Image\Image
     */
    public function getResampledImage()
    {
        return $this->resampledImage;
    }

    /**
     * @return \Intervention\Image\Image
     */
    public function getResizedImage()
    {
        return $this->resizedImage;
    }

    /**
     * @param string $resourceName
     * @return resource
     */
    public function getResourceFromName($resourceName)
    {
        switch ($resourceName)
        {
            case Consts::NAME_SOURCE:
                $imgResource = $this->getImageSource();
                break;
            case Consts::NAME_ORIGINAL:
                $imgResource = $this->getImageOriginal();
                break;
            case Consts::NAME_RESAMPLED:
                $imgResource = $this->getResampledImage();
                break;
        }

        return $imgResource;
    }

    /**
     * @return integer
     */
    public function getWidth()
    {
        if (!isset($this->width)) {
            $img = $this->imageProcess->make($this->getImageResource());
            $this->width = $img->width();
        }

        return $this->width;
    }

    /**
     * @param integer $dstW
     * @param integer $dstH
     * @param mixed $background
     * @return \G4\Image\Image
     */
    public function imageCopyResampled($dstW, $dstH, $background)
    {
        $image = $this->imageProcess->make($this->getImageOriginal());
        $this->resampledImage = $this->imageProcess->imageCopyResampled($image, $dstW, $dstH, $background);
        return $this;
    }

    /**
     * @param string $resourceName
     * @param integer $x1
     * @param integer $y1
     * @return \G4\Image\Image
     */
    public function imageFilledRectangle($resourceName, $x1, $y1)
    {
        $image = $this->getResourceFromName($resourceName);
        $img = $this->imageProcess->make($image);
        $x2 = $img->getWidth();
        $y2 = $img->getHeight();
        $this->imageProcess->imageFilledRectangle($img, $x1, $y1, $x2, $y2);
        return $this;
    }

    /**
     * @param string $resourceName
     * @param mixed $tile
     * @return \G4\Image\Image
     */
    public function imageSetTile($resourceName, $tile)
    {
        $image = $this->getResourceFromName($resourceName);
        $img = $this->imageProcess->make($image);
        $this->imageProcess->imageSetTile($img, $tile);
        return $this;
    }

    /**
     * @param resource $dstImg
     * @param string $dstPath
     * @return boolean
     */
    public function outputImage($dstImg, $dstPath)
    {
        $img = $this->imageProcess->make($dstImg);
        $localFile = PATH_TMP . md5($dstPath);

        $path = dirname($localFile);

        if(!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        switch ($this->getMimeType())
        {
            case Consts::IMAGE_JPEG:
                $success = $this->imageProcess->imageJpeg($img, $localFile, Consts::QUALITY_JPEG);
                break;
            case Consts::IMAGE_GIF:
                $success = $this->imageProcess->imageGif($img, $localFile);
                break;
            case Consts::IMAGE_PNG:
                $success = $this->imageProcess->imagePng($img, $localFile, Consts::QUALITY_PNG);
                break;
        }

        $this->storage
            ->setLocalFile($localFile)
            ->setRemoteFile('/' . $dstPath)
            ->put();

        unlink($localFile); // delete from tmp folder
        return $success;
    }

    /**
     * @param integer $width
     * @param integer $height
     * @return \G4\Image\Image
     */
    public function resize($width, $height)
    {
        $img = $this->imageProcess->make($this->getImageResource());
        $this->resizedImage = $this->imageProcess->resize($img, $width, $height);
        return $this;
    }

    /**
     * @param string $resourceName
     * @param float $angle
     * @param string $bgdColor
     * @param integer $ignoreTransparent
     * @return \G4\Image\Image
     */
    public function rotate($resourceName, $angle, $bgdColor, $ignoreTransparent = 0)
    {
        $image = $this->getResourceFromName($resourceName);
        $path = $this->getPathFromResourceName($resourceName);
        $img = $this->imageProcess->make($image);
        $rotateImg = $this->imageProcess->rotate($img, $angle, $bgdColor, $ignoreTransparent);
        $this->outputImage($this->getCreatedImageResource($rotateImg), $path);

        return $this;
    }

    /**
     * @param string $value
     * @return \G4\Image\Image
     */
    public function setBase64Encoded($value)
    {
        $this->base64Encoded = $value;
        return $this;
    }

    /**
     * @param string $integer
     * @return \G4\Image\Image
     */
    public function setHeight($value)
    {
        $this->height = $value;

        return $this;
    }

    /**
     * @param string $photoId
     * @param string $mimeType
     * @return \G4\Image\Image
     */
    public function setImagePath(\G4\Image\StorageConfig $storageConfig = null, $photoId = null, $mimeType = null)
    {
        $this->imagePath = new \G4\Image\Path($storageConfig, $photoId, $mimeType);
        return $this;
    }

    /**
     * @param resource $value
     * @return \G4\Image\Image
     */
    public function setImageResource($value)
    {
        $this->imageResource = $value;
        return $this;
    }

    /**
     * @param string $value
     * @return \G4\Image\Image
     */
    public function setMimeType($value)
    {
        $this->mimeType = $value;
        return $this;
    }

    /**
     * @param integer $value
     * @return \G4\Image\Image
     */
    public function setWidth($value)
    {
        $this->width = $value;
        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function upload()
    {
        $this
            ->checkFileType($_FILES['files']['type'][0])
            ->checkFileSize($_FILES['files']['size'][0]);

        $img = $this->imageProcess->make($_FILES['files']['tmp_name'][0]);
        $photoContent = file_get_contents($img->dirname . '/' . $img->filename);

        if (!$photoContent) {
            throw new \Exception('File upload error', Consts::HTTP_CODE_400);
        }

        $this->checkImageWidth($img->width());
        $photo = 'data:'
            . $img->mime
            . ';base64,'
            . base64_encode($photoContent);

        @unlink($img->dirname . '/' . $img->filename);

        return [
            'code'  => Consts::HTTP_CODE_200,
            'photo' => $photo,
        ];

    }

    /**
     * @return boolean
     */
    private function allowedImageHeader()
    {
        foreach ( Consts::getAllowedHeaders() as $header ) {
            if ( strpos( $this->base64Encoded, $header ) !== false ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $type
     * @return \G4\Image\Image
     * @throws \Exception
     */
    private function checkFileType($type)
    {
        if (!in_array($type, Consts::getAllowedFileTypes())) {
            throw new \Exception('Filetype ' . $type .  ' not allowed', Consts::HTTP_CODE_400);
        }
        return $this;
    }

    /**
     * @param integer $size
     * @return \G4\Image\Image
     * @throws \Exception
     */
    private function checkFileSize($size)
    {
        if($size > Consts::MAX_FILE_SIZE) {
            throw new \Exception('Image size exceeds 12MB', Consts::HTTP_CODE_400);
        }
        return $this;
    }

    /**
     * @param integer $width
     * @return \G4\Image\Image
     * @throws \Exception
     */
    private function checkImageWidth($width)
    {
        if($width < Consts::MIN_IMAGE_WIDTH) {
            throw new \Exception('Image requires a minimum width of ' . Consts::MIN_IMAGE_WIDTH . 'px', Consts::HTTP_CODE_400);
        }
        return $this;
    }

    /**
     * @param string $decoded
     * @return \G4\Image\Image
     * @throws \Exception
     */
    private function checkIfImageIsProperlyDecoded($decoded)
    {
        $imageSize = getimagesizefromstring($decoded);
        if (!is_array($imageSize)){
            throw new \Exception('Cannot create image from post data.', Consts::HTTP_CODE_400);
        }
        return $this;
    }

    /**
     * @param string $path
     * @return resource
     * @throws \Exception
     */
    private function getImageResourceFromPath($path, $localFileKey = null)
    {
        $localFileName = md5(microtime(true) . $path);

        $imagePath = $this->storage
            ->setLocalFile($localFileName, $localFileKey)
            ->setRemoteFile($path)
            ->get();

        if ($imagePath === false) {
            throw new \Exception(sprintf("Local file %s not available for remote %s", $localFileName, $path));
        }

        return $this->getCreatedImageResource($imagePath);
    }

    /**
     * @param string $resourceName
     * @return string
     */
    private function getPathFromResourceName($resourceName)
    {
        switch ($resourceName)
        {
            case Consts::NAME_SOURCE:
                $imgPath = $this->getImagePath()->getSourcePath();
                break;
            case Consts::NAME_ORIGINAL:
                $imgPath = $this->getImagePath()->getOriginalPath();
                break;
        }

        return $imgPath;
    }

    /**
     * @return Image
     */
    public function orientateAndSaveToOriginalPath()
    {
        $img = $this->imageProcess->make($this->getImageResource());
        switch ($this->orientation) {
            case 2:
                $image = $img->flip();
                break;
            case 3:
                $image = $img->rotate(180);
                break;
            case 4:
                $image = $img->rotate(180)->flip();
                break;
            case 5:
                $image = $img->rotate(270)->flip();
                break;
            case 6:
                $image = $img->rotate(270);
                break;
            case 7:
                $image = $img->rotate(90)->flip();
                break;
            case 8:
                $image = $img->rotate(90);
                break;
            default:
                $image = $img;
        }

        $this->outputImage(
            $this->getCreatedImageResource($image),
            $this->getImagePath()->getOriginalPath()
        );
        return $this;
    }

    /**
     * @return void
     */
    private function extractExifDataAndSetOrientation()
    {
        if ($this->isJpeg()) {
            $exifData = exif_read_data($this->base64Header . $this->base64Encoded);
            $this->orientation = !empty($exifData['Orientation'])
                ? (int) $exifData['Orientation']
                : 0;
        }
    }

    /**
     * @return string|null
     */
    private function getMimeTypeFromBase64()
    {
        $string = substr($this->base64Encoded, 0, 30);
        if (preg_match('/^data:(.*?);base64,/', $string, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * @return bool
     */
    private function isJpeg()
    {
        return strpos($this->base64Header, Consts::IMAGE_JPEG) !== false;
    }

    /**
     * @param integer $width
     * @param integer $height
     * @return Image
     */
    public function resizeAndOrientate($width, $height)
    {
        $image = $this->imageProcess->make($this->getImageResource());
        $resized = $this->imageProcess->resize($image, $width, $height);
        $orientatedAndResized = $this->orientateImage($resized);

        $this->resizedImage = $orientatedAndResized;
        return $this;
    }

    /**
     * @param \Intervention\Image\Image $inputImage
     * @return \Intervention\Image\Image
     */
    private function orientateImage($inputImage)
    {
        switch ($this->orientation) {
            case 2:
                $image = $inputImage->flip();
                break;
            case 3:
                $image = $inputImage->rotate(180);
                break;
            case 4:
                $image = $inputImage->rotate(180)->flip();
                break;
            case 5:
                $image = $inputImage->rotate(270)->flip();
                break;
            case 6:
                $image = $inputImage->rotate(270);
                break;
            case 7:
                $image = $inputImage->rotate(90)->flip();
                break;
            case 8:
                $image = $inputImage->rotate(90);
                break;
            default:
                $image = $inputImage;
        }

        return $image;
    }

    /**
     * @return void
     */
    private function setBase64Header()
    {
        $this->base64Header = explode(self::SEPARATOR_COMMA, $this->base64Encoded, 2)[0];
        $this->base64Header .= self::SEPARATOR_COMMA;
    }
}

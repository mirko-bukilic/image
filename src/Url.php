<?php

namespace G4\Image;

class Url
{
    /**
     * @var array
     */
    private $derivativesUrls;

    /**
     * @var string
     */
    private $hostname;

    /**
     * @var array
     */
    private $allImageSizes;

    /**
     * @var integer
     */
    private $photoTsModified;

    /**
     * @var boolean
     */
    private $privatePhoto;

    /**
     * @var \G4\Image\Path
     */
    private $imagePath;


    public function __construct(\G4\Image\Path $imagePath = null, $hostname = null, $allImageSizes  = null, $photoTsModified = null, $privatePhoto = false)
    {
        $this->hostname = $hostname;
        $this->allImageSizes = $allImageSizes;
        $this->photoTsModified = $photoTsModified;
        $this->privatePhoto = $privatePhoto;
        $this->imagePath = $imagePath;
        $this->derivativesUrls = [];
    }

    /**
     * @param integer $drvId
     * @param boolean $displayPrivate
     * @return string
     */
    public function getDerivedUrl($drvId, $displayPrivate=false)
    {
        $pathSuffix = $this->imagePath->getStorageConfig()->getPathSuffixDerived();

        if ($displayPrivate) {
            $filename = $this->imagePath->getDerivedHashedFilename($drvId);
        }else {
            $filename = $this->privatePhoto
                ? $this->imagePath->getDerivedFilename($drvId)
                : $this->imagePath->getDerivedHashedFilename($drvId);
        }

        return $this->buildUrl($pathSuffix, $filename);
    }

    /**
     * @param array $imageSizes
     * @param boolean $hashedFilenames
     * @return array
     */
    public function getDerivativesUrls(array $imageSizes = null, $hashedFilenames=false)
    {
        if (!$this->hasDerivativesUrls()) {
            $this->fillDerivativesUrls($imageSizes, $hashedFilenames);
        }
        return $this->derivativesUrls;
    }

    /**
     * @return string
     */
    public function getOriginalUrl()
    {
        $pathSuffix = $this->imagePath->getStorageConfig()->getPathSuffixOriginal();
        return $this->buildUrl($pathSuffix, $this->imagePath->getOriginalFilename());
    }

    /**
     * @return string
     */
    public function getSourceUrl()
    {
        $pathSuffix = $this->imagePath->getStorageConfig()->getPathSuffixSource();
        return $this->buildUrl($pathSuffix, $this->imagePath->getOriginalFilename());
    }

    /**
     * @return string
     */
    private function appendTsModifiedQueryString()
    {
        return '?'. $this->photoTsModified;
    }

    /**
     * @param string $pathSuffix
     * @param string $filename
     * @return string
     */
    private function buildUrl($pathSuffix, $filename)
    {
        return $this->imagePath->buildBase($this->hostname, $pathSuffix, $filename);
    }

    /**
     * @param array $imageSizes
     * @param boolean $hashedFilenames
     */
    private function fillDerivativesUrls($imageSizes, $hashedFilenames)
    {
        if ($imageSizes != null) {
            foreach($imageSizes as $imageSize) {

                if (in_array($imageSize, [\G4\Image\Consts::NAME_SOURCE, \G4\Image\Consts::NAME_ORIGINAL])) {
                    $this->derivativesUrls[$imageSize] = $imageSize == \G4\Image\Consts::NAME_ORIGINAL
                        ? $this->getOriginalUrl()
                        : $this->getSourceUrl();
                    continue;
                }


                $drvId = array_search(strtoupper($imageSize), $this->allImageSizes);
                if($drvId > 0) {
                    $this->derivativesUrls[$imageSize] = $this->getDerivedUrl($drvId, $hashedFilenames);
                }

                $this->derivativesUrls[$imageSize] .= $this->appendTsModifiedQueryString();
            }
        }
    }

    /**
     * @return boolean
     */
    private function hasDerivativesUrls()
    {
        return count($this->derivativesUrls) > 0;
    }
}


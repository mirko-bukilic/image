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
     * @var \G4\Image\Path
     */
    private $imagePath;


    public function __construct(\G4\Image\StorageConfig $photoStorageConfig = null, $hostname = null, $photoId = null, $photoMimeType = null)
    {
        $this->hostname = $hostname;
        $this->imagePath = new \G4\Image\Path($photoStorageConfig, $photoId, $photoMimeType);
        $this->derivativesUrls = [];
    }

    /**
     * @param integer $drvId
     * @param boolean $privatePhoto
     * @param boolean $displayPrivate
     * @return string
     */
    public function getDerivedUrl($drvId, $privatePhoto = false, $displayPrivate = false)
    {
        $pathSuffix = $this->imagePath->getStorageConfig()->getPathSuffixDerived();

        if ($displayPrivate) {
            $filename = $this->imagePath->getDerivedHashedFilename($drvId);
        }else {
            $filename = $privatePhoto
                ? $this->imagePath->getDerivedFilename($drvId)
                : $this->imagePath->getDerivedHashedFilename($drvId);
        }

        return $this->buildUrl($pathSuffix, $filename);
    }

    /**
     * @param integer $photoTsModified
     * @param array $allImageSizes
     * @param array $requiredImageSizes
     * @param boolean $privatePhoto
     * @param boolean $hashedFilenames
     * @return array
     */
    public function getDerivativesUrls(array $allImageSizes = null, array $requiredImageSizes = null, $photoTsModified = null, $privatePhoto = false, $hashedFilenames = false)
    {
        if (!$this->hasDerivativesUrls()) {
            $this->fillDerivativesUrls($allImageSizes, $requiredImageSizes, $photoTsModified, $privatePhoto, $hashedFilenames);
        }
        return $this->derivativesUrls;
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
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
     * @param integer $photoTsModified
     * @return string
     */
    private function appendTsModifiedQueryString($photoTsModified)
    {
        return '?'. $photoTsModified;
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
     * @param array $allImageSizes
     * @param array $requiredImageSizes
     * @param integer $photoTsModified
     * @param boolean $privatePhoto
     * @param boolean $hashedFilenames
     */
    private function fillDerivativesUrls($allImageSizes, $requiredImageSizes, $photoTsModified, $privatePhoto, $hashedFilenames)
    {
        if ($requiredImageSizes != null) {
            foreach($requiredImageSizes as $imageSize) {

                if (in_array($imageSize, [\G4\Image\Consts::NAME_SOURCE, \G4\Image\Consts::NAME_ORIGINAL])) {
                    $this->derivativesUrls[$imageSize] = ($imageSize == \G4\Image\Consts::NAME_ORIGINAL)
                        ? $this->getOriginalUrl()
                        : $this->getSourceUrl();
                    continue;
                }

                $drvId = array_search(strtoupper($imageSize), $allImageSizes);
                if($drvId > 0) {
                    $this->derivativesUrls[$imageSize] = $this->getDerivedUrl($drvId, $privatePhoto, $hashedFilenames);
                }

                $this->derivativesUrls[$imageSize] .= $this->appendTsModifiedQueryString($photoTsModified);
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


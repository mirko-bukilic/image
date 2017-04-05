<?php

namespace G4\Image;

use G4\Image\Consts;

class Path
{
    /**
     * @var string
     */
    private $photoId;

    /**
     * @var string
     */
    private $mimeType;

    /**
     * @var \G4\Image\StorageConfig
     */
    private $storageConfig;

    public function __construct(\G4\Image\StorageConfig $storageConfig = null, $photoId = null, $mimeType = null)
    {
        $this->photoId = $photoId;
        $this->mimeType = $mimeType;
        $this->storageConfig = $storageConfig;
    }

    /**
     * @param string $basePath
     * @param string $pathSuffix
     * @param string $filename
     * @return string
     */
    public function buildBase($basePath, $pathSuffix, $filename)
    {
        $tmp = '';
        $tmp .= $basePath;
        $tmp .= DIRECTORY_SEPARATOR;
        $tmp .= $pathSuffix;
        $tmp .= $this->getImagePathSuffix();
        $tmp .= $filename;

        return $tmp;
    }

    /**
     * @param integer $drvId
     * @return string
     */
    public function getDerivedFilename($drvId)
    {
        return $this->getFilename($drvId);
    }

    /**
     * @param integer $drvId
     * @return string
     */
    public function getDerivedHashedFilename($drvId)
    {
        return $this->getFilename($drvId, true);
    }

    /**
     * @param integer $drvId
     * @return string
     */
    public function getDerivedPath($drvId)
    {
        return $this->buildPath($this->storageConfig->getPathSuffixDerived(), $this->getDerivedFilename($drvId));
    }

    /**
     * @param integer $drvId
     * @return string
     */
    public function getHashedPath($drvId)
    {
        return $this->buildPath($this->storageConfig->getPathSuffixDerived(), $this->getDerivedHashedFilename($drvId));
    }

    /**
     * @return string
     */
    public function getImagePathSuffix()
    {
        $preparedString = substr(str_replace('-', '', $this->photoId),0, 9);

        return chunk_split($preparedString, 3, '/');
    }

    /**
     * @return string
     */
    public function getFileHash()
    {
        return md5($this->photoId . Consts::PHOTOS_SALT);
    }

    /**
     * @return string
     */
    public function getOriginalFilename()
    {
        return $this->getFilename(null, true);
    }

    /**
     * @return string
     */
    public function getOriginalPath()
    {
        return $this->buildPath($this->storageConfig->getPathSuffixOriginal(), $this->getOriginalFilename());
    }

    /**
     * @return string
     */
    public function getSourcePath()
    {
        return $this->buildPath($this->storageConfig->getPathSuffixSource(), $this->getOriginalFilename());
    }

    /**
     * @return \G4\Image\StorageConfig
     */
    public function getStorageConfig()
    {
        return $this->storageConfig;
    }

    /**
     * @param string $pathSuffix
     * @param string $filename
     * @return string
     */
    private function buildPath($pathSuffix, $filename)
    {
        return $this->buildBase($this->storageConfig->getStoragePath(), $pathSuffix, $filename);
    }

    /**
     * @param integer $drvId
     * @param boolean $hashed
     * @return string
     */
    private function getFilename($drvId = null, $hashed=false)
    {
        if(!$this->photoId || empty($this->mimeType)) {
            throw new \Exception('Required fields are missing');
        }

        // sanitize input
        $drvId = (int) $drvId;

        $filename = $this->photoId;

        if ($hashed) {
            $filename .= '.' . $this->getFileHash();
        }

        if(!empty($drvId)) {
            $filename .= Consts::formatDerivativesSuffix($drvId);
        }

        $filename .= '.' . Consts::getFileExtensionByType($this->mimeType);

        return $filename;
    }


}

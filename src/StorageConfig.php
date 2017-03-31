<?php
namespace G4\Image;

class StorageConfig
{
    /**
     * @var string
     */
    private $pathSuffixDerived;

    /**
     * @var string
     */
    private $pathSuffixOriginal;

    /**
     * @var string
     */
    private $pathSuffixSource;

    /**
     * @var string
     */
    private $storagePath;

    public function __construct($storagePath, $pathSuffixSource, $pathSuffixOriginal, $pathSuffixDerived)
    {
        $this->storagePath = $storagePath;
        $this->pathSuffixSource = $pathSuffixSource;
        $this->pathSuffixOriginal = $pathSuffixOriginal;
        $this->pathSuffixDerived = $pathSuffixDerived;
    }

    public function getPathSuffixDerived()
    {
        return $this->pathSuffixDerived;
    }

    public function getPathSuffixOriginal()
    {
        return $this->pathSuffixOriginal;
    }

    public function getPathSuffixSource()
    {
        return $this->pathSuffixSource;
    }

    public function getStoragePath()
    {
        return $this->storagePath;
    }

}

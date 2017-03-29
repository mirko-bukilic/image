<?php
namespace G4\Image;

class Consts
{
    const IMAGE_GIF  = 'image/gif';
    const IMAGE_JPEG = 'image/jpeg';
    const IMAGE_PNG  = 'image/png';

    const GIF_EXTENSION  = 'gif';
    const JPEG_EXTENSION = 'jpg';
    const PNG_EXTENSION  = 'png';

    const QUALITY_JPEG = 90;
    const QUALITY_PNG  = 9;

    const MAX_FILE_SIZE   = 12582912; // 12 MB
    const MIN_IMAGE_WIDTH = 50; // 50 px

    const NAME_ORIGINAL  = 'ORIGINAL';
    const NAME_RESAMPLED = 'RESAMPLED';
    const NAME_SOURCE    = 'SOURCE';

    const PHOTOS_SALT = 'jv023kdpamvugyfjzxldkguodls40575kdjf723nc';

    const TRANSPARENT_WHITE_COLOR = [255, 255, 255, 0];

    /**
     * @var array
     */
    protected static $allowedHeaders = [
        'data:image/jpeg;base64,',
        'data:image/png;base64,',
        'data:image/gif;base64,',
    ];

    /**
     * @var array
     */
    protected static $allowedFileTypes = [
        self::IMAGE_GIF,
        self::IMAGE_PNG,
        self::IMAGE_JPEG,
    ];

    protected static $derivativesSuffix = 'drv';

    protected static $derivativesPathSeparator = '_';

    protected static $typeMap = array (
        self::IMAGE_JPEG => self::JPEG_EXTENSION,
        self::IMAGE_GIF  => self::GIF_EXTENSION,
        self::IMAGE_PNG  => self::PNG_EXTENSION,
    );

    public static function formatDerivativesSuffix($derivativeId)
    {
        return self::$derivativesPathSeparator . self::$derivativesSuffix . $derivativeId;
    }

    public static function getAllowedHeaders()
    {
        return self::$allowedHeaders;
    }

    public static function getAllowedFileTypes()
    {
        return self::$allowedFileTypes;
    }

    public static function getFileExtensionByType($mimeType)
    {
        return array_key_exists($mimeType, self::$typeMap)
            ? self::$typeMap[$mimeType]
            : null;
    }
}

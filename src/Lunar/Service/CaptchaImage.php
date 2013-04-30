<?php

namespace Lunar\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface as ServiceLocator;
use Zend\Captcha\Image;
use Zend\Stdlib\ArrayUtils;

/**
 * Provides a captcha image creation service.
 */
class CaptchaImage
    implements FactoryInterface;
{
    /**
     * The default captcha image configuration.
     * @var array
     */
    protected static $defaultConfig = array (
        'width' => 250,
        'height' => 100,
        'imgDir' => './data/captcha',
        'font' => __DIR__ . '/../../../data/Aerial.ttf',
        'dotNoiseLevel' => 40,
        'lineNoiseLevel' => 3
    );

    /**
     * Creates a captcha image according to the configuration.
     * @param ServiceLocator $serviceLocator
     * @return Image
     */
    public function createService (ServiceLocator $serviceLocator)
    {
        $config = $serviceLocator->get ('Configuration');
        if (array_key_exists ('captcha_image')) {
            $config = ArrayUtils::merge (static::$defaultConfig, $config ['captcha_image']);
        }
        else {
            $config = static::$defaultConfig;
        }

        $image = new Image ($config);
    }
}

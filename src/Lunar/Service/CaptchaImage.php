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
    implements FactoryInterface
{
    /**
     * Creates a captcha image according to the configuration.
     * @param ServiceLocator $serviceLocator
     * @return Image
     */
    public function createService (ServiceLocator $serviceLocator)
    {
        $config = $serviceLocator->get ('Configuration');
        if (array_key_exists ('captcha_image', $config)) {
            $config = $config ['captcha_image'];
        }

        $imgDir
            = array_key_exists ('imgDir', $config)
            ? $config ['imgDir']
            : './data/captcha';

        if (!is_dir ($imgDir) && !mkdir ($imgDir, true)) {
            throw new \RuntimeException (
                'Cannot find or create the captcha image directory.'
            );
        }

        $config ['imgUrl'] = $serviceLocator->get ('Router')->assemble (
            array (), array ('name' => 'Lunar/captcha')
        );

        return new Image ($config);
    }
}

<?php
namespace Lunar;

return array (
    'service_manager' => array (
        'shared' => array (
            'CaptchaImage' => false
        ),
        'factories' => array (
            'CaptchaImage' => 'Lunar\Service\CaptchaImage'
        )
    ),

    'controllers' => array (
        'invokables' => array (
            'Lunar\Controller\CaptchaImage' => 'Lunar\Controller\CaptchaImageController'
        )
    ),

    'router' => array (
        'routes' =>  array (
            'Lunar' => array (
                'type' => 'literal',
                'options' => array (
                    'route' => '/lunar',
                    'defaults' => array (
                        __NAMESPACE__ => 'Lunar\Controller',
                        'controller' => 'CaptchaImage',
                        'action' => 'captcha'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array (

                    'captcha' => array (
                        'type' => 'segment',
                        'options' => array (
                            'route' => '/captcha[/:id]'
                        ),
                        'defaults' => array ()
                    )
                )
            )
        )
    ),

    // Translation sources
    'translation_sources' => array (

        // Currently only gettext is supported
        'adapter' => 'Lunar\I18n\Translation\Adapter\GettextAdapter',

        // The directories containing translatable sources
        'directories' => array (
            'view',
            'src/Form'
        ),

        // The file extensions of the files containing translatable sources
        'file_extensions' => array (
            'php',
            'phtml'
        ),

        // The keywords that indicate a translatable message (method names).
        'keywords' => array (
            'translate',
            'setLegend',
            'setLabel',
            'setTitle',
            'setMessage'
        )
    ),

    // Captcha images
    'captcha_image' => array (
        'font' => __DIR__ . '/../data/Aerial.ttf',
        'width' => 250,
        'height' => 100,
        'imgDir' => 'data/captcha',
        'dotNoiseLevel' => 40,
        'lineNoiseLevel' => 3
    )
);

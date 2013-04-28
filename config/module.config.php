<?php

return array (
    // Translation sources
    'translation_sources' => array (

        // Currently only gettext is supported
        'adapter' => 'Lunar\I18n\Translation\Adapter\GettextAdapter',

        // The directories containing translatable sources
        'directories' => array (
            'view', 'src/Form'
        ),

        // The file extensions of the files containing translatable sources
        'file_extensions' => array (
            'php', 'phtml'
        ),

        // The keywords that indicate a translatable message (method names).
        'keywords' => array (
            'translate', 'setLegend', 'setLabel', 'setTitle', 'setMessage'
        )
    )
);

# Lunar

## Services and Helpers for Zend Framework 2

Lunar is a set of services and helpers for the usage with Zend Framework 2.

Currently the following features are included:

* A translation helper tool that extracts the translation sources and prepares and updates
  the translations with and for gettext.
* A captcha image service named 'CaptchaImage'.

In order to use the functionality of this module, make sure you include it in your
`config/application.config.php` as in the following snippet:

```php
return array (
    'modules' => array (
        ...
        'Lunar'
        ...
    )
    ...
);
```

### Translation tool

The translation tool helps to extract translation sources from the project, to update
those sources and to translate the prepared translations for and with gettext.

#### The configuration

The following array content contains all configuration items for the translation tool:
```php
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
    )
```

#### Directories

The directories of the message catalogues and translation files are not yet configurable,
they point to the following folders:

* Message catalogues (`.pot` and `.po` files) are located within `module/{Module name}/po`
* Translation files (`.mo` files) are located within `module/{Module name}/language`

#### The tool

The tool itself provides the following methods:

    Usage: ./vendor/bin/translate [ options ]
    -h|--help            Show this help message
    -p|--prepare         Generate the pot file
    -u|--update          Update the po files
    -t|--translate       Generate the translation catalogs, the mo-files
    -m|--module <string> Translate within the given module

For example:

    ./vendor/bin/translate --prepare --module Application

Will create a new message catalogue `application.pot` that for example can be copied to
`de.po`, edited with poEdit and then processed with:

    ./vendor/bin/translate --translate --module Application

The above command will create the `application.mo` ready to use within the application.
As soon as more translations are available or some translations are gone, you will need to
create a new catalogue and update the translation sources:

    ./vendor/bin/translate --prepare --update --module Application

### CaptchaImage

The captcha image service creates an image on demand and delivers it on request. The image
gets deleted after it has been delivered. The captcha image is rendered with an included
free Aerial font from a package called arkpandora that gets distributed under the
Bitstream Vera license.

#### Configuration

The configuration for the captcha image is quit small and simple:
```php
    // Captcha images
    'captcha_image' => array (
        'font' => __DIR__ . '/../data/Aerial.ttf',
        'width' => 250,
        'height' => 100,
        'imgDir' => 'data/captcha',
        'dotNoiseLevel' => 40,
        'lineNoiseLevel' => 3
    )
```
#### Usage

The `CaptchaImage` service provides `Zend\Captcha\Image` instances. Since the service is
not shared, every image is a new instance. A dirty usage example:

```php
class FoobarController
    extends AbstractActionController
{
    public function indexAction ()
    {
        ...
        $form = new MyForm ();

        $captcha = new \Zend\Form\Element\Captcha ('captcha');
        $captcha->setLabel ('Please enter these characters in order to verify you are human');

        // This always creates a new instance of Zend\Captcha\Image
        $image = $this->getServiceLocator ()->get ('CaptchaImage');

        $captcha->setCaptcha ($image);

        $form->add ($captcha);
        ...
    }
}
```

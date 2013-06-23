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
return array (
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
);
```

#### Directories

The directories of the message catalogues and translation files are not yet configurable,
they point to the following folders:

* Message catalogues (`.pot` and `.po` files) are located within `module/{name of the module}/po`
* Translation files (`.mo` files) are located within `module/{name of the module}/language`

#### The tool

The tool itself provides the following methods:

    Usage: ./vendor/bin/translate [ options ]
    -h|--help            Show this help message
    -p|--prepare         Generate the pot file
    -u|--update          Update the po files
    -t|--translate       Generate the translation catalogues, the mo-files
    -m|--module <string> Translate within the given module

For example:

    ./vendor/bin/translate --prepare --module Application

Will create a new message catalogue `application.pot` that for example can be copied to
`de.po`, edited with poEdit and then processed with:

    ./vendor/bin/translate --translate --module Application

The above command will create the `application.mo` ready to use within the application.
This means, you will need to provide configuration for the translation adapter within your
modules. You probably have done this already, otherwise you can use the following
snippet that comes from the original Zend Framework skeleton application.

`module.config.php`:
```php
return array (
    ...
    // I18n
    'translator' => array (
        'locale' => 'de',
        'translation_file_patterns' => array (
            array (
                'type'     => 'Gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo'
            )
        )
    )
    ...
);
```

As soon as more translations are available or some translations are gone, you will need to
create a new catalogue and update the translation sources:

    ./vendor/bin/translate --prepare --update --module Application

The module may be given in underscore notation, instead of naming the module 'Application'
as in the above examples, using 'application' for the module name would lead to the same
result (a module 'FooBar' may be given as 'foo_bar').

### CaptchaImage

The captcha image service creates an image on demand and delivers it on request. The image
gets deleted after it has been delivered. The captcha image is rendered with an included
free Aeriaw font from a package called 'arkpandora' that gets distributed under the
Bitstream Vera license.

#### Configuration

The configuration for the captcha image is quite small and simple and hopefully
self-explanatory:
```php
return array (
    // Captcha images
    'captcha_image' => array (
        'font' => __DIR__ . '/../data/Aerial.ttf', // keep in mind that this is the
                                                   // default configuration file within
                                                   // the lunar module. You do not need to
                                                   // provide any other font if you can
                                                   // live with the provided one.
        'width' => 250,
        'height' => 100,
        'imgDir' => 'data/captcha', // This is the path to the created images from the
                                    // project root. If it doesn't exist, it will be
                                    // created if the needed permissions are available.
        'dotNoiseLevel' => 40,
        'lineNoiseLevel' => 3
    )
);
```
#### Usage

The `CaptchaImage` service provides `Zend\Captcha\Image` instances. Since the service is
not shared, every image is a new instance. A quick and dirty usage example:

```php
class FoobarController
    extends AbstractActionController
{
    public function indexAction ()
    {
        ...
        $form = new MyForm ();

        $captcha = new \Zend\Form\Element\Captcha ('captcha');
        $captcha->setLabel (
            'Please enter these characters in order to verify you are human'
        );

        // This always creates a new instance of Zend\Captcha\Image
        $image = $this->getServiceLocator ()->get ('CaptchaImage');

        $captcha->setCaptcha ($image);

        $form->add ($captcha);
        ...

        if ($this->getRequest ()->isPost ()) {
            // The captcha has been rendered and the form has been submitted.
            // By now the image does not exist physically any more, it has been
            // destroyed by the captcha image controller that provided the image
            // as soon as the client requested the image file.
            $form->setData ($this->getRequest ()->getPost ());

            if ($form->isValid ()) { // The captcha gets validated
                ...
            }
        }

        return new ViewModel (
            array ('form' => $form) // The image gets created as soon as form gets
                                    // rendered within the view.
        );
    }
}
```

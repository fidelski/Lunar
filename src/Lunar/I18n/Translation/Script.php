<?php

namespace Lunar\I18n\Translation;

use Lunar\I18n\Translation\Translator,
    Lunar\I18n\Translation\TranslationException,
    Lunar\I18n\Translation\Module,
    Lunar\I18n\Translation\Adapter\GettextAdapter,
    Zend\Mvc\Application;

/**
 * Encapsulates the execution of the translation script.
 */
class Script
{
    /**
     * Instance of Zend_Console_Getopt
     * @var $_options Zend_Console_Getopt
     */
    private $_options = null;

    /** @var Application $application */
    private $application;

    /**
     * Parses the commandline.
     * @throws \RuntimeException if the commandline cannot be parsed
     */
    public function __construct()
    {
        if (($this->_options = static::getOptions()) == null){
            throw new \RuntimeException();
        }
    }

    /**
     * Runs translation
     * @return  the exit code, 0 on success, 1 on failure
     */
    public function run()
    {
        $translator = new Translator (
            new Module(
                isset($this->_options->m) ? $this->_options->m : 'default'
            )
        );
        $translator->setAdapter(
            new GettextAdapter ()
        );
        if (isset($this->_options->h)){
            echo $this->_options->getUsageMessage();
            return 0;
        }
        if (!isset($this->_options->p) && !isset($this->_options->u) && !isset($this->_options->t)){
            echo $this->_options->getUsageMessage();
            return 1;
        }
        try {
            if (isset($this->_options->p)){
                echo "Preparing translation sources...\n";
                $translator->prepare();
            }
            if (isset($this->_options->u)){
                echo "Updating translation sources...\n";
                $translator->update();
            }
            if (isset($this->_options->t)){
                echo "Generating translation catalogue...\n";
                $translator->translate();
            }
        }
        catch (TranslationException $e){
            echo $e->getMessage() . PHP_EOL;
            return 1;
        }
        return 0;
    }

    /**
     * Factory method.
     * @return TranslateScript
     */
    public static function create ()
    { return new self (); }

    /**
     * Returns the Zend\Console\Getopt instance for this script
     * @return  Zend\Console\Getopt|null the options for this script or null if an error
     *          occured parsing the commandline
     */
    protected static function getOptions()
    {
        $options = new \Zend\Console\Getopt(
            array(
                'h|help' => 'Show this help message',
                'p|prepare' => 'Generate the pot file',
                'u|update' => 'Update the po files',
                't|translate' => 'Generate the translation catalogs, the mo-files',
                'm|module=s' => 'Translate within the given module'
            )
        );
        try {
            $options->parse();
        }
        catch (\Exception $e){
            echo 'Cannot parse the commandline: ' . $e->getMessage() . PHP_EOL;
            return null;
        }

        return $options;
    }
}

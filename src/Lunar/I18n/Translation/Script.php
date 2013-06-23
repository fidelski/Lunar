<?php

namespace Lunar\I18n\Translation;

use Lunar\I18n\Translation\Translator,
    Lunar\I18n\Translation\TranslationException,
    Lunar\I18n\Translation\Module,
    Lunar\I18n\Translation\Adapter\GettextAdapter,
    Zend\ServiceManager\ServiceLocatorInterface as ServiceLocator,
    Zend\Stdlib\ArrayUtils;

/**
 * Encapsulates the execution of the translation script.
 */
class Script
{
    /**
     * Instance of Zend_Console_Getopt
     * @var Zend_Console_Getopt $_options
     */
    protected $_options = null;

    /** @var array $config the configuration */
    protected $config = array ();

    /**
     * Parses the commandline.
     * @param array|\Traversable|null $config
     * @throws \RuntimeException if the commandline cannot be parsed
     */
    public function __construct($config = null)
    {
        if ($config) {
            $this->setConfig ($config);
        }
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
        $translator = $this->createTranslator ($this->createModule ());

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
     * @param array|\Traversable|null $config
     * @return TranslateScript
     */
    public static function create ($config = null)
    { return new self ($config); }

    /**
     * Creates a module according options and config.
     * @return Module
     */
    protected function createModule ()
    {
        $module = new Module (
            isset ($this->_options->m) ? $this->_options->m : 'default'
        );

        $config = $this->getConfig ();
        if (array_key_exists ('file_extensions', $config)) {
            $module->setFileExtensions ($config ['file_extensions']);
        }
        if (array_key_exists ('directories', $config)) {
            $module->setSourceDirectories ($config ['directories']);
        }
        if (array_key_exists ('keywords', $config)) {
            $module->setMessageKeywords ($config ['keywords']);
        }

        return $module;
    }

    /**
     * Creates a translator instance that operates on the given module.
     * @param Module $module the module the translator should operate on
     * @return Translator
     */
    protected function createTranslator (Module $module)
    {
        $translator = new Translator ($module);

        $config = $this->getConfig ();
        if (array_key_exists ('adapter', $config)) {
            $adapter = new $config ['adapter'] ();
            $translator->setAdapter ($adapter);
        }

        return $translator;
    }

    /**
     * @param array|\Traversable $config
     * @return Script
     */
    public function setConfig ($config)
    {
        $config = ArrayUtils::iteratorToArray ($config, true);

        if (array_key_exists ('translation_sources', $config)) {
            $config = $config ['translation_sources'];
        }

        $this->config = $config;

        return $this;
    }

    /**
     * @return array
     */
    public function getConfig ()
    { return $this->config; }

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
                't|translate' => 'Generate the translation catalogues, the mo-files',
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

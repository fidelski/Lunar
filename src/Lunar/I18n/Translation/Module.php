<?php
/**
 * @namespace
 */
namespace Lunar\I18n\Translation;

/**
 * Describes a module within the application with its translation sources.
 */
class Module
{
    /**
     * @const string DEFAULT_NAME the default module to use
     */
    const DEFAULT_NAME                      = 'default';

    /** @var string $name the current module */
    protected $name                         = self::DEFAULT_NAME;

    /** @var string $path the path to the module */
    protected $path                         = null;

    /** @var array $sourceDirectories */
    protected static $sourceDirectories     = array (
        'view', 'src/Form'
    );

    /**
     * All known file extensions
     * @var array $fileExtensions
     */
    protected static $fileExtensions        = array (
        'php', 'phtml'
    );

    /**
     * The keywords that should trigger an extraction of the message
     * @var array $messageKeywords
     */
    private static $messageKeywords         = array (
        'translate', 'setLegend', 'setLabel', 'setTitle'
    );

    /**
     * Constructor
     * @param string $module the module to use
     * @throws ModuleException if the given module does not exist
     */
    public function __construct($module = self::DEFAULT_NAME)
    {
        if (self::DEFAULT_NAME != $module){
            $this->setName($module);
        }
    }

    /**
     * Sets the the module to use.
     * @param string $name the module to use
     * @return Module
     * @throws ModuleException if the module does not exist
     */
    public function setName($name)
    {
        if (!is_string ($name)) {
            throw new ModuleException ('The module name must be a string.');
        }
        $name = trim($name, " \t\n\r\0\x0B" . DIRECTORY_SEPARATOR);

        $path = $this->buildPath ($name);
        if (!is_dir ($path)) {
            throw new ModuleException (
                "The module '{$name}' cannot be found within '{$path}'"
            );
        }
        $this->name = $name;
        $this->path = $path;

        return $this;
    }

    /**
     * Returns the currently used module.
     * @return string the name of the currently used module
     */
    public function getName()
    { return $this->name; }

    /**
     * Returns all files that need to be translated.
     * @return array
     */
    public function getTranslationSources ()
    {
        $files = array();

        foreach (self::$sourceDirectories as $sub_directory) {

            $path = $this->getPath () . DIRECTORY_SEPARATOR . $sub_directory;

            if (!is_dir ($path)) continue;

            $iter = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iter as $fileInfo){
                $extension = pathinfo($fileInfo->getPathname(), PATHINFO_EXTENSION);

                if (empty($extension)) continue;

                if (in_array($extension, self::$fileExtensions)){
                    $files [] = $fileInfo->getPathname ();
                }
            }
        }

        return $files;
    }

    /**
     * Returns the path to the currently used module.
     * @return string the path to the currently used module
     */
    public function getPath()
    {
        if (null === $this->path) {

            $path = $this->buildPath ($this->getName ());

            if (!is_dir ($path)) {
                throw new ModuleException (
                    "The module '{$this->getName ()}' cannot be found."
                );
            }

            $this->path = $path;
        }

        return $this->path;
    }

    /**
     * Returns all method names that should trigger an extraction of a translatable message.
     * @return array all method names that should trigger an extraction of a translatable message
     */
    public function getMessageIdentifiers ()
    { return self::$messageKeywords; }

    /**
     * Builds the path to the given module.
     * @param string $name the name of the module
     * @return string the path to the given module
     */
    protected function buildPath ($name)
    {
        $path = 'module' . DIRECTORY_SEPARATOR;

        if (self::DEFAULT_NAME === $name){
            $path .=  'Application';
        }
        else {
            $filter = new \Zend\Filter\Word\UnderscoreToCamelCase ();
            $path .=  $filter->filter ($name);
        }

        return $path;
    }
}

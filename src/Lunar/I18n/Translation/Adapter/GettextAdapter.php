<?php
/**
 * @namespace
 */
namespace Lunar\I18n\Translation\Adapter;

use Lunar\I18n\Translation\Module,
    Lunar\I18n\Translation\Message,
    Lunar\I18n\Translation\TranslationException,
    Lunar\I18n\Translation\Catalogue\CatalogueInterface;

/**
 * Implements the gettext Translation Adapter Interface.
 *
 * This Adapter requires the gettext binary to be installed, actually
 * msgfmt, msgmerge and xgettext need to be found within PATH.
 */
class GettextAdapter
    implements TranslationAdapter
{
    /**
     * The maximum number of characters on a single line within the pot file
     * @const int LINE_LIMIT
     */
    const LINE_LIMIT        = 75;

    /**
     * The currently used module
     * @var Module $_module
     */
    private $_module            = null;

    /**
     * All catalogues
     * @var array $_catalogues
     */
    private $_catalogues        = array();

    /** @var string $pot_file_path */
    private $pot_file_path      = null;

    /** @var string $po_file_directory */
    private $po_file_directory  = null;

    /**
     * Sets the module to be used / to work in.
     * @param Module $module
     */
    public function setModule(Module $module)
    {
        $this->_module = $module;
        return $this;
    }

    /**
     * Returns the currently used module.
     * @return Module
     */
    public function getModule()
    {
        if (null === $this->_module) {
            throw new TranslationException ('No module set.');
        }

        return $this->_module;
    }

    /**
     * Adds a catalogue.
     * @param CatalogueInterface $catalogue
     * @return GettextAdapter
     */
    public function addCatalogue(CatalogueInterface $catalogue)
    {
        $this->_catalogues[] = $catalogue;
        return $this;
    }

    /**
     * Returns all registered catalogues.
     * @return array of CatalogueInterface
     */
    public function getCatalogues()
    { return $this->_catalogues; }

    /**
     * Prepares the translation - generates the translation sources.
     * @return GettextAdapter
     * @throws  TranslationException
     */
    public function prepare()
    {
        $this
            ->_generatePotFile()
            ->_appendCataloguesToPotFile();

        return $this;
    }

    /**
     * Translates the sources.
     * @return GettextAdapter
     */
    public function translate()
    {
        foreach ($this->allPoFiles() as $pofile){
            $this->_translate($pofile->path, $pofile->language);
        }
        return $this;
    }

    /**
     * Updates the translation sources with already present translations.
     * @return GettextAdapter
     */
    public function update()
    {
        $potfilePath = realpath($this->getPotFilePath ());

        if (empty($potfilePath))
            throw new TranslationException('Cannot locate the potfile');

        foreach ($this->allPoFiles() as $pofile){
            $this->_update($pofile->path, $potfilePath);
        }

        return $this;
    }

    /**
     * Generates the pot file.
     * @return GettextAdapter
     * @throws  TranslationException
     * @requires xgettext utility within PATH
     */
    protected function _generatePotFile()
    {
        // assure the path
        $poPath = $this->getModule()->getPath () . DIRECTORY_SEPARATOR . 'po';
        if (!is_dir($poPath) && !mkdir($poPath)) {
            throw new TranslationException('Cannot create the po-file path ' . $poPath);
        }

        // generate the POTFILES.in file
        $potfilesIn = $poPath . DIRECTORY_SEPARATOR . 'POTFILES.in';
        $potFiles = $this->getModule ()->getTranslationSources ();
        if (false === file_put_contents($potfilesIn, implode(PHP_EOL, $potFiles))){
            throw new TranslationException('Cannot create the POTFILES.in file');
        }

        // build the xgettext command and execute it
        $filter = new \Zend\Filter\Word\UnderscoreToCamelCase ();
        $moduleName = $filter->filter ($this->getModule()->getName());
        $command = 'xgettext ';

        foreach ($this->getModule ()->getMessageKeywords () as $keyword){
            $command .= '--keyword=' . trim($keyword) . ' ';
        }
        $command .= '--default-domain=' . escapeshellarg($moduleName)
            . ' --package-name=' . escapeshellarg($moduleName)
            . ' --language=PHP'
            . ' --output=' . escapeshellarg($this->getPotFilePath ())
            . ' --files-from=' . escapeshellarg($potfilesIn)
            . ' --add-comments'
            . ' --force-po'
            . ' --width ' . self::LINE_LIMIT;

        $retval = 1;
        $messages = array();
        exec($command, $messages, $retval);
        if (0 != $retval){
            throw new TranslationException(
                'Cannot create the Translation source file ' . $this->getPotFilePath ()
            );
        }

        // set the utf-8 charset (a pitty to set manually every time)
        if (file_exists($this->getPotFilePath ())) {
            $this->_setPotFileCharset($this->getPotFilePath (), 'utf-8');
        }

        return $this;
    }

    /**
     * Sets the charset in the potfile.
     * @param string $filepath
     * @return GettextAdapter
     * @throws TranslationException
     */
    protected function _setPotFileCharset($filepath, $charset)
    {
        $fileContent = file_get_contents($filepath);
        if (false === $fileContent || empty($fileContent))
            throw new TranslationException('Cannot read the potfile content from ' . $filepath);

        $search = 'Content-Type: text/plain; charset=CHARSET';
        if (false !== ($pos = strpos($fileContent, $search))){
            $content = substr($fileContent, 0, $pos);
            $content .= 'Content-Type: text/plain; charset=' . $charset;
            $content .= substr($fileContent, $pos + strlen($search));
            if (false === file_put_contents($filepath, $content))
                throw new TranslationException('Cannot write the potfile content to ' . $filepath);
        }
        return $this;
    }

    /**
     * Appends the registered catalogues to the pot file.
     * @return GettextAdapter
     * @throws TranslationException
     */
    protected function _appendCataloguesToPotFile()
    {
        $catalogues = $this->getCatalogues();
        if (empty($catalogues)) return $this;

        $potfile = $this->getPotFilePath ();

        if (!file_exists($potfile))
            throw new TranslationException('Cannot find the potfile ' . $potfile);

        if (false === ($content = file_get_contents($potfile)) || empty($content))
            throw new TranslationException('Cannot read the potfile ' . $potfile);

        $catalogue = array_shift($catalogues);
        foreach ($catalogues as $_catalogue){
            $catalogue->merge($_catalogue);
        }
        // this one got eaten by file_get_contents, so re-append it
        $content .= PHP_EOL;
        $content = $this->_appendCatalogue($catalogue, $content);
        if (false === file_put_contents($potfile, $content))
            throw new TranslationException('Cannot write the potfile ' . $potfile);

        return $this;
    }

    /**
     * Appends a catalogue.
     * @param CatalogueInterface $catalogue
     * @param string $content
     * @return string
     */
    protected function _appendCatalogue(CatalogueInterface $catalogue, $content)
    {
        foreach ($catalogue->messages() as $message){
            $idContent = $this->_messagePartToString($message->id());
            if (false !== ($pos = strpos($content, $idContent))){
                $head = substr($content, 0, $pos);
                $head .= $this->_messageSourcesToString($message);
                $head .= substr($content, $pos);
                $content = $head;
            }
            else {
                $content .= $this->_messageToString($message);
            }
        }
        return $content;
    }

    /**
     * Generates an entry for the pot file of the given message.
     * @return string
     */
    public function _messageToString(Message $message)
    {
        $str = $this->_messageSourcesToString($message);
        $str .= $this->_messagePartToString($message->id());
        if ($message->hasTranslation())
            $str .= $this->_messagePartToString($message->translation(), 'msgstr');
        else
            $str .= 'msgstr ""' . PHP_EOL;
        $str .= PHP_EOL;
        return $str;
    }

    /**
     * Generates an entry for the pot file of the given message sources.
     * Returns the source as a translation source string
     * @return string
     */
    protected function _messageSourcesToString(Message $message)
    {
        $str = '';
        foreach ($message->sources() as $source){
            $str .= '#: ' . $source->filename()
                . ($source->line() ? ':' . $source->line() : '')
                . PHP_EOL;
        }
        return $str;
    }

    /**
     * Generates an entry for the pot file of the given message string.
     * @param string $id
     * @param string $prefix
     * @return string
     */
    protected function _messagePartToString($id, $prefix = 'msgid')
    {
        $str = $prefix . ' ' . '"';
        $id = preg_replace('/([^\\\])?"/', '\\"', $id);
        if (self::LINE_LIMIT < strlen($prefix . '"' . $id . '"')){
            $str .= '"' . PHP_EOL . '"'
                . wordwrap($id, self::LINE_LIMIT, ' ' . '"' . PHP_EOL . '"');
        }
        else {
            $str .= $id;
        }
        $str .= '"' . PHP_EOL;
        return $str;
    }

    /**
     * Translates all found translations within the given path.
     * @param string $poFilePath the path to translate in
     * @param string $language the language to translate
     * @return GettextAdapter
     */
    protected function _translate($poFilePath, $language)
    {
        $moFilePath
            = $this->getModule()->getPath() . DIRECTORY_SEPARATOR
            . 'language' . DIRECTORY_SEPARATOR
            . $language . '.mo';

        $command = 'msgfmt -o ' . escapeshellarg($moFilePath) . ' ' . escapeshellarg($poFilePath);
        $retval = 1;
        $messages = array();
        exec($command, $messages, $retval);
        if (0 != $retval)
            throw new TranslationException('Cannot translate the po-file ' . $poFilePath);
        return $this;
    }

    /**
     * Updates present translation sources with already present translations.
     * @param string $pofile the pofile that should get updated
     * @param string $potfile the potfile that should get used in order to update the pofile
     */
    protected function _update($pofile, $potfile)
    {
        $command = 'msgmerge -U ' . escapeshellarg($pofile) . ' ' . escapeshellarg($potfile);
        $retval = 1;
        $messages = array();
        exec($command, $messages, $retval);
        if (0 != $retval)
            throw new TranslationException(
                'Cannot update the pofile ' . $pofile . ' with the potfile ' . $potfile
            );
        return $this;
    }

    /**
     * Returns an array of ArrayObjects containing path (string) and language (string).
     * @return array
     */
    protected function allPoFiles()
    {
        $poFiles = array();
        $iter = new \DirectoryIterator ($this->getPoFileDirectory ());

        foreach ($iter as $fileInfo){

            if (
                !$fileInfo->isDot()
                && !$fileInfo->isDir()
                && $fileInfo->getExtension () === 'po'
            ){
                $poFiles [] = new \ArrayObject(
                    array(
                        'path' => $fileInfo->getPathname (),
                        'language' => $fileInfo->getBasename ('.po')
                    ),
                    \ArrayObject::ARRAY_AS_PROPS
                );
            }
        }

        return $poFiles;
    }

    /**
     * Returns the path to the pot file.
     * @return string the path to the pot file
     */
    protected function getPotFilePath ()
    {
        if (null === $this->pot_file_path) {

            $path = $this->getPoFileDirectory () . DIRECTORY_SEPARATOR;

            $filter = new \Zend\Filter\Word\CamelCaseToUnderscore ();
            $path
                .= strtolower ($filter->filter ($this->getModule ()->getName ()))
                . '.pot';

            $this->pot_file_path = $path;
        }

        return $this->pot_file_path;
    }

    /**
     * Returns the path to the po files.
     * @return string the path to the po files
     */
    protected function getPoFileDirectory ()
    {
        if (null === $this->po_file_directory) {

            $this->po_file_directory
                = $this->getModule ()->getPath ()
                . DIRECTORY_SEPARATOR . 'po';
        }

        return $this->po_file_directory;
    }
}

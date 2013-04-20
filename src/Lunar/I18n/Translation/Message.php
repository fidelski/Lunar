<?php
/**
 * @namespace
 */
namespace Lunar\I18n\Translation;

/**
 * Represents a single message within the translation system
 */
class Message
{
    /**
     * @var string $_id the message identifier
     */
    private $_id            = '';

    /**
     * @var array $_sources all sources of this message
     */
    private $_sources       = array();

    /**
     * @var string $_translation an optional translation for the message
     */
    private $_translation   = null;

    /**
     * Constructs the message with the id, filename and the line number
     * @param string $id the identifier of the message (the untranslated message)
     * @param string $filename the path to the file of the translation source
     * @param int|string|null $line the line within the file of the translation source
     */
    public function __construct($id, $filename, $line = null)
    {
        $this->_id = (string) $id;

        $this->addSource(new Source($filename, $line));
    }

    /**
     * Returns the id of the message
     * @return string
     */
    public function id()
    { return $this->_id; }

    /**
     * Returns an array of all sources
     * @return array of Source
     */
    public function sources()
    { return $this->_sources; }

    /**
     * Adds a source
     * @param string|Source $filename
     * @param int|string|null $line
     * @return Message
     */
    public function addSource($filename, $line = null)
    {
        $source = null;
        if ($filename instanceof Source){
            $source = $filename;
        }
        else {
            $source = new Source($filename, $line);
        }
        $key = $source->filename() . ($source->line() ? ':' . $source->line() : '');
        if (!array_key_exists($key, $this->_sources)){
            $this->_sources[$key] = $source;
        }
        return $this;
    }

    /**
     * Sets the translation of the message.
     * @param string $translation the translation of the message
     * @return Message
     */
    public function setTranslation($translation)
    {
        $translation = trim((string) $translation);
        if (!empty($translation))
            $this->_translation = $translation;
        else
            $this->_translation = null;
        return $this;
    }


    /**
     * Returns the translation of the message or null if it has not been set.
     * @return null|string the translation of the message or null if it has not been set.
     */
    public function translation()
    { return $this->_translation; }

    /**
     * Whether the message has a translation set.
     * @return boolean true if the message has a translation set, false otherwise
     */
    public function hasTranslation()
    { return null !== $this->_translation; }
}

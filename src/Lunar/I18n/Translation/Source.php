<?php
/**
 * @namespace
 */
namespace Lunar\I18n\Translation;

/**
 * Represents a source location
 */
class Source
{
    /**
     * @var string $_filename the path to the translation source
     */
    private $_filename = '';

    /**
     * @var string|int|null $_line the line of of the translation source within the file
     */
    private $_line = null;

    /**
     * @param string $filename the path of the translation source
     * @param string|int|null $line the line within the translation source
     */
    public function __construct($filename, $line = null)
    {
        $this->_filename = (string) $filename;
        $this->_line = $line;
    }

    /**
     * Returns the path to the file of the translation source.
     * @return string the path to the file of the translation source
     */
    public function filename()
    { return $this->_filename; }

    /**
     * Returns the line within the file of the translation source.
     * @return int|string|null the line within the file of the translation source
     */
    public function line()
    { return $this->_line; }
}

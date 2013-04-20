<?php
/**
 * @namespace
 */
namespace Lunar\I18n\Translation\Catalogue;

use Lunar\I18n\Translation\Message,
    Lunar\I18n\Translation\Module;

/**
 * Represents a standard catalogue that may be used as a base
 * class for different catalogue types.
 */
abstract class AbstractCatalogue
    implements CatalogueInterface
{
    /** @var Module $_module */
    private $_module            = null;

    /** @var array $_messages */
    private $_messages = array();

    /**
     * @param Module $module
     */
    public function setModule(Module $module)
    {
        $this->_module = $module;
        return $this;
    }

    /**
     * @return null|Module
     */
    public function getModule()
    { return $this->_module; }

    /**
     * @param Message $message
     * @return AbstractCatalogue
     */
    public function addMessage(Message $message)
    {
        foreach ($this->_messages as $_message){
            if (0 == strcmp($_message->id(), $message->id())){
                foreach ($message->sources() as $source){
                    $_message->addSource($source);
                }
                return $this;
            }
        }
        $this->_messages[] = $message;
        return $this;
    }

    /**
     * @return array of Message
     */
    public function messages()
    { return $this->_messages; }

    /**
     * @param CatalogueInterface $catalogue
     * @return AbstractCatalogue
     */
    public function merge(CatalogueInterface $catalogue)
    {
        foreach ($catalogue->messages() as $message){
            $this->addMessage($message);
        }
        return $this;
    }
}

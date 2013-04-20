<?php
/**
 * @namespace
 */
namespace Lunar\I18n\Translation\Catalogue;

use Lunar\I18n\Translation\Message,
    Lunar\I18n\Translation\Module;

/**
 * Represents a catalogue of messages to be translated
 */
interface CatalogueInterface
{
    /**
     * Adds a single message to the catalogue
     * No double entries of the same message shall exist after
     * adding a message.
     * @param Message $message the message to add
     * @return CatalogueInterface
     */
    public function addMessage(Message $message);

    /**
     * Returns all messages as an array.
     * @return array of Message
     */
    public function messages();

    /**
     * Merges the given catalogue into this one.
     * @param CatalogueInterface $catalogue the catalogue to merge
     * @return CatalogueInterface
     */
    public function merge(CatalogueInterface $catalogue);

    /**
     * Sets the module
     * @param Module the module to use / work in
     * @return CatalogueInterface
     */
    public function setModule(Module $module);

    /**
     * Returns the module or null if none was set
     * @return null|Module the module currently working in
     */
    public function getModule();
}

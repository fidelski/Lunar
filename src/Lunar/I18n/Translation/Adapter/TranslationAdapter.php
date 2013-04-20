<?php
/**
 * @namespace
 */
namespace Lunar\I18n\Translation\Adapter;

use Lunar\I18n\Translation\Module,
    Lunar\I18n\Translation\Catalogue\CatalogueInterface;

/**
 * Interface for an adapter that implements gathering of messages and preparation
 * and translation of the message catalogues.
 */
interface TranslationAdapter
{
    /**
     * Set the module to be used
     * @param Module $module
     */
    public function setModule(Module $module);

    /**
     * Return the currently used module
     * @return Module
     */
    public function getModule();

    /**
     * Add a catalogue with messages
     * @param CatalogueInterface $catalogue
     */
    public function addCatalogue(CatalogueInterface $catalogue);

    /**
     * Returns all registered catalogues.
     * @return array of CatalogueInterface
     */
    public function getCatalogues();

    /**
     * Process translation
     * @return void
     */
    public function translate();

    /**
     * Prepare translation - create translation sources.
     * @return void
     */
    public function prepare();

    /**
     * Update existing translation sources with existing translations.
     * @return void
     */
    public function update();
}

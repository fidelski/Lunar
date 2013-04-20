<?php

namespace Lunar\I18n\Translation;

use Lunar\I18n\Translation\Adapter\TranslationAdapter,
    Lunar\I18n\Translation\Adapter\GettextAdapter,
    Lunar\I18n\Translation\Catalogue\CatalogueInterface;


class Translator
{
    /**
     * @var Module $_module
     */
    private $_module                            = null;

    /**
     * @var TranslationAdapter
     */
    private $_adapter                           = null;

    /**
     * @var array $_moduleCatalogues
     */
    private static $_defaultCatalogues          = array(
        'acl_resources'  => 'Lunar\I18n\Translation\Catalogue\AclResources'
    );

    /**
     * @var array $_catalogues
     */
    private $_catalogues                        = array();

    /**
     * @var array $_cataloguesLoaded
     */
    private $_cataloguesLoaded                  = array();

    /**
     * Constructor
     * @param Module $module
     */
    public function __construct(Module $module = null)
    {
        if ($module){
            $this->setModule($module);
        }
        $this->_catalogues = self::$_defaultCatalogues;
    }

    /**
     * @param  Module $module
     * @return Translator
     */
    public function setModule(Module $module)
    {
        $this->_module = $module;
        $this->getAdapter()->setModule($module);
        return $this;
    }

    /**
     * @return Module
     */
    public function getModule()
    {
        if (null === $this->_module){
            $this->setModule(new Module());
        }
        return $this->_module;
    }

    /**
     * @param TranslationAdapter
     * @return Translator
     */
    public function setAdapter(TranslationAdapter $adapter)
    {
        $this->_adapter = $adapter;
        $this->_adapter->setModule($this->getModule());
        return $this;
    }

    /**
     * @return TranslationAdapter
     */
    public function getAdapter()
    {
        if (null === $this->_adapter){
            $this->setAdapter(new GettextAdapter());
        }
        return $this->_adapter;
    }

    /**
     * @param string $name
     * @return Module
     */
    public function setModuleName($name)
    {
        $this->setModule(new Module($name));
        return $this;
    }

    /**
     * @return string
     */
    public function getModuleName()
    { return $this->getModule()->getModule(); }

    /**
     * @return Translator
     * @throws  TranslationException
     */
    public function prepare()
    {
        $this->_addCatalogues($this->getAdapter());
        $this->getAdapter()->prepare();
        return $this;
    }

    /**
     * @return Translator
     * @throws  TranslationException
     */
    public function update()
    {
        $this->getAdapter()->update();
        return $this;
    }

    /**
     * @return Translator
     * @throws  TranslationException
     */
    public function translate()
    {
        $this->getAdapter()->translate();
        return $this;
    }

    /**
     * @return Translator
     */
    private function _addCatalogues(TranslationAdapter $adapter)
    {
        if (in_array($adapter, $this->_cataloguesLoaded)) return $this;

        foreach ($this->_catalogues as $catalogueType){
            $catalogue = new $catalogueType;
            if ( ! ($catalogue instanceof CatalogueInterface)){
                throw new TranslationException(
                    'Catalogue ' . get_class($catalogue)
                    . ' must implement Lunar\I18n\Translation\Catalogue\CatalogueInterface'
                );
            }
            $catalogue->setModule($this->getModule());
            $adapter->addCatalogue($catalogue);
        }

        $this->_cataloguesLoaded[] = $adapter;
        return $this;
    }
}

<?php
/**
 * @namespace
 */
namespace Lunar\I18n\Translation\Catalogue;

use Lunar\I18n\Translation\Module,
    Lunar\I18n\Translation\Message;

/**
 * Collects the messages from the acl system.
 */
class AclResources
    extends AbstractCatalogue
{
    /**
     * @var array $_ignoreIds
     */
    private static $_ignoredIds        = array('id');

    /**
     * @param Module $module
     * @return AclResources
     */
    public function setModule(Module $module)
    {
        parent::setModule($module);
        $this->_processAclResourcesConfigs();
        return $this;
    }

    /**
     * Processes all of the possible acl_resources configurations
     * @return  AclResources
     */
    protected function _processAclResourcesConfigs()
    {
        $path = $this->getModule()->getPath() . DIRECTORY_SEPARATOR
            . 'configs' . DIRECTORY_SEPARATOR;
        foreach (array('ini', 'xml', 'yml', 'json') as $extension){
            $filePath = $path . 'acl_resources.' . $extension;
            if (file_exists($filePath)){
                $config = null;
                switch ($extension){
                    case 'ini':
                        $config = new \Zend\Config\Ini($filePath);
                        break;

                    case 'xml':
                        $config = new \Zend\Config\Xml($filePath);
                        break;

                    case 'yml':
                        $config = new \Zend\Config\Yml($filePath);
                        break;

                    case 'json':
                        $config = new \Zend\Config\Json($filePath);
                        break;
                }
                if ($config){
                    $this->_pushConfig($config, $filePath);
                }
            }
        }
        return $this;
    }

    /**
     * Adds the messages from the given traversable $config to this catalogue.
     * @param   $config a traversable item with messages to add
     * @param   $filePath the path to the file, where $config is in
     */
    protected function _pushConfig($config, $filePath)
    {
        foreach ($config as $key => $value){
            if (is_array($value) || $value instanceof \Traversable){
                $this->_pushConfig($value, $filePath);
            }
            else {
                if (in_array($key, self::$_ignoredIds)) continue;
                $this->addMessage(new Message($value, $filePath));
            }
        }
    }
}

<?php

namespace Excellence\StoreCredit\Model\Adminhtml\System\Config;

use Magento\Store\Model\ScopeInterface;

class ModuleVersion extends \Excellence\Base\Block\Adminhtml\System\Config\Form\Version
{
    protected $_moduleList;
    protected $_moduleManager;
    protected $_productMetadata;
    protected $_serverAddress;
    protected $_storeManager;
    protected $_cacheManager;
    protected $_objectManager;

    protected $_wikiLink ='http://wiki.xmagestore.com';
    protected $_moduleName = 'Excellence_StoreCredit';
    protected $_moduleTitle = 'Store Credit';

    public function __construct(
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\HTTP\PhpEnvironment\ServerAddress $serverAddress,
        \Magento\Framework\App\Cache\Proxy $cacheManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($moduleList,$moduleManager,$storeManager,$productMetadata,$serverAddress,$cacheManager,$objectManager,$context,$data);

        $this->_moduleList       = $moduleList;
        $this->_moduleManager    = $moduleManager;
        $this->_storeManager     = $storeManager;
        $this->_productMetadata  = $productMetadata;
        $this->_serverAddress    = $serverAddress;
        $this->_cacheManager    = $cacheManager;
        $this->_objectManager    = $objectManager;
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->getModuleInfoHtml();
    }

    public function getWikiLink()
    {
        return $this->_wikiLink;
    }

    public function getModuleTitle()
    {
        return $this->_moduleTitle;
    }
    public function getModuleName(){
        
        return $this->_moduleName;
    }
    public function getModuleInfoHtml()
    {
        $m = $this->_moduleList->getOne($this->getModuleName());
        $html = '<tr><td class="label" colspan="4" style="text-align: left;"><div style="padding:10px;background-color:#f8f8f8;border:1px solid #ddd;margin-bottom:7px;">
            ' . $this->getModuleTitle() . ' v' . $m['setup_version'] . ' was developed by <a href="https://xmagestore.com" target="_blank">xMagestore</a>.
            For manual & tutorials please refer to <a href="' . $this->_wikiLink . '" target="_blank">our online documentation<a/>.
         </div></td></tr>';

         return $html;
    }
}
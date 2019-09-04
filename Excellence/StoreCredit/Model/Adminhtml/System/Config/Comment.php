<?php

namespace Excellence\StoreCredit\Model\Adminhtml\System\Config;

use Magento\Store\Model\ScopeInterface;

class Comment extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_moduleList;
    protected $_moduleManager;
    protected $_productMetadata;
    protected $_serverAddress;
    protected $_storeManager;
    protected $_cacheManager;
    protected $_objectManager;

    protected $_wikiLink;// = 'http://wiki.xmagestore.com';
    protected $_moduleName;// = 'Geoip Store Switcher';

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
        parent::__construct($context, $data);

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
        return $this->_moduleName;
    }

    public function getModuleInfoHtml()
    {
        $html = '<tr><td class="label" colspan="4" style="text-align: left;"><div style="padding:10px;background-color:#f8f8f8;border:1px solid #ddd;margin-bottom:7px;">
            Enter amount in Equivalent Currency field which is equal to unit credit point i.e. 1 store credit point will be equal to amount entered in <strong>Equivalent Currency</strong> for example: 1 unit credit point ≅ 1 USD (where USD is Base Currency)</div></td></tr>';

         return $html;
    }
}
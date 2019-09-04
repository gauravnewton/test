<?php
namespace Excellence\StoreCredit\Block;
  
class Main extends \Magento\Framework\View\Element\Template
{
    const EQ_CURRENCY = 'storecredit/points_currency/currency';

    protected $_storecredit;
    protected $customerSession;
    protected $helper;
    protected $datahelper;
    protected $price;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Excellence\StoreCredit\Model\StorecreditFactory $storecredit,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Pricing\Helper\Data $helper,
        \Magento\Directory\Helper\Data $datahelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $price
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->price = $price;
        $this->datahelper = $datahelper;
        $this->helper = $helper;
        $this->storeManager = $context->getStoreManager();
        $this->customerSession = $customerSession;
        $this->_storecredit = $storecredit;
        parent::__construct($context);
    }
    protected function _prepareLayout()
    {
 
        parent::_prepareLayout();
        if ($this->getCustomerRecord()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'reward.history.pager'
            )->setAvailableLimit(array(5=>5,10=>10,15=>15,20=>20))
             ->setShowPerPage(true)->setCollection(
                $this->getCustomerRecord()
            );
            $this->setChild('pager', $pager);
            $this->getCustomerRecord()->load();
        }
        return $this;
    }
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    public function getCustomerRecord(){
        $page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest
        ()->getParam('limit') : 5;

        $customerId = $this->customerSession->getCustomer()->getId();
        $model = $this->_storecredit->create();
        return $model->getCollection()->addFieldtofilter('customer_id',$customerId)->setOrder('store_credit_id','DESC')->setPageSize($pageSize)->setCurPage($page);
    }
    public function getCreditBalance(){
        $total = 0;
        $customerId = $this->customerSession->getCustomer()->getId();
        $model = $this->_storecredit->create();
        $data = $model->getCollection()->addFieldtofilter('customer_id',$customerId);
        foreach ($data as $arr) {
            $total = $total + (int)$arr['credit'];
        }
        $amount = $this->scopeConfig->getValue(self::EQ_CURRENCY,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $total = $amount * $total;
        $formattedPrice = $this->helper->currency($total, true, false);
        return $formattedPrice;
    }

     public function getCreditBalanceMain(){
        $total = 0;
        $customerId = $this->customerSession->getCustomer()->getId();
        $model = $this->_storecredit->create();
        $data = $model->getCollection()->addFieldtofilter('customer_id',$customerId);
        foreach ($data as $arr) {
            $total = $total + (int)$arr['credit'];
        }
        $amount = $this->scopeConfig->getValue(self::EQ_CURRENCY,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $total = $amount * $total;
        return $total;
    }

    public function getCreditAmount($amount){
        $multiplyer = $this->scopeConfig->getValue(self::EQ_CURRENCY,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $total_amt = $amount * $multiplyer;
        $baseCurrencyCode = $this->storeManager->getStore()->getBaseCurrencyCode(); 
        $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $formattedPrice = $this->helper->currency($total_amt, true, false);
        return $formattedPrice;
    }
}

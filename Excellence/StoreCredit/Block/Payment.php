<?php
namespace Excellence\StoreCredit\Block;

class Payment extends \Magento\Framework\View\Element\Template
{
	protected $mainBlock;
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\SessionFactory $session,
        \Excellence\StoreCredit\Block\Main $mainBlock
  )
  {
    $this->mainBlock =$mainBlock;
    $this->_scopeConfigObject = $context->getScopeConfig();
    $this->pageConfig = $context->getPageConfig();
    $this->session   = $session;
    parent::__construct($context);
  }

	public function credit()
	{   
		$StoreCreditBalance=$this->mainBlock->getCreditBalance();
		return $StoreCreditBalance;
	}
	public function enable()
	{
	 return	$this->_scopeConfigObject->getValue('storecredit/advanced_setting/enable_control');
	}
	public function getQuoteId()
  {
    $quoteId = $this->session->create()->getQuote()->getEntityId();
    return $quoteId;
  }
  public function checkCredit()
  {   
    $checkBal= $this->mainBlock->getCreditBalanceMain();
    return $checkBal;
  }
}
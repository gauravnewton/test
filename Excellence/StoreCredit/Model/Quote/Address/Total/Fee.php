<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Excellence\StoreCredit\Model\Quote\Address\Total;
class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
   /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $quoteValidator = null; 
    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Magento\Customer\Model\Session $customerSession,
        \Excellence\StoreCredit\Block\Main $mainBlock,
        \Excellence\StoreCredit\Model\StoreusedFactory $storeusedFactory,
        \Magento\Framework\Registry $registry
    )
    {
        $this->quoteValidator = $quoteValidator;
        $this->customerSession = $customerSession;
        $this->mainBlock = $mainBlock;
        $this->_storeused = $storeusedFactory;
        $this->registry     = $registry;
    }
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) 
    {
        parent::collect($quote, $shippingAssignment, $total);
        $address = $shippingAssignment->getShipping()->getAddress();
        if($address->getAddressType() == 'billing'){
            return $this;
        }
        $totalCreditBalance = $this->mainBlock->getCreditBalanceMain();
        if($this->customerSession->isLoggedIn()) {
   
            $quoteEntityId = $quote->getEntityId();
            $collection    = $this->_storeused->create()->getCollection()->addFieldToFilter('quote_id', $quoteEntityId);
            if(!empty($collection->getData()) || $collection->getData()){
                $checked = $collection->getFirstItem()->getDebit();
                if($checked == 'true'){ 
                    if($quote->getBaseSubtotal() <= $totalCreditBalance){
                        $balance = $quote->getBaseSubtotal();
                    } 
                    else{
                        $balance = $totalCreditBalance;
                    }
                    
                    $total->addTotalAmount('usedcredit', -$balance);
                    $total->addBaseTotalAmount('usedcredit', -$balance);
                    $total->setUsedcredit(-$balance);
                    $total->setBaseUsedcredit(-$balance);
                    $quote->setUsedcreditAmount(-$balance);
                    $quote->setBaseUsedcreditAmount(-$balance);
                    $total->setGrandTotal($total->getGrandTotal());
                    $total->setBaseGrandTotal($total->getBaseGrandTotal());
                } 
                else { 

                    $balance = 0;
                    $total->addTotalAmount('usedcredit', -$balance);
                    $total->addBaseTotalAmount('usedcredit', -$balance);
                    $total->setUsedcredit(-$balance);
                    $total->setBaseUsedcredit(-$balance);
                    $quote->setUsedcreditAmount(-$balance);
                    $quote->setBaseUsedcreditAmount(-$balance);
                    $total->setGrandTotal($total->getGrandTotal());
                    $total->setBaseGrandTotal($total->getBaseGrandTotal());
                }
            }
        }
        return $this;
    } 
 
   
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {  
        $result = '';
        $totalCreditBalance = $this->mainBlock->getCreditBalanceMain();
        if($this->customerSession->isLoggedIn()){
            $quoteEntityId = $quote->getEntityId();
            $collection    = $this->_storeused->create()->getCollection()->addFieldToFilter('quote_id', $quoteEntityId);
            if(!empty($collection->getData()) || $collection->getData()){
                $checked = $collection->getFirstItem()->getDebit();
                $balance = 0;
                $feeAmount = $quote->getUsedcreditAmount();
                if($checked == 'true'){
                    if($quote->getSubtotal() <= $totalCreditBalance){
                            $balance = $quote->getSubtotal();
                    }
                    else{
                            $balance = $totalCreditBalance;
                    }
                }
                $result = array(
                    'code'  => 'usedcredit',
                    'title' => __("Store Credit"),
                    'value' => "-".$balance,
                );
            } else {
                $result = array(
                    'code'  => 'usedcredit',
                    'title' => __('Not Calculated yet'),
                    'value' => 0,
                );
            }
           
            if ($result == '') {
                return $total;
            }
     
            return $result;
        }
    }
    public function convertPrice($amount, $store = null, $currency = null)
    {
        $objectManager       = \Magento\Framework\App\ObjectManager::getInstance();
        $priceCurrencyObject = $objectManager->get('Magento\Framework\Pricing\PriceCurrencyInterface');
        $storeManager        = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        if ($store == null) {
            $store = $storeManager->getStore()->getStoreId();
        }
      
        if($amount > 0 ) {
            $rate   = $priceCurrencyObject->convert($amount, $store) / $amount;
        $amount = $amount / $rate;
        }
         return $priceCurrencyObject->round($amount);
    }
    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Credit Score');
    }
}
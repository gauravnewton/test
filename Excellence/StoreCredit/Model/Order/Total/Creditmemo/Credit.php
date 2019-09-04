<?php

namespace Excellence\StoreCredit\Model\Order\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class Credit extends AbstractTotal
{
    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigObject,
        \Magento\Quote\Api\Data\PaymentInterface $payment,
        \Magento\Sales\Model\OrderFactory $order
        )
    {
        $this->order = $order;
        $this->request = $request;
        $this->_quoteValidator = $quoteValidator;
        $this->_scopeConfigObject = $scopeConfigObject;
        $this->_checkoutSession = $checkoutSession;
    }
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $balance = 0;
        $orderId = $this->request->getParam('order_id'); 
       
        $collection = $this->order->create()->getcollection()->addFieldtoFilter('entity_id',$orderId);
        foreach ($collection as $data => $value) {
            $balance = $value->getUsedcreditAmount();
        }
        
        $creditmemo->setTotalAmount('usedcredit', $balance);
        $creditmemo->setBaseTotalAmount('usedcredit', $balance);

        $creditmemo->setUsedcreditAmount($balance);
        $creditmemo->setBaseUsedcreditAmount($balance);
        
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $balance);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $balance);
        return $this;
    }
}
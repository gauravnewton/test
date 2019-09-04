<?php

namespace Excellence\StoreCredit\Model\Order\Total\Invoice;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class Fee extends AbstractTotal
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
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $balance = 0;
        $orderId = $this->request->getParam('order_id'); 
       
        $collection = $this->order->create()->getcollection()->addFieldtoFilter('entity_id',$orderId);
        foreach ($collection as $data => $value) {
            $balance = $value->getUsedcreditAmount();
        }
        
        $invoice->setTotalAmount('usedcredit', $balance);
        $invoice->setBaseTotalAmount('usedcredit', $balance);

        $invoice->setUsedcreditAmount($balance);
        $invoice->setBaseUsedcreditAmount($balance);
        
        $invoice->setGrandTotal($invoice->getGrandTotal() - $balance);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $balance);
        return $this;
    }
}
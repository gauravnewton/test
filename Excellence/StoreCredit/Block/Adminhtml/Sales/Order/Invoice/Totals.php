<?php

namespace Excellence\StoreCredit\Block\Adminhtml\Sales\Order\Invoice;

class Totals extends \Magento\Framework\View\Element\Template
{
   public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigObject
    )
   {
    $this->_scopeConfigObject = $scopeConfigObject;
}

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return mixed
     */
    public function getInvoice()
    {
        return $this->getParentBlock()->getInvoice();
    }
    /**
     * Initialize payment fee totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getInvoice();
        $this->getSource();


        if(!$this->getInvoice()->getOrder()->getUsedcreditAmount()) {
            return $this;
        }
            $bal = $this->getInvoice()->getOrder()->getUsedcreditAmount();
            $total = new \Magento\Framework\DataObject(
                [
                'code' => 'store_credit',
                'value' => $bal,
                'label' => __('Store Credit'),
                ]
            );

            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
            return $this;
    }
}
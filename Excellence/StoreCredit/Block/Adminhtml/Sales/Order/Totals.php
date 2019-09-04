<?php

namespace Excellence\StoreCredit\Block\Adminhtml\Sales\Order;

class Totals extends \Magento\Framework\View\Element\Template
{

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->_order = $this->getOrder();
        $this->getSource();
       
        if (!$this->_order->getUsedcreditAmount()) {
            return $this;
        }
        $bal = $this->_order->getUsedcreditAmount();
        $total = new \Magento\Framework\DataObject(
            [
                'code'  => 'credit_amount',
                'value' => $bal,
                'label' => __('Credit used'),
            ]
        );
        $this->getParentBlock()->addTotalBefore($total, 'grand_total');

        return $this;
    }
}

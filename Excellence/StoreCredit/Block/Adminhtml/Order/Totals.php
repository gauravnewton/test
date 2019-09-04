<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Excellence\StoreCredit\Block\Adminhtml\Order;

/**
 * Adminhtml order totals block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Totals extends \Magento\Sales\Block\Adminhtml\Totals//\Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $isRefunded = '';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get('\Magento\Framework\App\Request\Http');
        $orderId = $request->getParam('order_id');
        $order = $objectManager->get('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
        $orderPool = $order->create();
        $usedCredit = $this->getSource()->getUsedcreditAmount();
        $orderCollection = $orderPool->addAttributeToSelect('*')->addFieldToFilter('entity_id',$orderId);
        foreach ($orderCollection as $key => $value) {
           $isRefunded = $value->getTotalRefunded();
        }
        $scopeConfig   = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $isEnable = $scopeConfig->getValue('storecredit/advanced_setting/enable_control',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($usedCredit !=0 && $isRefunded != NULL){
            $this->_totals['paid'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'paid',
                    'strong' => true,
                    'value' => $this->getSource()->getTotalPaid(),
                    'base_value' => $this->getSource()->getBaseTotalPaid(),
                    'label' => __('Total Paid (Excluding Store Credit)'),
                    'area' => 'footer',
                ]
            );
        }
        else{
            $this->_totals['paid'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'paid',
                    'strong' => true,
                    'value' => $this->getSource()->getTotalPaid(),
                    'base_value' => $this->getSource()->getBaseTotalPaid(),
                    'label' => __('Total Paid'),
                    'area' => 'footer',
                ]
            );
        }
        
        if($usedCredit !=0 && $isRefunded != NULL){
            $this->_totals['refunded'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'refunded',
                    'strong' => true,
                    'value' => $this->getSource()->getTotalRefunded(),
                    'base_value' => $this->getSource()->getBaseTotalRefunded(),
                    'label' => __('Total Refunded (Excluding Store Credit)'),
                    'area' => 'footer',
                ]
            );
        }
        else{
            $this->_totals['refunded'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'refunded',
                    'strong' => true,
                    'value' => $this->getSource()->getTotalRefunded(),
                    'base_value' => $this->getSource()->getBaseTotalRefunded(),
                    'label' => __('Total Refunded'),
                    'area' => 'footer',
                ]
            );
        }
        if($usedCredit !=0 && $isRefunded != NULL){
            $this->_totals['usedcredit_amount'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'usedcredit_amount',
                    'strong' => true,
                    'value' => $this->getSource()->getUsedcreditAmount(),
                    'base_value' => $this->getSource()->getUsedcreditAmount(),
                    'label' => __('Store Credit Refunded'),
                    'area' => 'footer',
                ]
            );
        }
        
        $this->_totals['due'] = new \Magento\Framework\DataObject(
            [
                'code' => 'due',
                'strong' => true,
                'value' => $this->getSource()->getTotalDue(),
                'base_value' => $this->getSource()->getBaseTotalDue(),
                'label' => __('Total Due'),
                'area' => 'footer',
            ]
        );
        return $this;
    }
}

<?php
/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Excellence\StoreCredit\Block\Adminhtml\Customer;
/**
 * Render the link in the profile grid
 */
class Transactions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    const EQ_CURRENCY = 'storecredit/points_currency/currency';
    
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }
    /**
     * Render the column block
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $lastCredit = $row->getData('credit');
        $dataToReturn = array();
        if(!isset($lastCredit)){
            return;
        }
        $amount = $this->_scopeConfig->getValue(self::EQ_CURRENCY,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $credit =  round($lastCredit*$amount);
        return $credit;
    }
}

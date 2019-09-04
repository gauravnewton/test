<?php
namespace Excellence\StoreCredit\Model\ResourceModel\Storecredit;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Excellence\StoreCredit\Model\Storecredit','Excellence\StoreCredit\Model\ResourceModel\Storecredit');
    }
}

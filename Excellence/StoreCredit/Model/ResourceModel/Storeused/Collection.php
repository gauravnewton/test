<?php
namespace Excellence\StoreCredit\Model\ResourceModel\Storeused;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Excellence\StoreCredit\Model\Storeused','Excellence\StoreCredit\Model\ResourceModel\Storeused');
    }
}

<?php
namespace Excellence\StoreCredit\Model\ResourceModel;
class Storeused extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('excellence_storecredit_storeused','store_used_id');
    }
}

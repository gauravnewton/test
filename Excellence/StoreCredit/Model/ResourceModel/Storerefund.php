<?php
namespace Excellence\StoreCredit\Model\ResourceModel;
class Storerefund extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('excellence_storecredit_refund','excellence_refund_id');
    }
}

<?php
namespace Excellence\StoreCredit\Model\ResourceModel;
class Storecredit extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('excellence_storecredit_storecredit','store_credit_id');
    }
}

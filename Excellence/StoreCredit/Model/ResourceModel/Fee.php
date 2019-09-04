<?php

namespace Excellence\StoreCredit\Model\ResourceModel;

/**
 * Fee Resource Model
 */
class Fee extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('excellence_storeCredit', 'fee_id');
    }
}

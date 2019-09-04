<?php

namespace Excellence\StoreCredit\Model;

/**
 * Fee Model
 *
 * @method \Excellence\Fee\Model\Resource\Page _getResource()
 * @method \Excellence\Fee\Model\Resource\Page getResource()
 */
class Fee extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Excellence\StoreCredit\Model\ResourceModel\Fee');
    }

}

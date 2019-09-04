<?php
namespace Excellence\StoreCredit\Model;

class Storeused extends \Magento\Framework\Model\AbstractModel 
{
    const CACHE_TAG = 'store_used_id';

    protected function _construct()
    {
        $this->_init('Excellence\StoreCredit\Model\ResourceModel\Storeused');
    }
    
    
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
   
}

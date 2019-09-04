<?php

namespace Excellence\StoreCredit\Controller\Adminhtml\Sales\Order;

class CreditmemoLoader extends \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader {

   protected function _canCreditmemo($order)
   {
       if(!$order->isCanceled() && $order->getState() !== \Magento\Sales\Model\Order::STATE_CLOSED)
           $order->setForcedCanCreditmemo(true);

       return parent::_canCreditmemo($order);
   }
   public function load()
   {
       $result = parent::load();
       if($result instanceof \Magento\Sales\Model\Order\Creditmemo){
           $result->setAllowZeroGrandTotal(true);
       }
       return $result;
   }
}
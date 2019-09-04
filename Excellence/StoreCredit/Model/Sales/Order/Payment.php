<?php
namespace Excellence\StoreCredit\Model\Sales\Order;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Info;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface;

class Payment extends \Magento\Sales\Model\Order\Payment
{
	public function refund($creditmemo)
    {
    	$usedCredit = 0;
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$scopeConfig   = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
		$request = $objectManager->get('\Magento\Framework\App\Request\Http');
		$data = $request->getPost('creditmemo');
		$order = $objectManager->get('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
		$isEnable = $scopeConfig->getValue('storecredit/advanced_setting/enable_control',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    	$orderId = $request->getParam('order_id');
    	$orderPool = $order->create();
    	$orderCollection = $orderPool->addAttributeToSelect('*')->addFieldToFilter('entity_id',$orderId);
    	if($isEnable){
    		$refundTotal = 0;
    		foreach ($orderCollection as $key => $value) {
    			$usedCredit = $value->getUsedcreditAmount();
    			$discount = $value->getDiscountAmount();
                $tax = $value->getTaxAmount();
                $shipping = $value->getShippingAmount();
    		}
    		if($usedCredit == 0){
                $refundTotal =  $creditmemo->getGrandTotal();
            }
            elseif($usedCredit !=0 && $creditmemo->getGrandTotal() !=0 ){
                $refundTotal = $creditmemo->getSubTotal() + $shipping + $tax - $discount;
            }
            elseif($creditmemo->getGrandTotal()==0){
                $refundTotal = $creditmemo->getSubTotal();
            }
    		$baseAmountToRefund = $this->formatAmount($refundTotal);
	        $this->setTransactionId(
	            $this->transactionManager->generateTransactionId($this, Transaction::TYPE_REFUND)
	        );

	        // call refund from gateway if required
	        $isOnline = false;
	        $gateway = $this->getMethodInstance();
	        $invoice = null;
	        if ($gateway->canRefund() && $creditmemo->getDoTransaction()) {
	            $this->setCreditmemo($creditmemo);
	            $invoice = $creditmemo->getInvoice();
	            if ($invoice) {
	                $isOnline = true;
	                $captureTxn = $this->transactionRepository->getByTransactionId(
	                    $invoice->getTransactionId(),
	                    $this->getId(),
	                    $this->getOrder()->getId()
	                );
	                if ($captureTxn) {
	                    $this->setTransactionIdsForRefund($captureTxn);
	                }
	                $this->setShouldCloseParentTransaction(true);
	                // TODO: implement multiple refunds per capture
	                try {
	                    $gateway->setStore(
	                        $this->getOrder()->getStoreId()
	                    );
	                    $this->setRefundTransactionId($invoice->getTransactionId());
	                    $gateway->refund($this, $baseAmountToRefund);

	                    $creditmemo->setTransactionId($this->getLastTransId());
	                } catch (\Magento\Framework\Exception\LocalizedException $e) {
	                    if (!$captureTxn) {
	                        throw new \Magento\Framework\Exception\LocalizedException(
	                            __('If the invoice was created offline, try creating an offline credit memo.'),
	                            $e
	                        );
	                    }
	                    throw $e;
	                }
	            }
	        }

	        // update self totals from creditmemo
	        $this->_updateTotals(
	            [
	                'amount_refunded' => $refundTotal,
	                'base_amount_refunded' => $baseAmountToRefund,
	                'base_amount_refunded_online' => $isOnline ? $baseAmountToRefund : null,
	                'shipping_refunded' => $creditmemo->getShippingAmount(),
	                'base_shipping_refunded' => $creditmemo->getBaseShippingAmount(),
	            ]
	        );

	        // update transactions and order state
	        $transaction = $this->addTransaction(
	            Transaction::TYPE_REFUND,
	            $creditmemo,
	            $isOnline
	        );
	        if ($invoice) {
	            $message = __('We refunded %1 online.', $this->formatPrice($baseAmountToRefund));
	        } else {
	        	if($isEnable && !empty($data['credit_refund'])){
	        			$message = $this->hasMessage() ? $this->getMessage() : __(
	               		'We refunded %1 offline (including store credit)',
	            		$this->formatPrice($baseAmountToRefund)
            		);
	        	}
	        	else{
	        		$message = $this->hasMessage() ? $this->getMessage() : __(
	               		'We refunded %1 offline',
	            		$this->formatPrice($baseAmountToRefund)
	            	);
	        	}

	            
	        }
	        $message = $message = $this->prependMessage($message);
	        $message = $this->_appendTransactionToMessage($transaction, $message);
	        $this->setOrderStateProcessing($message);
	        $this->_eventManager->dispatch(
	            'sales_order_payment_refund',
	            ['payment' => $this, 'creditmemo' => $creditmemo]
	        );
	        return $this;
    	}
    	else{
    		return parent::refund($creditmemo);
    	}
    }
}
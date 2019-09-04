<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Excellence\StoreCredit\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;

class Save extends \Magento\Backend\App\Action
{
    const MODULE_STATUS = 'storecredit/advanced_setting/enable_control';
    const EQ_CURRENCY = 'storecredit/points_currency/currency';
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var CreditmemoSender
     */
    protected $creditmemoSender;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    protected $storecredit;
    protected $storerefund;
    protected $order;
    protected $storeManager;
    protected $scopeConfig;

    /**
     * @param Action\Context $context
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader
     * @param CreditmemoSender $creditmemoSender
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        CreditmemoSender $creditmemoSender,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Excellence\StoreCredit\Model\Storecredit $storecredit,
        \Excellence\StoreCredit\Model\StorerefundFactory $storerefund,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $order,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig

    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->order = $order;
        $this->storerefund = $storerefund;
        $this->storecredit = $storecredit;
        $this->creditmemoLoader = $creditmemoLoader;
        $this->creditmemoSender = $creditmemoSender;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_creditmemo');
    }

    /**
     * Save creditmemo
     * We can save only new creditmemo. Existing creditmemos are not editable
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Backend\Model\View\Result\Forward
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {   $isEnable = $this->scopeConfig->getValue(
                                self::MODULE_STATUS,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
        $resultRedirect = $this->resultRedirectFactory->create();
        $orderId = $this->getRequest()->getParam('order_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPost('creditmemo');
        
        if (!empty($data['comment_text'])) {
            $this->_getSession()->setCommentText($data['comment_text']);
        }
        try {

            $this->creditmemoLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
            $this->creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
            $this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
            $creditmemo = $this->creditmemoLoader->load();
           
            if ($creditmemo) {
                if (!$creditmemo->isValidGrandTotal()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The credit memo\'s total must be positive.')
                    );
                }

                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );

                    $creditmemo->setCustomerNote($data['comment_text']);
                    $creditmemo->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                }

                if (isset($data['do_offline'])) {
                    //do not allow online refund for Refund to Store Credit
                    if (!$data['do_offline'] && !empty($data['credit_refund'])) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Cannot create online refund for Refund to Store Credit.')
                        );
                    }
                    //saving refunded amount back to store credit
                    if(!empty($data['credit_refund']) && $isEnable)
                    {
                        $order = $this->order->create();
                        $customerId = '';
                        $refundTotal = '';
                        $incrementId = '';
                        $name = '';
                        $email='';
                        $usedCredit = '';
                        $currency = $this->scopeConfig->getValue(
                                        self::EQ_CURRENCY,
                                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                                    );
                        $orderCollection = $order->addAttributeToSelect('*')->addFieldToFilter('entity_id',$orderId);
                        
                        foreach ($orderCollection as $key => $value){
                            $customerId  = $value->getCustomerId();
                            $incrementId = $value->getIncrementId();
                            $email = $value->getCustomerEmail();
                            $usedCredit = $value->getUsedcreditAmount();
                            $discount = $value->getDiscountAmount();
                            $tax = $value->getTaxAmount();
                            $shipping = $value->getShippingAmount();
                            $name = $value->getCustomerFirstname()." ".$value->getCustomerLastname();
                        }
                        $store = $this->storeManager->getStore()->getFrontendName();

                        $emailTempVar = array('name' => $name, 'email' => $email, 'subject' =>$store." credit" );
                        //$refundTotal = $creditmemo->getSubTotal();
                        if($usedCredit == 0){
                            $refundTotal =  $creditmemo->getGrandTotal();
                        }
                        elseif($usedCredit !=0 && $creditmemo->getGrandTotal() !=0 ){
                            $refundTotal = $creditmemo->getSubTotal() + $shipping + $tax - $discount;
                        }
                        elseif($creditmemo->getGrandTotal()==0){
                            $refundTotal = $creditmemo->getSubTotal();
                        }
                        $date = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                        $time = date('h:i A', strtotime($date)) . ' ' . date("jS F, Y", strtotime($date));
                        $model = $this->storerefund->create();
                        $model->setData('order_id',$orderId);
                        $model->setData('customer_id',$customerId);
                        $model->setData('refund_amount',$refundTotal);
                        $model->setData('comment',$data['comment_text']);
                        $model->setData('created_at',$time);
                        $model->save();
                        $actualRefund = ($refundTotal)/$currency;
                        $this->storecredit->refundInit($customerId,$actualRefund,$incrementId,$emailTempVar,$refundTotal);
                    }

                }
                $creditmemoManagement = $this->_objectManager->create(
                    'Magento\Sales\Api\CreditmemoManagementInterface'
                );
                $creditmemoManagement->refund($creditmemo, (bool)$data['do_offline'], !empty($data['send_email']));

                if (!empty($data['send_email'])) {
                    $this->creditmemoSender->send($creditmemo);
                }
                
                $this->messageManager->addSuccess(__('You created the credit memo.'));
                $this->_getSession()->getCommentText(true);
                $resultRedirect->setPath('sales/order/view', ['order_id' => $creditmemo->getOrderId()]);
                return $resultRedirect;
            } else {
                $resultForward = $this->resultForwardFactory->create();
                $resultForward->forward('noroute');
                return $resultForward;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_getSession()->setFormData($data);
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->messageManager->addError(__('We can\'t save the credit memo right now.'));
        }
        $resultRedirect->setPath('sales/*/new', ['_current' => true]);
        return $resultRedirect;
    }
}

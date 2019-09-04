<?php

namespace Excellence\StoreCredit\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CheckoutSubmitAllAfter implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    const ORDER_PLACE_AMOUNT = 'storecredit/admin_storecredit_Setting/order_place_credit';
    const MODULE_STATUS = 'storecredit/advanced_setting/enable_control';

    protected $request;
    protected $credit;
    protected $resource;
    protected $customer;
    protected $scopeConfig;
    protected $helper;
    protected $orderFactory;
    protected $customerSession;
    protected $defaultHelper;
    protected $messageManager;
    protected $storeManager;
    /**
     * AddFeeToOrderObserver constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Excellence\StoreCredit\Model\StorecreditFactory $credit,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $time,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Framework\Pricing\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Excellence\StoreCredit\Helper\Data $defaultHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager

    )
    {
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->defaultHelper =$defaultHelper;
        $this->customerSession = $customerSession;
        $this->orderFactory = $orderFactory;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->customer = $customer;
        $this->resource = $resource;
        $this->date = $date;
        $this->time = $time;
        $this->credit = $credit;
        $this->request = $request;
    }

    /**
     * set customer credit data
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isEnable = $this->scopeConfig->getValue(self::MODULE_STATUS,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        try{
            if($isEnable && $this->customerSession->isLoggedIn()){
                $amount = $this->scopeConfig->getValue(self::ORDER_PLACE_AMOUNT,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $event = $observer->getEvent();
                $customerId = $this->customerSession->getId();
                $customerSession = $this->customerSession->getCustomer()->getData();
                $store = $this->storeManager->getStore()->getFrontendName();
                $cx_name = $customerSession['firstname'].' '.$customerSession['lastname'];

                $cx_email = $customerSession['email'];
                $message = 'Congratulation! Your account has been credited with '.$amount.' store credit points.';
                $date = $this->time->formatDate($date = null,$format = \IntlDateFormatter::SHORT,$showTime = false);
                $time = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                $time = date('h:i A', strtotime($time));
                $orderIds = $event->getOrderIds();
                $order_id = $orderIds[0];
                $model = $this->orderFactory->create()->load($order_id);
                $id = $model->getIncrementId();
                $reason = "Credit rewarded for placing order #".$id.' successfully';
                $emailTempVar = array('name' => $cx_name,'email' => $cx_email, 'message' => $message, 'reason' => $reason, 'subject' =>$store.' credit');
                $creditModel = $this->credit->create();
                if($amount !=0){
                    $creditModel->setData('time',$time);
                    $creditModel->setData('credit',$amount);
                    $creditModel->setData('date',$date);
                    $creditModel->setData('reason',$reason);
                    $creditModel->setData('customer_id',$customerId);
                    $creditModel->save();
                    $this->defaultHelper->sendEmail($emailTempVar);
                }
                
            }
        }
        catch(\Exception $e){
            $this->messageManager->addError(__('Something went wrong while fetching customer details.'));
        } 
    }
}
<?php

namespace Excellence\StoreCredit\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class RegisterSuccess implements ObserverInterface
{
    const STORE_REGISTRATION = 'storecredit/admin_storecredit_Setting/registration_credit';
    const MODULE_STATUS = 'storecredit/advanced_setting/enable_control';
    const SUBSCRIBED = 'storecredit/admin_storecredit_Setting/subscribe_credit';
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $request;
    protected $credit;
    protected $resource;
    protected $customer;
    protected $scopeConfig;
    protected $defaultHelper;
    protected $messageManager;
    protected $storeManager;
    protected $customerSession;
    /**
     * AddFeeToOrderObserver constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $time,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Excellence\StoreCredit\Model\StorecreditFactory $credit,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Excellence\StoreCredit\Helper\Data $defaultHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
    	$this->defaultHelper = $defaultHelper;
        $this->scopeConfig = $scopeConfig;
        $this->customer = $customer;
        $this->resource = $resource;
        $this->date = $date;
        $this->time = $time;
        $this->credit = $credit;
        $this->request = $request;
        $this->customerSession = $customerSession;
    }

    /**
     * set customer credit data
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isEnable = $amount = $this->scopeConfig->getValue(self::MODULE_STATUS,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        try{
            if($isEnable){
                $is_subscribed = $this->request->getParam('is_subscribed');
                $amount = $this->scopeConfig->getValue(self::STORE_REGISTRATION,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $subscribeAmt = $this->scopeConfig->getValue(self::SUBSCRIBED,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $customer = $observer->getEvent()->getData('customer');
                $id = $customer->getId();
                $cx_name = $customer->getFirstName().' '.$customer->getLastName();
                $cx_email = $customer->getEmail();
                $store = $this->storeManager->getStore()->getFrontendName();
                $date = $this->time->formatDate($date = null,$format = \IntlDateFormatter::SHORT,$showTime = false);
                $time = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                $time = date('h:i A', strtotime($time));
                if($subscribeAmt!=0 && $is_subscribed){
                    $total = $amount + $subscribeAmt;
                    $message = 'Congratulation! Your account has been credited with '.$total.' store credit points.';
                    $reason = "Awarded for successful registration to store and subscribing for newsletter";
                    $emailTempVar = array('name' => $cx_name,'email' => $cx_email, 'message' => $message, 'reason' => $reason, 'subject' =>$store.' credit');
                    $model = $this->credit->create();
                    $model->setData('time',$time);
                    $model->setData('credit',$total);
                    $model->setData('date',$date);
                    $model->setData('reason',$reason);
                    $model->setData('customer_id',$id);
                    $model->save();
                    $this->defaultHelper->sendEmail($emailTempVar);
                }
                elseif($amount!=0){
                    $reason = "Awarded for successful registration to store";
                    $message = 'Congratulation! Your account has been credited with '.$amount.' store credit points.';
                    $emailTempVar = array('name' => $cx_name,'email' => $cx_email, 'message' => $message, 'reason' => $reason, 'subject' =>$store.' credit');
                    $model = $this->credit->create();
                    $model->setData('time',$time);
                    $model->setData('credit',$amount);
                    $model->setData('date',$date);
                    $model->setData('reason',$reason);
                    $model->setData('customer_id',$id);
                    $model->save();
                    $this->defaultHelper->sendEmail($emailTempVar);
                }
            }  
        }
        catch(\Exception $e){
            $this->messageManager->addError(__($e->getMessage()));
        } 
    }
}
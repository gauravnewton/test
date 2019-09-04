<?php

namespace Excellence\StoreCredit\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SaveCredit implements ObserverInterface
{
    const MODULE_STATUS = 'storecredit/advanced_setting/enable_control';
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $request;
    protected $credit;
    protected $resource;
    protected $customer;
    protected $helper;
    protected $customerSession;
    protected $messageManager;
    protected $storeManager;
    protected $scopeConfig;
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
        \Excellence\StoreCredit\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
        $this->customer = $customer;
        $this->resource = $resource;
        $this->date = $date;
        $this->time = $time;
        $this->customerSession = $customerSession;
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
        $isEnable = $this->scopeConfig->getValue(
                                self::MODULE_STATUS,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
        if($isEnable){
            $emailTempVar = array();
            $customer = $observer->getEvent()->getData('customer');
            $cx_name = $customer->getFirstName().' '.$customer->getLastName();
            $cx_email = $customer->getEmail();
            $id = $customer->getId();
            $store = $this->storeManager->getStore()->getFrontendName();
            $date = $this->time->formatDate($date = null,$format = \IntlDateFormatter::SHORT,$showTime = false);
            $time = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            $time = date('h:i A', strtotime($time));
            $post = $this->request->getPostValue();
            if($post['customer']['credit'] < 0){
                $message = 'There has been deduction in your store credit points by '.abs($post['customer']['credit']);
                $reason = $post['customer']['reason'].' by admin';
            }
            else{
                $message = 'Congratulation! Your account has been credited with '.$post['customer']['credit'].' store credit points.';
                $reason = $post['customer']['reason'].' by admin';
            }

            $emailTempVar = array('name' => $cx_name,'email' => $cx_email, 'message' => $message, 'reason' => $reason, 'subject' =>$store.' credit');
            $model = $this->credit->create();
            try{
                if($post['customer']['credit'] !=0){
                    $model->setData('time',$time);
                    $model->setData('credit',$post['customer']['credit']);
                    $model->setData('date',$date);
                    $model->setData('reason',$reason);
                    $model->setData('customer_id',$id);
                    $model->save();
                    $this->helper->sendEmail($emailTempVar);
                }
            }
            catch(\Exception $e)
            {
                $this->messageManager->addError(__($e->getMessage()));
            }
        }
    }
}
<?php

namespace Excellence\StoreCredit\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class OrderCompleted implements ObserverInterface
{
    const ORDER_COMPLETION_CREDIT = 'storecredit/admin_storecredit_Setting/order_complete_credit';
    const MODULE_STATUS = 'storecredit/advanced_setting/enable_control';
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $credit;
    protected $scopeConfig;
    protected $defaultHelper;
    protected $messageManager;
    protected $storeManager;
    /**
     * AddFeeToOrderObserver constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $time,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Excellence\StoreCredit\Model\StorecreditFactory $credit,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Excellence\StoreCredit\Helper\Data $defaultHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->defaultHelper = $defaultHelper;
        $this->scopeConfig = $scopeConfig;
        $this->date = $date;
        $this->time = $time;
        $this->credit = $credit;
    }

    /**
     * set customer credit data
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $isEnable = $this->scopeConfig->getValue(self::MODULE_STATUS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        try {
            if ($isEnable) {
                $orderCompetionCredit = $this->scopeConfig->getValue(self::ORDER_COMPLETION_CREDIT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $orderStatus = $observer->getEvent()->getOrder()->getStatus();

                if ($orderStatus == 'complete') {
                    $orderId = $observer->getEvent()->getOrder()->getIncrementId();
                    $cx_name = $observer->getEvent()->getOrder()->getcustomerFirstname();
                    $cx_email = $observer->getEvent()->getOrder()->getcustomerEmail();
                    $store = $this->storeManager->getStore()->getFrontendName();
                    $customer_id = $observer->getEvent()->getOrder()->getcustomerId();
                    $date = $this->time->formatDate($date = null, $format = \IntlDateFormatter::SHORT, $showTime = false);
                    $time = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                    $time = date('h:i A', strtotime($time));
                    $message = 'Congratulation! Your account has been credited with ' . $orderCompetionCredit . ' store credit points for completion of order #' . $orderId;
                    $reason = "Awarded for successful completion of order #" . $orderId;
                    $emailTempVar = array('name' => $cx_name, 'email' => $cx_email, 'message' => $message, 'reason' => $reason, 'subject' => $store . ' credit');

                    $model = $this->credit->create();
                    $model->setData('time', $time);
                    $model->setData('credit', $orderCompetionCredit);
                    $model->setData('date', $date);
                    $model->setData('reason', $reason);
                    $model->setData('customer_id', $customer_id);
                    $model->save();
                    $this->defaultHelper->sendEmail($emailTempVar);
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
    }
}

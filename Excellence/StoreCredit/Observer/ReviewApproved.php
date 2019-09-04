<?php

namespace Excellence\StoreCredit\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ReviewApproved implements ObserverInterface
{
    const REVIEW_APPROVAL_CREDIT = 'storecredit/admin_storecredit_Setting/review_approved_credit';
    const MODULE_STATUS = 'storecredit/advanced_setting/enable_control';
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $credit;
    protected $scopeConfig;
    protected $defaultHelper;
    protected $messageManager;
    protected $storeManager;
    protected $productFactory;
    protected $customer;
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
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\Customer $customer
    ) {
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->defaultHelper = $defaultHelper;
        $this->scopeConfig = $scopeConfig;
        $this->date = $date;
        $this->time = $time;
        $this->credit = $credit;
        $this->productFactory = $productFactory;
        $this->customer = $customer;
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
                $reviewApprovalCredit = $this->scopeConfig->getValue(self::REVIEW_APPROVAL_CREDIT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $reviewStatus = $observer->getDataObject()->getstatusId();
                if ($reviewStatus == 1) {
                    $product_id = $observer->getDataObject()->getentityPkValue();
                    $product = $this->productFactory->create()->load($product_id);
                    $productName = $product->getName();
                    $customerId = $observer->getDataObject()->getcustomerId();
                    $customerCollection = $this->customer->getCollection()->addAttributeToFilter('entity_id', array('eq' => $customerId));
                    foreach ($customerCollection as $customer) {
                        $cx_name = $customer->getFirstname();
                        $cx_email = $customer->getEmail();
                    }
                    $store = $this->storeManager->getStore()->getFrontendName();
                    $date = $this->time->formatDate($date = null, $format = \IntlDateFormatter::SHORT, $showTime = false);
                    $time = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                    $time = date('h:i A', strtotime($time));
                    $message = 'Congratulation! Your account has been credited with ' . $reviewApprovalCredit . ' store credit points for review approval of product ' . $productName;
                    $reason = "Review approval award for product " . $productName;
                    $emailTempVar = array('name' => $cx_name, 'email' => $cx_email, 'message' => $message, 'reason' => $reason, 'subject' => $store . ' credit');
                    $model = $this->credit->create();
                    $model->setData('time', $time);
                    $model->setData('credit', $reviewApprovalCredit);
                    $model->setData('date', $date);
                    $model->setData('reason', $reason);
                    $model->setData('customer_id', $customerId);
                    $model->save();
                    $this->defaultHelper->sendEmail($emailTempVar);
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
    }
}

<?php

namespace Excellence\StoreCredit\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddFeeToOrderObserver implements ObserverInterface
{
    const EQ_CURRENCY = 'storecredit/points_currency/currency';
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    protected $credit;
    protected $priceBlock;
    protected $helper;
    protected $messageManager;
    protected $priceHelper;
    protected $customerSession;
    protected $enableBlock;

    /**
     * AddFeeToOrderObserver constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Excellence\StoreCredit\Model\StorecreditFactory $credit,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $time,
        \Excellence\StoreCredit\Block\Main $priceBlock,
        \Excellence\StoreCredit\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Event\Observer $observer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Excellence\StoreCredit\Block\Payment $enableBlock,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->enableBlock = $enableBlock;
        $this->scopeConfig = $scopeConfig;
        $this->priceHelper = $priceHelper;
        $this->messageManager = $messageManager;
        $this->_observer = $observer;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->priceBlock= $priceBlock;
        $this->time = $time;
        $this->credit = $credit;
        $this->_checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
    }

    /**
     * Set payment fee to order
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isEnable = $this->enableBlock->enable();
        if($isEnable && $this->customerSession->isLoggedIn()){
            try{

                $multiplyer = $this->scopeConfig->getValue(self::EQ_CURRENCY,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $quote = $observer->getQuote();

                $order = $observer->getEvent()->getOrder();
                $orderId = $order->getIncrementId();
                $creditAmount = ($quote->getUsedcreditAmount())/$multiplyer; 

                $model = $this->credit->create();
                $customerId = $quote->getCustomerId();
                $date = $this->time->formatDate($date = null,$format = \IntlDateFormatter::SHORT,$showTime = false);
                $time = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                $time = date('h:i A', strtotime($time));
                $reason = "You have used credit for Order Purchase #".$orderId.".";
                if($creditAmount != 0){
                    $model->setData('time',$time);
                    $model->setData('date',$date);
                    $model->setData('credit',$creditAmount);
                    $model->setData('reason',$reason);
                    $model->setData('customer_id',$customerId);
                    $model->save();
                }
                
                $baseCreditAmount = $quote->getBaseUsedcreditAmount();  
                if(!$creditAmount || !$baseCreditAmount) {
                    return $this;
                }
                //Set fee data to order
                $order = $observer->getOrder();
                $creditAmount = abs($quote->getUsedcreditAmount()); 
                $order->setData('usedcredit_amount', $creditAmount);
                $order->setData('base_usedcredit_amount', $baseCreditAmount);

                $name = $quote->getCustomerFirstname().' '.$quote->getCustomerLastname();
                $email = $quote->getCustomerEmail();
                $formattedPrice = $this->priceHelper->currency($creditAmount, true, false);
                $store = $this->storeManager->getStore()->getFrontendName();
                $reason = "You have used ".$formattedPrice." credit for Order Purchase #".$orderId;
                $emailTempVar = array('name' =>$name, 'email' => $email, 'message' => $reason, 'subject' => $store.' credit');
                if($creditAmount != 0){
                    $this->helper->sendEmail($emailTempVar);
                }
                return $this;
            }
            catch(\Exception $e){
                $this->messageManager->addError(__('Something went wrong: '.$e->getMessage()));
            }
        }
    }
}
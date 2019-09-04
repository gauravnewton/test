<?php
namespace Excellence\StoreCredit\Model;

class Storecredit extends \Magento\Framework\Model\AbstractModel 
{
    const CACHE_TAG = 'store_credit_id';

    protected $order;
    protected $creditmemoFactory;
    protected $Invoice;
    protected $creditmemoService;
    protected $helper;
    protected $pricingHelper;
    protected $messageManager;
    protected $scopeConfig;
    protected $storeCredit;


    const ORDER_BONOUS_REWARD = 'storecredit/admin_storecredit_Setting/order_place_credit';


    protected function _construct()
    {
        $this->_init('Excellence\StoreCredit\Model\ResourceModel\Storecredit');
    }
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Excellence\StoreCredit\Model\StorecreditFactory $storeCredit,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $time,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
        \Excellence\StoreCredit\Helper\Data $helper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    )
    {
        $this->storeCredit = $storeCredit;
        $this->pricingHelper = $pricingHelper;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
        $this->order = $order;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->scopeConfig = $scopeConfig;
        $this->invoice = $invoice;
        $this->date = $date;
        $this->time = $time;
        parent::__construct($context,$registry);
    }
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
    public function refundInit($customerId,$actualRefund,$incrementId,$emailTempVar,$refundTotal){
        try{
            $date = $this->time->formatDate($date = null,$format = \IntlDateFormatter::SHORT,$showTime = false);
            $time = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            $time = date('h:i A', strtotime($time)); 
            $formattedPrice = $this->pricingHelper->currency($refundTotal, true, false);
        
            $comment = $formattedPrice." refunded to your account as equivalent credit points for order #".$incrementId."";
            $this->setData('time',$time);
            $this->setData('date',$date);
            $this->setData('credit',$actualRefund);
            $this->setData('reason',$comment);
            $this->setData('customer_id',$customerId);
            $message = "We have refunded ".$formattedPrice." for your order #".$incrementId." and has been added successfully as store credit points in your account.";
            $this->save();
            $emailTempVar['message'] = $message;
            $this->helper->sendEmail($emailTempVar);
            $this->rollBackOrderReward($customerId,$incrementId);
        }
        catch(\Exception $e){
            $this->messageManager->addError(__($e->getMessage()));
        }
    }
    /*
    * This function will rollback the bonous credited to user on successfull order place
    */
    public function rollBackOrderReward($customerId,$incrementId){
        try{
            /*
            * Grabbing reward point vakue on order success from store config.
            */
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $rollBackAmount = $this->scopeConfig->getValue(self::ORDER_BONOUS_REWARD, $storeScope);
            $date = $this->time->formatDate($date = null,$format = \IntlDateFormatter::SHORT,$showTime = false);
            $time = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            $time = date('h:i A', strtotime($time)); 
            $reason = "$".$rollBackAmount." debited on cancelation of order #".$incrementId ;
            $storeCreditBonous = $this->storeCredit->create();
            $storeCreditBonous->setData('time',$time);
            $storeCreditBonous->setData('date',$date);
            $storeCreditBonous->setData('credit',-$rollBackAmount);
            $storeCreditBonous->setData('reason',$reason);
            $storeCreditBonous->setData('customer_id',$customerId);
            $storeCreditBonous->save();
        } catch(\Exception $execpt){
            $this->messagemanager->addError(__($execpt->getMessage()));
        }
    }
}

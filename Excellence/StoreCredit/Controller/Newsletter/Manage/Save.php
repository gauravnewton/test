<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Excellence\StoreCredit\Controller\Newsletter\Manage;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;

class Save extends \Magento\Newsletter\Controller\Manage
{

    const NEWSLETTER_CREDIT = 'storecredit/admin_storecredit_Setting/subscribe_credit';
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

    protected $credit;

    protected $scopeConfig;

    protected $time;

    protected $data;
    protected $messageManager;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CustomerRepository $customerRepository
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Excellence\StoreCredit\Model\StorecreditFactory $credit,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $time,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerRepository $customerRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Excellence\StoreCredit\Helper\Data $helper,
        \Excellence\StoreCredit\Block\Payment $enableBlock
    ) {
        $this->storeManager = $storeManager;
        $this->enableBlock = $enableBlock;
        $this->helper = $helper;
        $this->time = $time;
        $this->date= $date;
        $this->scopeConfig = $scopeConfig;
        $this->credit = $credit;
        $this->_customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerRepository = $customerRepository;
        $this->subscriberFactory = $subscriberFactory;
        parent::__construct($context, $customerSession);
    }

    /**
     * Save newsletter subscription preference action
     *
     * @return void|null
     */
    public function execute()
    {

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->_redirect('customer/account/');
        }

        $customerId = $this->_customerSession->getCustomerId();
        if ($customerId === null) {
            $this->messageManager->addError(__('Something went wrong while saving your subscription.'));
        } else {
            try {
                $customer = $this->customerRepository->getById($customerId);
                $storeId = $this->storeManager->getStore()->getId();
                $customer->setStoreId($storeId);
                $cx_name = $customer->getFirstName().' '.$customer->getLastName();
                $cx_email = $customer->getEmail();
                $this->customerRepository->save($customer);
                $checkSubscription = $this->helper->checkSubscription($customerId);
                $isEnable = $this->enableBlock->enable();
                $store = $this->storeManager->getStore()->getFrontendName();
                if ((boolean)$this->getRequest()->getParam('is_subscribed', false)){
                    if(empty($checkSubscription) && $isEnable){
                        $amount = $this->scopeConfig->getValue(self::NEWSLETTER_CREDIT,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $message = 'Congratulation! Your account has been credited with '.$amount.' store credit points.';
                        $date = $this->time->formatDate($date = null,$format = \IntlDateFormatter::SHORT,$showTime = false);
                        $time = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                        $time = date('h:i A', strtotime($time));
                        $reason = "Credit awarded for newsletter subscription";
                        $emailTempVar = array('name' => $cx_name,'email' => $cx_email, 'message' => $message, 'reason' => $reason, 'subject' => $store.' credit');
                        if($amount !=0){
                            $creditModel = $this->credit->create();
                            $creditModel->setData('time',$time);
                            $creditModel->setData('credit',$amount);
                            $creditModel->setData('date',$date);
                            $creditModel->setData('reason',$reason);
                            $creditModel->setData('customer_id',$customerId);
                            $creditModel->setData('is_subscribed_once',1);
                            $creditModel->save();
                            $this->helper->sendEmail($emailTempVar);
                        }
                        
                    }
                    $this->subscriberFactory->create()->subscribeCustomerById($customerId);
                    $this->messageManager->addSuccess(__('We saved the subscription.'));
                } 
                else {
                    $this->subscriberFactory->create()->unsubscribeCustomerById($customerId);
                    $this->messageManager->addSuccess(__('We removed the subscription.'));
                }
            } 
            catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while saving your subscription.'));
            }
        }
        $this->_redirect('customer/account/');
    }
}

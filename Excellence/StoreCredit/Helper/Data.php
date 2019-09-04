<?php

/**
 * Fee data helper
 */
namespace Excellence\StoreCredit\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_EMAIL_TEMPLATE_FIELD  = 'storecredit/email_settings/credit_email';
    const EMAIL_SENDER = 'storecredit/email_settings/email_identity';
    const GEN_NAME = 'trans_email/ident_general/name';
    const GEN_EMAIL = 'trans_email/ident_general/email';
    const SALES_NAME = 'trans_email/ident_sales/name';
    const SALES_EMAIL = 'trans_email/ident_sales/email';
    const CUSTOM_SUPPORT_NAME = 'trans_email/ident_support/name';
    const CUSTOM_SUPPORT_EMAIL = 'trans_email/ident_support/email';
    const CUSTOM_1_NAME = 'trans_email/ident_custom1/name';
    const CUSTOM_1_EMAIL = 'trans_email/ident_custom1/email';
    const CUSTOM_2_NAME = 'trans_email/ident_custom2/name';
    const CUSTOM_2_EMAIL = 'trans_email/ident_custom2/email';
    const EQ_CURRENCY = 'storecredit/points_currency/currency';
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $inlineTranslation;

    protected $transportBuilder;

    protected $time;
    protected $customerInt;
    protected $resource;
    protected $messageManager;
    protected $storecredit;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $time,
        \Magento\Customer\Api\Data\CustomerInterface $customerInt,
        \Magento\Framework\App\ResourceConnection $resource,
        \Excellence\StoreCredit\Model\StorecreditFactory $storecredit,
        \Magento\Framework\Message\ManagerInterface $messageManager

    ) {
        $this->storecredit = $storecredit;
        $this->messageManager = $messageManager;
        $this->resource = $resource;
        $this->customerInt = $customerInt;
        $this->time = $time;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }
    public function sendEmail($emailTempVar){
        // Email Template
        $email = '';
        $name = '';
        $templateId = 'credit_email';
        $emailType = $this->_scopeConfig->getValue(
            self::EMAIL_SENDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        switch ($emailType) {
            case 'general':
                $name  =    $this->_scopeConfig->getValue(
                                self::GEN_NAME,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                            );
                $email =    $this->_scopeConfig->getValue(
                                self::GEN_EMAIL,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                            );
                break;
            case 'sales':
                $name  =    $this->_scopeConfig->getValue(
                                self::SALES_NAME,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                            );
                $email =    $this->_scopeConfig->getValue(
                                self::SALES_EMAIL,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                            );
                break;
            case 'support':
                $name  =    $this->_scopeConfig->getValue(
                                self::CUSTOM_SUPPORT_NAME,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                            );
                $email =    $this->_scopeConfig->getValue(
                                self::CUSTOM_SUPPORT_EMAIL,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                            );
                break;
            case 'custom1':
                $name  =    $this->_scopeConfig->getValue(
                                self::CUSTOM_1_NAME,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                            );
                $email =    $this->_scopeConfig->getValue(
                                self::CUSTOM_1_EMAIL,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                            );
                break;
            case 'custom2':
                $name  =    $this->_scopeConfig->getValue(
                                self::CUSTOM_2_NAME,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                            );
                $email =    $this->_scopeConfig->getValue(
                                self::CUSTOM_2_EMAIL,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                            );
                break;
            
            default:
                $name  =    $this->_scopeConfig->getValue(
                                self::GEN_NAME,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                            );
                $email =    $this->_scopeConfig->getValue(
                                self::GEN_EMAIL,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                            );
                break;
        }
        $from = array('email' => (string)$email, 'name' => (string)$name);
        $this->inlineTranslation->suspend();
        $toName = $emailTempVar['name'];
        $toMail = $emailTempVar['email'];
        $store = $this->_storeManager->getStore($this->customerInt->getStoreId());
        if ($templateId) {
            $transport = $this->transportBuilder
                    ->setTemplateIdentifier($templateId)
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND, 
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                        ]
                    )
                    ->setTemplateVars([
                        'subject' => $emailTempVar['subject'],
                        'store' => $store,
                        'name' => $emailTempVar['name'],
                        'message' => $emailTempVar['message']
                    ])
                    ->setFrom($from)
                    ->addTo($toMail, $toName)
                    ->getTransport();
                    $transport->sendMessage(); 
                    $this->inlineTranslation->resume();

        }

        return $this;
    }
    public function checkSubscription($customerId){
        try{
            $model = $this->storecredit->create();
            $response = $model->getCollection()->addFieldtofilter('customer_id',$customerId)
                                               ->addFieldtofilter('is_subscribed_once',1);
            $result = $response->getData();                 
            return $result;
        }
        catch(\Exception $e)
        {
            $this->messageManager->addError(__('Something went wrong while fetching customer details.'));
        }
    } 
}

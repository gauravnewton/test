<?php

namespace Excellence\StoreCredit\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJson;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_helper;
    protected $pageFactory;
    protected $session;
    protected $resultRedirect;
    protected $urlInterface;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Controller\Result\Redirect $resultRedirect
    )
    {
        $this->resultRedirect = $resultRedirect;
        $this->session = $session;
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /**
     * Trigger to re-calculate the collect Totals
     *
     * @return bool
     */
    public function execute()
    {
        if ($this->session->isLoggedIn()) {
            $resultPage = $this->pageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Store Credit'));
            return $resultPage;
        }
        else{
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }
        
    }
}

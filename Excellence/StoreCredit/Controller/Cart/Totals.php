<?php
namespace Excellence\StoreCredit\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;

class Totals extends \Magento\Framework\App\Action\Action
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

    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Json\Helper\Data $helper,
         \Magento\Framework\Registry $registry,
         \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    )
    {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->registry     = $registry;
        $this->_resultJson = $resultJson;
        $this->_cart = $cart;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Trigger to re-calculate the collect Totals
     *
     * @return bool
     */
    public function execute()
    {  
        $response = [
            'errors' => false,
            'message' => 'Re-calculate successful.'
        ];
        try {
            //Trigger to re-calculate totals
            $cartQuote = $this->_cart->getQuote();

           $check = $this->getRequest()->getPost('checked');

           $this->registry->register('checked', $check);
           // $this->_checkoutSession->getQuote()->save();
           $cartQuote->collectTotals();
           $this->quoteRepository->save($cartQuote);
           $this->_checkoutSession->getQuote()->save($cartQuote);
           $this->registry->unregister('checked');
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
          
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultJson = $this->_resultJson->create();
        return $resultJson->setData($response);
    }
}

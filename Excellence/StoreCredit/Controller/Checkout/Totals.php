<?php

namespace Excellence\StoreCredit\Controller\Checkout;

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
        \Excellence\StoreCredit\Model\StoreusedFactory $storeusedFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson
    )
    {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->registry     = $registry;
        $this->_resultJson = $resultJson;
        $this->_storeused = $storeusedFactory;
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
         $check = $this->getRequest()->getPost('checked');
         $quoteEntityId = $this->getRequest()->getPost('quote_id');
      
           $storeUsedModel = $this->_storeused->create();
           $dataCollection = $storeUsedModel->getCollection()->addFieldToFilter('quote_id', $quoteEntityId);
            $dibitData                   = array();
            $dibitData['quote_id']       = $quoteEntityId;
            $dibitData['debit'] = $check;
            if (!$dataCollection->getData() || empty($dataCollection->getData())) {
                $storeUsedModel->setData($dibitData);
                $storeUsedModel->save();
            } else {
                $storeUsedModel->load($dataCollection->getFirstItem()->getStoreUsedId())->addData($dibitData);
                $storeUsedModel->save();
            }
           
           $this->_checkoutSession->getQuote()->collectTotals()->save();
        
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
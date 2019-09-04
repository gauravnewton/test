<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Excellence\StoreCredit\Block\Adminhtml\Edit\Tab\View;
 
use Magento\Customer\Controller\RegistryConstants;
 
/**
 * Adminhtml customer recent orders grid block
 */
class CreditGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;
 
    /**
     * @var \Magento\Sales\Model\Resource\Order\Grid\CollectionFactory
     */
    protected $_collectionFactory;
 
    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Sales\Model\Resource\Order\Grid\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Excellence\StoreCredit\Model\StorecreditFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_logger = $context->getLogger();
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * Initialize the orders grid.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('store_credit_id');
        $this->setDefaultSort('store_credit_id', 'desc');
        $this->setSortable(true);
        $this->setPagerVisibility(true);
        $this->setFilterVisibility(true);
        $this->setUseAjax(true);
    }
    /**
     * {@inheritdoc}
     */
    protected function _preparePage()
    {
        
        $this->getCollection();
    }
 
    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $page=($this->getRequest()->getParam('page'))? $this->getRequest()->getParam('page') : 1;
        $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest
        ()->getParam('limit') : 5;

        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $model = $this->_collectionFactory->create();
        $collection = $model->getCollection()->addFieldtofilter('customer_id',$customerId)->setOrder('store_credit_id','DESC')->setPageSize($pageSize)->setCurPage($page);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    /**
     * {@inheritdoc}
     */
     protected function _prepareColumns()
    {
        $this->addColumn(
            'date',
            ['header' => __('Date'), 'index' => 'date', 'type' => 'text', 'width' => '100px','editable' => FALSE,]
        );
        $this->addColumn(
            'time',
            [
                'header' => __('Time'),
                'index' => 'time',
                'editable' => FALSE,
            ]
        );
        $this->addColumn(
            'credit',
            [
                'header' => __('Credit/Debit'),
                'index' => 'credit',
                'editable' => FALSE,
                'renderer' => 'Excellence\StoreCredit\Block\Adminhtml\Customer\Transactions',
            ]
        );
        $this->addColumn(
            'reason',
            [
                'header' => __('Reason'),
                'index' => 'reason',
                'editable' => FALSE,
            ]
        );
        return parent::_prepareColumns();
    }
 
    /**
     * Get headers visibility
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getHeadersVisibility()
    {
        return $this->getCollection()->getSize() >= 0;
    }
    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return false;
    }
}
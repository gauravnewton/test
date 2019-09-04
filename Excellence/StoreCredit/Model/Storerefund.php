<?php
namespace Excellence\StoreCredit\Model;

class Storerefund extends \Magento\Framework\Model\AbstractModel 
{
    const CACHE_TAG = 'store_credit_id';

    protected $order;
    protected $creditmemoFactory;
    protected $Invoice;
    protected $creditmemoService;

    protected function _construct()
    {
        $this->_init('Excellence\StoreCredit\Model\ResourceModel\Storerefund');
    }
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $time,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
        array $data = []
    )
    {
        $this->order = $order;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->invoice = $invoice;
        $this->date = $date;
        $this->time = $time;
        parent::__construct($context,$registry);
    }
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}

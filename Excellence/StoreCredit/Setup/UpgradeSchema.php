<?php

namespace Excellence\StoreCredit\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $quoteAddressTable = 'quote_address';
        $quoteTable = 'quote';
        $orderTable = 'sales_order';
        $invoiceTable = 'sales_invoice';
        $creditmemoTable = 'sales_creditmemo';

        //Setup two columns for quote, quote_address and order
        //Quote address tables
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quoteTable),
                'usedcredit_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Used Credit Amount'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quoteTable),
                'base_usedcredit_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Base Used Credit Amount'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quoteAddressTable),
                'usedcredit_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Used Credit Amount'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quoteAddressTable),
                'base_usedcredit_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Base Used Credit Amount'
                ]
            );
        //Order tables
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'usedcredit_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Used Credit Amount'

                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'base_usedcredit_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Base Used Credit Amount'

                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'usedcredit_amount_refunded',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Base Store Credit Amount Refunded'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'base_usedcredit_amount_refunded',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Base Store Credit Amount Refunded'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'usedcredit_amount_invoiced',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Store Credit Amount Invoiced'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'base_usedcredit_amount_invoiced',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Base Fee Amount Invoiced'
                ]
            );
        //Invoice tables
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($invoiceTable),
                'usedcredit_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Used Credit Amount'

                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($invoiceTable),
                'base_usedcredit_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Base Store Credit Amount'

                ]
            );
        //Credit memo tables
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($creditmemoTable),
                'usedcredit_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Used Credit Amount'

                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($creditmemoTable),
                'base_usedcredit_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Base Used Credit Amount'

                ]
            );
        $setup->endSetup();
    }
}
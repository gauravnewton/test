<?php
/**
 * Copyright © 2015 Excellence. All rights reserved.
 */

namespace Excellence\StoreCredit\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
	
        $installer = $setup;

        $installer->startSetup();

		/**
         * Create table 'storecredit_storecredit'
         */  
//START table setup
    $table = $installer->getConnection()->newTable(
                $installer->getTable('excellence_storecredit_storecredit')
        )->addColumn(
                'store_credit_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true,'default' => null],
                'time'
            )
            ->addColumn(
                'date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true,'default' => null],
                'date'
            )
            ->addColumn(
                'credit',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true,'default' => null],
                'credit'
            )
            ->addColumn(
                'reason',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true,'default' => null],
                'reason'
            )->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true,'default' => null],
                'Customer Entity id'
            )->addColumn(
                'is_subscribed_once',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true,'default' => null],
                'Subscribe Once'
            );
    $installer->getConnection()->createTable($table);

        //END   table setup
        $installer->endSetup(); 

        //refund table
        $table = $installer->getConnection()->newTable(
            $installer->getTable('excellence_storecredit_refund')
        )->addColumn(
            'excellence_refund_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Order Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true,'default' => null],
            'Customer Id'
        )->addColumn(
            'refund_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true,'default' => null],
            'Refund'
        )->addColumn(
            'comment',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true,'default' => null],
            'Refund_comment'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'time'
        );
        $installer->getConnection()->createTable($table);
        //END   table setup
          //START table setup
    $table = $installer->getConnection()->newTable(
                $installer->getTable('excellence_storecredit_storeused')
        )->addColumn(
                'store_used_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )->addColumn(
                'debit',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true,'default' => null],
                'debit'
            )->addColumn(
                'quote_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true,'default' => null],
                'Quote Id'
            );
    $installer->getConnection()->createTable($table);

        //END   table setup
        $installer->endSetup(); 
    }
}

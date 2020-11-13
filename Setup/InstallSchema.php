<?php

namespace Braspag\Unirgy\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema  implements InstallSchemaInterface
{
    const MEDIUMTEXT_SIZE = 16777216;
    const TEXT_SIZE = 65536;

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        $vendorTable = $installer->getTable('udropship_vendor');
        $connection->addColumn($vendorTable, 'braspag_subordinate_merchantid', [
            'TYPE'=>Table::TYPE_TEXT,
            'nullable' => true,
            'COMMENT'=>'braspag_subordinate_merchantid'
        ]);

        $connection->addColumn($vendorTable, 'braspag_subordinate_default_mdr', [
            'TYPE'=>Table::TYPE_DECIMAL,
            'LENGTH' => '12,4',
            'nullable' => true,
            'default' => 0,
            'COMMENT'=>'braspag_subordinate_default_mdr'
        ]);

        $connection->addColumn($vendorTable, 'braspag_subordinate_default_fee', [
            'TYPE'=>Table::TYPE_DECIMAL,
            'LENGTH' => '12,4',
            'nullable' => true,
            'default' => 0,
            'COMMENT'=>'braspag_subordinate_default_fee'
        ]);

        $vendorProductTable = $installer->getTable('udropship_vendor_product');
        $connection->addColumn($vendorProductTable, 'braspag_subordinate_mdr', [
            'TYPE'=>Table::TYPE_DECIMAL,
            'LENGTH' => '12,4',
            'nullable' => true,
            'default' => 0,
            'COMMENT'=>'braspag_subordinate_mdr'
        ]);

        $connection->addColumn($vendorProductTable, 'braspag_subordinate_fee', [
            'TYPE'=>Table::TYPE_DECIMAL,
            'LENGTH' => '12,4',
            'nullable' => true,
            'default' => 0,
            'COMMENT'=>'braspag_subordinate_fee'
        ]);

        $installer->endSetup();
    }
}

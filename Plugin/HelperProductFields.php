<?php
/**
 * Copyright © Braspag, Inc. All rights reserved.
 */

namespace Braspag\Unirgy\Plugin;

/**
 * Class UiDataProviderProductFormModifierVendors
 * @package Braspag\Unirgy\Plugin
 */
class HelperProductFields
{
    /**
     * @param \Unirgy\DropshipMulti\Helper\ProductFields $subject
     * @param $fields
     * @return mixed
     */
    public function afterGetFields(\Unirgy\DropshipMulti\Helper\ProductFields $subject, $fields)
    {
        $fields[] = 'braspag_subordinate_mdr';
        $fields[] = 'braspag_subordinate_fee';

        return $fields;
    }

    public function afterGetFieldsByKeys(\Unirgy\DropshipMulti\Helper\ProductFields $subject, $result)
    {
        $fields[] = 'braspag_subordinate_mdr';
        $fields[] = 'braspag_subordinate_fee';

        return $fields;
    }

    public function afterIsAllowedField(\Unirgy\DropshipMulti\Helper\ProductFields $subject, $field, $return)
    {
        if ($field === 'braspag_subordinate_mdr' || $field === 'braspag_subordinate_fee') {
            return true;
        }

        return $return;
    }
}

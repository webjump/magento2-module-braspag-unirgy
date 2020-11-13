<?php
/**
 * Copyright © Braspag, Inc. All rights reserved.
 */

namespace Braspag\Unirgy\Plugin;

/**
 * Class PaymentSplitVendorSourceListObserver
 * @package Braspag\Unirgy\Observer
 */
class PaymentSplitVendorSourceList
{
    const PAYMENT_SPLIT_MARKETPLACE_VENDOR_CODE_UNIRGY = 'unirgy';
    const PAYMENT_SPLIT_MARKETPLACE_VENDOR_NAME_UNIRGY = 'Unirgy';

    /**
     * @param \Webjump\BraspagPagador\Model\Source\PaymentSplitMarketplaceVendor $subject
     * @param $result
     * @return array
     */
    public function afterToOptionArray(\Webjump\BraspagPagador\Model\Source\PaymentSplitMarketplaceVendor $subject, $result)
    {
        $result[] = [
            'value' => self::PAYMENT_SPLIT_MARKETPLACE_VENDOR_CODE_UNIRGY,
            'label' => self::PAYMENT_SPLIT_MARKETPLACE_VENDOR_NAME_UNIRGY,
        ];

        return $result;
    }
}

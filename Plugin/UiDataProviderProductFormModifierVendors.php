<?php
/**
 * Copyright © Braspag, Inc. All rights reserved.
 */

namespace Braspag\Unirgy\Plugin;

use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;

/**
 * Class UiDataProviderProductFormModifierVendors
 * @package Braspag\Unirgy\Plugin
 */
class UiDataProviderProductFormModifierVendors
{
    protected $locator;

    public function __construct(
        \Magento\Catalog\Model\Locator\LocatorInterface $locator
    ) {
        $this->locator = $locator;
    }

    /**
     * @param \Unirgy\DropshipMulti\Ui\DataProvider\Product\Form\Modifier\Vendors $subject
     * @param $meta
     * @return mixed
     */
    public function afterModifyMeta(\Unirgy\DropshipMulti\Ui\DataProvider\Product\Form\Modifier\Vendors $subject, $meta)
    {
        $costFields = [
            'braspag_subordinate_default_mdr' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Field::NAME,
                            'formElement' => Input::NAME,
                            'dataType' => Price::NAME,
                            'label' => __('MRD'),
                            'enableLabel' => true,
                            'dataScope' => 'braspag_subordinate_mdr',
                            'addbefore' => $this->locator->getStore()
                                ->getBaseCurrency()
                                ->getCurrencySymbol(),
                        ],
                    ],
                ],
            ],
            'braspag_subordinate_default_fee' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Field::NAME,
                            'formElement' => Input::NAME,
                            'dataType' => Price::NAME,
                            'label' => __('Fee'),
                            'enableLabel' => true,
                            'dataScope' => 'braspag_subordinate_fee',
                            'addbefore' => $this->locator->getStore()
                                ->getBaseCurrency()
                                ->getCurrencySymbol(),
                        ],
                    ],
                ],
            ],
        ];

        $metaData = [
            'arguments' => [
                    'data' => [
                        'config' => [
                            'component' => 'Unirgy_DropshipMulti/js/form/components/group',
                            'componentType' => Container::NAME,
                            'label' => __('Braspag Subordinate'),
                            'visible' => true,
                            'showLabel' => false
                        ],
                    ],
                ],
                'children' => $costFields
            ];

        $meta['udmulti_vendors_fieldset']['children']['udmulti_vendors']['children']['record']['children']['container_costs'] = [];
        $meta['udmulti_vendors_fieldset']['children']['udmulti_vendors']['children']['record']['children']['container_costs'] = $metaData;

        return $meta;
    }
}

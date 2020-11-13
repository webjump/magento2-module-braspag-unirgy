<?php

/**
 * @author      Webjump Core Team <dev@webjump.com.br>
 * @copyright   2020 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br  Copyright
 *
 * @link        http://www.webjump.com.br
 */

namespace Braspag\Unirgy\Plugin;

/**
 * Class SplitDataProvider
 * @package Braspag\Unirgy\Plugin
 */
class SplitDataProvider
{
    protected $paymentSplitConfig;
    protected $unirgyVendor;
    protected $objectFactory;
    protected $marketplaceMerchantId;
    protected $marketplaceDefaultMdr = 0;
    protected $marketplaceDefaultFee = 0;
    protected $marketplaceSalesParticipation;
    protected $marketplaceSalesParticipationType;
    protected $marketplaceSalesParticipationPercent = 0;
    protected $marketplaceSalesParticipationFixedValue = 0;
    protected $marketplaceParticipationFinalValue = 0;
    protected $subordinates = [];

    /**
     * SplitDataProvider constructor.
     * @param \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\Config $paymentSplitConfig
     * @param \Unirgy\Dropship\Model\Vendor $unirgyVendor
     * @param \Magento\Framework\DataObjectFactory $objectFactory
     */
    public function __construct(
        \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\Config $paymentSplitConfig,
        \Unirgy\Dropship\Model\Vendor $unirgyVendor,
        \Magento\Framework\DataObjectFactory $objectFactory
    ) {
        $this->paymentSplitConfig = $paymentSplitConfig;
        $this->unirgyVendor = $unirgyVendor;
        $this->objectFactory = $objectFactory;

        $this->marketplaceMerchantId = $this->paymentSplitConfig->getPaymentSplitMarketPlaceCredendialsMerchantId();
        $this->marketplaceSalesParticipation = (bool) $this->paymentSplitConfig->getPaymentSplitMarketPlaceGeneralSalesParticipation();
        $this->marketplaceSalesParticipationType = $this->paymentSplitConfig->getPaymentSplitMarketPlaceGeneralSalesParticipationType();
        $this->marketplaceSalesParticipationPercent = floatval($this->paymentSplitConfig->getPaymentSplitMarketPlaceGeneralSalesParticipationPercent());
        $this->marketplaceSalesParticipationFixedValue = floatval($this->paymentSplitConfig->getPaymentSplitMarketPlaceGeneralSalesParticipationFixedValue());
    }

    /**
     * @param \Webjump\BraspagPagador\Model\SplitDataProvider $subject
     * @param $result
     * @param $storeMerchantId
     * @param int $storeDefaultMdr
     * @param int $storeDefaultFee
     * @return mixed
     */
    public function afterGetData(
        \Webjump\BraspagPagador\Model\SplitDataProvider $subject,
        $result,
        $storeMerchantId,
        $storeDefaultMdr = 0,
        $storeDefaultFee = 0
    ) {

        if ($this->paymentSplitConfig->getPaymentSplitMarketPlaceVendor()
            !== \Braspag\Unirgy\Plugin\PaymentSplitVendorSourceList::PAYMENT_SPLIT_MARKETPLACE_VENDOR_CODE_UNIRGY
        ) {
            return $result;
        }

        $this->marketplaceDefaultMdr = floatval($storeDefaultMdr);
        $this->marketplaceDefaultFee = floatval($storeDefaultFee);

        $this->subordinates = $items = [];

        $itemType = 'quote';

        if (!empty($subject->getQuote())) {
            $items = $subject->getQuote()->getAllVisibleItems();
            $itemType = 'quote';
        }

        if (empty($items) && !empty($subject->getOrder())) {
            $items = $subject->getOrder()->getAllVisibleItems();
            $itemType = 'order';
        }

        if (empty($items)) {
            $items = $subject->getSession()->getQuote()->getAllVisibleItems();
            $itemType = 'quote';
        }

        foreach ($items as $item) {

            $product = $item->getProduct();

            $productVendor = $product->getCustomOption('udropship_vendor');

            $vendor = $this->unirgyVendor->load($productVendor->getValue());

            $productVendorData = \json_decode($product->getCustomOption('udmp_vendor_data')->getValue(), true);

            $braspagSubordinateMdr = floatval($productVendorData['braspag_subordinate_mdr']);
            $braspagSubordinateFee = floatval($productVendorData['braspag_subordinate_fee']);

            $braspagSubordinateMerchantId = $vendor->getBraspagSubordinateMerchantid();

            if (empty($braspagSubordinateMerchantId)) {
                $braspagSubordinateMerchantId = $this->marketplaceMerchantId;
            }

            if (!isset($this->subordinates[$braspagSubordinateMerchantId])) {
                $this->subordinates[$braspagSubordinateMerchantId] = [];
                $this->subordinates[$braspagSubordinateMerchantId]['amount'] = 0;

                if ($braspagSubordinateMerchantId !== $this->marketplaceMerchantId) {
                    $this->subordinates[$braspagSubordinateMerchantId]['fares'] = [
                        "mdr" => floatval($this->marketplaceDefaultMdr),
                        "fee" => floatval($this->marketplaceDefaultFee)
                    ];
                }

                $this->subordinates[$braspagSubordinateMerchantId]['skus'] = [];
            }

            $braspagSubordinateMdr = $this->getSubordinateItemMdr($braspagSubordinateMdr, $subject, $vendor, $product);
            $braspagSubordinateFee = $this->getSubordinateItemFee($braspagSubordinateFee, $subject, $vendor, $product);

            if (isset($this->subordinates[$braspagSubordinateMerchantId]['fares'])) {
                $this->subordinates[$braspagSubordinateMerchantId]['fares']['mdr'] = $braspagSubordinateMdr;
                $this->subordinates[$braspagSubordinateMerchantId]['fares']['fee'] = $braspagSubordinateFee;
            }

            $itemPrice = floatval($item->getPriceInclTax()-$item->getDiscountAmount());

            $this->subordinates[$braspagSubordinateMerchantId]['amount'] += $itemPrice * 100;

            $itemsObject = $this->objectFactory->create();
            $items = [
                "item_id" => $item->getId(),
                "item_type" => $itemType,
                "sku" => $product->getSku()
            ];

            $itemsObject->addData($items);

            $this->subordinates[$braspagSubordinateMerchantId]['items'][] =  $itemsObject;
        }

        if ($this->marketplaceSalesParticipation) {
            $this->removeMarketplaceParticipationValuesFromSubordinates();
            $this->addMarketplaceParticipationValues();
        }

        $result = $subject->getSplitAdapter()->adapt($this->subordinates, $this->marketplaceMerchantId);

        return $result;
    }

    /**
     * @param $vendorProductMdr
     * @param $subject
     * @param $vendor
     * @param $product
     * @return int|null
     */
    protected function getSubordinateItemMdr($vendorProductMdr, $subject, $vendor, $product)
    {
        $braspagSubordinateMdr = null;

        if (!empty($vendorProductMdr)) {
            $braspagSubordinateMdr = $vendorProductMdr;
        }

        if (empty($braspagSubordinateMdr)) {
            $braspagSubordinateMdr = $vendor->getBraspagSubordinateDefaultMdr();
        }

        if (empty($braspagSubordinateMdr)) {
            $braspagSubordinateMdr = $product->getResource()
                ->getAttributeRawValue(
                    $product->getId(),
                    'braspag_subordinate_mdr',
                    $subject->getStoreManager()->getStore()->getId()
                );
        }

        if (empty($braspagSubordinateMdr)) {
            $braspagSubordinateMdr = $this->marketplaceDefaultMdr;
        }

        return $braspagSubordinateMdr;
    }

    /**
     * @param $vendorProductFee
     * @param $subject
     * @param $vendor
     * @param $product
     * @return int|null
     */
    protected function getSubordinateItemFee($vendorProductFee, $subject, $vendor, $product)
    {
        $braspagSubordinateFee = null;

        if (!empty($vendorProductFee)) {
            $braspagSubordinateFee = $vendorProductFee;
        }

        if (empty($braspagSubordinateFee)) {
            $braspagSubordinateFee = $vendor->getBraspagSubordinateDefaultFee();
        }

        if (empty($braspagSubordinateFee)) {
            $braspagSubordinateFee = $product->getResource()
                ->getAttributeRawValue(
                    $product->getId(),
                    'braspag_subordinate_fee',
                    $subject->getStoreManager()->getStore()->getId()
                );
        }

        if (empty($braspagSubordinateFee)) {
            $braspagSubordinateFee = $this->marketplaceDefaultFee;
        }

        return $braspagSubordinateFee;
    }

    /**
     * @return $this
     */
    protected function removeMarketplaceParticipationValuesFromSubordinates()
    {
        foreach ($this->subordinates as $subordinateId => $subordinateData) {

            $subordinateAmountOriginal = floatval($subordinateData['amount']) / 100;

            if ($this->marketplaceSalesParticipation && $subordinateId !== $this->marketplaceMerchantId) {

                $subordinateAmount = $subordinateAmountOriginal;

                if ($this->marketplaceSalesParticipationType === '1') {
                    $subordinateAmount = (floatval($this->marketplaceSalesParticipationPercent) / 100) * $subordinateAmount;
                }

                if ($this->marketplaceSalesParticipationType === '2'
                    && $subordinateAmount >= $this->marketplaceSalesParticipationFixedValue
                ) {
                    $subordinateAmount = floatval($subordinateAmount) - floatval($this->marketplaceSalesParticipationFixedValue);
                }

                $this->subordinates[$subordinateId]['amount'] = $subordinateAmount * 100;

                $this->marketplaceParticipationFinalValue += $subordinateAmountOriginal-$subordinateAmount;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function addMarketplaceParticipationValues()
    {
        if (!isset($this->subordinates[$this->marketplaceMerchantId])) {
            $this->subordinates[$this->marketplaceMerchantId] = [];
            $this->subordinates[$this->marketplaceMerchantId]['amount'] = 0;
        }

        $this->subordinates[$this->marketplaceMerchantId]['amount'] += $this->marketplaceParticipationFinalValue * 100;

        return $this;
    }
}

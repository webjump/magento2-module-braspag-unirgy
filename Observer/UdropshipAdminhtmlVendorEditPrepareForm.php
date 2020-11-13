<?php

namespace Braspag\Unirgy\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UdropshipAdminhtmlVendorEditPrepareForm extends \Unirgy\DropshipSellYours\Observer\AbstractObserver
    implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $form = $observer->getEvent()->getForm();
        $fieldset = $form->getElement('vendor_form');

        $fieldset->addField('braspag_subordinate_merchantid', 'text', [
            'name'      => 'braspag_subordinate_merchantid',
            'label'     => __('Braspag Subordinate Merchant ID'),
            'note'      => __(''),
        ]);

        $fieldset->addField('braspag_subordinate_default_mdr', 'text', [
            'name'      => 'braspag_subordinate_default_mdr',
            'label'     => __('Braspag Subordinate Default MDR'),
            'note'      => __(''),
        ]);

        $fieldset->addField('braspag_subordinate_default_fee', 'text', [
            'name'      => 'braspag_subordinate_default_fee',
            'label'     => __('Braspag Subordinate Default Fee'),
            'note'      => __(''),
        ]);

        return $this;
    }
}

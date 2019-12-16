<?php

/**
 * Class Gene_Doddle_Helper_Data
 * @author Dave Macaulay <dave@gene.co.uk>
 */ 
class Gene_Doddle_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Return the full shipping method code
     *
     * @return string
     */
    public function getShippingMethodCode()
    {
        $carrier = Mage::getSingleton('gene_doddle/carrier');
        return $carrier->getCarrierCode() . '_' . $carrier::METHOD_KEY;
    }
}
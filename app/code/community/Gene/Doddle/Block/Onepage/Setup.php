<?php

/**
 * Class Gene_Doddle_Block_Onepage_Setup
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
class Gene_Doddle_Block_Onepage_Setup extends Mage_Core_Block_Template
{

    /**
     * Return the value of the shipping method radio button
     *
     * @return string
     */
    protected function getDoddleShippingMethodValue()
    {
        return Mage::helper('gene_doddle')->getShippingMethodCode();
    }

    /**
     * Return the configuration Google API key
     *
     * @return mixed
     */
    protected function getGoogleApiKey()
    {
        return Mage::getStoreConfig('carriers/gene_doddle/google_api_key');
    }

}
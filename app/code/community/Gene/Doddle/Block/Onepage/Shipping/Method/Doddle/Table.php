<?php

/**
 * Class Gene_Doddle_Block_Onepage_Shipping_Method_Doddle_Table
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
class Gene_Doddle_Block_Onepage_Shipping_Method_Doddle_Table extends Mage_Core_Block_Template
{
    /**
     * Return the store API
     *
     * @return \Gene_Doddle_Model_Api_Doddle_Stores
     */
    private function _getStoreApi()
    {
        return Mage::getSingleton('gene_doddle/api_doddle_stores');
    }

    /**
     * Return the closest stores to the searched lat/long
     *
     * @return array
     * @throws \Exception
     */
    protected function getClosestStores()
    {
        return $this->_getStoreApi()->getClosestStores($this->getLat(), $this->getLong());
    }

    /**
     * Return the collection date
     *
     * @return string
     */
    protected function getCollectionDate()
    {
        // Attempt to load the configuration option
        $daysUntilCollection = Mage::getStoreConfig('carriers/gene_doddle/collection_lead_time');
        if($daysUntilCollection) {
            return Mage::getModel('core/date')->date('l, d F', strtotime('+' . $daysUntilCollection.' days'));
        }

        return Mage::helper('gene_doddle')->__('<em>Unknown</em>');
    }

}
<?php

/**
 * Class Gene_Doddle_Model_Carrier
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
class Gene_Doddle_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * Carrier's code, as defined in parent class
     *
     * @var string
     */
    protected $_code = 'gene_doddle';

    /**
     * Set the method key
     */
    const METHOD_KEY = 'collection';

    /**
     * Returns available shipping rates for Inchoo Shipping carrier
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = Mage::getModel('shipping/rate_result');

        // Append our method
        $result->append($this->_getStandardRate());

        return $result;
    }

    /**
     * Returns Allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            self::METHOD_KEY => $this->getConfigData('name')
        );
    }

    /**
     * Get Standard rate object
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getStandardRate()
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setMethod('collection');

        // If we're saving the payment, it means we're generating the review step
        if(Mage::app()->getRequest()->getActionName() == 'savePayment' && Mage::getSingleton('checkout/session')->getDoddleStoreName()) {
            $rate->setCarrierTitle(Mage::helper('gene_doddle')->__('Collect from Doodle'));
            $rate->setMethodTitle(Mage::getSingleton('checkout/session')->getDoddleStoreName());
        } else {
            $rate->setCarrierTitle($this->getConfigData('title'));
            $rate->setMethodTitle($this->getConfigData('name'));
        }

        $rate->setPrice($this->getConfigData('price'));
        $rate->setCost(0);

        return $rate;
    }

}
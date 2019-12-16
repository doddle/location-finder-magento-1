<?php

/**
 * Class Gene_Doddle_Block_Onepage_Shipping_Method_Doddle
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
class Gene_Doddle_Block_Onepage_Shipping_Method_Doddle extends Mage_Core_Block_Template
{

    /**
     * Set the template on construction
     *
     * @return $this
     */
    protected function _construct()
    {
        // Run any parent functionality
        parent::_construct();

        // Force set our template
        $this->setTemplate('gene/doddle/onepage/shipping/method/doddle.phtml');

        // Just in case anything is chaining
        return $this;
    }

    /**
     * Retrieve parent block
     *
     * @return Mage_Checkout_Block_Onepage_Shipping_Method_Available
     */
    public function getParentBlock()
    {
        return $this->_parentBlock;
    }

    /**
     * Check the cookie to see whether or not the shipping method step should only show Doddle
     *
     * @return mixed
     */
    protected function onlyDoddle()
    {
        return Mage::registry('doddle_only');
    }

    /**
     * Return the raw price of the shipping method
     *
     * @return mixed
     */
    protected function getPrice()
    {
        return Mage::getModel('gene_doddle/carrier')->getConfigData('price');
    }

    /**
     * Return the price of the shipping method formatted
     *
     * @return mixed
     */
    protected function getFormattedPrice()
    {
        return Mage::app()->getStore()->getBaseCurrency()->format($this->getPrice());
    }

    /**
     * Return the more information block
     *
     * @return mixed
     */
    protected function getMoreInformation()
    {
        // Check that more information is set
        if($moreInformation = Mage::getStoreConfig('carriers/gene_doddle/more_information')) {

            // Strip out any nasty tags
            $filter = new Zend_Filter_StripTags(array(
                'allowTags' => array('a', 'p', 'br', 'hr', 'h2', 'h3', 'h4', 'strong', 'em')
            ));
            return $filter->filter($moreInformation);

        }

        return false;
    }

    /**
     * Only render this block if the Doddle module is enabled and available for this quote
     *
     * @return string
     */
    protected function _toHtml()
    {
        if(in_array(Mage::getSingleton('gene_doddle/carrier')->getCarrierCode(), array_keys($this->getParentBlock()->getShippingRates()))) {
            return parent::_toHtml();
        }
        return '';
    }

}
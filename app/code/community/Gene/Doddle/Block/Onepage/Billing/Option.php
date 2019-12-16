<?php

/**
 * Class Gene_Doddle_Block_Onepage_Shipping_Method_Doddle
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
class Gene_Doddle_Block_Onepage_Billing_Option extends Mage_Core_Block_Template
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
        $this->setTemplate('gene/doddle/onepage/billing/option.phtml');

        // Just in case anything is chaining
        return $this;
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

}
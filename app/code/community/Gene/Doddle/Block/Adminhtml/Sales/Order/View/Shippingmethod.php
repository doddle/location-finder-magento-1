<?php

/**
 * Class Gene_Doddle_Block_Adminhtml_Sales_Order_View_Shippingmethod
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
class Gene_Doddle_Block_Adminhtml_Sales_Order_View_Shippingmethod extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Info
{

    /**
     * Internal constructor, that is called from real constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();

        // Set our template
        $this->setTemplate('gene/doddle/shippingmethod.phtml');
    }

    /**
     * Return the pre-advice ID
     *
     * @return Gene_Doddle_Model_Resource_Request_Collection
     */
    protected function getPreAdvices()
    {
        $request = Mage::getResourceModel('gene_doddle/request_collection')
            ->addFieldToFilter('order_id', $this->getOrder()->getId());

        if($request->count()) {
            return $request;
        }

        return false;
    }
}
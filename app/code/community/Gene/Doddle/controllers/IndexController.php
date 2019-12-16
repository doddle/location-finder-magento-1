<?php

/**
 * Class Gene_Doddle_IndexController
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
class Gene_Doddle_IndexController extends Mage_Core_Controller_Front_Action
{

    /**
     * Return the table for the closest 5 stores
     *
     * @return bool
     */
    public function getClosestLongLatAction()
    {
        // Grab the long and lat from the request
        $long = $this->getRequest()->getParam('long');
        $lat = $this->getRequest()->getParam('lat');

        // Verify they're both set and not false
        if(!$long || !$lat) {
            return $this->returnAsJson(array('error' => $this->__('You must specify both latitude and longitude to use this action.')));
        }

        // Load up the layout
        $this->loadLayout();

        // Return a formatted JSON response
        return $this->returnAsJson(array(
            'success' => 'true',
            'html' => $this->getLayout()->getOutput()
        ));
    }

    /**
     * Return JSON to the browser
     *
     * @param $json
     *
     * @return bool
     */
    protected function returnAsJson($json)
    {
        // Set the response
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($json));
        return false;
    }

}
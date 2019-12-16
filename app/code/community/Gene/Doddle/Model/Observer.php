<?php

/**
 * Class Gene_Doddle_Model_Observer
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
class Gene_Doddle_Model_Observer
{
    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Insert the Doddle block into the shipping methods step
     *
     * @param \Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function insertDoddleMarkup(Varien_Event_Observer $observer)
    {
        /* @var $block Mage_Checkout_Block_Onepage_Shipping_Method_Available */
        $block = $observer->getBlock();

        // Are we dealing with the shipping method available section
        if($block instanceof Mage_Checkout_Block_Onepage_Shipping_Method_Available) {

            // Grab the transport object
            $transport = $observer->getTransport();

            // Append our blocks HTML onto the end of the generated HTML
            $html = (!Mage::registry('doddle_only') ? $transport->getHtml() : '') . Mage::app()
                    ->getLayout()
                    ->createBlock('gene_doddle/onepage_shipping_method_doddle')
                    ->setParentBlock($block)
                    ->toHtml();

            // Set the HTML back into the transport object
            $transport->setHtml($html);

        }

        return $this;
    }

    /**
     * Swap out the progress template if we're using Doddle
     *
     * @param \Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function modifyShippingMethodProgress(Varien_Event_Observer $observer)
    {
        /* @var $block Mage_Checkout_Block_Onepage_Progress */
        $block = $observer->getBlock();

        // If we're within progress and the shipping method
        if($block instanceof Mage_Checkout_Block_Onepage_Progress && $block->getTemplate() == 'checkout/onepage/progress/shipping_method.phtml') {

            // Check we're using Doddle
            if($block->getShippingMethod() == Mage::helper('gene_doddle')->getShippingMethodCode()) {

                // The old switcheroo
                $block->setTemplate('gene/doddle/onepage/progress/shipping_method.phtml');

            }

        }

        return $this;
    }

    /**
     * Capture the users selected store choice if they've choosen to use Doddle
     *
     * @param \Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function captureStoreSelection(Varien_Event_Observer $observer)
    {
        /* @var $request Zend_Controller_Request_Abstract */
        $request = $observer->getRequest();

        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getQuote();

        // Has the use selected to use Doddle?
        if($quote->getShippingAddress()->getShippingMethod() == Mage::helper('gene_doddle')->getShippingMethodCode()) {

            // Grab the store ID
            $storeId = $request->getParam('doddle-store');

            // If it's not empty attempt to load the store
            if(!empty($storeId)) {

                // Attempt to load the store
                $store = Mage::getModel('gene_doddle/store')->load($storeId);
            }

            // Validate both potential failures at once
            if(empty($storeId) || isset($store) && !$store) {

                // Build our result array
                $result = array(
                    'error' => -1,
                    'message' => Mage::helper('gene_doddle')->__('The Doddle store you\'ve selected is no longer available, please try and locate your nearest store again.')
                );

                // Send it over
                Mage::app()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                Mage::app()->getResponse()->sendResponse();
                exit;
            }

            // Set the store ID within the session
            Mage::getSingleton('checkout/session')->setDoddleStoreId($storeId)->setDoddleStoreName($store->getName());

        } else {

            // Otherwise make sure this data isn't present in the session
            Mage::getSingleton('checkout/session')->unsDoddleStoreId()->unsDoddleStoreName();
        }

        return $this;
    }

    /**
     * Just before the order is saved we want to swap the shipping
     * address out to the Doddle address
     *
     * @param \Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function changeToDoddleAddress(Varien_Event_Observer $observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getOrder();

        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getQuote();

        // Has the use selected to use Doddle?
        if($order->getShippingMethod() == Mage::helper('gene_doddle')->getShippingMethodCode()) {

            // Retrieve the store ID from the session
            $storeId = Mage::getSingleton('checkout/session')->getDoddleStoreId();

            // Verify we've got a store ID
            if(!$storeId) {
                Mage::throwException(Mage::helper('gene_doddle')->__('No store has been selected for collection from Doddle, please try again.'));
            }

            // Load up the store
            /* @var $store Gene_Doddle_Model_Store */
            $store = Mage::getModel('gene_doddle/store')->load($storeId);

            // Check the store can load
            if(!$store) {
                Mage::throwException(Mage::helper('gene_doddle')->__('The Doddle store you\'ve selected is no longer available, please try and locate your nearest store again.'));
            }

            // Change the address shipping address
            $order->getShippingAddress()->addData($store->getMagentoShippingAddress());

            // This order can no longer be shipped partially as Doddle has to receive it all at once
            $order->setCanShipPartially(0)->setCanShipPartiallyItem(0);

            // Update the quote
            $quote->getShippingAddress()->addData($store->getMagentoShippingAddress());

        }

        return $this;
    }

    /**
     * Insert a third option on the billing information step
     *
     * @param \Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function insertShipToDoddle(Varien_Event_Observer $observer)
    {
        /* @var $block Mage_Checkout_Block_Onepage_Billing */
        $block = $observer->getBlock();

        // Check we're within the correct block
        if(Mage::getStoreConfigFlag('carriers/gene_doddle/active') && $block instanceof Mage_Checkout_Block_Onepage_Billing) {

            // Grab the transport object
            $transport = $observer->getTransport();

            // We need to convert the string as we have some special characters
            $html = mb_convert_encoding($transport->getHtml(), 'HTML-ENTITIES', "UTF-8");

            // Use DomQuery for jQuery like searching
            $zendDomQuery = new Zend_Dom_Query($html);

            // Find the "no" use shipping address option
            $useShippingNo = $zendDomQuery->query('input[id="billing:use_for_shipping_no"]');

            // Verify we found the element
            if($useShippingNo->count()) {

                // Retrieve the parent LI
                $parentLi = $useShippingNo->current()->parentNode;

                // Retrieve out Doddle block
                $doddleOption = Mage::app()->getLayout()->createBlock('gene_doddle/onepage_billing_option')->toHtml();

                // We have to use a fragment to get the HTML to output
                $fragment = $useShippingNo->getDocument()->createDocumentFragment();
                $fragment->appendXML($doddleOption);

                // Append the fragment
                $parentLi->appendChild($fragment);

                // Set the HTML as our new content
                $transport->setHtml($useShippingNo->getDocument()->saveHTML());

            }

        }

        return $this;
    }

    /**
     * Intercept the saveBilling action to allow the user to skip shipping information
     *
     * @param \Varien_Event_Observer $observer
     */
    public function skipShippingInformation(Varien_Event_Observer $observer)
    {
        /** @var Mage_Checkout_OnepageController $controllerAction */
        $controllerAction = $observer->getControllerAction();

        // Grab the data from the request
        $billing = $controllerAction->getRequest()->getPost('billing', array());

        // Check to see if the user has selected Doddle
        if(isset($billing['use_for_shipping']) && $billing['use_for_shipping'] == 'doddle') {

            // Set to 1
            $billing['use_for_shipping'] = 1;

            // If so we do want to actually use the shipping in place of billing
            $controllerAction->getRequest()->setPost('billing', $billing);

            // Set something within the session so everyone is aware
            Mage::register('doddle_only', true);
        }

    }

    /**
     * Replace the shipping method info box with Doddle information
     *
     * @param \Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function replaceShippingMethodInfo(Varien_Event_Observer $observer)
    {
        /* @var $block Mage_Adminhtml_Block_Sales_Order_View_Tab_Info */
        $block = $observer->getBlock();

        // Check we're within the correct block
        if($block instanceof Mage_Adminhtml_Block_Sales_Order_View_Tab_Info) {

            // Verify we have a loaded order
            if($order = Mage::registry('current_order')) {

                // Verify that this order actually used Doddle
                if($order->getShippingMethod() == Mage::helper('gene_doddle')->getShippingMethodCode()) {

                    // Grab the transport object
                    $transport = $observer->getTransport();

                    // We need to convert the string as we have some special characters
                    $html = mb_convert_encoding($transport->getHtml(), 'HTML-ENTITIES', "UTF-8");

                    // Use DomQuery for jQuery like searching
                    $zendDomQuery = new Zend_Dom_Query($html);

                    // Grab the shipping address heading
                    $shippingAddressHead = $zendDomQuery->query('.head-shipping-method');

                    // Verify we can match onto that
                    if ($shippingAddressHead->count()) {

                        // Retrieve the title
                        $title = $shippingAddressHead->current();

                        // Grab the fieldset
                        $fieldset = $title->parentNode->parentNode->getElementsByTagName('fieldset');

                        // Verify we have found it
                        if ($fieldset->length) {

                            // Retrieve the first item in the element list
                            $fieldsetElement = $fieldset->item(0);

                            // Remove the old junk
                            while ($fieldsetElement->childNodes->length) {
                                $fieldsetElement->removeChild($fieldsetElement->firstChild);
                            }

                            // Retrieve out Doddle block
                            $doddleBlock = Mage::app()->getLayout()->createBlock('gene_doddle/adminhtml_sales_order_view_shippingmethod')->toHtml();

                            // We have to use a fragment to get the HTML to output
                            $fragment = $shippingAddressHead->getDocument()->createDocumentFragment();
                            $fragment->appendXML($doddleBlock);

                            // Append the fragment
                            $fieldsetElement->appendChild($fragment);

                            // Set the HTML as our new content
                            $transport->setHtml($shippingAddressHead->getDocument()->saveHTML());

                        }
                    }

                }

            }

        }

        return $this;
    }

    /**
     * Inform Doddle about an order dispatched
     *
     * @param \Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function informDoddle(Varien_Event_Observer $observer)
    {
        /* @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = $observer->getShipment();

        // Retrieve the order
        $order = $shipment->getOrder();

        // Verify this order is from Doddle
        if($order->getShippingMethod() == Mage::helper('gene_doddle')->getShippingMethodCode()) {

            // Verify at least one tracking number has been added
            if(count($shipment->getAllTracks()) == 0) {
                Mage::throwException('Doddle requires at least one tracking code to be supplied, multiple tracking codes can be supplied if the order was shipped seperately.');
            }

            // Grab an instance of the API
            $preAdviceApi = Mage::getSingleton('gene_doddle/api_doddle_preadvice');

            // Attempt to push the order to Doddle and retrieve the status
            $status = $preAdviceApi->pushOrder($order, $shipment->getTracksCollection());

            // Push the order over to Doddle
            if($status == Gene_Doddle_Model_Request::STATUS_COMPLETE) {

                // Add in our session message
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('gene_doddle')->__('Pre-advice has been successfully sent to Doddle.'));

            } else if($status == Gene_Doddle_Model_Request::STATUS_QUEUE) {

                // Inform the user the request failed but it's okay
                Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('gene_doddle')->__('We were unable to connect with Doddle to send pre-advice information, the request has been queued.'));

            } else {

                // This means something went really wrong
                Mage::throwException('A severe error has occurred whilst trying to advise Doddle of the shipment.');

            }

        }

        return $this;
    }

    /**
     * Attempt to run any API requests left in the queue
     *
     * @return $this
     */
    public function runQueue()
    {
        // Retrieve all queued requests
        $queuedOrders = Mage::getResourceModel('sales/order_collection');

        // Add in total sales amounts
        $queuedOrders->getSelect()->joinInner(
            array('doddle' => $queuedOrders->getTable('gene_doddle/request')),
            'main_table.entity_id = doddle.order_id AND doddle.status = \'' . Gene_Doddle_Model_Request::STATUS_QUEUE . '\'',
            array('doddle_status' => 'doddle.status', 'doddle.request_id')
        )->group('main_table.entity_id');

        // If we have more then one queued items let's get processing
        if($queuedOrders->count() >= 1) {

            // Grab an instance of the API
            $preAdviceApi = Mage::getSingleton('gene_doddle/api_doddle_preadvice');

            // Loop through each of the orders
            foreach($queuedOrders as $order) {

                // Check the order has shipments
                if($order->hasShipments()) {

                    // Loop through the collection
                    /* @var $shipment Mage_Sales_Model_Order_Shipment */
                    foreach ($order->getShipmentsCollection() as $shipment) {

                        // Verify at least one tracking number has been added
                        if (count($shipment->getAllTracks()) == 0) {

                            // Update the request to failed
                            $this->updateRequest($order->getData('request_id'), 'No tracking information for shipment', Gene_Doddle_Model_Request::STATUS_FAILED);

                        } else {

                            // Attempt to push the order to Doddle
                            $preAdviceApi->pushOrder($order, $shipment->getTracksCollection(), $order->getData('request_id'));

                        }

                    }

                } else {

                    // Update the request to failed
                    $this->updateRequest($order->getData('request_id'), 'Order has no shipments', Gene_Doddle_Model_Request::STATUS_FAILED);

                }

            }

        }

        return $this;
    }

    /**
     * Update a request with more information
     *
     * @param $requestId
     * @param $message
     * @param $status
     *
     * @return \Gene_Doddle_Model_Request
     * @throws \Exception
     */
    private function updateRequest($requestId, $message, $status)
    {
        // Otherwise update the request
        $request = Mage::getModel('gene_doddle/request')->load($requestId);
        $request->addData(array(
            'message' => Mage::helper('gene_doddle')->__($message),
            'status' => $status
        ));
        return $request->save();
    }

}
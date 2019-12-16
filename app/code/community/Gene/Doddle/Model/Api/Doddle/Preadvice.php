<?php

/**
 * Class Gene_Doddle_Model_Api_Doddle_Preadvice
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
class Gene_Doddle_Model_Api_Doddle_Preadvice extends Gene_Doddle_Model_Api_Doddle_Abstract
{

    /**
     * Push an order through to Doddle
     *
     * @param \Mage_Sales_Model_Order $order
     * @param \Mage_Sales_Model_Order_Shipment_Track $track
     *
     * @return bool
     */
    public function pushOrder(Mage_Sales_Model_Order $order, $tracks = false, $requestId = false)
    {
        // Verify we have an order
        if($order->getId()) {

            // Build our pre-advice array
            $preAdvice = array(
                'retailerID' => Mage::getStoreConfig(self::RETAILED_ID_XML_PATH, $order->getStoreId()),
                'orderID'    => $order->getId(),
                'customer'   => $this->buildCustomer($order),
                'parcels'    => $this->buildParcels($order, $tracks)
            );

            // Encode the request as JSON
            $preAdviceJson = @json_encode($preAdvice);

            // Check that we managed to build it into JSON
            if($preAdviceJson) {

                // Retrieve an access token from the API
                if($accessToken = parent::getAccessToken($this->buildScope('preadvice', $order->getStoreId()))) {

                    // Build up our authorization
                    $headers = array(
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json'
                    );

                    // Build our HTTP request
                    $http = parent::buildRequest('parcels/preadvice', Varien_Http_Client::POST, $preAdviceJson, true, $headers);

                    // Make the request
                    $response = parent::makeRequest($http);

                    // Create a new request
                    $request = Mage::getModel('gene_doddle/request');

                    // Load the request if we have a request ID
                    if($requestId) {
                        $request->load($requestId);
                    }

                    $request->createRequest($order, $response);

                    // Return the status of the request
                    return $request->getStatus();

                } else {
                    Mage::throwException('Unable to retrieve an access token from Doddle, please make sure the module\'s API settings are correctly configured.');
                }

            }

        }

        return false;
    }

    /**
     * Build the customer aspect of the API request
     *
     * @param \Mage_Sales_Model_Order $order
     *
     * @return array
     */
    public function buildCustomer(Mage_Sales_Model_Order $order)
    {
        // Build up our customer array
        $customer = array(
            'name' => array(
                'first' => $order->getBillingAddress()->getFirstname(),
                'last' => $order->getBillingAddress()->getLastname()
            ),
            'address' => array(
                'streetAddress' => $order->getBillingAddress()->getStreet(),
                'town' => $order->getBillingAddress()->getCity(),
                'postCode' => $order->getBillingAddress()->getPostcode(),
                'countryCode' => $order->getBillingAddress()->getCountry()
            ),
            'contact' => array(
                'emailAddress' => $order->getCustomerEmail(),
                'phoneNumber' => $order->getBillingAddress()->getTelephone()
            ),
            'sendSMS' => true,
            'verification' => array()
        );

        // Verify we have prefix information first
        if($prefix = $order->getBillingAddress()->getPrefix()) {
            $customer['name']['prefix'] = $prefix;
        }

        // Attempt to determine whether a card was used for this transaction
        if($order->getPayment()->getCcLast4()
            && strlen($order->getPayment()->getCcLast4()) == 4
            && $order->getPayment()->getCcType()) {

            // This means they can use their card number as verification
            $customer['verification'] = array(
                'type' => 'cardNumber',
                'value' => $order->getPayment()->getCcLast4()
            );

        } else {

            // Otherwise the user will need to verify their postcode
            $customer['verification'] = array(
                'type' => 'postCode',
                'value' => $order->getBillingAddress()->getPostcode()
            );

        }

        return $customer;
    }

    /**
     * Build up an array of parcels
     *
     * @param \Mage_Sales_Model_Order $order
     *
     * @return array
     */
    public function buildParcels(Mage_Sales_Model_Order $order, $tracks = false)
    {
        // Retrieve the tracking codes
        $trackingCodes = $order->getTracksCollection();

        // Are we only wanting to notify about a single order?
        if($tracks && $tracks instanceof Mage_Sales_Model_Resource_Order_Shipment_Track_Collection) {

            // Override the tracking codes
            $trackingCodes = $tracks;
        }

        // Grab the parcel
        $parcels = array();

        // Loop through all the tracking codes
        /* @var $trackingCode Mage_Sales_Model_Order_Shipment_Track */
        foreach ($trackingCodes as $key => $trackingCode) {

            // Start building our parcels
            $parcels[] = $this->trackToParcel($trackingCode, $order);
        }

        return $parcels;

    }

    /**
     * Convert a tracking code into a parcel
     *
     * @param \Mage_Sales_Model_Order_Shipment_Track $track
     * @param \Mage_Sales_Model_Order                $order
     *
     * @return array
     */
    protected function trackToParcel(Mage_Sales_Model_Order_Shipment_Track $track, Mage_Sales_Model_Order $order)
    {
        $parcel = array(
            'id'      => $track->getNumber(),
            'storeID' => $order->getShippingAddress()->getDoddleStoreId()
        );

        // Add the weight information if we have it
        if ($weight = $track->getWeight()) {
            $parcel['weight'] = $weight;
        }

        return $parcel;
    }

}
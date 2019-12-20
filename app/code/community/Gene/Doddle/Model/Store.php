<?php

/**
 * Class Gene_Doddle_Model_Store
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
class Gene_Doddle_Model_Store extends Varien_Object
{
    protected $_idFieldName = 'storeId';

    /**
     * Return a formatted version of the address
     * @dodo align with stores v3 data
     *
     * @return bool|string
     */
    public function getAddress()
    {
        if($this->getId()) {
            // Grab the address from the stores data
            $addressData = $this->getData('place/address');

            // Build up an array of address elements
            $addressElements = array();

            // Add the street address
            if(isset($addressData['line1'])) {
                $addressElements[] = $addressData['line1'];
            }

            if(isset($addressData['line2'])) {
                $addressElements[] = $addressData['line2'];
            }

            // Include the town
            if(isset($addressData['town']) && !empty($addressData['town'])) {
                $addressElements[] = $addressData['town'];
            }

            return implode(', ', $addressElements);
        }

        return false;
    }

    /**
     * Get store name.
     * Can't use Varien magic getter due to formatting of API response fields
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData('storeName');
    }

    /**
     * Return the stores latitude
     *
     * @return bool|mixed
     */
    public function getLat()
    {
        if($this->getId()) {
            // Grab the address from the stores data
            return $this->getData('geo/lat');
        }

        return false;
    }

    /**
     * Return the stores longitude
     *
     * @return bool|mixed
     */
    public function getLong()
    {
        if($this->getId()) {
            // Grab the address from the stores data
            return $this->getData('geo/lon');
        }

        return false;
    }

    /**
     * @return bool|mixed
     */
    public function getDistance()
    {
        if($this->getId()) {
            // Grab the distance and unit from the stores data
            return sprintf('%s %s',
                $this->getData('locationInfo/distance'),
                $this->getData('locationInfo/unit')
            );
        }

        return false;
    }

    /**
     * Return an array correctly formatted for Magento
     *
     * @return array
     */
    public function getMagentoShippingAddress()
    {
        if($this->getId()) {

            // Grab the address from the stores data
            $addressData = $this->getData('address');

            // Build up an array of address elements
            $address = array(
                'firstname' => 'Doddle',
                'lastname' => $this->getData('name'),
                'company' => '',
                'street' => implode("\n", array_filter($addressData['streetAddress'])),
                'city' => $addressData['town'],
                'region' => $addressData['county'],
                'postcode' => $addressData['postCode'],
                'country_id' => $addressData['countryCode'],
                'telephone' => $this->getData('phoneNumber'),
                'doddle_store_id' => $this->getId(),
                'same_as_billing' => 0,
                'save_in_address_book' => 0
            );

            // Some data seems to just have comma's set, so attempt to cleanse these
            foreach($address as $key => $addressItem) {
                if($addressItem == ',') {
                    $address[$key] = '';
                }
            }

            return $address;

        }

        return false;
    }

    /**
     * Return the stores opening times as a string
     * 
     * @return array|bool
     */
    public function getOpeningHours()
    {
        $response = false;

        if ($this->getId()) {
            $openingHours = $this->getData('openingHours');
            // Verify we have an array of opening hours
            if (is_array($openingHours)) {
                $response = array();

                // Per day, if the store is open, format the opening times or default to 'false'
                foreach ($openingHours as $day => $data) {
                    $times = false;
                    if (isset($data['isOpen']) && $data['isOpen'] == true && isset($data['hours'])) {
                        foreach ($data['hours'] as $hours) {
                            // Append line break if more than one opening time on a day
                            if ($times != false) {
                                $times .= '<br />';
                            }
                            $times .= sprintf('%s - %s', reset($hours), end($hours));
                        }
                    }

                    $response[] = array(
                        'label' => ucfirst(preg_replace('/\B([A-Z])/', ' $1', $day)),
                        'value' => $times
                    );
                }
            }
        }

        return $response;
    }

    /**
     * Mimic Magento core functionality with load() function
     *
     * @param $id
     *
     * @return $this
     */
    public function load($id)
    {
        $storeData = $this->getApi()->getStore($id, true);
        if($storeData && $storeData['resources']) {
            $this->addData($storeData['resources']);
        }

        return $this;
    }

    /**
     * Retrieve an instance of the API
     *
     * @return Gene_Doddle_Model_Api_Doddle_Stores
     */
    protected function getApi()
    {
        return Mage::getSingleton('gene_doddle/api_doddle_stores');
    }

}
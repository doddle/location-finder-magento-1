<?php

/**
 * Class Gene_Doddle_Model_Api_Doddle_Stores
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
class Gene_Doddle_Model_Api_Doddle_Stores extends Gene_Doddle_Model_Api_Doddle_Abstract
{
    /**
     * Store an array of requested stores
     * @var array
     */
    private $stores = false;

    /**
     * The location in the cache of our stores JSON
     */
    const DODDLE_STORE_CACHE_KEY = 'gene_doddle_stores';

    /**
     * Retrieve all the stores from the API
     *
     * @return array
     */
    public function getStores()
    {
        // Only even attempt to load the stores once
        if(!$this->stores) {

            // Attempt to load the stores from the cache
            if($stores = $this->getCache()->load(self::DODDLE_STORE_CACHE_KEY)) {

                // If they load from the cache then use those values
                $this->stores = Mage::helper('core')->jsonDecode($stores);

            } else {

                // Otherwise make a request to the API
                $http = parent::buildRequest('stores');
                $this->stores = parent::makeRequest($http);

                // Only update the cache if the request returns stores
                if(!empty($this->stores)) {

                    // Store the stores within our cache
                    $this->getCache()->save(Mage::helper('core')->jsonEncode($this->stores), self::DODDLE_STORE_CACHE_KEY, array(self::DODDLE_STORE_CACHE_KEY), 60 * 60 * 24);
                }

            }
        }

        return $this->stores;
    }

    /**
     * Return all stores as a collection
     *
     * @return \Varien_Data_Collection
     */
    public function getStoresCollection()
    {
        return $this->createStoreCollection($this->getStores());
    }

    /**
     * Get the closest stores depending on long & lat
     *
     * @param     $long
     * @param     $lat
     * @param int $size
     *
     * @return array
     */
    public function getClosestStores($lat, $long, $size = 5)
    {
        $stores = array();

        // Retrieve an access token from the API
        if($accessToken = parent::getAccessToken($this->buildScope('stores:read', $this->getStoreId()))) {

            // Build up our authorization
            $headers = array(
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            );

            // @todo get size from config ?
            // @todo include distance and unit here (also from config) ?
            $call = sprintf(
                'stores/latitude/%s/longitude/%s?companyId=%s&limit=%s&services=COLLECTIONS&includeOpeningHours=true',
                $lat,
                $long,
                $this->getCompanyId(),
                $size
            );

            // Build our HTTP request
            $http = $this->buildRequest($call, Varien_Http_Client::GET, false, false, $headers);

            // Make the request
            $response = parent::makeRequest($http);

            if ($response['resources']) {
                foreach ($response['resources'] as $resource) {
                    if ($resource['store']) {
                        // Move location info in to store data to ease retrieval from model
                        // @todo review storing separately as per API response
                        if ($resource['locationInfo']) {
                            $resource['store']['locationInfo'] = $resource['locationInfo'];
                        }
                        $stores[] = $resource['store'];
                    }
                }
            }
        } else {
            Mage::throwException('Unable to retrieve an access token from Doddle, please make sure the module\'s API settings are correctly configured.');
        }

        return $this->createStoreCollection($stores);
    }

    /**
     * Return a singular store
     *
     * @param $storeId
     *
     * @return bool|mixed
     */
    public function getStore($storeId, $returnData = false)
    {
        // Retrieve an access token from the API
        if($accessToken = parent::getAccessToken($this->buildScope('stores:read', $this->getStoreId()))) {

            // Build up our authorization
            $headers = array(
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            );

            $call = sprintf(
                'stores/%s',
                $storeId
            );

            // Build our HTTP request
            $http = $this->buildRequest($call, Varien_Http_Client::GET, false, false, $headers);

            // Retrieve the store from the API
            $store = parent::makeRequest($http);

            // If the store loads and isn't false
            if($store) {
                // Do we just want the data?
                if($returnData) {
                    return $store;
                }

                // Add the data into a model and return
                return Mage::getModel('gene_doddle/store')->addData($store);
            }
        } else {
            Mage::throwException('Unable to retrieve an access token from Doddle, please make sure the module\'s API settings are correctly configured.');
        }

        return false;
    }

    /**
     * Build the store objects into Varien_Objects
     *
     * @param $stores
     *
     * @return \Varien_Data_Collection
     * @throws \Exception
     */
    public function createStoreCollection($stores)
    {
        // Build a new basic collection
        $collection = new Varien_Data_Collection();

        // Loop through each store
        foreach($stores as $store) {

            // Create a new instance of the store model and append the data
            $storeItem = Mage::getModel('gene_doddle/store')->addData($store);

            // Add the item into our collection
            $collection->addItem($storeItem);
        }

        return $collection;
    }

    /**
     * Calculate the distance in miles between point A and B
     *
     * @param $a
     * @param $b
     *
     * @return float
     */
    protected function distance($a, $b)
    {
        list($lat1, $lon1) = $a;
        list($lat2, $lon2) = $b;

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return $miles;
    }

    /**
     * Return an instance of the caching system
     *
     * @return \Zend_Cache_Core
     */
    protected function getCache()
    {
        return Mage::app()->getCache();
    }

    /**
     * @return mixed
     */
    private function getCompanyId()
    {
        return Mage::getStoreConfig(self::RETAILED_ID_XML_PATH, $this->getStoreId());
    }
}
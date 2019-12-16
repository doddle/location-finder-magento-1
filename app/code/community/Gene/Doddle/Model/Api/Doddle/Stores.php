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
        // Push our location into an array
        $userLocation = array($lat, $long);

        // Grab the stores
        $stores = $this->getStores();

        // If we get no stores returned
        if(empty($stores)) {
            return false;
        }

        // Run through an array_map function
        $distances = array_map(function($store) use($userLocation) {
            $a = array($store['address']['lat'], $store['address']['long']);
            return $this->distance($a, $userLocation);
        }, $stores);

        // Sort correctly
        asort($distances);

        // Get the closest stores
        $closestStores = array();
        foreach($distances as $key => $distance) {

            // Add the distance into our array
            $stores[$key]['distance'] = number_format($distance, 1);

            // Add the store into our closestStores array
            $closestStores[] = $stores[$key];

            // Watch the size of the response
            if(count($closestStores) == $size) {
                break;
            }
        }

        return $this->createStoreCollection($closestStores);
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
        // Retrieve the store from the API
        $http = parent::buildRequest('stores/' . $storeId);
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
}
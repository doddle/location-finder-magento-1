<?php

/**
 * Class Gene_Doddle_Model_Api_Doddle_Abstract
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
abstract class Gene_Doddle_Model_Api_Doddle_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Set the location of the API
     */
    const API_URL_XML_PATH = 'carriers/gene_doddle/stores_api';

    /**
     * Define the locations of our API information within Magento
     */
    const API_KEY_XML_PATH = 'carriers/gene_doddle/api_key';
    const API_SECRET_XML_PATH = 'carriers/gene_doddle/api_secret';
    const API_SCOPE_XML_PATH = 'carriers/gene_doddle/api_scope';

    /**
     * Location of our retailed ID
     */
    const RETAILED_ID_XML_PATH = 'carriers/gene_doddle/retailer_id';

    /**
     * The OAuth API URL
     */
    const API_ENVIRONMENT_XML_PATH = 'carriers/gene_doddle/environment';
    const API_STAGING_OAUTH_XML_PATH = 'carriers/gene_doddle/staging_api';
    const API_PRODUCTION_OAUTH_XML_PATH = 'carriers/gene_doddle/production_api';

    /**
     * Store the access token
     * @var string
     */
    protected $accessToken = false;

    /**
     * Retrieve an access token from the powers that be
     *
     * @return string
     */
    public function getAccessToken($scope)
    {
        // Define the post data
        $postData = array(
            'grant_type' => 'client_credentials',
            'scope' => $scope
        );

        // Make the request to the API
        $http = $this->buildRequest('oauth/token', Varien_Http_Client::POST, $postData, true, false);

        // Add in our auth
        $http->setAuth(
            Mage::getStoreConfig(self::API_KEY_XML_PATH),
            Mage::getStoreConfig(self::API_SECRET_XML_PATH)
        );

        // Make the request
        $response = $this->makeRequest($http);

        // Verify we received an access token back
        if(isset($response['access_token']) && !empty($response['access_token'])) {
            $this->accessToken = $response['access_token'];
        }

        return $this->accessToken;
    }

    /**
     * Function to making requests the API
     *
     * @param $call
     */
    protected function buildRequest($call, $method = Varien_Http_Client::GET, $postData = false, $oAuth = false, $headers = false)
    {
        // Grab a new instance of HTTP Client
        $http = new Varien_Http_Client();

        // Set the URI to the method
        if($oAuth) {

            // Build up our query array, Doddle requires api_key with all requests
            $query = array(
                'api_key' => Mage::getStoreConfig(self::API_KEY_XML_PATH)
            );

            // Use the staging URL by default
            $oauthUrl = Mage::getStoreConfig(self::API_STAGING_OAUTH_XML_PATH);

            // If we're in production mode override with the production URL
            if(Mage::getStoreConfig(self::API_ENVIRONMENT_XML_PATH) == Gene_Doddle_Model_System_Config_Environment::DODDLE_PRODUCTION) {
                $oauthUrl = Mage::getStoreConfig(self::API_PRODUCTION_OAUTH_XML_PATH);
            }

            // Use the OAuth URL
            $http->setUri($oauthUrl . $call . '?' . http_build_query($query));

        } else {
            $http->setUri(Mage::getStoreConfig(self::API_URL_XML_PATH) . $call);
        }

        // Set the method in, defaults to GET
        $http->setMethod($method);

        // Do we need to add in any post data?
        if($method == Varien_Http_Client::POST) {
            if (is_array($postData) && !empty($postData)) {

                // Add in our post data
                $http->setParameterPost($postData);

            } else if (is_string($postData)) {

                // Try and decode the string
                try {

                    // Attempt to decode the JSON
                    $decode = Mage::helper('core')->jsonDecode($postData, Zend_Json::TYPE_ARRAY);

                    // Verify it decoded into an array
                    if ($decode && is_array($decode)) {

                        // Include the post data as the raw data body
                        $http->setRawData($postData, 'application/json')->request(Varien_Http_Client::POST);
                    }

                } catch (Zend_Json_Exception $e) {
                    $this->_log($e);
                    return false;
                }

            }
        }

        // Are we attempting to add any headers into the request
        if($headers && is_array($headers) && !empty($headers)) {

            // Add in our headers
            $http->setHeaders($headers);
        }

        // Return the HTTP body
        return $http;
    }

    /**
     * Make the request
     *
     * @param $http Varien_Http_Client
     *
     * @return bool|mixed
     */
    protected function makeRequest($http)
    {
        try {
            // Make the request to the server
            $response = $http->request();
        } catch (Exception $e) {
            $this->_log($e);
            return false;
        }

        // Check the status of the request
        if($response->getStatus() == 200) {

            // Retrieve the raw body, which should be JSON
            $body = $response->getRawBody();

            // Catch any errors
            try {

                // Attempt to decode the response
                $decodedBody = Mage::helper('core')->jsonDecode($body, Zend_Json::TYPE_ARRAY);

                // Return the decoded response
                return $decodedBody;

            } catch (Zend_Json_Exception $e) {
                $this->_log($e);
            } catch (Exception $e) {
                $this->_log($e);
            }

        } else {

            // If the request is anything but a 200 response make a log of it
            $this->_log($response->getStatus() . "\n" . $response->getRawBody());
        }

        return false;

    }

    /**
     * Function to log anything we're wanting to log
     *
     * @param $data
     */
    protected function _log($data)
    {
        if($data instanceof Exception) {
            $data = $data->getMessage() . "\n" . $data->getTraceAsString();
        }
        Mage::log($data, null, 'gene_doddle.log', true);
    }

    /**
     * Build the scope string from an array
     *
     * @param $scope
     *
     * @return string
     */
    protected function buildScope($scope, $storeId)
    {
        // If we've not been given an array convert it over
        if(!is_array($scope)) {
            $scope = array($scope);
        }

        // Retrieve any extra scopes
        $scopes = Mage::getStoreConfig(self::API_SCOPE_XML_PATH, $storeId);
        if($scopes) {
            // Convert the string into an array
            $scopesArray = explode(' ', $scopes);

            // Merge the array's
            $scope = array_merge($scope, $scopesArray);
        }

        return implode(' ', $scope);
    }
}
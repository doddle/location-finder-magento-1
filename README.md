# Location Finder Magento 1 Extension
Extension to allow easy installation of the Doddle location finder on the checkout page of a Magento 1 Store

Extension Version 2.0.0

This document is to show you how to connect the Australia Post location finder to your Magento site. The location finder is a plugin that allows you to show Australia Post click and collect locations on your checkout with minimal integration effort. 

The plugin itself gives users the opportunity too:
- Search for their nearest click and collect location
- Select from Australia Posts thousands of locations across the country
- See where the location is on a map
- Get access to store information such as the full address and opening hours


## Installation

The extension requires a full Magento 1 environment. You will soon be able to find the plugin in in the Magento Connect store but it is currently available on this github.

Simply copy the files into the correct folders in your magento base installation, flush the cache on your Magento store, and it should be ready for configuration.

## Setup Extension in Magento Admin Area

Once installed on your site you’ll need to do the following:
- Browse to the Magento admin area
- From the top menu, browse to “System → Configuration → Shipping Methods → Doddle” 
- Update settings as follows:
  - Shipping methods settings
    - Enable to module
  - Doddle settings
    - Variant: Australia Post
    - Retailer ID: AUSTRALIA_POST
  - Adding your API keys
    - API keys will be provided as part of the onboarding process giving you access to the Location Finder functionality
    - You may need to acquire your own google maps API key.

## Using the extension on the Frontend
Viewing the Magento extension be done by the following steps:
- Go to your frontend
- Add something to your basket
- Proceed to your checkout page
- Fill in all the necessary fields that you usually would
- At the “Shipping Method” section, click on “Doddle” 
- Search for an Australian location of your choice – you can search by postcode, address, city or landmark (standard Google Maps search terms)
- The overlay will appear with a list of locations on the left and a map (populated with pins) on the right
- You can also use the search box here to search different locations
- Select a store to pick up from – expand a store from the list to see opening hours
- Proceed through the checkout and confirm you can fully checkout




/**
 * Doddle Class for Magento One Page Checkout
 *
 * @class Doddle
 * @author Dave Macaulay <dave@gene.co.uk>
 */
var Doddle = Class.create();
Doddle.prototype = {

    /**
     * Set some values in the class when we initialize
     *
     * @param shippingMethodValue   The shipping method code/value
     * @param latLongAjaxUrl            The ajax URL for requesting stores via lat & long
     */
    initialize: function (shippingMethodValue, latLongAjaxUrl) {

        this.shippingMethodValue = shippingMethodValue;
        this.latLongAjaxUrl = latLongAjaxUrl;

        this.storeSelection = false;
    },

    /**
     * Watch for the user changing the shipping method
     */
    observeShippingMethods: function() {

        // Loop through each radio and watch for changes
        $$('[name="shipping_method"]').each(function(element) {
            Element.observe(element, 'change', function(event) {
                if(Event.findElement(event).value == this.shippingMethodValue) {

                    // Hide the shipping address in the progress area
                    if($('shipping-progress-opcheckout') != undefined) {
                        $('shipping-progress-opcheckout').hide();
                    }

                    $('doddle-box').show();

                } else {

                    // Show the shipping address in the progress area
                    if($('shipping-progress-opcheckout') != undefined) {
                        $('shipping-progress-opcheckout').show();
                    }

                    $('doddle-box').hide();

                }
            }.bind(this));
        }.bind(this));

        // Check to see if Doddle is selected when the step loads
        if($$('[name="shipping_method"]:checked').first() != undefined) {

            // Determine which method is selected
            if($$('[name="shipping_method"]:checked').first().value == this.shippingMethodValue) {

                // Hide the shipping address in the progress area
                if($('shipping-progress-opcheckout') != undefined) {
                    $('shipping-progress-opcheckout').hide();
                }

                $('doddle-box').show();

            } else {

                // Show the shipping address in the progress area
                if($('shipping-progress-opcheckout') != undefined) {
                    $('shipping-progress-opcheckout').show();
                }

                $('doddle-box').hide();

            }

        }

    },

    /**
     * If the user hits submit within the search box submit our action instead of the checkouts action
     */
    handleSubmit: function() {

        // If the user presses enter within the input we return false
        $('doddle-search-input').observe('keypress', function(e) {

            // Look for keyCode 13 (which is enter)
            if(e.keyCode == 13) {

                // Make the request
                this.getPositionFromAddress($('doddle-search-input').value, $('doddle-search-button'));

                if (e.preventDefault) {
                    e.preventDefault();
                }
                return false;
            }
        }.bind(this));

    },

    /**
     * Get the users current position from GeoLocation
     *
     * @param buttonElement
     */
    getCurrentPosition: function(buttonElement) {

        // Add the loading class name to the button
        if(buttonElement) {
            $(buttonElement).addClassName('loading').setAttribute('disabled', 'disabled');
        }

        // Do a call to the browsers GeoLocation API
        navigator.geolocation.getCurrentPosition(
            function(position) {

                // Update the stores table
                this.findClosestStores(position.coords.latitude, position.coords.longitude, function() {

                    // Remove the loading class
                    if(buttonElement) {
                        $(buttonElement).removeClassName('loading').removeAttribute('disabled');
                    }

                });

            }.bind(this),
            function(error) {

                // Aim the message towards the user
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        this.throwError('You have to agree for us to use your location to use this feature.');
                        break;
                    default:
                        this.throwError('We\'re unable to automatically retrieve your location.');
                        break;
                }

                // Remove the loading class
                if(buttonElement) {
                    $(buttonElement).removeClassName('loading').removeAttribute('disabled');
                }

            }.bind(this)
        );

    },

    /**
     * Get the position from an address and find the closest stores
     *
     * @param address
     * @param buttonElement
     */
    getPositionFromAddress: function(address, buttonElement) {

        // Check to see if the address is empty
        if(address == '') {
            return false;
        }

        // Add the loading class name to the button
        if(buttonElement) {
            $(buttonElement).addClassName('loading').setAttribute('disabled', 'disabled');
        }

        // Start a new instance of the Geocoder
        geocoder = new google.maps.Geocoder();

        // Make the request to the API
        geocoder.geocode({
            address: address
        }, function(results, status) {

            if(status == google.maps.GeocoderStatus.OK) {

                // Grab the geo coding information from the model
                geoLocation = results.first().geometry.location;

                // Update the stores table
                this.findClosestStores(geoLocation.lat(), geoLocation.lng(), function() {

                    // Remove the loading class
                    if(buttonElement) {
                        $(buttonElement).removeClassName('loading').removeAttribute('disabled');
                    }

                });
            }

        }.bind(this));

    },

    /**
     * Find the closest stores user lat & long
     *
     * @param lat
     * @param long
     * @param callback
     */
    findClosestStores: function(lat, long, callback) {

        this.captureUserSelection();

        // Make a new ajax request to the server
        new Ajax.Request(
            this.latLongAjaxUrl,
            {
                method: 'get',
                onSuccess: function(transport) {

                    // Verify we have some response text
                    if (transport && transport.responseJSON) {

                        // If we have JSON use it
                        response = transport.responseJSON;

                    } else if(transport && transport.responseText) {

                        // Parse as an object
                        try {
                            response = eval('(' + transport.responseText + ')');
                        }
                        catch (e) {
                            response = {};
                        }

                    }

                    // If the request was successful let's update!
                    if(response.success && response.html) {

                        // Update the DOM with our table
                        $('doddle-table').innerHTML = response.html;

                        // Force the height of the map to the height of the tables
                        $$('#doddle-table .doddle-map').each(function (e) {
                            if(e.previous('.opening-times') != undefined) {
                                e.setStyle({height: e.previous('.opening-times').getHeight() + 'px'});
                            }
                        });

                    } else if(response.error) {

                        // Attempt to throw the error nicely
                        this.throwError(response.error);
                    }

                    // If there is a callback function defined call it
                    if(callback) {
                        callback();
                    }

                    // Restore any selection the user has made
                    this.restoreUserSelection();

                }.bind(this),
                parameters: {'lat': lat,'long': long}
            }
        );

    },

    /**
     * Capture the current users selection to be restored after an operation
     */
    captureUserSelection: function() {

        // Always set back to false
        this.storeSelection = false;

        // Check that one of the radio buttons is checked
        if($$('[name=doddle-store]:checked').first() != undefined) {
            this.storeSelection = $$('[name=doddle-store]:checked').first().value;
        }

    },

    /**
     * Restore the users previous selection
     */
    restoreUserSelection: function() {

        // Check there is a previously store selection
        if(this.storeSelection) {

            // Check that the radio button still exists
            if($('store-' + this.storeSelection) != undefined) {
                $('store-' + this.storeSelection).checked = true;
            }

            // Unset the store selection
            this.storeSelection = false;

        }

    },

    /**
     * Throw an error, attempt to make it pretty
     *
     * @param message
     */
    throwError: function(message) {

        // Verify the message DOM exists
        if($$('#doddle-box ul.messages').first() != undefined) {

            // Pull out the message box
            messageBox = $$('#doddle-box ul.messages').first();

            // Build our new mark up
            errorElement = new Element('li').update(new Element('span').update(message));

            // Retrieve the UL
            ul = messageBox.down('ul');

            // Remove any previous errors
            if(ul.down('li') != undefined) {
                ul.down('li').remove();
            }

            // Add in the message
            ul.insert(errorElement);

            // Show the message box
            messageBox.show();

            // Hide the message box after 7.5 seconds
            hideMessages = false;
            clearTimeout(hideMessages);
            hideMessages = setTimeout(function () {
                messageBox.hide();
            }, 7500);

        } else {
            alert(message);
        }

    }

};
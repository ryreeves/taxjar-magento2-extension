/**
 * Taxjar_SalesTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Taxjar
 * @package    Taxjar_SalesTax
 * @copyright  Copyright (c) 2017 TaxJar. TaxJar is a trademark of TPS Unlimited, Inc. (http://www.taxjar.com)
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

define([
    'jquery',
    'jquery/ui',
    'validation'
], function ($) {
    'use strict';

    return function (addressValidation) {

        $.widget('mage.addressValidation', addressValidation, {

            /**
             * Validation creation
             * @protected
             */
            _create: function () {

                let button = $(this.options.selectors.button, this.element);

                this.element.validation({

                    /**
                     * Submit Handler
                     * @param {Element} form - address form
                     */
                    submitHandler: function (form) {
                        button.attr('disabled', true);

                        //TODO: add loading spinner

                        $.get('/rest/V1/Taxjar/can_validate_address/?XDEBUG_SESSION_START=10530', function (enabled) {
                            if (enabled) {

                                let addr = {
                                    'street0': form.street_1.value,
                                    'city': form.city.value,
                                    'region': form.region_id.value,
                                    'country': form.country_id.value,
                                    'postcode': form.postcode.value
                                };

                                $.ajax({
                                    type: 'POST',
                                    url: '/rest/V1/Taxjar/address_validation/',
                                    data: JSON.stringify(addr),
                                    contentType: 'application/json; charset=utf-8',
                                    dataType: 'json',
                                    success: function (response) {
                                        console.log(response);
                                        $('#form-validate fieldset:nth-child(2)').append('<div>' + response + '</div>');
                                    },
                                    failure: function (err) {
                                        console.log(err);
                                    }
                                });

                                // form.submit();  //TODO: re-enable form submission
                            } else {
                                form.submit();
                            }
                        });


                    }
                });
            }
        });

        return $.mage.addressValidation;
    }
});

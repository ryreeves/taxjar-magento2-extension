var config = {
    map: {
        '*': {
            'taxjar_suggested_addresses' : 'Taxjar_SalesTax/js/suggested_addresses'
        }
    },
    config: {
        mixins: {
            'Magento_Customer/js/addressValidation': {
                'Taxjar_SalesTax/js/address_validation': true
            }
        }
    }
};

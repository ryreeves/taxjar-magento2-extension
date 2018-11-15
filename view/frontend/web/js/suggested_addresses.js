define([
    'ko',
    'jquery',
    'jquery/ui'
], function (ko, $) {
    'use strict';

    return function (config) {

        self.suggestedAddresses = function (addrs = []) {
            addrs = [{name: 'abc'}, {name: 'def'}];
            return ko.observableArray(addrs);
        }
    }
});

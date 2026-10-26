define([], function () {
    'use strict';

    var productName = 'Joust Duffle Bag';
    var price = 34;
    var qty = 2;

    debugger;

    var total = price * qty;
    var message = productName + ' x ' + qty + ' = $' + total;

    console.log('Debug practice:', message);
});

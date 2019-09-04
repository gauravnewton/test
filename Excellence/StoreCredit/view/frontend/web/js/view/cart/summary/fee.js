/*global define*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Customer/js/model/customer'
    ],
    function (Component, quote, totals, customer) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Excellence_StoreCredit/cart/summary/fee'
            },
            title:'Store Credit',
            totals: quote.getTotals(),
            loggin: customer.isLoggedIn, 
            isDisplayed: function() {

                if(this.loggin()){
                return this.getPureValue() != 0;
                }
                else{
                    return null;
                }
            },
            getPaymentFee: function() {
                if (!this.totals()) {
                    return null;
                }
                return totals.getSegment('usedcredit').value;
            },
            getPureValue: function() {
                var price = 0;
                
                if (this.totals() && totals.getSegment('usedcredit').value) {
                      price = parseFloat(totals.getSegment('usedcredit').value);
                }
                return price;
            },
            getValue: function() {
                return this.getFormattedPrice(this.getPureValue());
            }
        });
    }
);
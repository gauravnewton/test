define([
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'ko',
    'jquery',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/action/get-payment-information'
], function (Component,quote,ko , jQuery, getTotalsAction, fullScreenLoader,totals, getPaymentInformationAction) {
    'use strict';
     var saveUrl = window.saveUrl;
     var quoteId = window.quoteentityid;
    
     return Component.extend({
        defaults: {
            template: 'Excellence_StoreCredit/payment'
        },
        isVisible : ko.observable(false),
        amount : ko.observable(window.amount),
        zeroCheck : ko.observable(window.zeroCheck),
        creditValue: ko.observable(false),
        initialize: function(){
             var self = this;
             this._super();
            if (window.checkbox == 1){
                 this.creditValue(true);
                 this.creditUsed();
                 sessionStorage.removeItem('AdminId');
            } 
            else {
                 this.creditValue(false);
                 
            }
            if(window.login==1 && window.enable==1 && window.zeroCheck!= 0){
               self.isVisible(true);
            }else{
                self.isVisible(false);
            }
        },
        creditValue: ko.observable(false),
        creditUsed: function() {
            var self = this;
          
            fullScreenLoader.startLoader();
            jQuery.ajax({
                    url: saveUrl,
                    type: "POST",
                    data: {
                        checked: self.creditValue(),
                        quote_id: quoteId
                     },
                    success: function(response) {
                        if (response) {
                            var deferred = jQuery.Deferred();
                            getTotalsAction([], deferred);
                            fullScreenLoader.stopLoader();
                            getPaymentInformationAction(deferred);
                            jQuery.when(deferred).done(function() {
                                // isApplied(false);
                                totals.isLoading(false);
                            });
                            totals.isLoading(true);
                           
                        }
                    }
            });
           return true;
        } 
  
    });
});
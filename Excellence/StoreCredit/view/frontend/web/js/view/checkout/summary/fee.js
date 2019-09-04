define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
         'ko'
    ],
    function (Component, quote, priceUtils, totals ,ko) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Excellence_StoreCredit/checkout/summary/fee'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function() {
                return this.isFullMode();
            },
            isVisible : ko.observable(false),
            title: ko.observable('StoreCredit'),
            getValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('usedcredit').value;
                }
                if(totals.getSegment('usedcredit').value < 0){
                    this.isVisible(true);
                  } else {
                     this.isVisible(false);
                }
                this.title(totals.getSegment('usedcredit').title);

                return this.getFormattedPrice(price);
            },
            getBaseValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().base_usedcredit;
                }
                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
            }
        });
    }
);

/*global define*/
define(
    [
        'Excellence_StoreCredit/js/view/cart/summary/fee'
    ],
    function (Component) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Excellence_StoreCredit/cart/totals/fee'
            },
            /**
             * @override
             *
             * @returns {boolean}
             */
            isDisplayed: function () {
                return this.getPureValue() != 0;
            }
        });
    }
);

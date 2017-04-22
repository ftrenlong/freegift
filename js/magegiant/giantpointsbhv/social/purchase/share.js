/*==================Social Earn Ajax Process=================*/
GiantPointsSocialPurchaseShare = Class.create();
GiantPointsSocialPurchaseShare.prototype =
{
    initialize: function (args) {
        this.rewardUrl = args.rewardUrl;
        this.notifElem = $$(args.notificationElement).first();
        this.orderId = args.orderId;
        this.productId = args.productId;
        this.action = args.action;
        this.successCallbacks = [];
        this.init();
    },
    init: function () {
        this.ajaxLoading = this.notifElem.down('#social_ajax_loader');
        this.message = this.notifElem.down('#social_earn_info_message');
    },
    onShare: function () {
        var me = this;
        this.message.innerHTML = '';
        this.startLoading();
        new Ajax.Request(this.rewardUrl,
            {
                method: 'post',
                parameters: {
                    product_id: this.productId,
                    order_id: this.orderId,
                    action: this.action
                },
                onSuccess: this.onSuccess.bind(this),
                onFailure: function () {
                    me.stopLoading()
                }
            }
        );
        return this
    },
    onSuccess: function (response) {
        var data = response.responseText;
        try {
            data = data.evalJSON();
            if (data.description != '') {
                this.message.innerHTML = data.description;
            }
            if (this.toplinkPointBalance && data.point_balance != '') {
                this.toplinkPointBalance.innerHTML = data.point_balance;
            }
        } catch (e) {
            if (data != '' && data.description != '') {
                this.elInfo.innerHTML = data.description;
            }
        }
        this.stopLoading();
    },
    addSuccessCallback: function (callback) {
        this.successCallbacks.push(callback);
        return this
    },
    startLoading: function () {
        this.ajaxLoading.show();
    },
    stopLoading: function () {
        this.ajaxLoading.hide();
    }
};
GiantPointsSocialWidgetHover = Class.create();
GiantPointsSocialWidgetHover.prototype = {
    initialize: function () {
        this.widgets = [];
        return this;
    },
    addWidget: function (widgetName) {
        this.widgets.push(widgetName);
        return this;
    }
};
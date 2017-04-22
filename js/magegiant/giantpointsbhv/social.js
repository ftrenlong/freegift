/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magegiant.com license that is
 * available through the world-wide-web at this URL:
 * https://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (https://magegiant.com/)
 * @license     https://magegiant.com/license-agreement/
 */
/*==================Social Earn Ajax Process=================*/

var SocialEarnAjax = Class.create({
    initialize: function (url, parameters, defaultMessage, elLoader, elInfo) {
        this.url = url;
        this.parameters = parameters;
        this.defaultMessage = defaultMessage;
        this.elLoader = elLoader;
        this.elInfo = elInfo;
        this.toplinkPointBalance = $('toplink_point_balance');
        this.handle();

    },
    handle: function () {
        this.elLoader.show();
        this.elInfo.hide();
        new Ajax.Request(this.url, {
            method: 'post',
            parameters: this.parameters,
            onSuccess: function (response) {
                var data = response.responseText;
                try {
                    data = data.evalJSON();
                    if (data.description != '') {
                        this.elInfo.innerHTML = data.description;
                    } else {
                        this.elInfo.innerHTML = this.defaultMessage;
                    }
                    if (data.point_balance != '') {
                        this.toplinkPointBalance.innerHTML = data.point_balance;
                    }
                } catch (e) {
                    if (data != '' && data.description != '') {
                        this.elInfo.innerHTML = data.description;
                    }
                }
                this.elLoader.hide();
                this.elInfo.show();
            }.bind(this)
        });
    }
});

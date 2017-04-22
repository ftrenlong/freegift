/* *
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

var PointOfSale = {
    /**
     * activate a day
     * @param {type} e
     * @returns {undefined}
     */
    activeField: function (e) {
        // en(dis)able hours
        e.ancestors()[0].select('SELECT')[0].disabled = !e.checked;
        e.ancestors()[0].select('SELECT')[1].disabled = !e.checked;
        // en(dis)able lunch time
        e.ancestors()[0].select('INPUT')[1].disabled = !e.checked;
        if (!e.checked) { // if the day is not selected => remove the lunch time
            e.ancestors()[0].select('INPUT')[1].checked = false;
            e.ancestors()[0].select('SELECT')[2].disabled = !e.checked;
            e.ancestors()[0].select('SELECT')[3].disabled = !e.checked;
        }
        PointOfSale.summary();
    },
    activeLunchTime: function (e) {
        e.ancestors()[0].select('SELECT')[2].disabled = !e.checked;
        e.ancestors()[0].select('SELECT')[3].disabled = !e.checked;
        PointOfSale.summary();
    },
    summary: function () {
        hours = {};
        $$('.' + elementId + '_day').each(function (e) {
            if (e.checked) {
                if (typeof hours[e.value] == "undefined") {
                    hours[e.value] = {};
                }
                hours[e.value]['from'] = e.ancestors()[0].select('SELECT')[0].value;
                hours[e.value]['to'] = e.ancestors()[0].select('SELECT')[1].value;
            }
        });
        $$('.' + elementId + '_lunch').each(function (e) {
            if (e.checked) {
                if (typeof hours[e.value] == "undefined") {
                    hours[e.value] = {};
                }
                hours[e.value]['lunch_from'] = e.ancestors()[0].select('SELECT')[2].value;
                hours[e.value]['lunch_to'] = e.ancestors()[0].select('SELECT')[3].value;
            }
        });
        $('hours').value = Object.toJSON(hours);
    }

};

document.observe('dom:loaded', function () {
    var data = $('hours').value.evalJSON();

    for (var day in data) {
        $(day).checked = true;
        var time = data[day];
        $(day + '_open').setValue(time.from);
        $(day + '_close').setValue(time.to);
        if (typeof time.lunch_from != "undefined") {
            $(day+'_lunch').checked = true;
            $(day + '_lunch_open').setValue(time.lunch_from);
            $(day + '_lunch_close').setValue(time.lunch_to);
        }
    }

    $$('.' + elementId + '_day').each(function (e) {
        if (!e.checked) {
            e.ancestors()[0].select('SELECT')[0].disabled = true;
            e.ancestors()[0].select('SELECT')[1].disabled = true;
        }
    });
    
    $$('.' + elementId + '_lunch').each(function (e) {
        if (!e.checked) {
            e.ancestors()[0].select('SELECT')[2].disabled = true;
            e.ancestors()[0].select('SELECT')[3].disabled = true;
        }
    })
})
document.observe('dom:loaded',
        function () {
            shippingBox = $$('.info-set .col-1')[0];
            dropdownBox = $$('.info-set .col-2')[0];

            dropdown = $("shipping_method");
            dropdownWidth = dropdown.getStyle("width");

            switcher();
            $("shipping_method").observe("change", function () {

                switcher();
            })

        }
);

function switcher() {
    value = $("shipping_method").value;

    if (value.indexOf("pickupatstore_") != -1) {
        shippingBox.setStyle({"display": "none"});
        dropdownBox.removeClassName("col-2");
        dropdownBox.addClassName("col-1");
        dropdown.setStyle({width: "100%"});


    } else {
        shippingBox.setStyle({"display": "block"});
        dropdownBox.removeClassName("col-1");
        dropdownBox.addClassName("col-2");
        dropdown.setStyle({width: dropdownWidth});
    }
}
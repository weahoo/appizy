define([
    'jquery',
    'numeral'
], function ($, numeral) {

    var APY = {};

    window.RANGE = function () {
        if (arguments.length == 1) {
            var value = null;
            var cell_ref = 's' + arguments[0][0] + 'r' + arguments[0][1] + 'c' + arguments[0][2];
            var item = $('[name=' + cell_ref + ']');

            if (item.length > 0) {
                value = APY.getInput(item.val(), item.data('type'));
            }

            return value;

        } else if (arguments.length == 2) {
            var head = arguments[0];
            var tail = arguments[1];
            var values = [];

            for (var i = 0; i <= (tail[1] - head[1]); i++) {
                var row = [];
                for (var j = 0; j <= tail[2] - head[2]; j++) {
                    cell_ref = 's' + head[0] + 'r' + (head[1] + i) + 'c' + (head[2] + j);

                    var item = $('[name=' + cell_ref + ']');

                    if (item.length > 0) {
                        row.push(APY.getInput(item.val(), item.data('type')));
                    } else {
                        row.push(null);
                    }
                }
                values.push(row);
            }
            return values;
        }
    };

    APY.getInput = function (value, type) {

        if (typeof type === "undefined") type = "string";

        if (type == "boolean") {
            value = (value == "true");
        } else if (value.length === 0) {
            value = "";
        } else if (type == "string") {

            if (!isNaN(value) && isFinite(value)) {
                value = parseFloat(value);
            } else {

            }

        } else {
            old_format = value;
            value = numeral().unformat(value);
        }
        return value;
    };

    /**
     * @param {string} output_name
     * @param {string|number} value
     * @param {string} type
     */
    APY.set = function (output_name, value, type) {

        // Set default type if necessary
        if (typeof type === "undefined") {
            type = (typeof value === "undefined") ? "string" : typeof value;
        }

        var element = $('[name=' + output_name + ']');
        var formats = $(element).data('format');

        element.data('type', type);

        // Format allowed for number, float and percentage
        if ((type == 'number' || type == 'float' || type == 'percentage' || type == 'currency') &&
            (typeof formats != "undefined")) {

            var formats_array = formats.toString().split(";", 3);
            var nb_format = formats_array.length;
            if (value == 0 && nb_format == 3) {
                myformat = formats_array[1];
            } else if (value < 0) {
                myformat = formats_array[0];
            } else {
                myformat = formats_array[nb_format - 1];
            }

            element.val(numeral(value).format(myformat));

        } else {
            element.val(value);
        }
    };

    window.onload = function () {
        var inputs = document.getElementsByTagName('input');
        for (var i = 0; i < inputs.length; i++) {
            input = inputs.item(i);

            var value = APY.getInput(inputs.item(i).value, inputs.item(i).dataset.type);
            if (!input.disabled) {
                APY.set(inputs.item(i).name, value, inputs.item(i).dataset.type);
            }
        }
        run_calc();
    };

    return APY;
});

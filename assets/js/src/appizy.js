define([
    'jquery',
    'numeral'
], function ($, numeral) {

    window.APY = window.APY || {};

    window.onload = function () {
        $('input, select').on('change', function () {
            $(this).setFormattedValue();
            run_calc();
        });

        $('input:enabled').each(function () {
            $(this).setFormattedValue();
        });
        run_calc();
    };

    window.RANGE = function () {
        var item, value;
        if (arguments.length == 1) {
            var cell_ref = 's' + arguments[0][0] + 'r' + arguments[0][1] + 'c' + arguments[0][2];

            if (window.APY.cells && window.APY.cells.hasOwnProperty(cell_ref)) {
                value = window.APY.cells[cell_ref];
            } else {
                item = $('[name=' + cell_ref + ']');
                value = item.length > 0 ? APY.getInput(item.val(), item.attr('data-value-type')) : null;
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

                    if (window.APY.cells && window.APY.cells.hasOwnProperty(cell_ref)) {
                        value = window.APY.cells[cell_ref];
                    } else {
                        item = $('[name=' + cell_ref + ']');
                        value = item.length > 0 ? APY.getInput(item.val(), item.attr('data-value-type')) : null;
                    }
                    row.push(value);
                }
                values.push(row);
            }
            return values;
        }
    };

    APY.getInput = function (value, type) {
        if (typeof type === 'undefined') type = 'string';

        if (type == 'boolean') {
            value = (value == 'true');
        } else if (value.length === 0) {
            value = '';
        } else if (type == 'string') {

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
        if (window.APY.cells && window.APY.cells.hasOwnProperty(output_name)) {
            window.APY.cells[output_name] = value;
        } else {
            // Set default type if necessary
            if (typeof type === "undefined") {
                type = (typeof value === "undefined") ? "string" : typeof value;
            }

            var element = $('[name=' + output_name + ']');
            var formats = $(element).data('format');

            element.attr('data-value-type', type);

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
        }
    };

    $.fn.setFormattedValue = function () {
        var value = $(this).val();
        var valueType = $(this).attr('data-value-type');
        var valueFormat = $(this).attr('data-format');

        var formattedValue = APY.formatValue(value, valueType, valueFormat);
        this.val(formattedValue);
    };

    APY.formatValue = function (value, type, formats) {
        var formattedValue = value;

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
            formattedValue = numeral(value).format(myformat);
        }

        return formattedValue;
    };

    return APY;
});

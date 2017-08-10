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

        $('input:enabled, select').each(function () {
            $(this).setFormattedValue();
        });
        run_calc();
    };

    window.RANGE = function () {
        var item, value;
        if (arguments.length === 1) {
            var cell_ref = 's' + arguments[0][0] + 'r' + arguments[0][1] + 'c' + arguments[0][2];

            if (window.APY.cells && window.APY.cells.hasOwnProperty(cell_ref)) {
                value = window.APY.cells[cell_ref];
            } else {
                item = $('[name=' + cell_ref + ']');
                value = item.length > 0 ? APY.getInput(item.attr('data-value'), item.attr('data-value-type')) : null;
            }

            return value;

        } else if (arguments.length === 2) {
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
                        value = item.length > 0 ? APY.getInput(item.attr('data-value'), item.attr('data-value-type')) : null;
                    }
                    row.push(value);
                }
                values.push(row);
            }
            return values;
        }
    };

    APY.getInput = function (rawInputValue, type) {
        var returnedValue;

        if (typeof type === 'undefined') type = 'string';

        if (type === 'boolean') {
            returnedValue = (rawInputValue === 'true');
        } else if (type === 'float' || type === 'number') {
            if (rawInputValue.length > 0) {
                returnedValue = parseFloat(rawInputValue);
            } else {
                returnedValue = 0;
            }
        } else if (type === 'percentage') {
            if (rawInputValue.length > 0) {
                returnedValue = numeral().unformat(rawInputValue);
            } else {
                returnedValue = 0;
            }
        } else {
            returnedValue = rawInputValue;
        }

        return returnedValue;
    };

    /**
     * @param {string} outputName
     * @param {string|number} value
     * @param {string} type
     */
    APY.set = function (outputName, value, type) {
        if (window.APY.cells && window.APY.cells.hasOwnProperty(outputName)) {
            window.APY.cells[outputName] = value;
        } else {
            if (typeof type === 'undefined') {
                type = (typeof value === 'undefined') ? 'string' : typeof value;
            }

            var element = $('[name=' + outputName + ']');
            var format = $(element).data('format');
            var myFormat;

            element.attr('data-value-type', type);
            element.attr('data-value', value);

            if ((type === 'number' || type === 'float' || type === 'percentage' || type === 'currency') &&
                (typeof format !== "undefined")) {

                var formatParts = format.toString().split(";", 3);
                var formatPartsNumber = formatParts.length;
                if (value === 0 && formatPartsNumber === 3) {
                    myFormat = formatParts[1];
                } else if (value < 0) {
                    myFormat = formatParts[0];
                } else {
                    myFormat = formatParts[formatPartsNumber - 1];
                }

                element.val(numeral(value).format(myFormat));

            } else {
                element.val(value);
            }
        }
    };

    $.fn.setFormattedValue = function () {
        var valueType = $(this).attr('data-value-type');
        var valueFormat = $(this).attr('data-format');

        var value = APY.getInput($(this).val(), valueType);
        var formattedValue = APY.formatValue(value, valueType, valueFormat);

        this.attr('data-value', value);
        this.val(formattedValue);
    };

    APY.formatValue = function (value, type, formats) {
        var myFormat;
        var formattedValue = value;

        if ((type === 'number' || type === 'float' || type === 'percentage' || type === 'currency') &&
            (typeof formats !== 'undefined')) {

            var formatsParts = formats.toString().split(";", 3);
            var formatPartsNumber = formatsParts.length;
            if (value === 0 && formatPartsNumber === 3) {
                myFormat = formatsParts[1];
            } else if (value < 0) {
                myFormat = formatsParts[0];
            } else {
                myFormat = formatsParts[formatPartsNumber - 1];
            }

            formattedValue = numeral(value).format(myFormat);
        }

        return formattedValue;
    };

    return APY;
});

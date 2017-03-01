/**
 * Functions not implemented in formulajs
 * @see https://github.com/sutoiku/formula.js
 */
define([], function () {
    var root = this;
    var Formula = root.Formula = {};

    Formula.ARGSTOARRAY = function (args) {
        return Array.prototype.slice.call(args, 0);
    };

    Formula.VLOOKUP = function (searchCriterion, array, columnIndex, exactMatch) {
        exactMatch = (exactMatch != false && exactMatch != 0 && exactMatch != undefined) || (typeof searchCriterion === 'string');

        var table = Formula.ARGSTOARRAY(array);
        var n = table.length;
        var currentValue;
        var result = '';
        for (var i = 0; i < n; i++) {
            currentValue = table[i][0];
            if (currentValue == searchCriterion || (!exactMatch && currentValue < searchCriterion)) {
                result = table[i][columnIndex - 1];
            }
        }
        return result;
    };

    return {
        ARGSTOARRAY: Formula.ARGSTOARRAY,
        VLOOKUP: Formula.VLOOKUP
    }
});

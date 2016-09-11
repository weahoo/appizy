/**
 * Functions not yet implemented in formulajs
 * @see https://github.com/sutoiku/formula.js
 */
(function () {
    var root = this;

    var Formula = root.Formula = {};

    Formula.ARGSTOARRAY = function (args) {
        return Array.prototype.slice.call(args, 0);
    };

    Formula.VLOOKUP = function (lookup_value, table_array, col_index_num) {
        var table = Formula.ARGSTOARRAY(table_array);
        var n = table.length;
        var result = '';
        for (var i = 0; i < n; i++) {
            if (table[i][0] == lookup_value) result = table[i][col_index_num - 1];
        }
        return result;
    };

}).call(this);


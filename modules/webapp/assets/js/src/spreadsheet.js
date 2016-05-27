define(function () {

    return {
        index: index,
        ref: ref
    };

    function index() {

        return '';
    }

    function ref () {
        if (arguments.length == 1) {
            var value = null;
            var cell_ref = 's' + arguments[0][0] + 'r' + arguments[0][1] + 'c' + arguments[0][2];
            var item = $('[name=' + cell_ref + ']');

            if (item.length > 0) {
                value = APY.getInput(item.val(), item.attr('data-value-type'));
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
                        row.push(APY.getInput(item.val(), item.attr('data-value-type')));
                    } else {
                        row.push(null);
                    }
                }
                values.push(row);
            }
            return values;
        }
    }
});

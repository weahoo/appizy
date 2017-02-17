define([
    '../src/appizy'
], function (appizy) {

    describe('Appizy,', function () {
        beforeEach(function () {
            window.APY = {};
            window.APY.cells = {};
        });
        describe('function: RANGE', function () {


            it('should return null by default', function () {
                expect(RANGE([0, 0, 0])).toEqual(null);
            });

            it('should return the value of the cell', function () {
                APY.cells['s0r0c0'] = 42;
                expect(RANGE([0, 0, 0])).toEqual(42);
            });

            it('should return empty string value for a cell', function () {
                APY.cells['s0r0c0'] = '';
                expect(RANGE([0, 0, 0])).toEqual('');
            });

            it('should return a matrix of cells values', function () {
                APY.cells = {
                    s0r0c0: 1,
                    s0r0c1: 2,
                    s0r1c0: 3,
                    s0r1c1: 4
                };
                expect(RANGE([0, 0, 0], [0, 1, 1])).toEqual([
                    [1, 2],
                    [3, 4]
                ]);
            });
        });

        describe('function: set', function () {
            it('should set a cell value', function () {
                APY.cells['s0r0c0'] = 42;
                appizy.set('s0r0c0', 3);
                expect(RANGE([0, 0, 0])).toEqual(3);
            })

            it('should support empty string value', function () {
                APY.cells['s0r0c0'] = 42;
                appizy.set('s0r0c0', '');
                expect(APY.cells['s0r0c0']).toEqual('');
            })
        });
    });
});

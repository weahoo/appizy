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
            });

            it('should support empty string value', function () {
                APY.cells['s0r0c0'] = 42;
                appizy.set('s0r0c0', '');
                expect(APY.cells['s0r0c0']).toEqual('');
            });
        });

        describe('function: getInput', function () {
            it('should string', function () {
                expect(appizy.getInput('abc','string')).toBe('abc');
            });

            it('should convert boolean string to "true"', function () {
                expect(appizy.getInput('true', 'boolean')).toBe(true);
            });

            it('should convert boolean string to "false"', function () {
                expect(appizy.getInput('false', 'boolean')).toBe(false);
            });

            it('should parse float string', function () {
                expect(appizy.getInput('0.42', 'float')).toEqual(0.42);
            });

            it('should parse very small float string', function () {
                expect(appizy.getInput('-0.0000000816393442622951', 'float')).toEqual(-0.0000000816393442622951);
            });

            it('should parse scientific notation float string', function () {
                expect(appizy.getInput('4.7155012434395e-8', 'float')).toEqual(0.000000047155012434395);
            });

            it('should parse percentage string to float', function () {
                expect(appizy.getInput('0.5', 'percentage')).toEqual(0.5);
            });

            it('should parse percentage string with "%" sign to float', function () {
                // Floating problem here, that's why Math.round is used
                expect(Math.round(appizy.getInput('47%', 'percentage') * 100) / 100).toEqual(0.47);
            });

            it('should parse number string to float', function () {
                expect(appizy.getInput('2.27180222782', 'number')).toEqual(2.27180222782);
            });
        });
    });
});

define([
    '../src/formula-addons'
], function (formula) {
    'use strict';

    var searchMatrix;

    var alphaSearchMatrix = [
        ['a', 1, 'foo'],
        ['b', 2, 'bar']
    ];

    var numSearchMatrix = [
        [10, 'a'],
        [11, 'b'],
        [12, 'c'],
        [13, 'd']
    ];

    describe('Module: formula add-ons', function () {
        describe('function: VLOOKUP', function () {

            it('should return the searched value', function () {
                searchMatrix = alphaSearchMatrix;
                expect(formula.VLOOKUP('a', searchMatrix, 2)).toEqual(1);

                expect(formula.VLOOKUP('b', searchMatrix, 3)).toEqual('bar');
            });

            it('should be empty by default', function () {
                expect(formula.VLOOKUP('', searchMatrix, '')).toEqual('');
            });

            it('should return the searched value', function () {
                searchMatrix = numSearchMatrix;

                expect(formula.VLOOKUP(10, searchMatrix, 2)).toEqual('a');
                expect(formula.VLOOKUP(13, searchMatrix, 2)).toEqual('d');
            });

            it('should return the nearest value in case of numeric search', function () {
                searchMatrix = numSearchMatrix;

                expect(formula.VLOOKUP(11.5, searchMatrix, 2)).toEqual('b');
            });


            it('should return the exact value in case of numeric search with ExactMatch', function () {
                searchMatrix = numSearchMatrix;

                expect(formula.VLOOKUP(11.5, searchMatrix, 2, 1)).toEqual('');
                expect(formula.VLOOKUP(11, searchMatrix, 2, 1)).toEqual('b');
            });
        });
    });
});

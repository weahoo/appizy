define([
    '../src/formula-addons'
], function (formula) {
    'use strict';

    var searchMatrix;

    describe('Module: formula add-ons', function () {
        describe('function: VLOOKUP', function () {

            beforeEach(function () {
               searchMatrix =  [
                   ['a', 1, 'foo'],
                   ['b', 2, 'bar']
               ];
            });
            it('should return the searched value', function () {
                expect(formula.VLOOKUP('a', searchMatrix, 2)).toEqual(1);

                expect(formula.VLOOKUP('b', searchMatrix, 3)).toEqual('bar');
            });

            it('should be empty by default', function () {
                expect(formula.VLOOKUP('', searchMatrix, '')).toEqual('');
            });
        });
    });
});

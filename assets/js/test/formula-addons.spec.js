define([
    '../src/appizy',
    '../src/formula-addons'
], function (appizy, formula) {
    'use strict';

    describe('Module: formula add-ons', function () {
        describe('function: VLOOKUP', function () {
            it('should return the searched value', function () {
                expect(formula.VLOOKUP('a', [
                    ['a', 1, 'foo'],
                    ['b', 2, 'bar']
                ], 2)).toEqual(1);

                expect(formula.VLOOKUP('b', [
                    ['a', 1, 'foo'],
                    ['b', 2, 'bar']
                ], 3)).toEqual('bar');
            });
        });
    });
});

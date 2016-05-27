define(['src/appizy'], function (appizy) {
    "use strict";

    describe('Appizy formatValue', function () {
        it('should format currency value', function () {
            expect(appizy.formatValue(10, 'currency', '$0.00')).toBe('$10.00');
        })
    });
});

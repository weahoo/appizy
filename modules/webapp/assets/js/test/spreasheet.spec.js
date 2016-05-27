define([
    'src/spreadsheet'
], function(spreadsheet){

    describe('Function index', function(){
        it('should return empty', function(){
            expect(spreadsheet.index()).toBe('');
        });
    });
});
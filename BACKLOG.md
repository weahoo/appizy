# Backlog

## Features

 * Handle Date content and basic functions associated with.
 * Make the Command more verbose. Reporting errors to the user so he/she can fix his/her spreadsheet.
 * Warn user in case of #REF or DIV/0 in the converted Spreadsheet, escape those formula.
 * Warn user in case of unsupported function in formula, escape the formula.

## Issues and limitations

 * Formula with dependency on a non-existent cell (for example a large SUM range). Manual solution: fill all cells with 
 default values.
 * A too long formula might exhausts the OpenFormula lexer. Manual solution: split or refactor the function. 
 * Too complicated number formats are not supported.

# Backlog

## Bugs

* File permission on docker created files (to restrictive)

## Features

 * Handle Date content and basic functions associated with.
 * Warn user in case of #REF or DIV/0 in the converted Spreadsheet, escape those formula.
 * Support ISNUMBER function
 * Support ISTEXT function
 
## Issues and limitations

 * Formula with dependency on a non-existent cell (for example a large SUM range). Manual solution: fill all cells with 
 default values.
 * Too complicated number formats are not supported.

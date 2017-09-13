<?php

namespace Appizy;

class WebAppConfiguration
{
    public static function operatorDictionary()
    {
        return [
            '+'  => '+',
            '-'  => '-',
            '/'  => '/',
            '*'  => '*',
            '('  => '(',
            ')'  => ')',
            ';'  => ',',
            ','  => ',',
            '<>' => '!=',
            '<=' => '<=',
            '>=' => '>=',
            '<'  => '<',
            '>'  => '>',
            '='  => '==',
            '&'  => '+',
            /**
             * CORRECTORS:
             * - Remove additional spaces in formula
             * - Removes "++" accepted by Excel/OO
             */
            ' '  => '',
            '++' => '+',
        ];
    }

    public static function externalFunctionDictionary()
    {
        return [
            'ABS'          => 'Formula.ABS',
            'ACOS'         => 'Formula.ACOS',
            'AND'          => 'Formula.AND',
            'ASIN'         => 'Formula.ASIN',
            'ATAN'         => 'Formula.ATAN',
            'AVEDEV'       => 'Formula.AVEDEV',
            'AVERAGE'      => 'Formula.AVERAGE',
            'CEILING'      => 'Formula.CEILING',
            'CONCATENATE'  => 'Formula.CONCATENATE',
            'COS'          => 'Formula.COS',
            'COUNTIF'      => 'Formula.COUNTIF',
            'DEGREES'      => 'Formula.DEGREES',
            'DOLLAR'       => 'Formula.DOLLAR',
            'FALSE'        => 'Formula.FALSE',
            'FINV'         => 'Formula.FINV',
            'FLOOR'        => 'Formula.FLOOR',
            'FV'           => 'Formula.FV',
            'GCD'          => 'Formula.GCD',
            'HYPERLINK'    => 'Formula.HYPERLINK',
            'IF'           => 'Formula.IF',
            'IFERROR'      => 'Formula.IFERROR',
            'IFNA'         => 'Formula.IFNA',
            'INT'          => 'Formula.INT',
            'ISBLANK'      => 'Formula.ISBLANK',
            'LCM'          => 'Formula.LCM',
            'LEN'          => 'Formula.LEN',
            'LEGACY.FINV'  => 'Formula.FINV',
            'LEGACY.STDEV' => 'Formula.STDEVS',
            'LEGACY.TDIST' => 'Formula.TDIST',
            'LOG'          => 'Formula.LOG',
            'LOG10'        => 'Formula.LOG10',
            'LOOKUP'       => 'Formula.LOOKUP',
            'LOWER'        => 'Formula.LOWER',
            'MAX'          => 'Formula.MAX',
            'MIN'          => 'Formula.MIN',
            'MOD'          => 'Formula.MOD',
            'MROUND'       => 'Formula.MROUND',
            'NOT'          => 'Formula.NOT',
            'NPER'         => 'Formula.NPER',
            'NPV'          => 'Formula.NPV',
            'OR'           => 'Formula.OR',
            'PI'           => 'Formula.PI',
            'PMT'          => 'Formula.PMT',
            'POWER'        => 'Formula.POWER',
            'PRODUCT'      => 'Formula.PRODUCT',
            'QUOTIENT'     => 'Formula.QUOTIENT',
            'RADIANS'      => 'Formula.RADIANS',
            'RAND'         => 'Formula.RAND',
            'RANDBETWEEN'  => 'Formula.RANDBETWEEN',
            'RANK'         => 'Formula.RANK',
            'REPT'         => 'Formula.REPT',
            'ROMAN'        => 'Formula.ROMAN',
            'ROUND'        => 'Formula.ROUND',
            'ROUNDUP'      => 'Formula.ROUNDUP',
            'ROUNDDOWN'    => 'Formula.ROUNDDOWN',
            'SEARCH'       => 'Formula.SEARCH',
            'SIN'          => 'Formula.SIN',
            'SQRT'         => 'Formula.SQRT',
            'SUM'          => 'Formula.SUM',
            'SUMIF'        => 'Formula.SUMIF',
            'SUMPRODUCT'   => 'Formula.SUMPRODUCT',
            'STDEV'        => 'Formula.STDEVS',
            'TAN'          => 'Formula.TAN',
            'TINV'         => 'Formula.TINV',
            'TDIST'        => 'Formula.TDIST',
            'TRIM'         => 'Formula.TRIM',
            'TRUE'         => 'Formula.TRUE',
            'TRUNC'        => 'Formula.TRUNC',
            'UPPER'        => 'Formula.UPPER',
            'VLOOKUP'      => 'Formula.VLOOKUP',
            'XOR'          => 'Formula.XOR',
        ];
    }
}

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
            'LEGACY.FINV'  => 'Formula.FINV',
            'LEGACY.STDEV' => 'Formula.STDEVS',
            'LEGACY.TDIST' => 'Formula.TDIST',
            'LOOKUP'       => 'Formula.LOOKUP',
            'MAX'          => 'Formula.MAX',
            'MIN'          => 'Formula.MIN',
            'MOD'          => 'Formula.MOD',
            'MROUND'       => 'Formula.MROUND',
            'NOT'          => 'Formula.NOT',
            'NPER'         => 'Formula.NPER',
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
            'ROUND'        => 'Formula.ROUND',
            'ROUNDUP'      => 'Formula.ROUNDUP',
            'ROUNDDOWN'    => 'Formula.ROUNDDOWN',
            'SIN'          => 'Formula.SIN',
            'SQRT'         => 'Formula.SQRT',
            'SUM'          => 'Formula.SUM',
            'SUMIF'        => 'Formula.SUMIF',
            'SUMPRODUCT'   => 'Formula.SUMPRODUCT',
            'STDEV'        => 'Formula.STDEVS',
            'TAN'          => 'Formula.TAN',
            'TINV'         => 'Formula.TINV',
            'TDIST'        => 'Formula.TDIST',
            'TRUE'         => 'Formula.TRUE',
            'TRUNC'        => 'Formula.TRUNC',
            'VLOOKUP'      => 'Formula.VLOOKUP',
            'XOR'          => 'Formula.XOR',
        ];
    }
}

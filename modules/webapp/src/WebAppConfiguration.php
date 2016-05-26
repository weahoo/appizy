<?php

namespace Appizy\WebApp;

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
            'SUM'         => 'Formula.SUM',
            'SUMIF'       => 'Formula.SUMIF',
            'SUMPRODUCT'  => 'Formula.SUMPRODUCT',
            'COS'         => 'Formula.COS',
            'ACOS'        => 'Formula.ACOS',
            'SIN'         => 'Formula.SIN',
            'ASIN'        => 'Formula.ASIN',
            'TAN'         => 'Formula.TAN',
            'ATAN'        => 'Formula.ATAN',
            'PI'          => 'Formula.PI',
            'POWER'       => 'Formula.POWER',
            'SQRT'        => 'Formula.SQRT',
            'MAX'         => 'Formula.MAX',
            'MIN'         => 'Formula.MIN',
            'AVERAGE'     => 'Formula.AVERAGE',
            'AVEDEV'      => 'Formula.AVEDEV',
            'RADIANS'     => 'Formula.RADIANS',
            'DEGREES'     => 'Formula.DEGREES',
            'ROUND'       => 'Formula.ROUND',
            'CEILING'     => 'Formula.CEILING',
            'FLOOR'       => 'Formula.FLOOR',
            'ROUNDUP'     => 'Formula.ROUNDUP',
            'ROUNDDOWN'   => 'Formula.ROUNDDOWN',
            'INT'         => 'Formula.INT',
            'TRUNC'       => 'Formula.TRUNC',
            'MROUND'      => 'Formula.MROUND',
            'ABS'         => 'Formula.ABS',
            'GCD'         => 'Formula.GCD',
            'LCM'         => 'Formula.LCM',
            'MOD'         => 'Formula.MOD',
            'RAND'        => 'Formula.RAND',
            'RANDBETWEEN' => 'Formula.RANDBETWEEN',
            'QUOTIENT'    => 'Formula.QUOTIENT',
            'PRODUCT'     => 'Formula.PRODUCT',
            'PMT'         => 'Formula.PMT',
            'RANK'        => 'Formula.RANK',
            'AND'         => 'Formula.AND',
            'FALSE'       => 'Formula.FALSE',
            'IF'          => 'Formula.IF',
            'IFERROR'     => 'Formula.IFERROR',
            'IFNA'        => 'Formula.IFNA',
            'NOT'         => 'Formula.NOT',
            'OR'          => 'Formula.OR',
            'TRUE'        => 'Formula.TRUE',
            'XOR'         => 'Formula.XOR',
            'ISBLANK'     => 'Formula.ISBLANK',
            'DOLLAR'      => 'Formula.DOLLAR',
            'CONCATENATE' => 'Formula.CONCATENATE',
            'HYPERLINK'   => 'Formula.HYPERLINK',
            'VLOOKUP'     => 'Formula.VLOOKUP',
            'LOOKUP'      => 'Formula.LOOKUP',
            'COUNTIF'     => 'Formula.COUNTIF'
        ];
    }
}
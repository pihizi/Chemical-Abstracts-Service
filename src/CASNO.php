<?php
/**
* @file CASNO.php
* @brief Chemical-Reagent CAS No. 
* @author PiHiZi <pihizi@msn.com>
* @version 0.1.0
* @date 2016-04-09
 */

namespace ChemicalReagent;

class CASNO
{
    /**
        * @brief 
        *
        * @param $value
        *
        * @return true | false
     */
    public static function verify($value)
    {
        //$pattern = '/^(\d{2,7})-(\d{2})-(\d)$/';
        $pattern = '/^(\d{2,8})-(\d{2})-(\d)$/';
        if (!preg_match($pattern, $value, $matches)) {
            return false;
        }
        $digits = array_reverse(str_split($matches[1].$matches[2]));
        $sum = 0;
        for ($i = 0, $l = count($digits); $i < $l; ++$i) {
            $sum += ($i + 1) * ((int) $digits[$i]);
        }
        return ($sum % 10) === (int) $matches[3];
    }
}

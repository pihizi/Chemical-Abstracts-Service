<?php

namespace ChemicalReagent;

class Conversion
{
    private static $native;
    private static $map = [];

    private $value;
    private $unit;

    /**
        * @brief 
        *
        * @param $string
        * @param $maps
        *       [
        *           [
        *               'ml'=> 1,
        *               'ul'=> 1000,
        *               'μl'=> 1000,
        *               'cl'=> 0.1,
        *               'dl'=> 0.01,
        *               'l'=> 0.001,
        *           ],
        *           [
        *               'g'=> 1,
        *               'ug'=> 1000000,
        *               'μg'=> 1000000,
        *               'mg'=> 1000,
        *               'kg'=> 0.001,
        *           ]
        *       ]
        *
        * @return 
     */
    public function __construct($string, array $maps=[])
    {
        $value = self::_parse($string);
        if (!empty($value)) {
            list($value, $unit) = $value;
            $this->value = $value;
            $this->unit = $unit;
            foreach ($maps as $map) {
                $keys = array_keys($map);
                if (in_array($this->unit, $keys)) {
                    self::$map = $map;
                    self::$native = $keys[0];
                    break;
                }
            }
        }
    }

    public function add($string)
    {
        $value = self::_parse($string);
        if (empty($value)) return $this;
        list($value, $unit) = $value;
        $newValue = @self::convert($unit, $this->unit, $value);
        if (false===$newValue) return $this;
        $this->value += $newValue;
        return $this;
    }

    public function sub($string)
    {
        $value = self::_parse($string);
        if (empty($value)) return $this;
        list($value, $unit) = $value;
        $newValue = @self::convert($unit, $this->unit, $value);
        if (false===$newValue) return $this;
        $this->value -= $newValue;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getUnit()
    {
        return $this->unit();
    }

    public function to($unit)
    {
        $this->value = self::convert($this->unit, $unit, $this->value);
        $this->unit = $unit;
        return $this;
    }

    private static function convert($from, $to, $value)
    {
        return ($value * self::getConversionRate($from)) / self::getConversionRate($to);
    }

    private static function getConversionRate($unit)
    {
        if (!isset(self::$map[$unit])) {
            throw new \ChemicalReagent\Exception("Undefined unit: {$unit}");
        }
        return self::$map[$unit];
    }

    private static function _foramt($decimals=3, $decPoint='.', $thousandSep=',')
    {
        return number_format($this->value, $decimals, $decPoint, $thousandSep);
    }

    public function beautify()
    {
        $myLen = strlen($this->value);
        $units = array_keys(self::$map);
        foreach ($units as $unit) {
            if ($unit===$this->unit) continue;
            $newValue = self::convert($this->unit, $unit, $this->value) ;
            $newLen = strlen($newValue);
            if ($newLen<$myLen || ($newLen==$myLen && strpos($newValue, '.')===false)) {
                $myLen = $newLen;
                $this->value = $newValue;
                $this->unit = $unit;
            }
        }
        return self::_foramt() . $this->unit;
    }

    public function out($decimals=3, $decPoint='.', $thousandSep=',')
    {
        return self::_foramt($decimals, $decPoint, $thousandSep) . $this->unit;
    }

    public function toString()
    {
        $this->to(self::$native);
        return self::_foramt($decimals, $decPoint, $thousandSep) . $this->unit;
    }

    private static function _parse($value)
    {
        $units = implode('|', array_keys(self::$map));
        if ($units) {
            $pattern = implode('', ['/(\d+(?:\.\d+)?)\s*(', $units, ')\s*(?:\*|\x|\X)\s*(\d+(?:\.\d+)?)/']);
        }
        if ($pattern && preg_match($pattern, $value, $mathces)) {
            list(,$value, $unit, $count) = $matches;
            return [$value*$count, $unit];
        }
        return [];
    }
}

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
        $maps = empty($maps) ? self::_getDefaultMaps() : $maps;
        $value = self::_init($string, $maps);
        if (!empty($value)) {
            list($value, $unit, $map) = $value;
            $keys = array_keys($map);
            $this->value = $value;
            $this->unit = $unit;
            self::$map = $map;
            self::$native = $keys[0];
        }
    }

    private static function _getMatches($string, $map)
    {
        $units = implode('|', array_keys($map));
        if ($units) {
            $pattern = implode('', ['/(\d+(?:\.\d+)?)\s*(', $units, ')\s*(?:\*|\x|\X)\s*(\d+(?:\.\d+)?)?/']);
        }
        if ($pattern && preg_match($pattern, $value, $matches)) {
            $value = $matches[1];
            $unit = $matches[2];
            if (isset($matches[3])) {
                $value *= $matches[3];
            }
            return [$value, $unit];
        }
        return [];
    }

    private static function _init($string, array $maps=[])
    {
        if (empty($maps)) return [];
        foreach ($maps as $map) {
            $matches = self::_getMatches($string, $map);
            if (!empty($matches)) {
                return array_merge($matches, [$map]);
            }
        }
        return [];
    }

    private static function _parse($string, array $map=[])
    {
        if (empty($map)) return [];
        return self::_getMatches($string, $map);
    }

    private static function _getDefaultMaps()
    {
        return [
            [
                'ml'=> 1,
                'ul'=> 1000,
                'μl'=> 1000,
                'cl'=> 0.1,
                'dl'=> 0.01,
                'l'=> 0.001,
            ],
            [
                'g'=> 1,
                'ug'=> 1000000,
                'μg'=> 1000000,
                'mg'=> 1000,
                'kg'=> 0.001,
            ]
        ];
    }

    public function add($string)
    {
        $value = self::_parse($string, self::$map);
        if (empty($value)) return $this;
        list($value, $unit) = $value;
        $newValue = @self::convert($unit, $this->unit, $value);
        if (false===$newValue) return $this;
        $this->value += $newValue;
        return $this;
    }

    public function sub($string)
    {
        $value = self::_parse($string, self::$map);
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
            throw new Exception("Undefined unit: {$unit}");
        }
        return self::$map[$unit];
    }

    private static function _foramt($decimals=3, $decPoint='.', $thousandSep=',')
    {
        return number_format($this->value, $decimals, $decPoint, $thousandSep);
    }

    public function beautify($decimals=3, $decPoint='.', $thousandSep=',')
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
        return self::_foramt($decimals, $decPoint, $thousandSep) . $this->unit;
    }

    public function out($decimals=3, $decPoint='.', $thousandSep=',')
    {
        return self::_foramt($decimals, $decPoint, $thousandSep) . $this->unit;
    }

    public function toString()
    {
        @$this->to(self::$native);
        return self::_foramt() . $this->unit;
    }

}

<?php

namespace ChemicalReagent\Conversion;

abstract class Value
{

    protected static $native;
    protected static $map = [];

    protected $value;
    protected $unit;

    private static function _parse($value)
    {
        $units = implode('|', array_keys(static::$map));
        if ($units) {
            $pattern = implode('', ['/(\d+(?:\.\d+)?)\s*(', $units, ')\s*(?:\*|\x|\X)\s*(\d+(?:\.\d+)?)/']);
        }
        if ($pattern && preg_match($pattern, $value, $mathces)) {
            list(,$value, $unit, $count) = $matches;
            return [$value*$count, $unit];
        }
        return [];
    }

    /**
        * @brief 
        *
        * @param $string
        *       5ml
        *       5ml*3
        * @return 
     */
    public function __construct($string)
    {
        $value = self::_parse($string);
        if (!empty($value)) {
            list($value, $unit) = $value;
            $this->value = $value;
            $this->unit = $unit;
        }
    }

    public function add($string)
    {
        $value = self::_parse($string);
        if (empty($value)) return $this;
        list($value, $unit) = $value;
        $newValue = @static::convert($unit, $this->unit, $value);
        if (false===$newValue) return $this;
        $this->value += $newValue;
        return $this;
    }

    public function sub($string)
    {
        $value = self::_parse($string);
        if (empty($value)) return $this;
        list($value, $unit) = $value;
        $newValue = @static::convert($unit, $this->unit, $value);
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
        $this->value = static::convert($this->unit, $unit, $this->value);
        $this->unit = $unit;
        return $this;
    }

    protected static function convert($from, $to, $value)
    {
        return ($value * static::getConversionRate($from)) / static::getConversionRate($to);
    }

    protected static function getConversionRate($unit)
    {
        if (!isset(static::$map[$unit])) {
            throw new \ChemicalReagent\Exception("Undefined unit: {$unit}");
        }
        return static::$map[$unit];
    }

    private static function _foramt($decimals=3, $decPoint='.', $thousandSep=',')
    {
        return number_format($this->value, $decimals, $decPoint, $thousandSep);
    }

    public function beautify()
    {
        $myLen = strlen($this->value);
        $units = array_keys(static::$maps);
        foreach ($units as $unit) {
            if ($unit===$this->unit) continue;
            $newValue =static::convert($this->unit, $unit, $this->value) ;
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
        $this->to(static::$native);
        return self::_foramt($decimals, $decPoint, $thousandSep) . $this->unit;
    }
}

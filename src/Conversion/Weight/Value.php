<?php

namespace ChemicalReagent\Conversion\Weight;

use ChemicalReagent\Conversion\Value as Base;

class Value extends Base
{
    protected static $native = Unit::G;
    protected static $map = [
        Unit::UG=> 1000000,
        Unit::AUG=> 1000000,
        Unit::MG=> 1000,
        Unit::G=> 1,
        Unit::KG=> 0.001
    ];
}

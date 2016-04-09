<?php

namespace ChemicalReagent\Conversion\Volume;

use ChemicalReagent\Conversion\Value as Base;

class Value extends Base
{
    protected static $native = Unit::ML;
    protected static $map = [
        Unit::UL=> 1000,
        Unit::AUL=> 1000,
        Unit::ML=> 1,
        Unit::CL=> 0.1,
        Unit::DL=> 0.01,
        Unit::L=> 0.001
    ];
}


        $obj = new \ChemicalReagent\Conversion('500ml');
        $obj = new \ChemicalReagent\Conversion('500ml * 3');
        $obj = new \ChemicalReagent\Conversion('500ml * 3', [
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
        ]);
        echo (sring)$obj;
        echo $obj->out();
        echo $obj->beautify();

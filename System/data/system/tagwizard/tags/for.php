<?php

$tagDefine = array(
    'tagname' => 'for',
    'description' => trans('Nummerische Schleife'),
    'attributes' => array(
        'from' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '1',
            'label' => trans('Wert Von'),
            'description' => 'Es sind nur Zahlen erlaubt. Bsp: 0 oder 1995',
            'required' => true,
        ),
        'to' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '10',
            'label' => trans('Wert Bis'),
            'description' => trans('Es sind nur Zahlen erlaubt. Bsp: 10 oder 2011'),
            'required' => true,
        ),
        'step' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Schritte'),
            'description' => trans('Es sind nur Zahlen erlaubt. Bsp: 1 oder 3'),
            'required' => false,
        ),
        'name' => array(
            'type' => 'text',
            'size' => 50,
            'default' => 'r',
            'label' => trans('Variablen Bezeichnung'),
            'description' => trans('Zugriffsvariable'),
            'required' => false,
        ),
    ),
    'isSingleTag' => false
);
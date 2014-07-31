<?php

$tagDefine = array(
    'tagname' => 'loop',
    'description' => trans('Verarbeitet den Inhalt eines Arrays.'),
    'attributes' => array(
        'name' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Bezeichnung'),
            'description' => 'Es sind nur Buchstaben und unterzeichen erlaubt.',
            'required' => true,
        ),
        'key' => array(
            'type' => 'text',
            'size' => 50,
            'default' => 'r',
            'label' => trans('Variablen Bezeichnung'),
            'description' => trans('Es sind nur Buchstaben und unterzeichen erlaubt. Bsp: r '),
            'required' => false,
        ),
    ),
    'isSingleTag' => false
);
<?php

$tagDefine = array(
    'tagname' => 'set',
    'description' => trans('Setzt eine Variable bei der Template Verarbeitung. Der cp:set Tag kann an beliebiegen stellen im Template stehen.'),
    'attributes' => array(
        'var' => array(
            'type' => 'text',
            'size' => 50,
            'default' => 'r',
            'label' => trans('Variablen Bezeichnung'),
            'description' => trans('Zugriffsvariable'),
            'required' => true,
        ),
        'value' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Variablen Inhalt'),
            'description' => trans('Es ist kein HTML Code hier erlaubt'),
            'required' => true,
        ),
    ),
    'isSingleTag' => true
);
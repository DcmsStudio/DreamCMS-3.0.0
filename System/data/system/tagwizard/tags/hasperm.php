<?php

$tagDefine = array(
    'tagname' => 'hasperm',
    'description' => trans('Hiermit haben Sie die Möglichkeit zu Prüfen ob ein Benutzer die benötigten Rechte besitzt.'),
    'attributes' => array(
        'perm' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Wert'),
            'description' => trans('Bsp: "controller/action" (ohne Anführungszeichen)'),
            'required' => true,
        ),
    ),
    'isSingleTag' => false,

);
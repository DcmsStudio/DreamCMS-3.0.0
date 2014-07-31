<?php

$tagDefine = array(
    'tagname' => 'seemode',
    'description' => trans('seemode.'),
    'attributes' => array(
        'tpl' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Formular Feld (Textarea) Name Attribut'),
            'description' => trans('Hierbei wird das Textarea diese Bezeichnung erhalten'),
            'required' => false,
        ),
        'modul' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Basis Url'),
            'description' => trans('Es ist kein HTML Code hier erlaubt'),
            'required' => true,
        ),

        'contentid' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Inhalt'),
            'description' => trans('z.Z. kein HTML Code erlaubt! Benutzen die eine Variable. Bsp: {$value}'),
            'required' => true,
        ),
        'edit' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Edit Url'),
            'description' => trans('Es sind sowohl Nummerische als auch Angaben in Pixel/Prozent möglich'),
            'required' => false,
        ),
        'publish' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Edit Url'),
            'description' => trans('Es sind sowohl Nummerische als auch Angaben in Pixel/Prozent möglich'),
            'required' => false,
        ),
        'state' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Online / Offline Status'),
            'description' => trans('Es sind sowohl Nummerische als auch Angaben in Pixel/Prozent möglich'),
            'required' => false,
        ),
        'container' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Übergeordnetes Element'),
            'description' => trans('Es sind sowohl Nummerische als auch Angaben in Pixel/Prozent möglich'),
            'required' => false,
        ),
        'delete' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Delete Url'),
            'description' => trans('Es sind sowohl Nummerische als auch Angaben in Pixel/Prozent möglich'),
            'required' => false,
        ), 
            
    ),
    'isSingleTag' => true,
);
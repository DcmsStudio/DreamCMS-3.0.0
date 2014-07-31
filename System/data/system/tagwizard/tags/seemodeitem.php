<?php

$tagDefine = array(
    'tagname' => 'seemodeitem',
    'description' => trans('seemodeitem.'),
    'attributes' => array(

        'modul' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Modul'),
            'description' => trans('Es ist kein HTML Code hier erlaubt'),
            'required' => true,
        ),

        'contentid' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Inhalts ID'),
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
            'label' => trans('Publish Url'),
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
        'delete' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Delete Url'),
            'description' => trans('Es sind sowohl Nummerische als auch Angaben in Pixel/Prozent möglich'),
            'required' => false,
        ), 
            
    ),
    'isSingleTag' => false,
);
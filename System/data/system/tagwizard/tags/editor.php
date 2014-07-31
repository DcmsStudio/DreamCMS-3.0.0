<?php

$tagDefine = array(
    'tagname' => 'editor',
    'description' => trans('Ermöglichkeit es Ihnen einen WYSIWYG Editor (z.Z. TinyMCE) in ein Template einzufügen.'),
    'attributes' => array(
        'name' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Formular Feld (Textarea) Name Attribut'),
            'description' => trans('Hierbei wird das Textarea diese Bezeichnung erhalten'),
            'required' => true,
        ),
        'baseurl' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Basis Url'),
            'description' => trans('Es ist kein HTML Code hier erlaubt'),
            'required' => true,
        ),

        'value' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Inhalt'),
            'description' => trans('z.Z. kein HTML Code erlaubt! Benutzen die eine Variable. Bsp: {$value}'),
            'required' => false,
        ),
        'width' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Breite des Editors'),
            'description' => trans('Es sind sowohl Nummerische als auch Angaben in Pixel/Prozent möglich'),
            'required' => false,
        ),
        'height' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Höhe des Editors'),
            'description' => trans('Es sind sowohl Nummerische als auch Angaben in Pixel/Prozent möglich'),
            'required' => false,
        ),
    ),
    'isSingleTag' => true,
);
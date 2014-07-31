<?php

$tagDefine = array(
    'tagname' => 'include',
    'description' => trans('Dieses Tag importiert ein Template/Dokument in das aktuelle Template.' ),
    'attributes' => array(
        'template' => array(
            'type' => 'text',
            'size' => 70,
            'default' => '',
            'label' => trans('Template'),
            'description' => '',
            'required' => true,
            'require' => 'template'
        ),
        'path' => array(
            'type' => 'text',
            'size' => 70,
            'default' => '',
            'label' => trans('Pfad zur Datei'),
            'description' => '',
            'required' => true,
            'require' => 'document'
        ),
        'type' => array(
            'type' => 'select',
            'default' => 'template',
            'values' => array(
                'template' => trans('Template'),
                'document' => trans('Dokument')
            ),
            'label' => trans('Typ der zu verwendenden Vorlage'),
        )
    ),
    'isSingleTag' => true
);
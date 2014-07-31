<?php


$tagDefine = array(
    'tagname' => 'extends',
    'description' => trans('Dieses Tag importiert das aktuelle Template in das übergeordnete Template. (Voraussetzung: das übergeordnete Template hat die gleichen <block/> Tags wie das aktuelle Template.) Achtung: Der Tag muss am Anfang eines Templates stehen!' ),
    'attributes' => array(
        'template' => array(
            'type' => 'text',
            'size' => 70,
            'default' => '',

            'label' => trans('Template'),
            'description' => '',
            'required'=> true,
            'require' => 'template'
         ),
         'path' => array(
            'type' => 'text',
            'size' => 70,
            'default' => '',
            'label' => trans('Pfad zur Datei'),
            'description' => '',
            'required'=> true,
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



?>
<?php

$tagDefine = array(
        'tagname'        => 'block',
        'description'    => trans( 'Hiermit haben Sie die Möglichkeit Blöcke zu definieren deren Inhalt an beliebigen Stellen in einem Template erscheinen. (Sollte es sich um einen Singletag (&lt;cp:block /&gt;) handeln.)' ),
        'attributes'     => array(
                'name' => array(
                        'type'        => 'text',
                        'size'        => 50,
                        'default'     => '',
                        'label'       => trans( 'Variablen Bezeichnung' ),
                        'description' => trans( 'Zugriffsvariable' ),
                        'required'    => true,
                )
        ),
        'isSingleTag'    => false,
        'allowSingleTag' => true,
);
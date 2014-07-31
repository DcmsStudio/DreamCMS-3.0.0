<?php

$tagDefine = array(
        'tagname'        => 'do',
        'description'    => trans( 'Hiermit haben Sie die Möglichkeit mit der Api interne Module/Plugins auszuführen.' ),
        'attributes'     => array(
                'modul' => array(
                        'type'        => 'text',
                        'size'        => 50,
                        'default'     => '',
                        'label'       => trans( 'Modul' ),
                        'description' => trans( 'Ruft das Modul auf, welches Sie dem Attribut gegeben haben.' ),
                        'required'    => false,
                )
                , 
                'plugin' => array(
                        'type'        => 'text',
                        'size'        => 50,
                        'default'     => '',
                        'label'       => trans( 'Plugin' ),
                        'description' => trans( 'Ruft das Plugin auf, welches Sie dem Attribut gegeben haben.' ),
                        'required'    => false,
                ),
                'call' => array(
                        'type'        => 'text',
                        'size'        => 50,
                        'default'     => '',
                        'label'       => trans( 'Methode' ),
                        'description' => trans( 'Die Methode die das Modul oder Plugin aufrufen soll. (ACHTUNG: Diese muss existieren und öffentlich sein.)' ),
                        'required'    => true,
                )
        ),
        'isSingleTag'    => true,
        'allowSingleTag' => true,
);
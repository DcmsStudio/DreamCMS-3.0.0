<?php

$tagDefine = array(
        'tagname'     => 'html',
        'description' => trans( 'Dieses Tag erzeugt einen HTML Tag.' ),
        'attributes'  => array(
                'id'    => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => '',
                        'label'       => trans( 'id Attribut' ),
                        'description' => '',
                        'required'    => false,
                ),
                'class' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => '',
                        'label'       => trans( 'class Attribut' ),
                        'description' => '',
                        'required'    => false,
                ),
                'width' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => '',
                        'label'       => trans( 'width Attribut' ),
                        'description' => '',
                        'required'    => false,
                ),
                'forceclose' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => '',
                        'label'       => trans( 'Tag schließen (true/false)' ),
                        'description' => '',
                        'required'    => false,
                ),
                'name' => array(
                        'type'    => 'text',
                        'size'    => 70,
                        'default' => '',
                        'label'       => trans( 'der Name des HTML Tags (Bsp: "em" oder "strong" usw... ohne Anführungszeichen!)' ),
                        'description' => '',
                        'required'    => true,
                ),
        ),
        'isSingleTag' => true
);
?>
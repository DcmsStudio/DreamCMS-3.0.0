<?php

$tagDefine = array(
        'tagname'     => 'footnote',
        'description' => trans( 'Dieses Tag erzeugt Fußnoten.' ),
        'attributes'  => array(
                'template'    => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => '',
                        'label'       => trans( 'template Attribut' ),
                        'description' => '',
                        'required'    => false,
                ),
                'value' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => '',
                        'label'       => trans( 'value Attribut' ),
                        'description' => '',
                        'required'    => false,
                ),
                'var' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => '',
                        'label'       => trans( 'var Attribut' ),
                        'description' => '',
                        'required'    => false,
                ),
                'auto' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => '',
                        'label'       => trans( 'Boolean' ),
                        'description' => '',
                        'required'    => false,
                ),
                'name' => array(
                        'type'    => 'text',
                        'size'    => 70,
                        'default' => '',
                        'label'       => trans( 'Expression' ),
                        'description' => '',
                        'required'    => true,
                ),
        ),
        'isSingleTag' => true
);
?>
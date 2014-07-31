<?php

$tagDefine = array(
        'tagname'     => 'thumb',
        'description' => trans( 'Dieses Tag erzeugt ein Thumbnail.' ),
        'attributes'  => array(
                'src' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => '',
                        'label'       => trans( 'Expression' ),
                        'description' => '',
                        'required'    => true,
                ),
                'width' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => '90',
                        'label'       => trans( 'Beite' ),
                        'description' => '',
                        'required'    => true,
                ),
                
                'height' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => '60',
                        'label'       => trans( 'Höhe' ),
                        'description' => '',
                        'required'    => true,
                ),
                
                'title' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => '',
                        'label'       => trans( 'title Attribut' ),
                        'description' => '',
                        'required'    => false,
                ),
                
                'aspect' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => 'true',
                        'label'       => trans( 'Aspect (true/false)' ),
                        'description' => '',
                        'required'    => false,
                ),
                
                'shrink' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => 'false',
                        'label'       => trans( 'Shrink (true/false)' ),
                        'description' => '',
                        'required'    => false,
                ),
                
                
                'cache' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => '',
                        'label'       => trans( 'Cache' ),
                        'description' => '',
                        'required'    => false,
                ),
                
                
                'chain' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => 'mythumbchain',
                        'label'       => trans( 'Chain (the Image Chain)' ),
                        'description' => '',
                        'required'    => false,
                ),
                
        ),
        'isSingleTag' => true
);
?>
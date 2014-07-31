<?php

$tagDefine = array(
        'tagname'     => 'captcha',
        'description' => trans( 'Dieses ein Captcha.' ),
        'attributes'  => array(
                'audio'    => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => 'true',
                        'label'       => trans( 'Audio Button (true/false)' ),
                        'description' => '',
                        'required'    => false,
                ),
                'reload' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => 'true',
                        'label'       => trans( 'Reload Button (true/false)' ),
                        'description' => '',
                        'required'    => false,
                ),
                'width' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => '170',
                        'label'       => trans( 'Beite' ),
                        'description' => '',
                        'required'    => false,
                ),
                'height' => array(
                        'type'        => 'text',
                        'size'        => 70,
                        'default'     => '45',
                        'label'       => trans( 'Höhe' ),
                        'description' => '',
                        'required'    => false,
                ),
                'name' => array(
                        'type'    => 'text',
                        'size'    => 70,
                        'default' => '',
                        'label'       => trans( 'Expression' ),
                        'description' => '',
                        'required'    => false,
                ),
        ),
        'isSingleTag' => true
);
?>
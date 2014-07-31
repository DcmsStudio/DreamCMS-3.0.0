<?php


$tagDefine = array(
    'tagname' => 'if',
    'description' => trans('Dieses Tag prüft eine Bedingung ob diese zutrifft.' ),
    'attributes' => array(
        'condition' => array(
		    'type' => 'text',
		    'size' => 70,
		    'default' => '',
		    'label' => trans('Expression'),
		    'description' => '',
		    'required'=> true,
	    ),
     ),
    'isSingleTag' => false

);
?>
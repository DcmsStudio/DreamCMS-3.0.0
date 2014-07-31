<?php
$tagDefine = array(
    'tagname' => 'elseif',
    'description' => trans('Dieses Tag leitet die Alternative ein, wenn die Bedingung eines if-Tags (z. B. <cp:if>, <cp:elseif>) nicht zutrifft.' ),
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
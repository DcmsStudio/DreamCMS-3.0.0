<?php


$tagDefine = array(
    'tagname' => 'tree',
    'description' => trans('Erzeugt ein Listen Tree. Dieser Tag ist Ideal geeignet um ein Menü/Liste Strukturiert darzustellen. Der Inhalt dieses Tags dient dabei als Vorlage.'),
    'attributes' => array(
        'name' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Bezeichnung'),
            'description' => 'Es sind nur Buchstaben und unterzeichen erlaubt.',
            'required' => true,
        ),
        'primarykey' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Primär Schlüssel'),
            'description' => 'Es sind nur Buchstaben und unterzeichen erlaubt.',
            'required' => true,
        ),
        'parentkey' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Schlüssel welcher sich auf den Primär Schlüssel bezieht'),
            'description' => 'Es sind nur Buchstaben und unterzeichen erlaubt.',
            'required' => true,
        ),
        'key' => array(
            'type' => 'text',
            'size' => 50,
            'default' => 'r',
            'label' => trans('Variablen Bezeichnung'),
            'description' => trans('Es sind nur Buchstaben und unterzeichen erlaubt. Bsp: r '),
            'required' => false,
        ),
        'mode' => array(
            'type' => 'text',
            'size' => 50,
            'default' => 'ul',
            'label' => trans('Modus'),
            'description' => trans('Es sind nur Buchstaben erlaubt. Sollten Sie keine Angaben machen wird automatisch ein UL-Tag erzeugt. Bsp: ul, ol, div usw. '),
            'required' => false,
        ),
        'class' => array(
            'type' => 'text',
            'size' => 50,
            'default' => 'r',
            'label' => trans('class Attribut für den Modus'),
            'description' => trans('Optional'),
            'required' => false,
        ),
        'id' => array(
            'type' => 'text',
            'size' => 50,
            'default' => 'r',
            'label' => trans('id Attribut für den Modus'),
            'description' => trans('Optional'),
            'required' => false,
        ),
        'style' => array(
            'type' => 'text',
            'size' => 50,
            'default' => 'r',
            'label' => trans('style Attribut für den Modus'),
            'description' => trans('Optional'),
            'required' => false,
        ),
    ),
    'isSingleTag' => false
);
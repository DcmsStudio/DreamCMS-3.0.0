<?php


$tagDefine = array(
    'tagname' => 'app',
    'description' => trans('Ermöglicht den Zugriff auf eine Anwendung mit derer Funktionen und Variablen, die mit dem Anwendungs-Generator erzeugt wurde. (nur während der Verarbeitung)'),
    'attributes' => array(
        'show' => array(
            'type' => 'text',
            'size' => 50,
            'default' => 'r',
            'label' => trans('Anwendungs Function'),
            'description' => trans('Function die innerhalb einer Anwendung aufgerufen werden soll.'),
            'required' => false,
        ),
        'get' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Variablen'),
            'description' => trans('Anwendungs Variable die wärend der Verarbeitung abgerufen wird.'),
            'required' => false,
        ),
        'style' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Variablen Inhalt'),
            'description' => trans('Es ist kein HTML Code hier erlaubt'),
            'required' => false,
        ),
        'check' => array(
            'type' => 'text',
            'size' => 50,
            'default' => '',
            'label' => trans('Variablen Inhalt'),
            'description' => trans('Es ist kein HTML Code hier erlaubt'),
            'required' => false,
        ),
        '__custom' => array(
            'type' => 'textarea',
            'cols' => 50,
            'rows' => 5,
            'default' => '',
            'label' => trans('Zusätzliche Attribute'),
            'description' => trans('Es ist kein HTML Code hier erlaubt. Attribute schreiben Sie bitte so z.B.: attribut="wert". Bitte nur ein Attribut pro Zeile!'),
            'required' => false,
        ),
    ),
    'isSingleTag' => true
);
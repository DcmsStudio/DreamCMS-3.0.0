<?php
self::$imageChains = array();
self::$imageChains['default'] = array();
self::$imageChains['thumbnail'] = array(
	array('resize', array('width' => 90, 'height' => 64, 'keep_aspect' => true, 'shrink_only' => false)),
);

self::$imageChains['maximum'] = array(
	array('resize', array('width' => 280, 'height' => 200, 'keep_aspect' => true, 'shrink_only' => false)),
);

self::$imageChains['gallerie_thumbnail'] = array(
	array('resize', array('width' => 120, 'height' => 120, 'keep_aspect' => true, 'shrink_only' => false)),
);

?>
<?php
    $mongo = new Mongo();
    $db = $mongo->selectDB('catchthecat');
    $collection = $db->highscores;
    $collection->ensureIndex(array('score' => 1, 'date' => 1));
?>

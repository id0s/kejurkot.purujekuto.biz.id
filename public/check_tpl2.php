<?php
$db = \Config\Database::connect();
$row = $db->table('piagam_template')->where('id', 2)->get()->getRowArray();
header('Content-Type: application/json');
echo json_encode($row, JSON_PRETTY_PRINT);

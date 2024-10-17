<?php
$rides = [
    ['route' => 'Bakakeng to City', 'time' => '8:00 AM', 'seats' => 12],
    ['route' => 'City to Bakakeng', 'time' => '10:00 AM', 'seats' => 8],
    ['route' => 'Bakakeng to City', 'time' => '2:00 PM', 'seats' => 15],
];

header('Content-Type: application/json');
echo json_encode($rides);
?>

<?php
$data = fetchData($apiUrl);
$sensors = $data['sensors'] ?? [];
list($lat, $lon) = getCoordinates($data);
$country = getCountry($lat, $lon);
?>

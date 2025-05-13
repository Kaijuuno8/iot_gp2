<?php
function fetchData($url) {
    $opts = ["http" => ["header" => "User-Agent: OpenSenseMap-Dashboard/1.0\r\n"]];
    $context = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);
    return $response ? json_decode($response, true) : null;
}

function getCoordinates($data) {
    $lat = $data['currentLocation']['latitude'] ?? null;
    $lon = $data['currentLocation']['longitude'] ?? null;
    if (!$lat || !$lon) {
        if (isset($data['loc'][0]['geometry']['coordinates'])) {
            $lon = $data['loc'][0]['geometry']['coordinates'][0];
            $lat = $data['loc'][0]['geometry']['coordinates'][1];
        }
    }
    return [$lat, $lon];
}

function getCountry($lat, $lon) {
    if ($lat && $lon) {
        $geoUrl = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=$lat&lon=$lon";
        $geoData = fetchData($geoUrl);
        return $geoData['address']['country'] ?? 'Unknown';
    }
    return 'Unknown';
}
?>

<?php
$boxId = "63ac947f1aaa3a001b8a34bd";
$apiUrl = "https://api.opensensemap.org/boxes/$boxId";

$response = file_get_contents($apiUrl);
if (!$response) {
    die("Failed to fetch data from OpenSenseMap.");
}
$data = json_decode($response, true);
$sensors = $data['sensors'] ?? [];

// Get coordinates
$lat = $data['currentLocation']['latitude'] ?? null;
$lon = $data['currentLocation']['longitude'] ?? null;
if (!$lat || !$lon && isset($data['loc'][0]['geometry']['coordinates'])) {
    $lon = $data['loc'][0]['geometry']['coordinates'][0];
    $lat = $data['loc'][0]['geometry']['coordinates'][1];
}

// Country via OpenStreetMap
$country = 'Unknown';
if ($lat && $lon) {
    $geoUrl = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=$lat&lon=$lon";
    $opts = ["http" => ["header" => "User-Agent: OpenSenseMap-Dashboard/1.0\r\n"]];
    $context = stream_context_create($opts);
    $geoResponse = file_get_contents($geoUrl, false, $context);
    if ($geoResponse) {
        $geoData = json_decode($geoResponse, true);
        $country = $geoData['address']['country'] ?? 'Unknown';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sensor Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body class="bg-gray-100 text-gray-800">
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-2 text-center">OpenSenseMap Sensor Dashboard</h1>
    <p class="text-center text-gray-600 mb-2">
        Country: <span class="font-semibold"><?= htmlspecialchars($country) ?></span>
    </p>
    <p class="text-center text-sm text-gray-400 mb-6">
        Refreshing in <span id="countdown">60</span> seconds...
    </p>

    <?php if ($lat && $lon): ?>
    <div id="map" class="w-full h-64 rounded-xl mb-6 shadow"></div>
    <script>
        const map = L.map('map').setView([<?= $lat ?>, <?= $lon ?>], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        L.marker([<?= $lat ?>, <?= $lon ?>]).addTo(map)
            .bindPopup("Sensor Location")
            .openPopup();
    </script>
    <?php endif; ?>

    <div id="sensor-cards" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <?php foreach ($sensors as $sensor): ?>
            <div class="sensor-card bg-white rounded-xl shadow p-4" data-id="<?= $sensor['_id'] ?>">
                <h2 class="text-xl font-semibold"><?= htmlspecialchars($sensor['title']) ?></h2>
                <p class="text-sm text-gray-500">Type: <?= htmlspecialchars($sensor['sensorType']) ?> | Unit: <?= htmlspecialchars($sensor['unit']) ?></p>
                <p class="sensor-value text-2xl font-bold mt-2"><?= $sensor['lastMeasurement']['value'] ?> <?= $sensor['unit'] ?></p>
                <p class="sensor-time text-xs text-gray-400">Last updated: <?= date('Y-m-d H:i:s', strtotime($sensor['lastMeasurement']['createdAt'])) ?></p>
                <canvas id="chart-<?= $sensor['_id'] ?>" class="mt-4 w-full h-48"></canvas>
            </div>
        <?php endforeach; ?>
    </div>

    <footer class="text-center text-gray-500 text-sm mt-10">
        &copy; <?= date("Y") ?> Sensor Dashboard | OpenSenseMap + OpenStreetMap
    </footer>
</div>

<script>
    const boxId = "<?= $boxId ?>";
    const charts = {};
    let countdown = 60;

    // Countdown Timer
    setInterval(() => {
        countdown--;
        document.getElementById("countdown").textContent = countdown;
        if (countdown === 0) {
            fetchData();
            countdown = 60;
        }
    }, 1000);

    // Initial Chart Load
    document.addEventListener('DOMContentLoaded', () => {
        fetchChartData();
    });

    async function fetchChartData() {
        const res = await fetch(`https://api.opensensemap.org/boxes/${boxId}`);
        const box = await res.json();

        for (const sensor of box.sensors) {
            const measurements = await fetch(`https://api.opensensemap.org/boxes/${boxId}/data/${sensor._id}?format=json&from=${getPastTime(1)}`);
            const data = await measurements.json();

            const labels = data.map(m => new Date(m.createdAt).toLocaleTimeString());
            const values = data.map(m => parseFloat(m.value));

            const ctx = document.getElementById(`chart-${sensor._id}`).getContext('2d');
            charts[sensor._id] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: sensor.title,
                        data: values,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        tension: 0.3
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: false }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    }

    async function fetchData() {
        const res = await fetch(`https://api.opensensemap.org/boxes/${boxId}`);
        const box = await res.json();

        for (const sensor of box.sensors) {
            const card = document.querySelector(`.sensor-card[data-id="${sensor._id}"]`);
            if (!card) continue;

            card.querySelector('.sensor-value').textContent = `${sensor.lastMeasurement.value} ${sensor.unit}`;
            card.querySelector('.sensor-time').textContent = "Last updated: " + new Date(sensor.lastMeasurement.createdAt).toLocaleString();

            const measurements = await fetch(`https://api.opensensemap.org/boxes/${boxId}/data/${sensor._id}?format=json&from=${getPastTime(1)}`);
            const data = await measurements.json();

            const labels = data.map(m => new Date(m.createdAt).toLocaleTimeString());
            const values = data.map(m => parseFloat(m.value));

            if (charts[sensor._id]) {
                charts[sensor._id].data.labels = labels;
                charts[sensor._id].data.datasets[0].data = values;
                charts[sensor._id].update();
            }
        }
    }

    function getPastTime(hoursAgo = 1) {
        const now = new Date();
        now.setHours(now.getHours() - hoursAgo);
        return now.toISOString();
    }
</script>
</body>
</html>

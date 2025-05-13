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

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

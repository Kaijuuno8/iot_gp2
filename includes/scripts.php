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

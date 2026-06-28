<?php
session_start();

require_once 'config.php';
require_once 'includes/dashboard_queries.php';

ob_start();
?>

<link rel="stylesheet" href="assets/css/dashboard.css">

<?php include 'includes/dashboard_stats.php'; ?>

<?php include 'includes/dashboard_charts.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const labels = <?= json_encode($days, JSON_UNESCAPED_UNICODE); ?>;
const values = <?= json_encode($vals, JSON_UNESCAPED_UNICODE); ?>;

const ctx = document.getElementById('revChart');
const chartEmpty = document.getElementById('chartEmpty');

const hasData = Array.isArray(values) && values.some(v => Number(v) > 0);

if (chartEmpty && !hasData) {
    chartEmpty.style.display = 'flex';
}

if (ctx) {

    new Chart(ctx, {

        type: 'line',

        data: {
            labels: labels,
            datasets: [{
                label: 'Xhiro (Σ Total)',
                data: values,
                borderWidth: 3,
                tension: 0.32,
                fill: false,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },

        options: {

            responsive: true,
            maintainAspectRatio: false,

            interaction: {
                mode: 'index',
                intersect: false
            },

            layout: {
                padding: {
                    top: 8,
                    right: 8,
                    bottom: 0,
                    left: 8
                }
            },

            plugins: {

                legend: {
                    display: true
                },

                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return ' Xhiro: ' +
                                Number(context.parsed.y || 0).toLocaleString(
                                    'en-US',
                                    {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }
                                );
                        }
                    }
                }

            },

            scales: {

                x: {
                    grid: {
                        display: false
                    }
                },

                y: {
                    beginAtZero: true
                }

            }

        }

    });

}

setTimeout(function () {

    if (document.visibilityState === 'visible') {
        location.reload();
    }

}, 60000);

</script>

<?php

$page_content = ob_get_clean();

include 'layout.php';

?>
<?php
use App\Helpers\ViewHelper;

// loadin the header, defaults to 'Reports' if title is missing
ViewHelper::loadAdminHeader($data['page_title'] ?? 'Reports');
// grab the stats array from the controller hope its not empty
$stats = $data['stats'];
?>

<div class="container-fluid">

    <div class="row">

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?= hs(trans('nav.reports')) ?: 'Reports' ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="<?= hs(APP_ADMIN_URL . '/reports/pdf/products') ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-earmark-pdf"></i> <?= hs(trans('admin.export_products_pdf')) ?: 'Export PDF' ?>
                    </a>
                </div>
            </div>

            <!-- stats Cards -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4 mb-4">
                <div class="col">
                    <div class="card h-100 border-primary border-start-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-muted text-uppercase fs-6">Total Orders</h5>
                            <p class="display-6 fw-bold my-2"><?= number_format((float)$stats['total_orders']) ?></p>
                            <small class="text-success"><i class="bi bi-arrow-up"></i> All time</small>
                        </div>
                    </div>
                </div>


                <div class="col">
                    <div class="card h-100 border-success border-start-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-muted text-uppercase fs-6">Total Revenue</h5>
                            <p class="display-6 fw-bold my-2">$<?= number_format((float)$stats['total_revenue'], 2) ?></p>
                            <small class="text-success"><i class="bi bi-currency-dollar"></i> Gross Sales</small>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 border-info border-start-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-muted text-uppercase fs-6">Customers</h5>
                            <p class="display-6 fw-bold my-2"><?= number_format((float)$stats['total_customers']) ?></p>
                            <small class="text-muted"><i class="bi bi-people"></i> Active Users</small>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 border-warning border-start-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-muted text-uppercase fs-6">Est. Profit</h5>
                            <p class="display-6 fw-bold my-2">$<?= number_format((float)$stats['estimated_profit'], 2) ?></p>
                            <small class="text-warning"><i class="bi bi-pie-chart"></i> 50% Margin</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- chart -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Revenue Overview</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" width="100%" height="30"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const ctx = document.getElementById('revenueChart');

        // passing php vars into js. kinda hacky but it works
        // json_encode makes sure numbers are numbers not strings
        const totalRevenue = <?= json_encode((float)$stats['total_revenue']) ?>;
        const estProfit = <?= json_encode((float)$stats['estimated_profit']) ?>;

        // Simple Chart showing Revenue vs Profit breakdown
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Total Revenue', 'Estimated Profit'],
                datasets: [{
                    label: 'Financials (USD)',
                    data: [totalRevenue, estProfit],
                    backgroundColor: [
                        'rgba(25, 135, 84, 0.6)',
                        'rgba(255, 193, 7, 0.6)'  
                    ],
                    borderColor: [
                        'rgba(25, 135, 84, 1)',
                        'rgba(255, 193, 7, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Revenue vs Estimated Profit'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    });
</script>

<?php
ViewHelper::loadAdminFooter();
?>

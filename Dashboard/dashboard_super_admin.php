<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?php echo htmlspecialchars($companyName ?: 'Dashboard'); ?> - Super Admin Dashboard</title>

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dark_mode_style.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .tile-number {
            font-size: 22px;
            font-weight: 700;
        }

        .tile-label {
            font-size: 13px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="content container-fluid">

        <!-- Monetary Totals -->
        <div class="row g-3 mb-3" id="tiles-orders"></div>

        <!-- Counts -->
        <div class="row g-3 mb-3" id="tiles-counts"></div>

        <!-- Daily Orders From Last Month -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-center">Daily Orders (Last 30 Days)</h5>
                        <canvas id="dailyOrdersChart" height="120"></canvas>
                        <div id="dailyOrdersNoData" class="text-center py-5 my-xl-3 text-muted" style="display:none;"><strong>No Results</strong></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Daily Orders From Last Month -->

        <div class="row mb-4">
            <!-- Pie Chart -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-center">Top 10 Moving Packages</h5>
                        <canvas id="fastPackagePie"></canvas>
                        <div id="fastPackageNoData" class="text-center py-5 my-xl-3 text-muted" style="display:none;"><strong>No Results</strong></div>
                    </div>
                </div>
            </div>
            <!-- Pie Chart -->

            <!-- Customer Addons Chart -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-center">Top 10 Moving Addons</h5>
                        <canvas id="customerAddonsChart" height="100"></canvas>
                        <div id="customerAddonsNoData" class="text-center py-5 my-xl-3 text-muted" style="display:none;">
                            <strong>No Results</strong>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Customer Addons Chart -->
        </div>

        <!-- Top 10 Customers Table -->
        <div class="row mb-4"> 
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-center">Top 10 Customers by Ordering</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-center" id="topCustomersTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Customer Name</th>
                                        <th>Address</th>
                                        <th>Contact No</th>
                                        <th>Email</th>
                                        <th class="text-center">No. of Orders</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tr id="topCustomersNoData" style="display:none;">
                                    <td colspan="5" class="text-center py-5 my-xl-3 text-muted">
                                        <strong>No Results</strong>
                                    </td>
                                </tr>
                                    <!-- Rows will be injected -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Top 10 Customers Table Chart -->
    </div>

    <script src="assets/js/jquery-3.2.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>

    <script>
        function formatMoney(v) {
            return Number(v).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // --- Make Tile HTML with card ---
        function makeTileHtml(label, value) {
            return `<div class="col-sm-6 col-md-2 mb-3">
        <div class="card">
            <div class="card-body text-center p-3">
                <div class="tile-number" data-target="${value}">0</div>
                <div class="tile-label">${label}</div>
            </div>
        </div>
    </div>`;
        }

        // --- Animate numbers ---
        function animateNumbers(containerSelector, duration = 800, isCurrency = false) {
            const tiles = document.querySelectorAll(containerSelector + ' .tile-number');
            tiles.forEach(tile => {
                const target = parseFloat(tile.getAttribute('data-target')) || 0;
                let count = 0;
                const steps = 100;
                const increment = target / steps;
                const stepTime = duration / steps;

                const interval = setInterval(() => {
                    count += increment;
                    if (count >= target) {
                        tile.innerText = isCurrency ?
                            Number(target).toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) :
                            Math.round(target).toLocaleString();
                        clearInterval(interval);
                    } else {
                        tile.innerText = isCurrency ?
                            Number(count).toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) :
                            Math.round(count).toLocaleString();
                    }
                }, stepTime);
            });
        }

        // --- Render Tiles ---
        function renderTiles(pageData) {
            // Counts Tiles
            let countsHtml = '';
            const countFields = [
                ['Users', pageData.Count_Users || 0],
                ['User Roles', pageData.Count_Roles || 0],
                ['Registered Customers', pageData.Count_Customers || 0],
                ['Packages', pageData.Count_Packages || 0],
                ['Addons', pageData.Count_Addon || 0],
                ['Reviews', pageData.Count_Addon || 0]
            ];
            countFields.forEach(([label, val]) => countsHtml += makeTileHtml(label, val));
            document.getElementById('tiles-counts').innerHTML = countsHtml;
            animateNumbers('#tiles-counts', 800, false);

            // Monetary Tiles
            const moneyHtml = `
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="tile-number" data-target="${pageData.Count_Pending_Orders || 0}">0</div>
                            <div class="tile-label">Pending Orders</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="tile-number" data-target="${pageData.Count_Approved_Orders || 0}">0</div>
                            <div class="tile-label">Approved Orders</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="tile-number" data-target="${pageData.Count_Completed_Orders || 0}">0</div>
                            <div class="tile-label">Completed Orders</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="tile-number" data-target="${pageData.Count_Canceled_Rejected_Orders || 0}">0</div>
                            <div class="tile-label">Cancel & Rejected Orders</div>
                        </div>
                    </div>
                </div>
                `;
            document.getElementById('tiles-orders').innerHTML = moneyHtml;
            animateNumbers('#tiles-orders', 800, false);
        }

        // --- Render Fast Moving Products Pie & Table ---
        function renderFastPackagesPie(data) {

            const canvas = document.getElementById("fastPackagePie");
            const noDataText = document.getElementById("fastPackageNoData");

            // ----------------------------
            // CASE 1: NO DATA
            // ----------------------------
            if (!data.fastPackages || data.fastPackages.length === 0) {

                // Hide chart, show "No Results"
                canvas.style.display = "none";
                noDataText.style.display = "block";

                return;
            }

            // ----------------------------
            // CASE 2: DATA EXISTS
            // ----------------------------

            // Show chart, hide "No Results"
            canvas.style.display = "block";
            noDataText.style.display = "none";

            // Render Pie Chart
            const labels = data.fastPackages.map(p => p.package_name);
            const ordersCount = data.fastPackages.map(p => p.orders_count);

            const colors = ['#b19316', '#000000', '#26af48', '#009efb', '#f39c12',
                '#8207DB', '#53EAFD', '#FFA2A2', '#162456', '#31C950'
            ];

            new Chart(canvas, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Qty Sold",
                        data: ordersCount,
                        backgroundColor: labels.map((_, i) => colors[i % colors.length])
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const index = context.dataIndex;
                                    const p = data.fastPackages[index];
                                    return [
                                        `Code: ${p.package_id}`,
                                        `Package: ${p.package_name}`,
                                        `Order Count: ${p.orders_count}`,
                                        `Price: $${p.price}`
                                    ];
                                }
                            }
                        },
                        legend: {
                            position: 'right',
                            labels: {
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        }


        // --- Render Daily Sales Bar Chart ---
        function renderdailyOrdersChart(data) {

            const chartCanvas = document.getElementById("dailyOrdersChart");
            const noDataText = document.getElementById("dailyOrdersNoData");

            // If no data → show "No Results", hide canvas, stop rendering
            if (!data.dailyOrders || data.dailyOrders.length === 0) {
                chartCanvas.style.display = "none";
                noDataText.style.display = "block";
                return;
            }

            // If data exists → show chart, hide "No Results"
            chartCanvas.style.display = "block";
            noDataText.style.display = "none";

            const ctx = chartCanvas;

            const labels = data.dailyOrders.map(s => s.date);
            const sales = data.dailyOrders.map(s => s.total_orders);

            // Two color alternating pattern
            const colors = sales.map((_, i) => i % 2 === 0 ? "#b19316" : "#000000");

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: "No of Daily Orders",
                        data: sales,
                        backgroundColor: colors
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: { display: true, text: 'Order Date' },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            title: { display: true, text: 'Order Count' },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    // Format Y-axis numbers without decimals, with comma separator
                                    return Number(value).toLocaleString(undefined, { maximumFractionDigits: 0 });
                                }
                            },
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return "Order Count: " + Number(context.raw).toLocaleString(undefined, {
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    });
                                }
                            }
                        }
                    }
                }
            });
        }

        // --- Render Top Customers Table ---
        function renderTopCustomers(data) {
            const tbody = document.querySelector("#topCustomersTable tbody");
            const noDataRow = document.getElementById("topCustomersNoData");

            // No results case
            if (!data.topCustomers || data.topCustomers.length === 0) {
                tbody.innerHTML = ""; 
                tbody.appendChild(noDataRow);
                noDataRow.style.display = "table-row";
                return;
            }

            // Results available → hide no-data row
            noDataRow.style.display = "none";

            // Clear existing rows
            tbody.innerHTML = "";

            // Append dynamic rows
            data.topCustomers.forEach((c, index) => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${c.customer_name}</td>
                    <td>${c.address}</td>
                    <td>${c.contact_no}</td>
                    <td>${c.email}</td>
                    <td class="text-center">${Number(c.order_count).toLocaleString()}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        // --- Render Customer Addons Bubble Chart (1 bubble per Addon, integer Y-axis) ---
function renderCustomerAddonsChart(data) {
    const canvas = document.getElementById("customerAddonsChart");
    const noDataText = document.getElementById("customerAddonsNoData");

    if (!data.customerAddons || data.customerAddons.length === 0) {
        canvas.style.display = "none";
        noDataText.style.display = "block";
        return;
    }

    canvas.style.display = "block";
    noDataText.style.display = "none";

    // Unique addons
    const addons = [...new Set(data.customerAddons.map(c => c.addon_name))];

    // Colors per addon
    const colors = ['#b19316', '#000000', '#26af48', '#009efb', '#f39c12',
                    '#8207DB', '#53EAFD', '#FFA2A2', '#162456', '#31C950'];

    // Create dataset: 1 bubble per addon
    const datasets = addons.map((addon, i) => {
        // Sum orders for this addon across all customers
        const totalOrders = data.customerAddons
            .filter(c => c.addon_name === addon)
            .reduce((sum, c) => sum + c.orders_count, 0);

        // Get price for tooltip
        const price = data.customerAddons.find(c => c.addon_name === addon)?.price || 0;

        return {
            label: addon,
            data: [{
                x: addon,
                y: totalOrders,
                r: Math.max(totalOrders * 5, 5), // bubble size proportional to total orders
                price: price
            }],
            backgroundColor: hexToRgba(colors[i % colors.length], 0.65),
            borderColor: hexToRgba(colors[i % colors.length], 1),
            borderWidth: 1.5
        };
    });

    new Chart(canvas, {
        type: 'bubble',
        data: {
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const c = context.raw;
                            return [
                                `Addon: ${c.x}`,
                                `Total Orders: ${c.y.toLocaleString()}`, // integer format with commas
                                `Price: $${c.price}`
                            ];
                        }
                    }
                }
            },
            scales: {
                x: {
                    type: 'category',
                    labels: addons,
                    title: { display: true, text: 'Addon Name' },
                    ticks: { font: { size: 12 } }
                },
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Total Order Count' },
                    ticks: {
                        font: { size: 12 },
                        callback: function(value) {
                            // Format Y-axis numbers without decimals, with comma separator
                            return Number(value).toLocaleString(undefined, { maximumFractionDigits: 0 });
                        }
                    }
                }
            }
        }
    });
}


        // Convert HEX to RGBA for smooth opacity
        function hexToRgba(hex, opacity) {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return `rgba(${r},${g},${b},${opacity})`;
        }

        // --- Fetch Dashboard Data ---
        function fetchDashboard() {
            $.ajax({
                url: '../../API/Admin/getDashboardSuperAdminData.php',
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    if (!res || res.success !== 'true') {
                        alert('Failed to fetch dashboard data');
                        return;
                    }
                    renderTiles(res.pageData || {});
                    renderFastPackagesPie(res);
                    renderdailyOrdersChart(res);
                    renderCustomerAddonsChart(res);
                    renderTopCustomers(res);
                }
            });
        }

        $(document).ready(fetchDashboard);
    </script>
</body>

</html>
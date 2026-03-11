<?php include("config.php"); ?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | <?= $org_short_name ?></title>
    <link rel="stylesheet" href="<?= $path_prefix ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $path_prefix ?>assets/css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<div class="d-flex flex-grow-1">
    <div class="bg-dark text-white p-3 d-print-none" style="width: 250px; background-color: #001d4a !important; min-height: 100vh; position: sticky; top: 0;">
        <h5 class="text-center mb-4 border-bottom pb-3">VMS ADMIN</h5>
        <ul class="nav flex-column gap-1">

            <li class="nav-item">
                <a href="dashboard_availability.php" class="nav-link text-white"><i class="bi bi-envelope-paper me-2"></i> Dashboard</a>
            </li>


            <li class="nav-item">
                <a href="enquiry_list.php" class="nav-link text-white"><i class="bi bi-envelope-paper me-2"></i> Enquiries</a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#masterMenu" role="button" aria-expanded="false">
                    <span><i class="bi bi-folder2-open me-2"></i> Masters</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse ps-3" id="masterMenu">
                    <ul class="nav flex-column">
                        <li><a href="venue_list.php" class="nav-link text-light small">Venue Master</a></li>
                        <li><a href="customer_list.php" class="nav-link text-light small">Customer Master</a></li>
                        <li><a href="vendor_list.php" class="nav-link text-light small">Vendor Master</a></li>
                        <li><a href="equipment_list.php" class="nav-link text-light small">Equipment Master</a></li>
                        <li><a href="venue_rates.php" class="nav-link text-light small">Venue Rate Master</a></li>
                        <li><a href="cancellation_policy.php" class="nav-link text-light small">Cancellation Policy</a></li>
                        <li><a href="blocked_dates.php" class="nav-link text-light small">Reserved Dates</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#tranMenu" role="button" aria-expanded="false">
                    <span><i class="bi bi-arrow-left-right me-2"></i> Transactions</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse ps-3" id="tranMenu">
                    <ul class="nav flex-column">
                        <li><a href="booking_list.php" class="nav-link text-light small">New Booking</a></li>
                        <li><a href="booking_list.php?tab=pending_utilization" class="nav-link text-light small">Utilization</a></li>
                        <li><a href="bill_list.php" class="nav-link text-light small">Final Billing</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#reportMenu" role="button" aria-expanded="false">
                    <span><i class="bi bi-graph-up-arrow me-2"></i> Reports</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse ps-3" id="reportMenu">
                    <ul class="nav flex-column">
                        <li><a href="report_customer_master.php" class="nav-link text-light small">Customer Master</a></li>
                        <li><a href="report_vendor_master.php" class="nav-link text-light small">Vendor Master</a></li>
                        <li><a href="report_equipment.php" class="nav-link text-light small">Equipment Master</a></li>
                        <li><a href="report_venue_rates.php" class="nav-link text-light small">Venue Rates</a></li>
                        <li><a href="report_cancellation.php" class="nav-link text-light small">Cancellation Policy</a></li>
                        <hr class="my-1 border-secondary">
                        <li><a href="report_booking_list.php" class="nav-link text-light small">Booking List</a></li>
                        <li><a href="report_utilization.php" class="nav-link text-light small">Utilization Report</a></li>
                        <li><a href="billing_list_report.php" class="nav-link text-light small">Invoice List</a></li>
                        <li><a href="report_billing_columnar.php" class="nav-link text-light small">Sales Register</a></li>
                        <li><a href="monthly_tax.php" class="nav-link text-light small">Monthly Tax Report</a></li>
                        <hr class="my-1 border-secondary">
                        <li><a href="report_duty_chart.php" class="nav-link text-light small">Daily Duty Chart</a></li>
                        <li><a href="report_vendor_royalty.php" class="nav-link text-light small">Vendor Royalty</a></li>
                    </ul>
                </div>
            </li>

            <hr class="border-light mt-4">
            <li class="nav-item">
                <a href="logout.php" class="nav-link text-warning"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
            </li>
        </ul>
    </div>

    <div class="flex-grow-1 d-flex flex-column">
        <header class="bg-white border-bottom p-3 d-flex justify-content-between align-items-center d-print-none">
            <span class="text-muted">Welcome, <strong>Administrator</strong></span>
            <div>
                 <span class="badge bg-primary me-2"><?= date('D, d-M-Y') ?></span>
                 <button onclick="window.location.reload()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
        </header>
        
        <main class="p-4 flex-grow-1">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleTheme() {
        const html = document.documentElement;
        const current = html.getAttribute('data-bs-theme');
        html.setAttribute('data-bs-theme', current === 'dark' ? 'light' : 'dark');
    }
</script>
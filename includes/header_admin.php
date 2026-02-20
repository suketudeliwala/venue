<?php include("config.php"); ?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel | <?= $org_short_name ?></title>
    <link rel="stylesheet" href="<?= $path_prefix ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $path_prefix ?>assets/css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<div class="d-flex flex-grow-1">
    <div class="bg-dark text-white p-3 d-print-none" style="width: 250px; background-color: #001d4a !important; min-height: 100%;">
        <h5 class="text-center mb-4 border-bottom pb-3">VMS ADMIN</h5>
        <ul class="nav flex-column gap-2">
            <li class="nav-item">
                <a href="enquiry_list.php" class="nav-link text-white"><i class="bi bi-envelope-paper me-2"></i> Enquiries</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#masterMenu">
                    <span><i class="bi bi-folder2-open me-2"></i> Masters</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse ps-3" id="masterMenu">
                    <a href="venue_list.php" class="nav-link text-light small">Venue Master</a>
                    <a href="customer_list.php" class="nav-link text-light small">Customer Master</a>
                    <a href="vendor_list.php" class="nav-link text-light small">Vendor Master</a>
                    <a href="equipment_list.php" class="nav-link text-light small">Equipment Master</a>
                    <a href="venue_rates.php" class="nav-link text-light small">Venue Rate Master</a>
                    <a href="blocked_dates.php" class="nav-link text-light small">Reserved Dates Master</a>
                </div>
            </li>
            <li class="nav-item">
                <a href="booking_new.php" class="nav-link text-white"><i class="bi bi-journal-check me-2"></i> New Booking</a>
            </li>
            <li class="nav-item">
                <a href="reports_mis.php" class="nav-link text-white"><i class="bi bi-graph-up me-2"></i> MIS Reports</a>
            </li>
            <hr class="border-light">
            <li class="nav-item">
                <a href="logout.php" class="nav-link text-warning"><i class="bi bi-box-arrow-right me-2"></i> Admin Logout</a>
            </li>
        </ul>
    </div>

    <div class="flex-grow-1 d-flex flex-column">
        <header class="bg-white border-bottom p-3 d-flex justify-content-between align-items-center d-print-none">
            <span class="text-muted">Welcome, <strong>Administrator</strong></span>
            <button onclick="toggleTheme()" class="btn btn-outline-secondary btn-sm">Toggle Mode</button>
        </header>
        
        <main class="p-4 flex-grow-1">

        
<!-- ### Key Changes Explained:
* **Sticky Footer Classes**: Both headers now initiate `d-flex flex-column min-vh-100` on the body.
* **Flex-Grow**: The `<main>` tag is assigned `flex-grow-1`, which tells it to take up all available vertical space, effectively pushing the footer to the bottom.
* **Navigation Correction**: I updated the links in the sidebar to match the file names we've been using (e.g., `enquiry_list.php`, `customer_list.php`).
* **Icons and Styles**: Ensured Bootstrap Icons are linked in the head of both files so social icons and sidebar icons appear correctly.


**Once you replace these files, does the footer sit properly at the bottom?** If so, would you like me to help you verify the **Venue Master** list page next? -->
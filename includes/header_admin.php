<?php include("config.php"); ?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel | <?= $org_short_name ?></title>
    <link rel="stylesheet" href="<?= $path_prefix ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $path_prefix ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="d-flex">
    <div class="bg-dark text-white min-vh-100 p-3" style="width: 250px; background-color: #001d4a !important;">
        <h5 class="text-center mb-4 border-bottom pb-3">VMS ADMIN</h5>
        <ul class="nav flex-column gap-2">
            <li class="nav-item"><a href="dashboard.php" class="nav-link text-white"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li class="nav-item"><a href="venue_add.php" class="nav-link text-white active bg-primary rounded"><i class="bi bi-building me-2"></i> Venue Master</a></li>
            <li class="nav-item"><a href="vendor_list.php" class="nav-link text-white"><i class="bi bi-people me-2"></i> Vendor Master</a></li>
            <li class="nav-item"><a href="booking_calendar.php" class="nav-link text-white"><i class="bi bi-calendar3 me-2"></i> Bookings</a></li>
            <hr>
            <li class="nav-item"><a href="logout.php" class="nav-link text-warning"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
        </ul>
    </div>

    <div class="flex-grow-1">
        <header class="bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
            <span class="text-muted">Welcome back, Administrator</span>
            <button onclick="toggleTheme()" class="btn btn-outline-secondary btn-sm">Toggle Dark/Light</button>
        </header>
        <div class="p-4"></div>
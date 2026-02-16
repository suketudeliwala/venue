<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $org_full_name ?></title>
    <link rel="stylesheet" href="<?= $path_prefix ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $path_prefix ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body style="font-family: 'Inter', sans-serif;">

<header class="py-3 border-bottom shadow-sm" style="background: linear-gradient(135deg, #001d4a, #006992);">
    <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
        <a href="<?= $path_prefix ?>index.php" class="text-decoration-none d-flex align-items-center">
            <img src="<?= $path_prefix ?><?= $org_logo_path ?>" alt="Logo" height="50" class="me-3">
            <div class="text-white">
                <h1 class="h4 mb-0"><?= $org_full_name ?></h1>
                <small class="opacity-75"><?= $slogan ?></small>
            </div>
        </a>
        <button onclick="toggleTheme()" class="btn btn-sm btn-outline-light rounded-pill px-3">
            🌓 Toggle Mode
        </button>
    </div>
</header>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background-color: #27476e;">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#vmsNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="vmsNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= $path_prefix ?>index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Venues</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Check Availability</a></li>
                <li class="nav-item"><a class="nav-link btn btn-warning text-dark ms-lg-3 px-3" href="<?= $path_prefix ?>admin/login.php">Admin Login</a></li>
            </ul>
        </div>
    </div>
</nav>
<main class="py-4"></main>
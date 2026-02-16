<?php
include("includes/config.php");
include("includes/header.php");
?>

<section class="hero-section text-white py-5" style="background: linear-gradient(rgba(0,29,74,0.8), rgba(0,29,74,0.8)), url('assets/images/hero_bg.jpg'); background-size: cover;">
    <div class="container text-center py-5">
        <h1 class="display-3 fw-bold mb-3"><?= $org_full_name ?></h1>
        <p class="lead mb-4"><?= $slogan ?></p>
        <div class="d-flex justify-content-center gap-3">
            <a href="#venue-gallery" class="btn btn-warning btn-lg px-4">View All Venues</a>
            <a href="pages/enquiry.php" class="btn btn-outline-light btn-lg px-4">Enquire Now</a>
        </div>
    </div>
</section>

<div class="container my-5" id="venue-gallery">
    <h2 class="text-center mb-4">Our Available Venues</h2>
    
    <div class="d-flex flex-nowrap overflow-auto pb-4 gap-4" style="scrollbar-width: thin;">
        <?php
        $venues = $conn->query("SELECT * FROM vms_venues WHERE status = 'Active'");
        while($v = $venues->fetch_assoc()):
        ?>
        <div class="card border-0 shadow-sm" style="min-width: 300px; border-radius: 15px;">
            <img src="assets/images/venues/<?= $v['venue_image'] ?>" class="card-img-top rounded-top-4" style="height: 200px; object-fit: cover;" alt="<?= $v['venue_name'] ?>">
            <div class="card-body">
                <h5 class="fw-bold text-navy"><?= $v['venue_name'] ?></h5>
                <p class="small text-muted mb-2"><i class="bi bi-people me-1"></i> Capacity: <?= $v['capacity_person'] ?></p>
                <a href="pages/check_availability.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-primary w-100">Check Availability</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include("includes/footer.php"); ?>
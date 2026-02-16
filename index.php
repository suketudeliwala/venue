<?php
include("includes/config.php");
include("includes/header.php");
?>

<div class="container py-5">
    <div class="row align-items-center mb-5">
        <div class="col-lg-6">
            <h1 class="display-4 fw-bold text-primary mb-3">Professional Venues for Your Every Need</h1>
            <p class="lead text-muted">From grand weddings to corporate seminars, we provide the perfect space for your community events. Your bookings directly support our charity activities.</p>
            <div class="d-flex gap-3">
                <a href="#venues" class="btn btn-primary btn-lg px-4 rounded-pill">View All Venues</a>
                <a href="#" class="btn btn-outline-secondary btn-lg px-4 rounded-pill">Enquire Now</a>
            </div>
        </div>
        <div class="col-lg-6 mt-4 mt-lg-0">
            <img src="assets/images/about/happyfamily.png" alt="VMS Hero" class="img-fluid rounded-4 shadow">
        </div>
    </div>

    <div id="venues" class="row g-4 pt-5">
        <h2 class="text-center mb-5">Our Available Facilities</h2>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 rounded-3">
                <div class="bg-light p-5 text-center rounded-top">
                    <i class="bi bi-house-door display-1 text-primary"></i>
                </div>
                <div class="card-body">
                    <h5 class="card-title fw-bold text-navy">Grand Banquet Hall</h5>
                    <p class="text-muted small">Capacity: 500 Persons | A/C Available</p>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-primary fw-bold">Starts ₹15,000</span>
                        <a href="pages/venue_details.php" class="btn btn-sm btn-outline-primary">Details</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>
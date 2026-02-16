<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 
?>

<div class="container-fluid py-4 min-vh-100">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">Add New Customer Record</h5>
                </div>

                <?php if(isset($_GET['msg'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= htmlspecialchars($_GET['msg']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card-body p-4">
                    <form action="../api/save_customer.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Contact Person Name</label>
                                <input type="text" name="contact_person" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Company Name</label>
                                <input type="text" name="company_name" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Address</label>
                                <textarea name="address" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Mobile Number</label>
                                <input type="text" name="mobile" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Phone</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Email ID</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">PAN No</label>
                                <input type="text" name="pan_no" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">GST No</label>
                                <input type="text" name="gst_no" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-12 text-end mt-4">
                                <button type="reset" class="btn btn-light me-2">Clear</button>
                                <button type="submit" class="btn btn-primary px-5">Save Customer Master</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
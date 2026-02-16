<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

if (!isset($_GET['id'])) { header("Location: customer_list.php"); exit; }
$id = intval($_GET['id']);
$res = $conn->query("SELECT * FROM vms_customers WHERE id = $id");
$c = $res->fetch_assoc();
?>

<div class="container-fluid py-4 min-vh-100">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($_GET['msg']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow border-0">
                <div class="card-header bg-navy text-white py-3" style="background-color: #001d4a !important;">
                    <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i>Edit Customer: <?= htmlspecialchars($c['contact_person']) ?></h5>
                </div>
                <div class="card-body p-4">
                    <form action="../api/update_customer.php" method="POST">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Contact Person Name</label>
                                <input type="text" name="contact_person" class="form-control" value="<?= htmlspecialchars($c['contact_person']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Company Name</label>
                                <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($c['company_name']) ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Address</label>
                                <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($c['address']) ?></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Mobile Number</label>
                                <input type="text" name="mobile" class="form-control" value="<?= $c['mobile'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?= $c['phone'] ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Email ID</label>
                                <input type="email" name="email" class="form-control" value="<?= $c['email'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">PAN No</label>
                                <input type="text" name="pan_no" class="form-control" value="<?= $c['pan_no'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">GST No</label>
                                <input type="text" name="gst_no" class="form-control" value="<?= $c['gst_no'] ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2"><?= htmlspecialchars($c['remarks']) ?></textarea>
                            </div>
                            <div class="col-12 text-end mt-4 border-top pt-3">
                                <a href="customer_list.php" class="btn btn-light me-2">Cancel</a>
                                <button type="submit" class="btn btn-warning px-5 fw-bold">Update Customer Record</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
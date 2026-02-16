<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

$id = intval($_GET['id']);
$v = $conn->query("SELECT * FROM vms_customers WHERE id = $id")->fetch_assoc();
?>

<div class="container-fluid py-4 min-vh-100">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow border-0">
                <div class="card-header bg-navy text-white py-3" style="background-color: #001d4a !important;">
                    <h5 class="mb-0">Edit Vendor: <?= htmlspecialchars($v['contact_person']) ?></h5>
                </div>
                <div class="card-body p-4">
                    <form action="../api/update_vendor.php" method="POST">
                        <input type="hidden" name="id" value="<?= $v['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control" value="<?= htmlspecialchars($v['contact_person']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Service Category</label>
                                <select name="business_type" class="form-select" required>
                                    <?php 
                                    $services = ['Decorator', 'Caterer', 'Sound System', 'Electrician', 'Photographer', 'Florist'];
                                    foreach($services as $s) {
                                        $sel = ($v['business_type'] == $s) ? 'selected' : '';
                                        echo "<option value='$s' $sel>$s</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mobile</label>
                                <input type="text" name="mobile" class="form-control" value="<?= $v['mobile'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= $v['email'] ?>">
                            </div>
                            <div class="col-12 text-end mt-4">
                                <a href="vendor_list.php" class="btn btn-light me-2">Cancel</a>
                                <button type="submit" class="btn btn-warning px-5 fw-bold">Update Vendor</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include("../includes/footer.php"); ?>
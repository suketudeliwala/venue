<?php
include("../includes/config.php");
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
include("../includes/header_admin.php");

$search = $_GET['search'] ?? '';
$where = "WHERE customer_type = 'Vendor'";
if($search) {
    $where .= " AND (company_name LIKE '%$search%' OR contact_person LIKE '%$search%' OR business_type LIKE '%$search%')";
}
$res = $conn->query("SELECT * FROM vms_customers $where ORDER BY contact_person ASC");
?>

<div class="container-fluid py-4 min-vh-100">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by Vendor, Company or Service..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
                    <a href="vendor_add.php" class="btn btn-success"><i class="bi bi-plus-circle"></i> Add New Vendor</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark" style="background-color: #001d4a;">
                    <tr>
                        <th>Contact Person / Company</th>
                        <th>Service Type</th>
                        <th>Mobile / Email</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-navy"><?= htmlspecialchars($row['contact_person']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($row['company_name']) ?></small>
                        </td>
                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row['business_type']) ?></span></td>
                        <td><?= $row['mobile'] ?><br><small><?= $row['email'] ?></small></td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="vendor_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                <a href="vendor_delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this vendor?')"><i class="bi bi-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
<?php
include("../includes/config.php");
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
include("../includes/header_admin.php");

$search = $_GET['search'] ?? '';
$where = $search ? "WHERE equipment_name LIKE '%$search%'" : "";
$res = $conn->query("SELECT * FROM vms_equipments $where ORDER BY equipment_name ASC");
?>

<div class="container-fluid py-4 min-vh-100">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search equipment..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" class="btn btn-primary shadow-sm"><i class="bi bi-search"></i> Search</button>
                    <a href="equipment_add.php" class="btn btn-success shadow-sm"><i class="bi bi-plus-lg"></i> Add New Equipment</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark" style="background-color: #001d4a;">
                    <tr>
                        <th>Equipment Name</th>
                        <th>Qty</th>
                        <th>Daily Rent (₹)</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-bold text-navy"><?= htmlspecialchars($row['equipment_name']) ?></td>
                        <td><?= $row['total_quantity'] ?></td>
                        <td><?= number_format($row['daily_rent'], 2) ?></td>
                        <td>
                            <span class="badge rounded-pill bg-<?= $row['status'] == 'Available' ? 'success' : 'danger' ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="equipment_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                <a href="equipment_delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this equipment?')"><i class="bi bi-trash"></i></a>
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
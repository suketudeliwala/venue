<?php
include("../includes/config.php");
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
include("../includes/header_admin.php");

$search = $_GET['search'] ?? '';
$where = $search ? "WHERE venue_name LIKE '%$search%'" : "";
$res = $conn->query("SELECT * FROM vms_venues $where ORDER BY venue_name ASC");
?>

<div class="container-fluid py-4 min-vh-100">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search venue by name..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="venue_add.php" class="btn btn-success">Add New Venue</a>
                    <button class="btn btn-dark">Reports</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-navy" style="background-color: #001d4a; color: white;">
                    <tr>
                        <th>Photo</th>
                        <th>Venue Name</th>
                        <th>Capacity/Size</th>
                        <th>A/C</th>
                        <th>Base Deposit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><img src="../assets/images/venues/<?= $row['venue_image'] ?>" width="60" height="40" class="rounded"></td>
                        <td class="fw-bold text-navy"><?= $row['venue_name'] ?></td>
                        <td><?= $row['capacity_person'] ?> Persons / <?= $row['sq_ft'] ?> Sq.Ft</td>
                        <td><?= $row['is_ac'] ? 'Yes' : 'No' ?></td>
                        <td>₹<?= number_format($row['base_deposit'], 2) ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="venue_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="venue_delete.php?id=<?= $row['id'] ?>" 
                                    class="btn btn-sm btn-danger" 
                                    onclick="return confirm('Are you sure you want to delete this venue? This cannot be undone.');">
                                    Delete
                                </a>
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
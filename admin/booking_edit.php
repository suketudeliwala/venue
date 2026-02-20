<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

if(!isset($_GET['id'])) { header("Location: booking_list.php"); exit; }
$id = intval($_GET['id']);

// Fetch Master Data
$master = $conn->query("SELECT b.*, c.contact_person, c.mobile, c.email FROM vms_booking_master b JOIN vms_customers c ON b.customer_id = c.id WHERE b.id = $id")->fetch_assoc();

// Fetch Slots
$slots = $conn->query("SELECT * FROM vms_booking_slots WHERE booking_id = $id");
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Edit Booking: <?= $master['tracking_no'] ?></h4>
        <a href="booking_list.php" class="btn btn-outline-secondary btn-sm">Back to List</a>
    </div>

    <form action="../api/update_booking_complete.php" method="POST">
        <input type="hidden" name="booking_id" value="<?= $id ?>">
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small">Customer / Applicant</label>
                    <input type="text" class="form-control" value="<?= $master['contact_person'] ?>" readonly>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold">Function Name</label>
                    <input type="text" name="function_name" class="form-control" value="<?= htmlspecialchars($master['function_name']) ?>" required>
                </div>
            </div>
        </div>

        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-primary text-white">Venue Slots</div>
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="slotsTable">
                    <thead>
                        <tr>
                            <th>Venue</th>
                            <th>Date</th>
                            <th>Times</th>
                            <th>Rent</th>
                            <th>RSD</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($s = $slots->fetch_assoc()): ?>
                        <tr class="slot-row">
                            <td>
                                <select name="slot_venue_id[]" class="form-select" required>
                                    <?php 
                                    $v_res = $conn->query("SELECT id, venue_name FROM vms_venues");
                                    while($v = $v_res->fetch_assoc()) {
                                        $sel = ($v['id'] == $s['venue_id']) ? 'selected' : '';
                                        echo "<option value='{$v['id']}' $sel>{$v['venue_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><input type="date" name="slot_date[]" class="form-control" value="<?= $s['booking_date'] ?>" required></td>
                            <td>
                                <div class="input-group">
                                    <input type="time" name="slot_start[]" class="form-control" value="<?= $s['start_time'] ?>" required>
                                    <input type="time" name="slot_end[]" class="form-control" value="<?= $s['finish_time'] ?>" required>
                                </div>
                            </td>
                            <td><input type="number" name="slot_rent[]" class="form-control rent-amt" value="<?= $s['slot_rent'] ?>"></td>
                            <td><input type="number" name="slot_rsd[]" class="form-control rsd-amt" value="<?= $s['slot_rsd'] ?>"></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-warning px-5 fw-bold">Update Entire Booking</button>
        </div>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

$view_date = isset($_GET['view_date']) ? $_GET['view_date'] : date('Y-m-d');

// 1. Fetch Blocked Date status for the selected date
$block_stmt = $conn->prepare("SELECT reason, is_hard_block FROM vms_blocked_dates WHERE block_date = ? LIMIT 1");
$block_stmt->bind_param("s", $view_date);
$block_stmt->execute();
$block_res = $block_stmt->get_result();
$date_blocked = $block_res->fetch_assoc();

$time_slots = [
    '08:00' => '08:00 - 10:00', '10:00' => '10:00 - 12:00',
    '12:00' => '12:00 - 14:00', '14:00' => '14:00 - 16:00',
    '16:00' => '16:00 - 18:00', '18:00' => '18:00 - 20:00',
    '20:00' => '20:00 - 22:00'
];

$venues = $conn->query("SELECT id, venue_name FROM vms_venues WHERE status = 'Active' ORDER BY venue_name ASC");

$bookings_sql = "SELECT s.*, b.tracking_no, c.contact_person 
                 FROM vms_booking_slots s 
                 JOIN vms_booking_master b ON s.booking_id = b.id 
                 JOIN vms_customers c ON b.customer_id = c.id
                 WHERE s.booking_date = '$view_date' AND b.status != 'Cancelled'";
$bookings_res = $conn->query($bookings_sql);

$booked_slots = [];
while($row = $bookings_res->fetch_assoc()){
    $booked_slots[$row['venue_id']][] = $row;
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-navy"><i class="bi bi-grid-3x3-gap me-2"></i>Live Availability Grid</h4>
        
        <div class="d-flex align-items-center gap-3">
            <?php if($date_blocked): ?>
                <div class="badge <?= $date_blocked['is_hard_block'] ? 'bg-dark' : 'bg-warning text-dark' ?> p-2 px-3 shadow-sm">
                    <i class="bi bi-exclamation-octagon-fill me-1"></i> 
                    <?= $date_blocked['is_hard_block'] ? 'HARD BLOCKED' : 'RESERVED' ?>: <?= htmlspecialchars($date_blocked['reason']) ?>
                </div>
            <?php endif; ?>
            <form class="d-flex align-items-center gap-2">
                <input type="date" name="view_date" class="form-control" value="<?= $view_date ?>" onchange="this.form.submit()">
            </form>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 text-center align-middle">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th style="width: 200px; background: #001d4a;">Venue</th>
                            <?php foreach($time_slots as $time => $label): ?>
                                <th class="small"><?= $label ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($v = $venues->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold bg-light text-start ps-3"><?= $v['venue_name'] ?></td>
                                <?php 
                                foreach($time_slots as $slot_start => $slot_label): 
                                    $slot_end = date('H:i', strtotime($slot_start . ' +2 hours'));
                                    $is_booked = false;
                                    $booking_info = "";

                                    // Check existing bookings
                                    if(isset($booked_slots[$v['id']])){
                                        foreach($booked_slots[$v['id']] as $b){
                                            if($b['start_time'] < $slot_end && $b['finish_time'] > $slot_start){
                                                $is_booked = true;
                                                $booking_info = $b['tracking_no'];
                                                break;
                                            }
                                        }
                                    }
                                    
                                    // Determine Color & Interaction
                                    $cell_class = 'bg-success-subtle cell-free';
                                    $cell_text = '<span class="text-success small"><i class="bi bi-plus-circle"></i> Free</span>';
                                    $onclick = "window.location.href='booking_new.php?v_id={$v['id']}&v_date={$view_date}'";

                                    if($is_booked) {
                                        $cell_class = 'bg-danger-subtle';
                                        $cell_text = '<span class="badge bg-danger">Booked</span><br><small style="font-size: 9px;">'.$booking_info.'</small>';
                                        $onclick = "";
                                    } elseif($date_blocked) {
                                        if($date_blocked['is_hard_block']) {
                                            $cell_class = 'bg-secondary text-white';
                                            $cell_text = '<small class="fw-bold">HARD BLOCK</small>';
                                            $onclick = "alert('This date is Hard Blocked: " . $date_blocked['reason'] . "')";
                                        } else {
                                            $cell_class = 'bg-warning-subtle text-warning-emphasis';
                                            $cell_text = '<small class="fw-bold">RESERVED</small><br><small style="font-size: 9px;">Trust Event</small>';
                                            // Soft block allows Admin override per your requirement
                                            $onclick = "if(confirm('This date is Reserved for: " . $date_blocked['reason'] . ". Proceed anyway?')) { window.location.href='booking_new.php?v_id={$v['id']}&v_date={$view_date}'; }";
                                        }
                                    }
                                ?>
                                    <td class="<?= $cell_class ?>" 
                                        style="height: 60px; cursor: pointer;"
                                        <?php if($onclick): ?> onclick="<?= $onclick ?>" <?php endif; ?>>
                                        <?= $cell_text ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex flex-wrap gap-4 small fw-bold">
        <div class="d-flex align-items-center"><span class="p-2 border bg-success-subtle me-2"></span> Available</div>
        <div class="d-flex align-items-center"><span class="p-2 border bg-danger-subtle me-2"></span> Customer Booked</div>
        <div class="d-flex align-items-center"><span class="p-2 border bg-warning-subtle me-2"></span> Reserved (Admin Only)</div>
        <div class="d-flex align-items-center"><span class="p-2 border bg-secondary me-2"></span> Hard Block (Independence Day etc.)</div>
    </div>
</div>

<style>
    .cell-free:hover { background-color: #d1e7dd !important; border: 2px solid #198754 !important; }
    .bg-warning-subtle:hover { background-color: #fff3cd !important; border: 2px solid #ffc107 !important; }
</style>

<?php include("../includes/footer.php"); ?>
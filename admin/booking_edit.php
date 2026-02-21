<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

if(!isset($_GET['id'])) { header("Location: booking_list.php"); exit; }
$id = intval($_GET['id']);

// Fetch Master Data
$master = $conn->query("SELECT b.*, c.contact_person, c.mobile, c.email, c.is_member 
                        FROM vms_booking_master b 
                        JOIN vms_customers c ON b.customer_id = c.id 
                        WHERE b.id = $id")->fetch_assoc();

// Fetch Slots
$slots = $conn->query("SELECT * FROM vms_booking_slots WHERE booking_id = $id");
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-pencil-square me-2"></i>Edit Booking: <?= $master['tracking_no'] ?></h4>
        <a href="booking_list.php" class="btn btn-outline-secondary btn-sm">Back to List</a>
    </div>

    <input type="hidden" id="field_is_member" value="<?= $master['is_member'] ?>">

    <form action="../api/update_booking_complete.php" method="POST">
        <input type="hidden" name="booking_id" value="<?= $id ?>">
        <input type="hidden" name="tracking_no" value="<?= $master['tracking_no'] ?>">
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small">Customer / Applicant</label>
                    <input type="text" class="form-control bg-light" value="<?= $master['contact_person'] ?> - <?= $master['mobile'] ?>" readonly>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold">Function Name</label>
                    <input type="text" name="function_name" class="form-control" value="<?= htmlspecialchars($master['function_name']) ?>" required>
                </div>
            </div>
        </div>

        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Venue Slots & Pricing</h5>
                <button type="button" class="btn btn-sm btn-light" onclick="addRow()">+ Add Venue/Day</button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="slotsTable">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="20%">Venue</th>
                            <th width="15%">Date</th>
                            <th width="20%">Times</th>
                            <th width="15%">Rent Type (Duration)</th>
                            <th width="12%">Rent (₹)</th>
                            <th width="12%">RSD (₹)</th>
                            <th width="6%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($s = $slots->fetch_assoc()): ?>
                        <tr class="slot-row">
                            <td>
                                <select name="slot_venue_id[]" class="form-select venue-picker" onchange="loadRatesForVenue(this.closest('tr'), this.value)" required>
                                    <?php 
                                    $v_res = $conn->query("SELECT id, venue_name FROM vms_venues WHERE status='Active'");
                                    while($v = $v_res->fetch_assoc()) {
                                        $sel = ($v['id'] == $s['venue_id']) ? 'selected' : '';
                                        echo "<option value='{$v['id']}' $sel>{$v['venue_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><input type="date" name="slot_date[]" class="form-control date-picker" value="<?= $s['booking_date'] ?>" required></td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="time" name="slot_start[]" class="form-control" value="<?= $s['start_time'] ?>" required>
                                    <input type="time" name="slot_end[]" class="form-control" value="<?= $s['finish_time'] ?>" required>
                                </div>
                            </td>
                            <td>
                                <select name="slot_rate_id[]" class="form-select rate-picker" onchange="fetchRateDetails(this)">
                                    <option value="">-- Select --</option>
                                    <?php 
                                    $current_v = $s['venue_id'];
                                    $r_res = $conn->query("SELECT * FROM vms_venue_rates WHERE venue_id = $current_v");
                                    while($r = $r_res->fetch_assoc()) {
                                        echo "<option value='{$r['id']}' 
                                                data-m='{$r['member_rate']}' 
                                                data-nm='{$r['non_member_rate']}' 
                                                data-rsd='{$r['rsd_amount']}'>{$r['duration_label']}</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" name="slot_rent[]" class="form-control rent-amt" value="<?= $s['slot_rent'] ?>" oninput="calculateGrandTotal()"></td>
                            <td><input type="number" step="0.01" name="slot_rsd[]" class="form-control rsd-amt" value="<?= $s['slot_rsd'] ?>" oninput="calculateGrandTotal()"></td>
                            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">×</button></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-4 text-end">
                <div class="card bg-light p-3 shadow-sm border-0">
                    <p class="mb-1">Total Rent: ₹<span id="sumRent">0.00</span></p>
                    <p class="mb-1">GST (18%): ₹<span id="sumGST">0.00</span></p>
                    <p class="mb-1">Total RSD: ₹<span id="sumRSD">0.00</span></p>
                    <hr>
                    <div class="h5 fw-bold text-primary">
                        <span>New Grand Total:</span>
                        <span id="grandTotal">0.00</span>
                    </div>
                    <button type="submit" class="btn btn-warning w-100 fw-bold py-2 mt-3">Update Booking</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Run calculation immediately on page load to show existing totals
    calculateGrandTotal();
});

function addRow() {
    const tableBody = document.querySelector('#slotsTable tbody');
    const firstRow = document.querySelector('.slot-row');
    const newRow = firstRow.cloneNode(true);
    newRow.querySelectorAll('input').forEach(input => input.value = '');
    newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
    tableBody.appendChild(newRow);
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.slot-row');
    if (rows.length > 1) {
        btn.closest('tr').remove();
        calculateGrandTotal();
    }
}

function loadRatesForVenue(row, venueId) {
    const rateSelect = row.querySelector('.rate-picker');
    if (!venueId) return;
    fetch(`../api/get_venue_rates.php?venue_id=${venueId}`)
        .then(res => res.json())
        .then(data => {
            rateSelect.innerHTML = '<option value="">-- Pick --</option>';
            data.forEach(rate => {
                rateSelect.innerHTML += `<option value="${rate.id}" data-m="${rate.member_rate}" data-nm="${rate.non_member_rate}" data-rsd="${rate.rsd_amount}">${rate.duration_label}</option>`;
            });
        });
}

function fetchRateDetails(element) {
    const row = element.closest('tr');
    const selectedOption = element.options[element.selectedIndex];
    const isMember = document.getElementById('field_is_member').value === "1";
    
    if (selectedOption.value) {
        const rentValue = isMember ? selectedOption.dataset.m : selectedOption.dataset.nm;
        row.querySelector('.rent-amt').value = rentValue;
        row.querySelector('.rsd-amt').value = selectedOption.dataset.rsd;
        calculateGrandTotal();
    }
}

function calculateGrandTotal() {
    let totalRent = 0;
    let totalRSD = 0;

    document.querySelectorAll('.rent-amt').forEach(el => totalRent += parseFloat(el.value || 0));
    document.querySelectorAll('.rsd-amt').forEach(el => totalRSD += parseFloat(el.value || 0));

    const gst = totalRent * 0.18;
    const grandTotal = totalRent + gst + totalRSD;

    document.getElementById('sumRent').innerText = totalRent.toFixed(2);
    document.getElementById('sumGST').innerText = gst.toFixed(2);
    document.getElementById('sumRSD').innerText = totalRSD.toFixed(2);
    document.getElementById('grandTotal').innerText = grandTotal.toFixed(2);
}
</script>

<?php include("../includes/footer.php"); ?>
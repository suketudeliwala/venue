<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

if(!isset($_GET['id'])) { header("Location: booking_list.php"); exit; }
$id = intval($_GET['id']);

// 1. Fetch Master Data with Biller Details
$master_query = "SELECT b.*, c.id as biller_id, c.contact_person, c.company_name, c.mobile, c.is_member 
                 FROM vms_booking_master b 
                 JOIN vms_customers c ON b.customer_id = c.id 
                 WHERE b.id = $id";
$master_res = $conn->query($master_query);

if($master_res->num_rows == 0) { die("Booking not found."); }
$master = $master_res->fetch_assoc();

// 2. Check Permissions: Disable if Paid & Utilized
$paid_res = $conn->query("SELECT SUM(amount_rent + amount_rsd) as total_paid FROM vms_receipts WHERE booking_id = $id");
$total_paid = $paid_res->fetch_assoc()['total_paid'] ?? 0;
$util_check = $conn->query("SELECT id FROM vms_utilization_reports WHERE booking_id = $id")->num_rows;

$is_locked = ($master['net_payable'] <= $total_paid && $util_check > 0);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-navy"><i class="bi bi-pencil-square me-2"></i>Edit Booking: <?= $master['tracking_no'] ?></h4>
        <?php if($is_locked): ?>
            <span class="badge bg-danger p-2"><i class="bi bi-lock-fill"></i> Locked: Fully Paid & Utilized</span>
        <?php endif; ?>
        <a href="booking_list.php" class="btn btn-secondary shadow-sm"><i class="bi bi-arrow-left me-1"></i> Back to List</a>
    </div>

    <form action="../api/update_booking_complete.php" method="POST" id="editBookingForm" onsubmit="event.preventDefault(); handleFormSubmit();">
        <input type="hidden" name="booking_id" value="<?= $id ?>">
        <input type="hidden" name="is_member" id="field_is_member" value="<?= $master['is_member'] ?>">

        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0">Customer / Biller Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Billing Entity (Change Customer)</label>
                        <select name="customer_id" id="customer_id" class="form-select select2" required onchange="fetchCustomerDetails(this.value)" <?= $is_locked ? 'disabled' : '' ?>>
                            <?php 
                            $cust_list = $conn->query("SELECT id, contact_person, company_name, mobile FROM vms_customers WHERE customer_type = 'Customer' ORDER BY contact_person ASC");
                            while($c = $cust_list->fetch_assoc()) {
                                $selected = ($c['id'] == $master['biller_id']) ? 'selected' : '';
                                echo "<option value='{$c['id']}' $selected>{$c['contact_person']} ({$c['company_name']}) - {$c['mobile']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Biller Status</label>
                        <input type="text" id="disp_member" class="form-control bg-light fw-bold" readonly value="<?= ($master['is_member'] == 1) ? 'MEMBER' : 'NON-MEMBER' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Mobile</label>
                        <input type="text" id="disp_mobile" class="form-control bg-light" readonly value="<?= $master['mobile'] ?>">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Function Name</label>
                        <input type="text" name="function_name" class="form-control" value="<?= htmlspecialchars($master['function_name']) ?>" required <?= $is_locked ? 'readonly' : '' ?>>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Venue & Slot Allocation</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered align-middle">
                    <thead class="bg-light text-center">
                        <tr>
                            <th width="20%">Venue</th>
                            <th width="15%">Date</th>
                            <th width="20%">Rate/Duration</th>
                            <th width="20%">Time Block</th>
                            <th>Rent (₹)</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody id="slotBody">
                        <?php 
                        $slots = $conn->query("SELECT * FROM vms_booking_slots WHERE booking_id = $id");
                        while($s = $slots->fetch_assoc()): 
                        ?>
                        <tr class="slot-row">
                            <td>
                                <select name="slot_venue_id[]" class="form-select venue-picker" required onchange="fetchRateSlots(this)" <?= $is_locked ? 'disabled' : '' ?>>
                                    <?php 
                                    $v_res = $conn->query("SELECT id, venue_name FROM vms_venues WHERE status='Active'");
                                    while($v = $v_res->fetch_assoc()) {
                                        $v_sel = ($v['id'] == $s['venue_id']) ? 'selected' : '';
                                        echo "<option value='{$v['id']}' $v_sel>{$v['venue_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><input type="date" name="slot_date[]" class="form-control date-picker" value="<?= $s['booking_date'] ?>" required onchange="fetchRateSlots(this.closest('tr').querySelector('.venue-picker'))" <?= $is_locked ? 'readonly' : '' ?>></td>
                            <td>
                                <select name="slot_rate_id[]" class="form-select rate-picker" required onchange="fetchRateDetails(this)" <?= $is_locked ? 'disabled' : '' ?>>
                                    <?php 
                                    $rates = $conn->query("SELECT id, duration_label, member_rate, non_member_rate, rsd_amount FROM vms_venue_rates WHERE venue_id = ".$s['venue_id']);
                                    while($r = $rates->fetch_assoc()) {
                                        $r_sel = ($r['id'] == $s['slot_rate_id']) ? 'selected' : '';
                                        echo "<option value='{$r['id']}' $r_sel data-m='{$r['member_rate']}' data-nm='{$r['non_member_rate']}' data-rsd='{$r['rsd_amount']}'>{$r['duration_label']}</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="time" name="slot_start[]" class="form-control s-time" value="<?= $s['start_time'] ?>" required onchange="validateAndCheckConflict(this)" <?= $is_locked ? 'readonly' : '' ?>>
                                    <input type="time" name="slot_end[]" class="form-control f-time" value="<?= $s['finish_time'] ?>" required onchange="validateAndCheckConflict(this)" <?= $is_locked ? 'readonly' : '' ?>>
                                </div>
                            </td>
                            <td><input type="number" name="slot_rent[]" class="form-control rent-amt text-end" value="<?= $s['slot_rent'] ?>" readonly></td>
                            <input type="hidden" name="slot_rsd[]" class="rsd-amt" value="<?= $s['slot_rsd'] ?>">
                            <td>
                                <?php if(!$is_locked): ?>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)"><i class="bi bi-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary btn-lg px-5" <?= $is_locked ? 'disabled' : '' ?>>Update Booking</button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Logic to handle Biller Change & Split Entity Price Switch
function fetchCustomerDetails(custId) {
    if(!custId) return;
    $.getJSON(`../api/get_customer_details.php?id=${custId}`, function(data) {
        $('#disp_mobile').val(data.mobile);
        const isMem = (data.is_member == 1);
        $('#field_is_member').val(data.is_member);
        $('#disp_member').val(isMem ? 'TRUST MEMBER' : 'GENERAL (NON-MEMBER)');
        
        // Refresh all grid rates based on the new Biller's membership
        document.querySelectorAll('.rate-picker').forEach(el => fetchRateDetails(el));
    });
}

function fetchRateSlots(element) {
    const row = element.closest('tr');
    const v_id = row.querySelector('.venue-picker').value;
    const date = row.querySelector('.date-picker').value;
    const rateSelect = row.querySelector('.rate-picker');
    if (!v_id || !date) return;

    $.getJSON(`../api/get_venue_rates.php?venue_id=${v_id}`, function(data) {
        rateSelect.innerHTML = '<option value="">-- Choose --</option>';
        data.forEach(rate => {
            rateSelect.innerHTML += `<option value="${rate.id}" data-m="${rate.member_rate}" data-nm="${rate.non_member_rate}" data-rsd="${rate.rsd_amount}">${rate.duration_label}</option>`;
        });
    });
}

function fetchRateDetails(element) {
    const row = element.closest('tr');
    const opt = element.options[element.selectedIndex];
    if (!opt || !opt.value) return;

    const isMember = (document.getElementById('field_is_member').value == "1");
    row.querySelector('.rent-amt').value = isMember ? opt.dataset.m : opt.dataset.nm;
}

/** * ERROR-PROOF VALIDATION LOGIC 
 */
async function handleFormSubmit() {
    const form = document.getElementById('editBookingForm');
    const rows = document.querySelectorAll('.slot-row');
    let isValid = true;

    // Step 1: Check for 0 amount rent
    rows.forEach((row, index) => {
        const rent = parseFloat(row.querySelector('.rent-amt').value) || 0;
        if (rent <= 0) {
            alert(`Row ${index + 1}: Rent amount cannot be zero or empty.`);
            isValid = false;
        }
    });

    if (!isValid) return false;

    // Step 2: Comprehensive Conflict Check
    for (let [index, row] of rows.entries()) {
        const v_id = row.querySelector('.venue-picker').value;
        const date = row.querySelector('.date-picker').value;
        const start = row.querySelector('.s-time').value;
        const end = row.querySelector('.f-time').value;

        if (v_id && date && start && end) {
            try {
                const response = await fetch(`../api/check_conflict.php?venue_id=${v_id}&date=${date}&start=${start}&end=${end}&exclude_booking=<?= $id ?>`);
                const res = await response.json();
                
                if (res.conflict) {
                    alert(`SAVE BLOCKED! Conflict detected on Row ${index + 1} for ${row.querySelector('.venue-picker option:selected').text}.\nThis slot is already booked for ${res.booking}.`);
                    isValid = false;
                    break;
                }
            } catch (error) {
                alert("Network error while checking conflicts. Please try again.");
                isValid = false;
                break;
            }
        }
    }

    if (isValid) {
        form.submit(); // Only submit if all checks pass
    }
}

// Visual feedback during manual changes
function validateAndCheckConflict(element) {
    const row = element.closest('tr');
    const v_id = row.querySelector('.venue-picker').value;
    const date = row.querySelector('.date-picker').value;
    const start = row.querySelector('.s-time').value;
    const end = row.querySelector('.f-time').value;

    if (!v_id || !date || !start || !end) return;

    $.getJSON(`../api/check_conflict.php`, {
        venue_id: v_id, 
        date: date, 
        start: start, 
        end: end,
        exclude_booking: <?= $id ?>
    }, function(res) {
        if (res.conflict) {
            alert(`CONFLICT DETECTED! Already booked for ${res.booking}.`);
            row.querySelector('.rent-amt').value = "0"; // Reset rent to force the 0-check
            row.querySelector('.s-time').style.borderColor = "red";
        } else {
            row.querySelector('.s-time').style.borderColor = "";
            fetchRateDetails(row.querySelector('.rate-picker'));
        }
    });
}
</script>
<?php include("../includes/footer.php"); ?>
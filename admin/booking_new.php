<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

// Generate Continuous Booking No: BK-YYYYMMDD-00001
$today_prefix = "BK-" . date('Ymd');
$check_count = $conn->query("SELECT COUNT(*) as total FROM vms_booking_master WHERE tracking_no LIKE '$today_prefix%'");
$count = $check_count->fetch_assoc()['total'] + 1;
$next_booking_no = $today_prefix . "-" . str_pad($count, 5, "0", STR_PAD_LEFT);
?>

<?php
// Catch data from Dashboard
$pre_venue = isset($_GET['v_id']) ? intval($_GET['v_id']) : '';
$pre_date  = isset($_GET['v_date']) ? $_GET['v_date'] : '';
?>

<div class="container-fluid py-4">
    <form id="mainBookingForm" action="../api/save_booking_complete.php" method="POST">
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-navy text-white py-3" style="background-color: #001d4a !important;">
                <h5 class="mb-0"><i class="bi bi-search me-2"></i>Step 1: Link to Enquiry Tracking</h5>
            </div>
            <div class="card-body bg-light">
                <div class="row g-3">
                    <div class="col-md-5 border-end">
                        <label class="form-label fw-bold">Search Enquiry No / Applicant</label>
                        <select id="fetch_enquiry" class="form-select select2">
                            <option value="">-- Direct Booking (No Enquiry) --</option>
                            <?php 
                            $enq_list = $conn->query("SELECT id, tracking_no, applicant_name FROM vms_enquiries WHERE status NOT IN ('Converted', 'Rejected') ORDER BY id DESC");
                            while($el = $enq_list->fetch_assoc()) {
                                echo "<option value='{$el['id']}'>{$el['tracking_no']} - {$el['applicant_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <div id="enq_info_box" class="p-3 border rounded bg-white shadow-sm" style="display:none; border-left: 5px solid #0dcaf0 !important;">
                            <h6 class="text-info fw-bold mb-2 small text-uppercase">Requirements from Enquiry:</h6>
                            <div class="row small g-2">
                                <div class="col-md-4"><strong>Exp. Date:</strong> <span id="ref_date" class="text-primary fw-bold"></span></div>
                                <div class="col-md-4"><strong>Attendees:</strong> <span id="ref_attendees" class="text-danger fw-bold"></span></div>
                                <div class="col-md-4"><strong>Enquirer Status:</strong> <span id="ref_member_status" class="badge bg-secondary"></span> <span id="ref_member_no" class="small text-muted"></span></div>
                                <div class="col-md-12"><strong>Services:</strong> <span id="ref_services" class="text-dark"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Step 2: Customer / Biller Selection</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <input type="hidden" name="enquiry_id" id="enquiry_id">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Select Billing Entity (Biller determines the Rate)</label>
                        <select name="customer_id" id="customer_id" class="form-select select2" required onchange="fetchCustomerDetails(this.value)">
                            <option value="">-- Search Customer Master --</option>
                            <?php 
                            $cust_list = $conn->query("SELECT id, contact_person, company_name, mobile FROM vms_customers WHERE customer_type = 'Customer' ORDER BY contact_person ASC");
                            while($c = $cust_list->fetch_assoc()) {
                                $display = $c['contact_person'] . " (" . ($c['company_name'] ?: 'Individual') . ")";
                                echo "<option value='{$c['id']}'>{$display} - {$c['mobile']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-success fw-bold">Biller Membership</label>
                        <input type="text" id="disp_member" class="form-control bg-light fw-bold" readonly placeholder="Select Customer...">
                        <input type="hidden" name="is_member" id="field_is_member" value="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Booking No</label>
                        <input type="text" name="tracking_no" class="form-control bg-light fw-bold" value="<?= $next_booking_no ?>" readonly>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Function Name / Occasion</label>
                        <input type="text" name="function_name" id="field_function_name" class="form-control" required>
                    </div>
                    <input type="hidden" name="applicant_name" id="hidden_c_name">
                    <input type="hidden" name="applicant_mobile" id="hidden_c_mobile">
                    <input type="hidden" name="applicant_email" id="hidden_c_email">
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white py-3">
                <h5 class="mb-0">Step 3: Venue & Slot Allocation</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered align-middle" id="slotTable">
                    <thead class="bg-light">
                        <tr class="text-center">
                            <th width="20%">Venue</th>
                            <th width="12%">Date</th>
                            <th width="18%">Rate/Duration</th>
                            <th width="20%">Time Block</th>
                            <th>Rent (₹)</th>
                            <th>RSD (₹)</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody id="slotBody">
                        <tr class="slot-row">
                            <td>

                                <select name="slot_venue_id[]" class="form-select venue-picker" required onchange="fetchRateSlots(this)">
                                    <option value="">-- Select Venue --</option>
                                    <?php 
                                    $v_res = $conn->query("SELECT id, venue_name FROM vms_venues WHERE status='Active'");
                                    while($v = $v_res->fetch_assoc()):
                                        $selected = ($v['id'] == $pre_venue) ? 'selected' : '';
                                        echo "<option value='{$v['id']}' $selected>{$v['venue_name']}</option>";
                                    endwhile;
                                    ?>
                                </select>
                            </td>
                            <td><input type="date" name="slot_date[]" class="form-control date-picker" value="<?= $pre_date ?>" required onchange="fetchRateSlots(this.closest('tr').querySelector('.venue-picker'))"></td>
                            <td>
                                <select name="slot_rate_id[]" class="form-select rate-picker" required onchange="fetchRateDetails(this)">
                                    <option value="">-- Select Venue & Date --</option>
                                </select>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="time" name="slot_start[]" class="form-control s-time" required onchange="validateAndCheckConflict(this)">
                                    <span class="input-group-text small">to</span>
                                    <input type="time" name="slot_end[]" class="form-control f-time" required onchange="validateAndCheckConflict(this)">
                                </div>
                            </td>
                            <td><input type="number" name="slot_rent[]" class="form-control rent-amt text-end" readonly></td>
                            <td><input type="number" name="slot_rsd[]" class="form-control rsd-amt text-end" readonly></td>
                            <td><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addRow()"><i class="bi bi-plus-circle me-2"></i>Add Extra Venue/Date</button>
            </div>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-light p-3 border-dark">
                    <div class="d-flex justify-content-between mb-2"><span>Sub-Total Rent:</span> <h6 id="sumRent">0.00</h6></div>
                    <div class="d-flex justify-content-between mb-2 text-muted small"><span>GST (18%):</span> <h6 id="sumGST">0.00</h6></div>
                    <div class="d-flex justify-content-between mb-2"><span>Total RSD:</span> <h6 id="sumRSD">0.00</h6></div>
                    <hr class="border-dark">
                    <div class="d-flex justify-content-between h5 text-primary fw-bold"><span>Grand Total:</span> <span id="grandTotal">0.00</span></div>
                    <button type="submit" class="btn btn-success btn-lg w-100 mt-3 shadow border-dark fw-bold">SAVE BOOKING RECORD</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    // If venue and date are pre-filled from dashboard, load the slots immediately
    if($('#slotBody .venue-picker').val() != "" && $('#slotBody .date-picker').val() != "") {
        fetchRateSlots($('#slotBody .venue-picker')[0]);
    }

    $('#fetch_enquiry').on('change', function() {
        const enqId = $(this).val();
        if(!enqId) { $('#enq_info_box').hide(); return; }

        $.getJSON(`../api/get_enquiry_details.php?id=${enqId}`, function(data) {
            $('#enquiry_id').val(data.id);
            $('#field_function_name').val(data.function_name);
            $('#enq_info_box').show();
            $('#ref_date').text(data.start_date);
            $('#ref_attendees').text(data.approx_attendees || '0');
            
            const enqMember = (data.is_member == 1);
            $('#ref_member_status').text(enqMember ? 'MEMBER' : 'NON-MEMBER');
            $('#ref_member_no').text(data.member_no ? '(ID: ' + data.member_no + ')' : '');
            
            let svcs = [];
            if(data.need_decorator==1) svcs.push("Decorator");
            if(data.need_caterer==1) svcs.push("Caterer");
            if(data.need_sound==1) svcs.push("Sound");
            $('#ref_services').text(svcs.join(", ") || "None");

            // Pre-fill Venue and Date from Enquiry
            const firstRow = $('.slot-row').first();
            if(data.venue_id && data.start_date) {
                firstRow.find('.venue-picker').val(data.venue_id);
                firstRow.find('.date-picker').val(data.start_date);
                fetchRateSlots(firstRow.find('.venue-picker')[0]);
            }
        });
    });
});

// CRITICAL FIX: The "Split Entity" Gatekeeper
function fetchCustomerDetails(custId) {
    if(!custId) {
        $('#field_is_member').val(0);
        $('#disp_member').val('Select Customer...');
        return;
    }
    $.getJSON(`../api/get_customer_details.php?id=${custId}`, function(data) {
        // Update Biller info
        $('#hidden_c_name').val(data.contact_person);
        $('#hidden_c_mobile').val(data.mobile);
        $('#hidden_c_email').val(data.email);
        
        // 1. Set the Global Membership Status from the CUSTOMER table
        const isMem = (data.is_member == 1);
        $('#field_is_member').val(data.is_member);
        $('#disp_member').val(isMem ? 'TRUST MEMBER' : 'GENERAL (NON-MEMBER)');
        
        // 2. IMPORTANT: Recalculate all Rent fields in the grid based on this Biller's status
        document.querySelectorAll('.rate-picker').forEach(el => fetchRateDetails(el));
    });
}

function fetchRateSlots(element) {
    const row = element.closest('tr');
    const venueId = row.querySelector('.venue-picker').value;
    const date = row.querySelector('.date-picker').value;
    const rateSelect = row.querySelector('.rate-picker');
    
    if (!venueId || !date) return;

    $.getJSON(`../api/get_venue_rates.php?venue_id=${venueId}`, function(data) {
        rateSelect.innerHTML = '<option value="">-- Choose Slot --</option>';
        data.forEach(rate => {
            rateSelect.innerHTML += `<option value="${rate.id}" 
                data-m="${rate.member_rate}" 
                data-nm="${rate.non_member_rate}" 
                data-rsd="${rate.rsd_amount}">${rate.duration_label}</option>`;
        });
    });
}

function fetchRateDetails(element) {
    const row = element.closest('tr');
    const opt = element.options[element.selectedIndex];
    if (!opt || !opt.value) return;

    // Use is_member status from Step 2 (Biller)
    const isMember = (document.getElementById('field_is_member').value == "1");
    const rentVal = isMember ? opt.dataset.m : opt.dataset.nm;
    
    row.querySelector('.rent-amt').value = rentVal;
    row.querySelector('.rsd-amt').value = opt.dataset.rsd;
    
    calculateGrandTotal();
}

function validateAndCheckConflict(element) {
    const row = element.closest('tr');
    const v_id = row.querySelector('.venue-picker').value;
    const date = row.querySelector('.date-picker').value;
    const start = row.querySelector('.s-time').value;
    const end = row.querySelector('.f-time').value;

    if (!v_id || !date || !start || !end) return;

    if (start >= end) {
        alert("End time must be after Start time.");
        element.value = "";
        return;
    }

    $.getJSON(`../api/check_conflict.php`, {venue_id: v_id, date: date, start: start, end: end}, function(res) {
        if (res.conflict) {
            alert(`CONFLICT! This venue is already booked for ${res.booking} during these hours.`);
            row.querySelector('.s-time').value = "";
            row.querySelector('.f-time').value = "";
        }
    });
}

function calculateGrandTotal() {
    let rent = 0, rsd = 0;
    document.querySelectorAll('.rent-amt').forEach(el => rent += parseFloat(el.value || 0));
    document.querySelectorAll('.rsd-amt').forEach(el => rsd += parseFloat(el.value || 0));
    
    let gst = rent * 0.18;
    document.getElementById('sumRent').innerText = rent.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('sumGST').innerText = gst.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('sumRSD').innerText = rsd.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('grandTotal').innerText = (rent + gst + rsd).toLocaleString('en-IN', {minimumFractionDigits: 2});
}

function addRow() {
    const body = document.getElementById('slotBody');
    const newRow = body.firstElementChild.cloneNode(true);
    newRow.querySelectorAll('input').forEach(i => i.value = '');
    newRow.querySelector('.rate-picker').innerHTML = '<option value="">-- Select Venue & Date --</option>';
    body.appendChild(newRow);
}

function removeRow(btn) {
    if(document.querySelectorAll('.slot-row').length > 1) {
        btn.closest('tr').remove();
        calculateGrandTotal();
    }
}
</script>

<?php include("../includes/footer.php"); ?>
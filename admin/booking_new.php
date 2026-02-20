<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 
?>

<div class="container-fluid py-4">
    <form id="mainBookingForm" action="../api/save_booking_complete.php" method="POST">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-navy text-white py-3" style="background-color: #001d4a !important;">
                <h5 class="mb-0"><i class="bi bi-search me-2"></i>Start from Enquiry (Optional)</h5>
            </div>
            <div class="card-body bg-light">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Select Enquiry Tracking No / Ref</label>
                        <select id="fetch_enquiry" class="form-select select2">
                            <option value="">-- Direct Booking (No Enquiry) --</option>
                            <?php 
                            // Update this line in your booking_new.php
                        $enq_list = $conn->query("SELECT id, tracking_no, applicant_name FROM vms_enquiries WHERE status NOT IN ('Converted', 'Rejected') ORDER BY id DESC");
                            while($el = $enq_list->fetch_assoc()) {
                                echo "<option value='{$el['id']}'>{$el['tracking_no']} - {$el['applicant_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-2">Selecting a reference will auto-fill the form below.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0">2. Customer & Event Details</h5>
            </div>
            <div class="card-body row g-3">
                <input type="hidden" name="enquiry_id" id="enquiry_id">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Customer Name / Contact Person</label>
                    <input type="text" name="applicant_name" id="field_applicant_name" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Mobile</label>
                    <input type="text" name="applicant_mobile" id="field_applicant_mobile" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="applicant_email" id="field_applicant_email" class="form-control">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold">Function Name / Occasion</label>
                    <input type="text" name="function_name" id="field_function_name" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Membership Status</label>
                    <select name="is_member" id="field_is_member" class="form-select">
                        <option value="0">Non-Member</option>
                        <option value="1">Member</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">3. Booking Slots (Venues & Dates)</h5>
                <button type="button" class="btn btn-sm btn-light" onclick="addRow()">+ Add Venue/Day</button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="slotsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="25%">Venue</th>
                            <th width="15%">Date</th>
                            <th width="25%">Time Slot</th>
                            <th width="15%">Rent Type</th>
                            <th width="10%">Rent</th>
                            <th width="10%">RSD</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="slot-row">
                            <td>
                                <select name="slot_venue_id[]" class="form-select venue-picker" onchange="checkAvailability(this)" required>
                                    <option value="">-- Select --</option>
                                    <?php 
                                    $v_res = $conn->query("SELECT id, venue_name FROM vms_venues WHERE status='Active'");
                                    while($v = $v_res->fetch_assoc()) echo "<option value='{$v['id']}'>{$v['venue_name']}</option>";
                                    ?>
                                </select>
                            </td>
                            <td><input type="date" name="slot_date[]" class="form-control date-picker" onchange="checkAvailability(this)" required></td>
                            <td>
                                <div class="input-group">
                                    <input type="time" name="slot_start[]" class="form-control" onchange="checkAvailability(this)" required>
                                    <input type="time" name="slot_end[]" class="form-control" onchange="checkAvailability(this)" required>
                                </div>
                            </td>
                            <td>
                                <select name="slot_rate_id[]" class="form-select rate-picker" onchange="fetchRateDetails(this)">
                                    <option value="">-- Pick --</option>
                                </select>
                            </td>
                            <td><input type="number" name="slot_rent[]" class="form-control rent-amt" readonly></td>
                            <td><input type="number" name="slot_rsd[]" class="form-control rsd-amt" readonly></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-4">
                <div class="card bg-light border-0 shadow-sm p-3">
                    <h6 class="fw-bold border-bottom pb-2">Financial Summary (Inc. 18% GST)</h6>
                    <div class="d-flex justify-content-between mb-2"><span>Rent Total:</span> <strong id="sumRent">0.00</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>GST Total:</span> <strong id="sumGST">0.00</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>RSD Total:</span> <strong id="sumRSD">0.00</strong></div>
                    <hr>
                    <div class="d-flex justify-content-between h5 fw-bold text-primary"><span>Grand Total:</span> <span id="grandTotal">0.00</span></div>
                    <button type="submit" class="btn btn-success btn-lg w-100 mt-3">Confirm & Save Booking</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // 1. Initialize Select2 if you have the library loaded
    if ($.fn.select2) {
        $('.select2').select2({ placeholder: "Select Option", width: '100%' });
    }

    // 2. TRIGGER: When Enquiry is selected from the Dropdown
    $('#fetch_enquiry').on('change', function() {
        const enqId = $(this).val();
        if(!enqId) {
            $('#mainBookingForm')[0].reset();
            return;
        }

        $.getJSON(`../api/get_enquiry_details.php?id=${enqId}`, function(data) {
            if(data.error) { alert(data.error); return; }
            
            // Fill Customer & Event Details
            $('#enquiry_id').val(data.id);
            $('#field_applicant_name').val(data.applicant_name);
            $('#field_applicant_mobile').val(data.applicant_mobile);
            $('#field_applicant_email').val(data.applicant_email);
            $('#field_function_name').val(data.function_name);
            $('#field_is_member').val(data.is_member);
            
            // Pre-fill the first row venue and date
            if(data.venue_id) {
                const firstRow = $('.slot-row').first();
                firstRow.find('.venue-picker').val(data.venue_id);
                firstRow.find('.date-picker').val(data.start_date);
                
                // Trigger the rate loader for this venue
                loadRatesForVenue(firstRow[0], data.venue_id);
            }
        });
    });
});

// 3. Row Management: Add New Row
function addRow() {
    const tableBody = document.querySelector('#slotsTable tbody');
    const firstRow = document.querySelector('.slot-row');
    const newRow = firstRow.cloneNode(true);

    // Reset all fields in the new row
    newRow.querySelectorAll('input').forEach(input => input.value = '');
    newRow.querySelectorAll('select').forEach(select => {
        select.selectedIndex = 0;
        if(select.classList.contains('rate-picker')) {
            select.innerHTML = '<option value="">-- Pick --</option>';
        }
    });
    newRow.style.backgroundColor = '';
    tableBody.appendChild(newRow);
}

// 4. Row Management: Remove Row
function removeRow(btn) {
    const rows = document.querySelectorAll('.slot-row');
    if (rows.length > 1) {
        btn.closest('tr').remove();
        calculateGrandTotal();
    }
}

// 5. Availability & Rate Loader Trigger
function checkAvailability(element) {
    const row = element.closest('tr');
    const venueId = row.querySelector('.venue-picker').value;
    const date = row.querySelector('.date-picker').value;
    const startTime = row.querySelector('input[name="slot_start[]"]').value;
    const endTime = row.querySelector('input[name="slot_end[]"]').value;

    // If venue is changed, reload the "Rent Type" dropdown
    if (element.classList.contains('venue-picker')) {
        loadRatesForVenue(row, venueId);
    }

    // Check availability via API if all fields are present
    if (venueId && date && startTime && endTime) {
        fetch(`../api/check_availability.php?venue_id=${venueId}&date=${date}&start_time=${startTime}&finish_time=${endTime}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'available') {
                    row.style.backgroundColor = 'rgba(25, 135, 84, 0.1)';
                } else {
                    row.style.backgroundColor = 'rgba(220, 53, 69, 0.1)';
                    alert(`Alert: ${data.message}`);
                }
            });
    }
}

// 6. API Call: Fetch available Rates for the specific Venue
function loadRatesForVenue(row, venueId) {
    const rateSelect = row.querySelector('.rate-picker');
    if (!venueId) return;

    fetch(`../api/get_venue_rates.php?venue_id=${venueId}`)
        .then(res => res.json())
        .then(data => {
            rateSelect.innerHTML = '<option value="">-- Pick --</option>';
            data.forEach(rate => {
                rateSelect.innerHTML += `<option value="${rate.id}" 
                    data-m="${rate.member_rate}" 
                    data-nm="${rate.non_member_rate}" 
                    data-rsd="${rate.rsd_amount}">${rate.duration_label}</option>`;
            });
        });
}

// 7. Auto-Fill Rent/RSD from Selection
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

// 8. Final Financial Calculations
function calculateGrandTotal() {
    let totalRent = 0;
    let totalRSD = 0;

    document.querySelectorAll('.rent-amt').forEach(el => totalRent += parseFloat(el.value || 0));
    document.querySelectorAll('.rsd-amt').forEach(el => totalRSD += parseFloat(el.value || 0));

    const gst = totalRent * 0.18; // 18% Total GST
    const grandTotal = totalRent + gst + totalRSD;

    document.getElementById('sumRent').innerText = totalRent.toFixed(2);
    document.getElementById('sumGST').innerText = gst.toFixed(2);
    document.getElementById('sumRSD').innerText = totalRSD.toFixed(2);
    document.getElementById('grandTotal').innerText = grandTotal.toFixed(2);
}

// Row management and calculation functions go here (same as previous block)
</script>
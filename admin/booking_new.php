<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

// Logic to fetch Enquiry data if passed via GET
$enq = null;
if(isset($_GET['enquiry_id'])) {
    $eid = intval($_GET['enquiry_id']);
    $enq = $conn->query("SELECT * FROM vms_enquiries WHERE id = $eid")->fetch_assoc();
}
?>

<div class="container-fluid py-4">
    <form id="mainBookingForm" action="../api/save_booking_complete.php" method="POST">
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-navy text-white">
                <h5 class="mb-0">1. Event & Customer Information</h5>
            </div>
            <div class="card-body row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Tracking No / Ref</label>
                    <input type="text" name="tracking_no" class="form-control" value="<?= $enq['tracking_no'] ?? '' ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Function Name</label>
                    <input type="text" name="function_name" class="form-control" value="<?= $enq['function_name'] ?? '' ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Customer</label>
                    <select name="customer_id" id="customer_select" class="form-select select2" required>
                        </select>
                </div>
            </div>
        </div>

        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">2. Venue & Date Slots</h5>
                <button type="button" class="btn btn-sm btn-light" onclick="addRow()">+ Add Another Venue/Slot</button>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0" id="slotsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Venue</th>
                            <th>Date</th>
                            <th>Time (Start - End)</th>
                            <th>Rate Type</th>
                            <th>Rent (₹)</th>
                            <th>RSD (₹)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="slot-row">
                            <td>
                                <select name="slot_venue_id[]" class="form-select venue-picker" onchange="checkAvailability(this)" required>
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
                                    </select>
                            </td>
                            <td><input type="number" name="slot_rent[]" class="form-control rent-amt" readonly></td>
                            <td><input type="number" name="slot_rsd[]" class="form-control rsd-amt" readonly></td>
                            <td><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">×</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-secondary text-white">3. Service Providers & Equipment</div>
                    <div class="card-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Decorator (Royalty %)</label>
                            <select name="decorator_id" class="form-select"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Caterer (Royalty %)</label>
                            <select name="caterer_id" class="form-select"></select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow border-0 bg-light">
                    <div class="card-body">
                        <h6 class="fw-bold border-bottom pb-2">Financial Summary</h6>
                        <div class="d-flex justify-content-between mb-2"><span>Total Rent:</span> <strong id="sumRent">0.00</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Total RSD:</span> <strong id="sumRSD">0.00</strong></div>
                        <div class="d-flex justify-content-between mb-2 text-primary"><span>SGST (9%):</span> <strong id="sumSGST">0.00</strong></div>
                        <div class="d-flex justify-content-between mb-2 text-primary"><span>CGST (9%):</span> <strong id="sumCGST">0.00</strong></div>
                        <hr>
                        <div class="d-flex justify-content-between h5 fw-bold"><span>Grand Total:</span> <span id="grandTotal">0.00</span></div>
                        <button type="submit" class="btn btn-success w-100 mt-3 btn-lg shadow">Confirm Booking</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2 for Customer search
    $('.select2').select2({
        placeholder: "Select Customer",
        width: '100%'
    });

    // Update totals whenever a rent or RSD value changes
    $(document).on('input', '.rent-amt, .rsd-amt', function() {
        calculateGrandTotal();
    });
});

// 1. Function to Add a New Venue/Slot Row
function addRow() {
    const tableBody = document.querySelector('#slotsTable tbody');
    const firstRow = document.querySelector('.slot-row');
    const newRow = firstRow.cloneNode(true);

    // Clear values in the new row
    newRow.querySelectorAll('input').forEach(input => input.value = '');
    newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
    
    // Reset validation status
    newRow.style.backgroundColor = '';
    
    tableBody.appendChild(newRow);
}

// 2. Function to Remove a Row
function removeRow(btn) {
    const rows = document.querySelectorAll('.slot-row');
    if (rows.length > 1) {
        btn.closest('tr').remove();
        calculateGrandTotal();
    } else {
        alert("At least one venue slot is required.");
    }
}

// 3. Real-time Availability & Conflict Checker
function checkAvailability(element) {
    const row = element.closest('tr');
    const venueId = row.querySelector('.venue-picker').value;
    const date = row.querySelector('.date-picker').value;
    const startTime = row.querySelector('input[name="slot_start[]"]').value;
    const endTime = row.querySelector('input[name="slot_end[]"]').value;

    // Only check if all primary fields are filled
    if (venueId && date && startTime && endTime) {
        fetch(`../api/check_availability.php?venue_id=${venueId}&date=${date}&start_time=${startTime}&finish_time=${endTime}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'available') {
                    row.style.backgroundColor = 'rgba(25, 135, 84, 0.1)'; // Light Green
                } else {
                    row.style.backgroundColor = 'rgba(220, 53, 69, 0.1)'; // Light Red
                    alert(`Alert: ${data.message}`);
                    if (data.status === 'blocked' || data.status === 'conflict') {
                        element.value = ''; // Reset the field that caused conflict
                    }
                }
            });
            
        // Also fetch rates for this venue if not already loaded
        loadRatesForVenue(row, venueId);
    }
}

// 4. Fetch Rates based on Venue Selection
function loadRatesForVenue(row, venueId) {
    const rateSelect = row.querySelector('.rate-picker');
    fetch(`../api/get_venue_rates.php?venue_id=${venueId}`)
        .then(response => response.json())
        .then(data => {
            rateSelect.innerHTML = '<option value="">-- Select Duration --</option>';
            data.forEach(rate => {
                rateSelect.innerHTML += `<option value="${rate.id}" data-m="${rate.member_rate}" data-nm="${rate.non_member_rate}" data-rsd="${rate.rsd_amount}">${rate.duration_label}</option>`;
            });
        });
}

// 5. Update Rent/RSD fields when Duration is selected
function fetchRateDetails(element) {
    const row = element.closest('tr');
    const selectedOption = element.options[element.selectedIndex];
    const isMember = document.getElementById('customer_select').dataset.isMember === '1'; // Logic to check member status
    
    if (selectedOption.value) {
        const rentValue = isMember ? selectedOption.dataset.m : selectedOption.dataset.nm;
        row.querySelector('.rent-amt').value = rentValue;
        row.querySelector('.rsd-amt').value = selectedOption.dataset.rsd;
        calculateGrandTotal();
    }
}

// 6. Automated Financial Calculations (Rent + GST + RSD)
function calculateGrandTotal() {
    let totalRent = 0;
    let totalRSD = 0;

    document.querySelectorAll('.rent-amt').forEach(el => totalRent += parseFloat(el.value || 0));
    document.querySelectorAll('.rsd-amt').forEach(el => totalRSD += parseFloat(el.value || 0));

    // Calculate Taxes (9% SGST + 9% CGST = 18% Total)
    const sgst = totalRent * 0.09;
    const cgst = totalRent * 0.09;
    const grandTotal = totalRent + sgst + cgst + totalRSD;

    // Update the UI
    document.getElementById('sumRent').innerText = totalRent.toFixed(2);
    document.getElementById('sumRSD').innerText = totalRSD.toFixed(2);
    document.getElementById('sumSGST').innerText = sgst.toFixed(2);
    document.getElementById('sumCGST').innerText = cgst.toFixed(2);
    document.getElementById('grandTotal').innerText = grandTotal.toFixed(2);
}
</script>

<!-- // JavaScript logic to:
// 1. addRow() / removeRow()
// 2. checkAvailability() via AJAX to api/check_availability.php
// 3. fetchRateDetails() via AJAX to pull from vms_venue_rates
// 4. updateTotals() to calculate GST and Grand Total -->

<?php include("../includes/footer.php"); ?>
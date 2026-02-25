<?php 
include("../includes/config.php"); 
$id = intval($_GET['id']);

// Fetch detailed Receipt, Booking, Enquiry, and Customer info
$sql = "SELECT r.*, b.tracking_no, b.function_name, e.tracking_no as enq_no, 
               c.contact_person, c.address 
        FROM vms_receipts r 
        JOIN vms_booking_master b ON r.booking_id = b.id 
        LEFT JOIN vms_enquiries e ON b.enquiry_id = e.id
        JOIN vms_customers c ON b.customer_id = c.id 
        WHERE r.id = $id";
$r = $conn->query($sql)->fetch_assoc();

// Fetch unique function dates
$date_res = $conn->query("SELECT DISTINCT booking_date FROM vms_booking_slots WHERE booking_id = ".$r['booking_id']." ORDER BY booking_date ASC");
$dates = [];
while($dt = $date_res->fetch_assoc()) { 
    $dates[] = date('d.m.Y', strtotime($dt['booking_date'])); 
}
$function_dates = implode(', ', $dates);

$is_rsd = ($r['amount_rsd'] > 0 && $r['amount_rent'] == 0);
$type_label = $is_rsd ? "REFUNDABLE SECURITY DEPOSIT (RSD)" : "ADVANCE RENT PAYMENT";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt - <?= $r['receipt_no'] ?></title>
    <style>
        @page { size: A5 landscape; margin: 5mm; }
        body { 
            font-family: 'Arial', sans-serif; 
            margin: 0; 
            padding: 5mm; 
            font-size: 12px; 
            line-height: 1.9; /* Reduced line height to prevent 2-page print */
        }
        .outer-border { 
            border: 2px solid #000; 
            padding: 8px; 
            height: 125mm; /* Locked height for A5 landscape */
            position: relative; 
            box-sizing: border-box;
        }
        .header { 
            display: flex; 
            align-items: center; 
            border-bottom: 2px solid #000; 
            padding-bottom: 5px; 
            margin-bottom: 8px; 
        }
        .logo-box { 
            width: 15%; /* Allots space for logo on the left */
            text-align: left;
        }
        .logo-box img { 
            max-height: 70px; /* Increased height since it's now side-by-side */
            width: auto; 
        }
        .org-details { 
            width: 70%; /* Centers the text in the remaining space */
            text-align: center;
        }
        .org-details h2 { 
            margin: 0; 
            text-transform: uppercase; 
            font-size: 17px; 
            line-height: 1.2; 
        }
        .org-details p { 
            margin: 0; 
            font-size: 10px; 
            line-height: 1.2; 
        }
        .spacer { 
            width: 15%; /* Empty spacer on the right to keep text perfectly centered */
        }

        .header { text-align: center; margin-bottom: 8px; }
        .header img { height: 40px; margin-bottom: 2px; } /* Organization Logo */
        .header h2 { margin: 0; text-transform: uppercase; font-size: 16px; line-height: 1.1; }
        .header p { margin: 1px 0; font-size: 10px; line-height: 1.1; }
        
        .title-box { text-align: center; margin: 5px 0; }
        .title-box span { border: 1px solid #000; padding: 2px 15px; background: #f9f9f9; font-weight: bold; font-size: 11px; }
        
        .receipt-head { display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 5px; }
        .receipt-body { margin-top: 5px; }
        .dotted-line { border-bottom: 1px dotted #000; font-weight: bold; padding: 0 4px; display: inline-block; }
        
        .footer-row { 
            position: absolute; 
            bottom: 10mm; 
            left: 8px; 
            right: 8px; 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-end; 
        }
        .amt-box { border: 2px solid #000; padding: 4px 12px; font-size: 15px; font-weight: bold; }
        .sig-area { text-align: center; width: 220px; font-size: 11px; }
    </style>
</head>
<body onload="window.print()">
    <div class="outer-border">

    <div class="header">
        <div class="logo-box">
            <img src="../assets/images/org_logo.png" alt="Logo">
        </div>
        
        <div class="org-details">
            <h2><?= $org_full_name ?></h2>
            <p><?= $org_address ?></p>
            <p>Tel: <?= $org_comm_phone ?> | Email: <?= $org_comm_email ?></p>
            <div class="title-box">
                <span><?= $type_label ?> RECEIPT</span>
            </div>
        </div>

        <div class="spacer"></div> </div>
    <!-- <div class="header">
            <img src="../assets/images/org_logo.png" alt="Logo"><br>
            <h2><?= $org_full_name ?></h2>
            <p><?= $org_address ?></p>
            <p>Tel: <?= $org_comm_phone ?> | Email: <?= $org_comm_email ?></p>
            <div class="title-box"><span><?= $type_label ?> RECEIPT</span></div>
        </div> -->

        <div class="receipt-head">
            <span>Recp.No.: <span class="text-primary"><?= $r['receipt_no'] ?></span></span>
            <span>Date: <?= date('d.m.Y', strtotime($r['receipt_date'])) ?></span>
        </div>

        <div class="receipt-body">
            Received with thanks from Shri/Smt: <span class="dotted-line" style="width: 70%;"><?= strtoupper($r['contact_person']) ?></span><br>
            Address: <span class="dotted-line" style="width: 89%;"><?= $r['address'] ?></span><br>
            The sum of Rupees: <span class="dotted-line" style="width: 82%;"><?= ucwords(getIndianCurrency($r['total_amount'])) ?></span><br>
            By <span class="dotted-line"><?= $r['payment_mode'] ?></span> No: <span class="dotted-line"><?= $r['instrument_no'] ?: 'CASH' ?></span> 
            Dated: <span class="dotted-line"><?= date('d.m.Y', strtotime($r['receipt_date'])) ?></span> 
            Bank: <span class="dotted-line"><?= $r['bank_name'] ?: '---' ?></span><br>
            As a deposit towards: <span class="dotted-line"><?= $r['function_name'] ?></span><br>
            Enquiry No: <span class="dotted-line"><?= $r['enq_no'] ?: 'Direct' ?></span> | 
            Booking ID: <span class="dotted-line"><?= $r['tracking_no'] ?></span> | 
            Function Date: <span class="dotted-line"><?= $function_dates ?></span>
        </div>

        <div class="footer-row">
            <div>
                <div class="amt-box">Rs. <?= number_format($r['total_amount'], 2) ?> /-</div>
                <div style="font-size: 9px; margin-top: 3px;">* (Cheque/Draft Subject to realization)</div>
            </div>
            <div class="sig-area">
                <strong>For <?= $org_full_name ?></strong>
                <br><br><br>
                <div style="border-top: 1px solid #000; padding-top: 2px;">Receiver's Signature</div>
            </div>
        </div>
    </div>
</body>
</html>
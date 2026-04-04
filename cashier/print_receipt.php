<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: dashboard.php?page=receipts');
    exit;
}

$payment_id = intval($_GET['id']);
$payment = $conn->query("
    SELECT p.*, s.student_number, s.firstname, s.lastname, s.year_level,
           c.code as course_code, c.name as course_name,
           su.first_name as cashier_first, su.last_name as cashier_last
    FROM payments p
    JOIN students s ON p.student_id = s.id
    LEFT JOIN courses c ON s.course_id = c.id
    LEFT JOIN system_users su ON p.received_by = su.id
    WHERE p.id = $payment_id
")->fetch_assoc();

if (!$payment) {
    echo '<div class="alert alert-danger">Payment not found.</div>';
    exit;
}

// Get total fees for student
$total_fees_row = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM student_fees WHERE student_id = {$payment['student_id']}")->fetch_assoc();
$total_fees = floatval($total_fees_row['total']);

// If no student_fees, fall back to tuition_fees
if ($total_fees == 0 && $payment['course_code']) {
    $tf = $conn->query("SELECT * FROM tuition_fees WHERE course_code = '{$payment['course_code']}' AND year_level = {$payment['year_level']} LIMIT 1")->fetch_assoc();
    if ($tf) {
        $total_fees = $tf['tuition_amount'] + $tf['miscellaneous_amount'] + $tf['laboratory_amount'] + $tf['other_fees'];
        $cf = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM course_fees WHERE course_code = '{$payment['course_code']}'")->fetch_assoc();
        $total_fees += $cf['total'];
    }
}

// Get all payments for this student
$all_payments = $conn->query("SELECT * FROM payments WHERE student_id = {$payment['student_id']} ORDER BY payment_date DESC");
$total_paid = 0;
$payments_list = [];
while ($p = $all_payments->fetch_assoc()) {
    $payments_list[] = $p;
    $total_paid += floatval($p['payment_amount']);
}

$balance = max(0, $total_fees - $total_paid);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Official Receipt - <?php echo htmlspecialchars($payment['or_number']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; max-width: 800px; margin: 0 auto; padding: 20px; }
        .receipt { border: 2px solid #333; padding: 25px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 1.4rem; }
        .header h2 { margin: 5px 0; font-size: 1rem; color: #555; }
        .title { text-align: center; font-size: 1.3rem; font-weight: bold; margin: 15px 0; text-transform: uppercase; }
        .info-table { width: 100%; margin: 15px 0; }
        .info-table td { padding: 5px; vertical-align: top; }
        .info-table .label { font-weight: bold; width: 140px; }
        .info-table .value { border-bottom: 1px solid #999; }
        .payment-box { margin: 15px 0; padding: 15px; background: #f8f9fa; border: 1px solid #ddd; }
        .payment-box h4 { margin: 0 0 10px 0; }
        .or-number { text-align: center; font-size: 1.5rem; font-weight: bold; color: #28a745; margin: 15px 0; padding: 10px; border: 2px dashed #28a745; }
        .footer { margin-top: 25px; text-align: center; font-size: 0.75rem; color: #888; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center; margin-bottom:20px;">
        <button onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="receipt">
        <div class="header">
            <h1>CJLG University</h1>
            <h2>Official Receipt</h2>
        </div>

        <div class="or-number">OR: <?php echo htmlspecialchars($payment['or_number']); ?></div>

        <table class="info-table">
            <tr>
                <td class="label">Student Name:</td>
                <td class="value"><?php echo htmlspecialchars($payment['firstname'] . ' ' . $payment['lastname']); ?></td>
            </tr>
            <tr>
                <td class="label">Student No:</td>
                <td class="value"><?php echo htmlspecialchars($payment['student_number']); ?></td>
            </tr>
            <tr>
                <td class="label">Course:</td>
                <td class="value"><?php echo htmlspecialchars(($payment['course_code'] ?? '-') . ' - Year ' . ($payment['year_level'] ?? '')); ?></td>
            </tr>
            <tr>
                <td class="label">Date:</td>
                <td class="value"><?php echo date('F j, Y', strtotime($payment['payment_date'])); ?></td>
            </tr>
        </table>

        <div class="payment-box">
            <h4>Payment Details</h4>
            <table class="info-table">
                <tr>
                    <td class="label">Total Tuition:</td>
                    <td class="value">P<?php echo number_format($total_fees, 2); ?></td>
                </tr>
                <tr>
                    <td class="label">Amount Paid:</td>
                    <td class="value"><strong>P<?php echo number_format($payment['payment_amount'], 2); ?></strong></td>
                </tr>
                <tr>
                    <td class="label">Total Paid (Cumulative):</td>
                    <td class="value"><strong>P<?php echo number_format($total_paid, 2); ?></strong></td>
                </tr>
                <tr>
                    <td class="label">Payment Method:</td>
                    <td class="value"><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                </tr>
                <tr>
                    <td class="label">Remaining Balance:</td>
                    <td class="value" style="color: <?php echo $balance > 0 ? '#dc3545' : '#28a745'; ?>">
                        <strong>P<?php echo number_format($balance, 2); ?></strong>
                    </td>
                </tr>
            </table>
        </div>

        <?php if (count($payments_list) > 1): ?>
        <div class="payment-box">
            <h4>Payment History</h4>
            <table style="width:100%; border-collapse: collapse;">
                <thead><tr style="border-bottom:1px solid #ddd;"><th style="padding:5px;">Date</th><th style="padding:5px;">OR Number</th><th style="padding:5px;text-align:right;">Amount</th></tr></thead>
                <tbody>
                    <?php foreach ($payments_list as $pp): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:5px;"><?php echo date('M d, Y', strtotime($pp['payment_date'])); ?></td>
                        <td style="padding:5px;"><?php echo htmlspecialchars($pp['or_number']); ?></td>
                        <td style="padding:5px;text-align:right;">P<?php echo number_format($pp['payment_amount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <table class="info-table" style="margin-top: 30px;">
            <tr>
                <td class="label">Cashier:</td>
                <td class="value"><?php echo htmlspecialchars(($payment['cashier_first'] ?? '') . ' ' . ($payment['cashier_last'] ?? '')); ?></td>
            </tr>
        </table>

        <div class="footer">
            <p>This is an official receipt issued by the Cashier's Office.</p>
            <p>Generated on: <?php echo date('F d, Y H:i:s'); ?></p>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>

<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: dashboard.php?page=permits');
    exit;
}

$permit_id = intval($_GET['id']);
$permit = $conn->query("
    SELECT p.*, s.student_number, s.firstname, s.lastname, s.year_level,
           c.code as course_code, c.name as course_name,
           su.first_name as issued_by_first, su.last_name as issued_by_last
    FROM permits p
    JOIN students s ON p.student_id = s.id
    LEFT JOIN courses c ON s.course_id = c.id
    LEFT JOIN system_users su ON p.issued_by = su.id
    WHERE p.id = $permit_id
")->fetch_assoc();

if (!$permit) {
    echo '<div class="alert alert-danger">Permit not found.</div>';
    exit;
}

// Get school info
$school = $conn->query("SELECT * FROM schools LIMIT 1")->fetch_assoc();
$school_name = $school ? $school['name'] : 'CJLG University';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Permit - <?php echo htmlspecialchars($permit['student_number']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .permit { border: 3px solid <?php echo $permit['status'] == 'Valid' ? '#28a745' : '#dc3545'; ?>; padding: 30px; position: relative; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 1.5rem; }
        .header h2 { margin: 5px 0; font-size: 1.2rem; color: #555; }
        .title { text-align: center; font-size: 1.8rem; font-weight: bold; margin: 20px 0; text-transform: uppercase; letter-spacing: 3px; }
        .status-badge { text-align: center; font-size: 2.5rem; font-weight: bold; padding: 15px; margin: 20px 0; border: 3px dashed; }
        .status-valid { color: #28a745; border-color: #28a745; background: #d4edda; }
        .status-invalid { color: #dc3545; border-color: #dc3545; background: #f8d7da; }
        .info-table { width: 100%; margin: 20px 0; }
        .info-table td { padding: 8px 5px; vertical-align: top; }
        .info-table .label { font-weight: bold; width: 180px; }
        .info-table .value { border-bottom: 1px solid #999; }
        .payment-summary { margin: 20px 0; padding: 15px; background: #f8f9fa; border: 1px solid #ddd; }
        .footer { margin-top: 30px; text-align: center; font-size: 0.8rem; color: #888; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 5rem; color: rgba(0,0,0,0.05); font-weight: bold; text-transform: uppercase; pointer-events: none; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center; margin-bottom:20px;">
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print Permit</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>

    <div class="permit">
        <?php if ($permit['status'] == 'Not Valid'): ?>
            <div class="watermark">NOT VALID</div>
        <?php endif; ?>

        <div class="header">
            <h1><?php echo htmlspecialchars($school_name); ?></h1>
            <h2>Official Enrollment Permit</h2>
        </div>

        <div class="title">Permit to Enroll</div>

        <table class="info-table">
            <tr>
                <td class="label">Student Name:</td>
                <td class="value"><?php echo htmlspecialchars($permit['firstname'] . ' ' . $permit['lastname']); ?></td>
                <td class="label">Student No:</td>
                <td class="value"><?php echo htmlspecialchars($permit['student_number']); ?></td>
            </tr>
            <tr>
                <td class="label">Course:</td>
                <td class="value"><?php echo htmlspecialchars(($permit['course_code'] ?? '-') . ' - ' . ($permit['course_name'] ?? '')); ?></td>
                <td class="label">Year Level:</td>
                <td class="value"><?php echo $permit['year_level']; ?></td>
            </tr>
            <tr>
                <td class="label">School Year:</td>
                <td class="value"><?php echo $permit['school_year']; ?></td>
                <td class="label">Term:</td>
                <td class="value"><?php echo $permit['term'] . ' - ' . $permit['period']; ?></td>
            </tr>
        </table>

        <div class="payment-summary">
            <h4>Payment Summary</h4>
            <table class="info-table">
                <tr>
                    <td class="label">Total Tuition:</td>
                    <td class="value">P<?php echo number_format($permit['total_tuition'], 2); ?></td>
                </tr>
                <tr>
                    <td class="label">Amount Due (<?php echo $permit['period']; ?>):</td>
                    <td class="value">P<?php echo number_format($permit['amount_due'], 2); ?></td>
                </tr>
                <tr>
                    <td class="label">Total Paid:</td>
                    <td class="value"><strong>P<?php echo number_format($permit['total_paid'], 2); ?></strong></td>
                </tr>
                <tr>
                    <td class="label">Balance:</td>
                    <td class="value" style="color: <?php echo ($permit['total_tuition'] - $permit['total_paid']) > 0 ? '#dc3545' : '#28a745'; ?>">
                        P<?php echo number_format($permit['total_tuition'] - $permit['total_paid'], 2); ?>
                    </td>
                </tr>
            </table>
        </div>

        <div class="status-badge <?php echo $permit['status'] == 'Valid' ? 'status-valid' : 'status-invalid'; ?>">
            <?php echo strtoupper($permit['status']); ?>
        </div>

        <table class="info-table" style="margin-top: 40px;">
            <tr>
                <td class="label">Issued By:</td>
                <td class="value"><?php echo htmlspecialchars(($permit['issued_by_first'] ?? '') . ' ' . ($permit['issued_by_last'] ?? '')); ?></td>
            </tr>
            <tr>
                <td class="label">Date Issued:</td>
                <td class="value"><?php echo date('F j, Y h:i A', strtotime($permit['issued_at'])); ?></td>
            </tr>
        </table>

        <div class="footer">
            <p>This permit is valid only for the term and period indicated above.</p>
            <p>Issued by the Finance Office - <?php echo htmlspecialchars($school_name); ?></p>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>

<?php
require_once '../config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$student = $conn->query("SELECT s.*, c.name as course_name FROM students s LEFT JOIN courses c ON s.course_code = c.code WHERE s.id = $student_id")->fetch_assoc();

$current_school_year = date('Y') . '-' . (date('Y') + 1);

$subjects = $conn->query("
    SELECT sub.* FROM subjects sub
    JOIN student_subjects ss ON sub.id = ss.subject_id
    WHERE ss.student_id = $student_id
    ORDER BY sub.semester, sub.subject_code
");

if ($subjects->num_rows == 0) {
    $subjects = $conn->query("
        SELECT * FROM subjects 
        WHERE course_code = '" . $student['course_code'] . "'
        AND school_year = '$current_school_year'
        ORDER BY semester, subject_code
    ");
}

$total_units = 0;
$subjects_array = [];
if ($subjects) {
    while ($row = $subjects->fetch_assoc()) {
        $subjects_array[] = $row;
        $total_units += $row['units'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Schedule - <?php echo htmlspecialchars($student['student_number']); ?></title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            font-size: 11px; 
            margin: 0; 
            padding: 20px;
            background: #fff;
        }
        .document {
            max-width: 210mm;
            margin: 0 auto;
            background: #fff;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 3px double #333; 
            padding-bottom: 15px;
        }
        .header h1 { 
            margin: 0; 
            font-size: 22px; 
            color: #333;
            letter-spacing: 2px;
        }
        .header h2 { 
            margin: 5px 0 0 0; 
            font-size: 14px; 
            font-weight: normal; 
            color: #666;
        }
        .header h3 {
            margin: 5px 0 0 0;
            font-size: 12px;
            font-weight: normal;
            color: #888;
        }
        .info-table { 
            width: 100%; 
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td { 
            padding: 8px 10px; 
            border: 1px solid #ddd;
        }
        .info-table td:first-child {
            width: 30%;
            font-weight: bold;
            background: #f5f5f5;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
        }
        th, td { 
            border: 1px solid #333; 
            padding: 8px;
        }
        th { 
            background-color: #2c3e50 !important; 
            color: white;
            text-align: left;
            font-weight: 600;
        }
        .text-center { text-align: center; }
        .footer { 
            margin-top: 30px; 
            text-align: center; 
            font-size: 10px; 
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 11px;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(0,0,0,0.03);
            pointer-events: none;
            z-index: -1;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>
    <div class="document">
        <div class="watermark">ENROLLMENT SYSTEM</div>
        
        <div class="header">
            <h1>OFFICIAL CLASS SCHEDULE</h1>
            <h2>Enrollment Management System</h2>
            <h3>Academic Year <?php echo $current_school_year; ?></h3>
        </div>
        
        <table class="info-table">
            <tr>
                <td>Student Name:</td>
                <td><?php echo htmlspecialchars($student['lastname'] . ', ' . $student['firstname'] . ' ' . ($student['middlename'] ? $student['middlename'] : '')); ?></td>
            </tr>
            <tr>
                <td>Student Number:</td>
                <td><?php echo htmlspecialchars($student['student_number']); ?></td>
            </tr>
            <tr>
                <td>Course:</td>
                <td><?php echo htmlspecialchars($student['course_code'] . ' - ' . ($student['course_name'] ?: 'N/A')); ?></td>
            </tr>
            <tr>
                <td>Year Level:</td>
                <td><?php echo htmlspecialchars($student['year_level']); ?> Year</td>
            </tr>
            <tr>
                <td>Semester:</td>
                <td>1st Semester</td>
            </tr>
            <tr>
                <td>Date Generated:</td>
                <td><?php echo date('F d, Y'); ?></td>
            </tr>
        </table>
        
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%">#</th>
                    <th style="width: 12%">Subject Code</th>
                    <th style="width: 25%">Description</th>
                    <th class="text-center" style="width: 7%">Units</th>
                    <th style="width: 18%">Schedule</th>
                    <th style="width: 13%">Room</th>
                    <th style="width: 20%">Instructor</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($subjects_array as $row): ?>
                <tr>
                    <td class="text-center"><?php echo $i++; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['subject_code']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td class="text-center"><?php echo $row['units']; ?></td>
                    <td><?php echo htmlspecialchars($row['schedule'] ?: 'TBA'); ?></td>
                    <td><?php echo htmlspecialchars($row['room'] ?: 'TBA'); ?></td>
                    <td><?php echo htmlspecialchars($row['instructor'] ?: 'TBA'); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($subjects_array)): ?>
                <tr>
                    <td colspan="7" class="text-center">No subjects enrolled</td>
                </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr style="background: #f5f5f5; font-weight: bold;">
                    <td colspan="3" class="text-center">TOTAL UNITS:</td>
                    <td class="text-center"><?php echo $total_units; ?></td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Student Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Registrar / Authorized Signature</div>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>This is an official document of the CJLG University.</strong></p>
            <p>Generated on: <?php echo date('F d, Y H:i:s'); ?> | Student Portal</p>
            <p class="no-print" style="margin-top: 10px;">
                <button onclick="window.print()" class="no-print btn btn-primary" style="padding: 10px 30px; font-size: 14px; cursor: pointer;">
                    <i class="fas fa-print"></i> Print Schedule
                </button>
            </p>
        </div>
    </div>
    
    <script>
        // Auto-trigger print dialog on load
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>

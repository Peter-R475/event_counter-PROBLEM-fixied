<?php
session_start();
require 'vendor/autoload.php';
include('connect.php');

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (isset($_GET['export']) && isset($_SESSION['event_name'])) {

    while (ob_get_level()) {
        ob_end_clean();
    }

    $eventName = $_SESSION['event_name'];
    $interval = $_GET['interval'] ?? 'hour';

    // Format in DB: DD:MM:YYYY:HH:MM:SS
    // Pos 1: DD, Pos 4: MM, Pos 7: YYYY, Pos 12: HH, Pos 15: MM
    if ($interval === 'minute') {
        $timeGroupExpr = "
            substr(`Time`, 7, 4) || '-' ||
            substr(`Time`, 4, 2) || '-' ||
            substr(`Time`, 1, 2) || ' ' ||
            substr(`Time`, 12, 2) || ':' ||
            substr(`Time`, 15, 2) || ':00'
        ";
    } else {
        $timeGroupExpr = "
            substr(`Time`, 7, 4) || '-' ||
            substr(`Time`, 4, 2) || '-' ||
            substr(`Time`, 1, 2) || ' ' ||
            substr(`Time`, 12, 2) || ':00:00'
        ";
    }

    $query = "SELECT 
                {$timeGroupExpr} AS `TimeGroup`, 
                AVG(`current_participants`) AS `avg_current`, 
                MAX(`total_participants`) AS `max_total` 
              FROM event_records 
              WHERE `event_name` = :event_name
              GROUP BY `TimeGroup`
              ORDER BY `TimeGroup` ASC";

    $stmt = $db_pdo->prepare($query);
    $stmt->bindParam(":event_name", $eventName, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query1 = "SELECT * 
               FROM event_info
               WHERE `name` = :event_name";

    $stmt1 = $db_pdo->prepare($query1);
    $stmt1->bindParam(":event_name", $eventName, PDO::PARAM_STR);
    $stmt1->execute();
    $data1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Safely construct title string with fallback
    $eventTitle = !empty($data1)
        ? "Event Name: " . $data1[0]['name'] . ' Event Location: ' . ($data1[0]['location'] . " Event Date:" . $data1[0]['date'] . " Event Time:" . $data1[0]['time'] . ' Description:' . $data1[0]['description'] . ' Staff:' . $data1[0]['staff_id'] ?? '')
        : $eventName;

    // Row 1: Merged title cell containing event info
    $sheet->mergeCells('A1:C1');
    $sheet->setCellValue('A1', $eventTitle);

    // Center align text horizontally and vertically in row 1
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

    // Row 2: Headers
    $sheet->setCellValue('A2', 'Time Interval');
    $sheet->setCellValue('B2', 'Avg Current Participants');
    $sheet->setCellValue('C2', 'Max Total Participants');
    $sheet->getStyle('A2:C2')->getFont()->setBold(true);

    // Row 3+: Data
    $row = 3;
    foreach ($data as $item) {
        $sheet->setCellValue('A' . $row, $item['TimeGroup']);
        $sheet->setCellValue('B' . $row, round((float) $item['avg_current'], 2));
        $sheet->setCellValue('C' . $row, (int) $item['max_total']);
        $row++;
    }

    // Auto-fit column widths
    foreach (range('A', 'C') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . rawurlencode($eventName) . '_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>
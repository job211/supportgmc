<?php

require_once '../vendor/autoload.php';
require_once '../includes/session.php';
require_once '../config/database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION["loggedin"]) || ($_SESSION["loggedin"] !== true || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'agent'))) {
    header("location: login.php");
    exit;
}

// Récupération des filtres depuis GET
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$service_filter = isset($_GET['service_id']) ? $_GET['service_id'] : '';
$type_filter = isset($_GET['type_id']) ? $_GET['type_id'] : '';
$user_filter = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$country_filter = isset($_GET['country_id']) ? $_GET['country_id'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$sql_base = "FROM tickets t JOIN users u ON t.created_by_id = u.id JOIN services s ON t.service_id = s.id LEFT JOIN users a ON t.assigned_to_id = a.id LEFT JOIN ticket_types tt ON t.type_id = tt.id LEFT JOIN countries c ON t.country_id = c.id";
$where_clauses = [];
$params = [];
$param_types = "";

if (!empty($search_term)) {
    $where_clauses[] = "t.title LIKE ?";
    $search_like = "%{$search_term}%";
    $params[] = $search_like;
    $param_types .= "s";
}
if (!empty($status_filter)) {
    $where_clauses[] = "t.status = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}
if (!empty($service_filter)) {
    $where_clauses[] = "t.service_id = ?";
    $params[] = $service_filter;
    $param_types .= "i";
}
if (!empty($type_filter)) {
    $where_clauses[] = "t.type_id = ?";
    $params[] = $type_filter;
    $param_types .= "i";
}
if (!empty($user_filter)) {
    $where_clauses[] = "t.created_by_id = ?";
    $params[] = $user_filter;
    $param_types .= "i";
}
if (!empty($country_filter)) {
    $where_clauses[] = "t.country_id = ?";
    $params[] = $country_filter;
    $param_types .= "i";
}
if (!empty($start_date)) {
    $where_clauses[] = "DATE(t.created_at) >= ?";
    $params[] = $start_date;
    $param_types .= "s";
}
if (!empty($end_date)) {
    $where_clauses[] = "DATE(t.created_at) <= ?";
    $params[] = $end_date;
    $param_types .= "s";
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

$sql = "SELECT t.id, t.title, t.description, t.status, t.priority, t.created_at, t.closed_at, u.username as creator, s.name as service, tt.name as type, c.name as country, a.username as assigned_user FROM tickets t JOIN users u ON t.created_by_id = u.id JOIN services s ON t.service_id = s.id LEFT JOIN users a ON t.assigned_to_id = a.id LEFT JOIN ticket_types tt ON t.type_id = tt.id LEFT JOIN countries c ON t.country_id = c.id $where_sql ORDER BY t.created_at DESC";

$stmt = mysqli_prepare($link, $sql);
if (!empty($param_types) && $stmt) {
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// En-têtes
$headers = [
    'ID Demande', 'Titre', 'Description', 'Statut', 'Priorité', 'Date Création', 'Date Fermeture', 'Demandeur', 'Service', 'Type', 'Pays', 'Responsable'
];
$col = 1;
foreach ($headers as $header) {
    $cell = $sheet->getCellByColumnAndRow($col, 1);
    $cell->setValue($header);
    $cell->getStyle()->getFont()->setBold(true);
    $cell->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE5E5E5');
    $col++;
}

$row = 2;
while ($ticket = mysqli_fetch_assoc($result)) {
    $sheet->setCellValueByColumnAndRow(1, $row, $ticket['id']);
    $sheet->setCellValueByColumnAndRow(2, $row, $ticket['title']);
    $sheet->setCellValueByColumnAndRow(3, $row, $ticket['description']);
    $sheet->setCellValueByColumnAndRow(4, $row, $ticket['status']);
    $sheet->setCellValueByColumnAndRow(5, $row, $ticket['priority']);
    $sheet->setCellValueByColumnAndRow(6, $row, $ticket['created_at']);
    $sheet->setCellValueByColumnAndRow(7, $row, $ticket['closed_at']);
    $sheet->setCellValueByColumnAndRow(8, $row, $ticket['creator']);
    $sheet->setCellValueByColumnAndRow(9, $row, $ticket['service']);
    $sheet->setCellValueByColumnAndRow(10, $row, $ticket['type']);
    $sheet->setCellValueByColumnAndRow(11, $row, $ticket['country']);
    $sheet->setCellValueByColumnAndRow(12, $row, $ticket['assigned_user']);
    $row++;
}

// Mise en forme automatique des colonnes
foreach (range('A', $sheet->getHighestColumn()) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Nom du fichier
$filename = 'tickets_export_' . date('Ymd_His') . '.xlsx';

// En-têtes HTTP pour téléchargement
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

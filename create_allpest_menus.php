<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

$pdo = new PDO('sqlite:' . __DIR__ . '/nuvis.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get Project ID for allpest_portal
$stmt = $pdo->query("SELECT prj_id FROM nu_projects WHERE prj_code = 'allpest_portal'");
$projectId = (int)$stmt->fetchColumn();

if (!function_exists('uuidv4')) {
    function uuidv4() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

// Clear existing menus for this project
$pdo->exec("DELETE FROM nu_menus WHERE project_id = $projectId");

$menus = [
    ['title' => 'Workspace', 'type' => 'form', 'target' => 'frm_dashboard', 'icon' => 'fas fa-th-large', 'order' => 1],
    ['title' => 'Job Orders', 'type' => 'form', 'target' => 'frm_job_orders', 'icon' => 'fas fa-clipboard-list', 'order' => 2],
    ['title' => 'Risk Assessment', 'type' => 'form', 'target' => 'frm_risk_assessment', 'icon' => 'fas fa-shield-alt', 'order' => 3],
    ['title' => 'Fumigation Records', 'type' => 'form', 'target' => 'frm_fumigation_logs', 'icon' => 'fas fa-smog', 'order' => 4],
    ['title' => 'Certificates', 'type' => 'form', 'target' => 'frm_certificates', 'icon' => 'fas fa-certificate', 'order' => 5],
    ['title' => 'Clients', 'type' => 'form', 'target' => 'frm_clients', 'icon' => 'fas fa-users', 'order' => 6],
    ['title' => 'Proposals', 'type' => 'form', 'target' => 'frm_proposals', 'icon' => 'fas fa-file-contract', 'order' => 7],
    ['title' => 'Inspection Reports', 'type' => 'form', 'target' => 'frm_inspection_reports', 'icon' => 'fas fa-bug', 'order' => 8],
    ['title' => 'Invoicing & Billing', 'type' => 'form', 'target' => 'frm_invoices', 'icon' => 'fas fa-file-invoice-dollar', 'order' => 9],
    ['title' => 'Service Schedules', 'type' => 'form', 'target' => 'frm_schedules', 'icon' => 'fas fa-calendar-alt', 'order' => 10],
    ['title' => 'Chemical Stock', 'type' => 'form', 'target' => 'frm_inventory', 'icon' => 'fas fa-flask', 'order' => 11],
    ['title' => 'User Management', 'type' => 'url', 'target' => '#users', 'icon' => 'fas fa-user-cog', 'order' => 12],
    ['title' => 'Audit Logs', 'type' => 'form', 'target' => 'frm_audit_logs', 'icon' => 'fas fa-history', 'order' => 13],
];

$stmt = $pdo->prepare("INSERT INTO nu_menus (mnu_id, project_id, mnu_title, mnu_type, mnu_target, mnu_icon, mnu_order) VALUES (?, ?, ?, ?, ?, ?, ?)");

foreach ($menus as $m) {
    $stmt->execute([
        uuidv4(),
        $projectId,
        $m['title'],
        $m['type'],
        $m['target'],
        $m['icon'],
        $m['order']
    ]);
}

echo "Registered " . count($menus) . " portal menus successfully!\n";

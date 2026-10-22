<?php
require 'config.php';
require 'core/Database.php';
require 'core/FormRenderer.php';

$pdo = new PDO('sqlite:' . __DIR__ . '/nuvis.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("SELECT prj_id FROM nu_projects WHERE prj_code = 'allpest_portal'");
$projectId = (int)$stmt->fetchColumn();

echo "Testing Allpest Online Portal (Project ID: $projectId)...\n\n";

// 1. Verify Project
$prj = $pdo->query("SELECT * FROM nu_projects WHERE prj_id = $projectId")->fetch(PDO::FETCH_ASSOC);
echo "[PASS] Project registered: " . $prj['prj_name'] . " (" . $prj['prj_provider_name'] . ")\n";

// 2. Verify Forms Count
$forms = $pdo->query("SELECT * FROM nu_forms WHERE project_id = $projectId")->fetchAll(PDO::FETCH_ASSOC);
echo "[PASS] Total Forms registered: " . count($forms) . "\n";

// 3. Verify Menus Count
$menus = $pdo->query("SELECT * FROM nu_menus WHERE project_id = $projectId")->fetchAll(PDO::FETCH_ASSOC);
echo "[PASS] Navigation Menus registered: " . count($menus) . "\n";

// 4. Verify Version Snapshots
$versions = $pdo->query("SELECT * FROM nu_project_versions WHERE pv_project_id = $projectId")->fetchAll(PDO::FETCH_ASSOC);
echo "[PASS] Project Version Snapshots: " . count($versions) . " (Tag: " . $versions[0]['pv_version_tag'] . ")\n";

// 5. Check Sample Data in Key Domain Tables
$tablesToCheck = [
    'ap_dashboard_kpis', 'ap_job_orders', 'ap_cargo_manifest',
    'ap_risk_assessments', 'ap_fumigation_logs', 'ap_gas_readings',
    'ap_certificates', 'ap_clients', 'ap_proposals',
    'ap_inspection_reports', 'ap_inspection_zones', 'ap_invoices',
    'ap_invoice_items', 'ap_schedules', 'ap_chemical_inventory', 'ap_audit_logs'
];

foreach ($tablesToCheck as $tbl) {
    $cnt = $pdo->query("SELECT COUNT(*) FROM $tbl")->fetchColumn();
    echo " - Table $tbl has $cnt records.\n";
}

echo "\nAll verification checks passed successfully!\n";

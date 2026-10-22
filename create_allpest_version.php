<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

$pdo = new PDO('sqlite:' . __DIR__ . '/nuvis.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get Project ID for allpest_portal
$stmt = $pdo->query("SELECT prj_id FROM nu_projects WHERE prj_code = 'allpest_portal'");
$projectId = (int)$stmt->fetchColumn();

// Gather project snapshot data
$snapshot = [
    'nu_projects' => $pdo->query("SELECT * FROM nu_projects WHERE prj_id = $projectId")->fetchAll(PDO::FETCH_ASSOC),
    'nu_forms' => $pdo->query("SELECT * FROM nu_forms WHERE project_id = $projectId")->fetchAll(PDO::FETCH_ASSOC),
    'nu_form_fields' => $pdo->query("SELECT * FROM nu_form_fields WHERE frm_id IN (SELECT frm_id FROM nu_forms WHERE project_id = $projectId)")->fetchAll(PDO::FETCH_ASSOC),
    'nu_menus' => $pdo->query("SELECT * FROM nu_menus WHERE project_id = $projectId")->fetchAll(PDO::FETCH_ASSOC),
];

// Record version snapshot v1.0.0
$verStmt = $pdo->prepare("INSERT INTO nu_project_versions (pv_project_id, pv_version_tag, pv_description, pv_snapshot_data, pv_created_by) VALUES (?, ?, ?, ?, ?)");
$verStmt->execute([
    $projectId,
    'v1.0.0',
    'Initial build of Allpest Online Portal prototype with all 12 modules, forms, subforms, lookups, navigation menus, and sample biosecurity data.',
    json_encode($snapshot),
    'usr_admin'
]);

echo "Version snapshot v1.0.0 captured in nu_project_versions!\n";

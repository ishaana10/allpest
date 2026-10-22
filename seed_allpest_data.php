<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

$pdo = new PDO('sqlite:' . __DIR__ . '/nuvis.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

// Clear existing domain data
$tablesToClear = [
    'ap_dashboard_kpis', 'ap_job_orders', 'ap_cargo_manifest',
    'ap_risk_assessments', 'ap_fumigation_logs', 'ap_gas_readings',
    'ap_certificates', 'ap_clients', 'ap_proposals',
    'ap_inspection_reports', 'ap_inspection_zones', 'ap_invoices',
    'ap_invoice_items', 'ap_schedules', 'ap_chemical_inventory', 'ap_audit_logs'
];
foreach ($tablesToClear as $t) {
    $pdo->exec("DELETE FROM $t");
}

// 1. Dashboard KPIs
$kpis = [
    ['Total Directory / Accounts', '28', 'All active directory accounts sync\'d', 'Jobs'],
    ['Authorized Fumigators', '6 Certified', 'BAF / AFAS Accredited Officers', 'Fumigation'],
    ['Field Technicians', '11 Active', 'Stationed in Lautoka & Suva Ports', 'Jobs'],
    ['Client Exporters', '9 Exporters', 'Restricted Portal Access Only', 'Compliance'],
    ['Active Jobs', '14 Active', '4 Under Venting, 2 Scheduled Today', 'Jobs'],
    ['Compliance Rate', '99.4%', 'AFAS / BAF Live Compliance Audit', 'Compliance'],
];
$stmt = $pdo->prepare("INSERT INTO ap_dashboard_kpis (kpi_id, kpi_title, kpi_value, kpi_subtext, kpi_category) VALUES (?, ?, ?, ?, ?)");
foreach ($kpis as $k) {
    $stmt->execute([uuidv4(), $k[0], $k[1], $k[2], $k[3]]);
}

// 2. Clients
$clientId1 = uuidv4();
$clientId2 = uuidv4();
$clientStmt = $pdo->prepare("INSERT INTO ap_clients (client_id, client_name, exporter_code, contact_person, email, phone, port_location, address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$clientStmt->execute([$clientId1, 'Pacific Timber Exports Pte Ltd', 'EXP-FJ-8821', 'Ana Kumar', 'operations@pacific-timber.example', '+679 666 1234', 'Lautoka Wharf Station', '12 Wharf Road, Lautoka, Fiji', 'Active']);
$clientStmt->execute([$clientId2, 'Coral Coast Fresh Produce Ltd', 'EXP-FJ-9043', 'David Miller', 'export@coralcoastlogistics.example', '+679 330 5678', 'Suva Port Terminal', '45 Kings Road, Suva, Fiji', 'Active']);

// 3. Job Orders & Cargo Manifest
$jobId1 = uuidv4();
$jobStmt = $pdo->prepare("INSERT INTO ap_job_orders (job_id, job_number, client_name, client_exporter_code, port_of_origin, port_of_destination, vessel_aircraft, voyage_flight_no, target_pest, treatment_type, scheduled_date, status, assigned_technician, commodity_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$jobStmt->execute([
    $jobId1,
    'JOB-2026-0841',
    'Pacific Timber Exports Pte Ltd',
    'EXP-FJ-8821',
    'Lautoka Port, Fiji',
    'Port of Sydney, Australia',
    'MV Pacific Trader',
    'V.2026-12',
    'Wood Borers',
    'Fumigation (Methyl Bromide)',
    '2026-05-28',
    'In Progress',
    'Rajesh Sharma',
    '40ft Sawn Hardwood Timber Packages (AFAS Compliant Stacking)'
]);

$cargoStmt = $pdo->prepare("INSERT INTO ap_cargo_manifest (item_id, job_id, container_no, seal_no, cargo_type, volume_m3, weight_kg, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$cargoStmt->execute([uuidv4(), $jobId1, 'TGHU-884920-1', 'BAF-SEAL-9932', 'Sawn Timber', 68.50, 24000.00, 'Dosed']);
$cargoStmt->execute([uuidv4(), $jobId1, 'MSKU-102938-4', 'BAF-SEAL-9933', 'Sawn Timber', 65.00, 22500.00, 'Pending Dosing']);

// 4. Site Risk Assessment
$riskStmt = $pdo->prepare("INSERT INTO ap_risk_assessments (assessment_id, job_id, site_location, inspector_name, assessment_date, structural_integrity, ventilation_status, gas_leak_prevention, ppe_compliant, exclusion_zone_set, risk_level, approval_status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$riskStmt->execute([
    uuidv4(),
    $jobId1,
    'Lautoka Wharf Station Terminal B',
    'Savenaca Tubuna',
    '2026-05-28',
    'Safe',
    'Safe',
    'Safe',
    'Yes',
    'Yes',
    'Low',
    'Approved',
    'Target container tarp seals inspected with gas detector. All safety barriers and warning signage placed.'
]);

// 5. Fumigation Log & Gas Readings
$logId1 = uuidv4();
$fumiStmt = $pdo->prepare("INSERT INTO ap_fumigation_logs (log_id, job_id, fumigant_chemical, dosage_rate, exposure_period_hrs, initial_gas_conc, final_gas_conc, ambient_temp_c, ventilation_start_time, clearance_time, rof_status, baf_officer_name, baf_officer_license, digital_signature_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$fumiStmt->execute([
    $logId1,
    $jobId1,
    'Methyl Bromide (CH3Br)',
    '48 g/m³',
    24.00,
    '48.2 g/m³',
    '38.5 g/m³',
    28.5,
    '2026-05-28 08:00:00',
    '2026-05-29 08:00:00',
    'Clearance Issued',
    'Mere Naidu',
    'FUM-FJ-084',
    'SHA256:8f4b39e1-BAF-AUTH-LIVE'
]);

$gasStmt = $pdo->prepare("INSERT INTO ap_gas_readings (reading_id, log_id, reading_timestamp, sampling_location, target_conc_g_m3, measured_conc_g_m3, temp_c, passed) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$gasStmt->execute([uuidv4(), $logId1, '2026-05-28 09:00:00', 'Top Front Port', 48.00, 48.20, 28.5, 1]);
$gasStmt->execute([uuidv4(), $logId1, '2026-05-28 13:00:00', 'Bottom Rear Port', 48.00, 44.10, 29.1, 1]);
$gasStmt->execute([uuidv4(), $logId1, '2026-05-28 21:00:00', 'Center Port', 48.00, 40.50, 27.8, 1]);

// 6. Certificates
$certStmt = $pdo->prepare("INSERT INTO ap_certificates (cert_id, cert_number, job_id, cert_type, client_name, issue_date, expiry_date, baf_accreditation_code, status, pdf_signature_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$certStmt->execute([
    uuidv4(),
    'CERT-FUM-2026-084',
    $jobId1,
    'Official Fumigation Certificate',
    'Pacific Timber Exports Pte Ltd',
    '2026-05-28',
    '2026-11-28',
    'BAF-AFAS-FJ0025MB',
    'Active',
    'RSA-4096-Encrypted-Sig-Token'
]);

// 7. Proposals
$propStmt = $pdo->prepare("INSERT INTO ap_proposals (proposal_id, proposal_no, client_id, target_pest, proposed_chemical, service_frequency, estimated_cost, valid_until, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$propStmt->execute([
    uuidv4(),
    'PROP-2026-019',
    $clientId1,
    'Subterranean Termites',
    'Bifenthrin Soil Barrier Treatment',
    'Monthly Contract',
    4500.00,
    '2026-06-30',
    'Accepted'
]);

// 8. Inspection Reports & Zones
$reportId1 = uuidv4();
$repStmt = $pdo->prepare("INSERT INTO ap_inspection_reports (report_id, report_no, job_id, inspection_date, technician_name, overall_infestation_level, pest_findings, recommendations, next_reinspection_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$repStmt->execute([
    $reportId1,
    'RPT-2026-044',
    $jobId1,
    '2026-05-27',
    'Tevita Vosabalavu',
    'Low',
    'Minor activity detected near timber pallet storage bays.',
    'Apply spot treatment and maintain 1m clear boundary from boundary fences.',
    '2026-06-27'
]);

$zoneStmt = $pdo->prepare("INSERT INTO ap_inspection_zones (zone_id, report_id, zone_name, pest_type, activity_level, chemical_applied, qty_applied) VALUES (?, ?, ?, ?, ?, ?, ?)");
$zoneStmt->execute([uuidv4(), $reportId1, 'Bay 4 Storage Zone', 'Termites', 'Trace', 'Termidor SC', '5 Liters']);

// 9. Invoices & Line Items
$invId1 = uuidv4();
$invStmt = $pdo->prepare("INSERT INTO ap_invoices (invoice_id, invoice_no, job_id, client_name, issue_date, due_date, subtotal, vat_amount, total_amount, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$invStmt->execute([
    $invId1,
    'INV-2026-0391',
    $jobId1,
    'Pacific Timber Exports Pte Ltd',
    '2026-05-28',
    '2026-06-28',
    1200.00,
    180.00,
    1380.00,
    'Pending'
]);

$itemStmt = $pdo->prepare("INSERT INTO ap_invoice_items (item_id, invoice_id, description, quantity, unit_price, amount) VALUES (?, ?, ?, ?, ?, ?)");
$itemStmt->execute([uuidv4(), $invId1, 'Fumigation Service - 2x 40ft Containers (Methyl Bromide)', 2.00, 500.00, 1000.00]);
$itemStmt->execute([uuidv4(), $invId1, 'BAF Official Certificate & ROF Clearance Fee', 1.00, 200.00, 200.00]);

// 10. Schedules
$schedStmt = $pdo->prepare("INSERT INTO ap_schedules (schedule_id, job_id, title, assigned_tech, port_station, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$schedStmt->execute([
    uuidv4(),
    $jobId1,
    'Methyl Bromide Dosing & Gas Monitoring',
    'Rajesh Sharma',
    'Lautoka Wharf Station',
    '2026-05-28 08:00:00',
    '2026-05-28 12:00:00',
    'In Progress'
]);

// 11. Chemical Inventory
$invChemStmt = $pdo->prepare("INSERT INTO ap_chemical_inventory (item_id, chemical_name, active_ingredient, batch_number, quantity_on_hand, unit_of_measure, expiry_date, reorder_level, sds_document_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$invChemStmt->execute([uuidv4(), 'Methyl Bromide 98%', 'Methyl Bromide (CH3Br)', 'BATCH-MB-2026A', 45.00, '50kg Cylinders', '2028-12-31', 10.00, 'https://allpest.com.fj/sds/methyl-bromide.pdf']);
$invChemStmt->execute([uuidv4(), 'Phosphine QuickPhos', 'Aluminium Phosphide', 'BATCH-PH3-9912', 120.00, 'Kg', '2027-08-15', 25.00, 'https://allpest.com.fj/sds/phosphine.pdf']);

// 12. Audit Logs
$auditStmt = $pdo->prepare("INSERT INTO ap_audit_logs (log_id, user_name, action_type, entity_affected, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
$auditStmt->execute([uuidv4(), 'Mere Naidu', 'ROF_RELEASE', 'Certificates / ROF Clearance', 'Released ROF Clearance #CERT-FUM-2026-084 for Job #JOB-2026-0841', '192.168.1.105']);
$auditStmt->execute([uuidv4(), 'Rajesh Sharma', 'CREATE', 'Job Orders', 'Created Job Order #JOB-2026-0841 for Pacific Timber Exports', '192.168.1.112']);

echo "Seeded realistic sample data into Allpest Online Portal successfully!\n";

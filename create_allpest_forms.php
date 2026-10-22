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

// Clear existing form metadata for this project
$pdo->exec("DELETE FROM nu_forms WHERE project_id = $projectId");
$pdo->exec("DELETE FROM nu_form_fields WHERE frm_id IN (SELECT frm_id FROM nu_forms WHERE project_id = $projectId)");

// Forms metadata definitions
$forms = [
    [
        'id' => 'frm_dashboard',
        'title' => 'Dashboard & KPIs',
        'table' => 'ap_dashboard_kpis',
        'icon' => 'fas fa-chart-line',
        'fields' => [
            ['name' => 'kpi_title', 'label' => 'KPI Title', 'type' => 'text', 'req' => 1],
            ['name' => 'kpi_value', 'label' => 'Current Value', 'type' => 'text', 'req' => 1],
            ['name' => 'kpi_subtext', 'label' => 'Subtext / Status', 'type' => 'text', 'req' => 0],
            ['name' => 'kpi_category', 'label' => 'Category', 'type' => 'select', 'options' => 'Fumigation,Compliance,Invoicing,Jobs', 'req' => 1],
        ]
    ],
    [
        'id' => 'frm_job_orders',
        'title' => 'Job Orders Management',
        'table' => 'ap_job_orders',
        'icon' => 'fas fa-clipboard-list',
        'fields' => [
            ['name' => 'job_number', 'label' => 'Job Order #', 'type' => 'text', 'req' => 1],
            ['name' => 'client_name', 'label' => 'Client Name', 'type' => 'text', 'req' => 1],
            ['name' => 'client_exporter_code', 'label' => 'Exporter Code (BAF)', 'type' => 'text', 'req' => 0],
            ['name' => 'port_of_origin', 'label' => 'Port of Origin', 'type' => 'text', 'req' => 1],
            ['name' => 'port_of_destination', 'label' => 'Port of Destination', 'type' => 'text', 'req' => 1],
            ['name' => 'vessel_aircraft', 'label' => 'Vessel / Flight', 'type' => 'text', 'req' => 0],
            ['name' => 'voyage_flight_no', 'label' => 'Voyage / Flight #', 'type' => 'text', 'req' => 0],
            ['name' => 'target_pest', 'label' => 'Target Pest', 'type' => 'select', 'options' => 'Wood Borers,Termites,Rodents,Cockroaches,General Pests', 'req' => 1],
            ['name' => 'treatment_type', 'label' => 'Treatment Type', 'type' => 'select', 'options' => 'Fumigation (Methyl Bromide),Fumigation (PH3),Pest Spray,Baiting', 'req' => 1],
            ['name' => 'scheduled_date', 'label' => 'Scheduled Date', 'type' => 'date', 'req' => 1],
            ['name' => 'status', 'label' => 'Job Status', 'type' => 'select', 'options' => 'Scheduled,In Progress,Under Venting,ROF Released,Completed,Cancelled', 'req' => 1],
            ['name' => 'assigned_technician', 'label' => 'Assigned Technician', 'type' => 'text', 'req' => 1],
            ['name' => 'commodity_description', 'label' => 'Commodity Details', 'type' => 'textarea', 'req' => 0],
            ['name' => 'cargo_manifest_subform', 'label' => 'Containers & Cargo Manifest', 'type' => 'subform', 'subform_frm_id' => 'frm_cargo_manifest', 'subform_fk' => 'job_id', 'req' => 0],
        ]
    ],
    [
        'id' => 'frm_cargo_manifest',
        'title' => 'Cargo Container Manifest',
        'table' => 'ap_cargo_manifest',
        'icon' => 'fas fa-boxes',
        'fields' => [
            ['name' => 'container_no', 'label' => 'Container #', 'type' => 'text', 'req' => 1],
            ['name' => 'seal_no', 'label' => 'Seal #', 'type' => 'text', 'req' => 1],
            ['name' => 'cargo_type', 'label' => 'Cargo Type', 'type' => 'text', 'req' => 1],
            ['name' => 'volume_m3', 'label' => 'Volume (m³)', 'type' => 'number', 'req' => 1],
            ['name' => 'weight_kg', 'label' => 'Weight (kg)', 'type' => 'number', 'req' => 0],
            ['name' => 'status', 'label' => 'Container Status', 'type' => 'select', 'options' => 'Pending Dosing,Dosed,Vented,Cleared', 'req' => 1],
        ]
    ],
    [
        'id' => 'frm_risk_assessment',
        'title' => 'Site Risk Assessment',
        'table' => 'ap_risk_assessments',
        'icon' => 'fas fa-user-shield',
        'fields' => [
            ['name' => 'job_id', 'label' => 'Job Order ID', 'type' => 'lookup', 'options' => 'ap_job_orders,job_id,job_number', 'req' => 1],
            ['name' => 'site_location', 'label' => 'Site Location / Terminal', 'type' => 'text', 'req' => 1],
            ['name' => 'inspector_name', 'label' => 'Safety Inspector Name', 'type' => 'text', 'req' => 1],
            ['name' => 'assessment_date', 'label' => 'Assessment Date', 'type' => 'date', 'req' => 1],
            ['name' => 'structural_integrity', 'label' => 'Structural Integrity Verified', 'type' => 'select', 'options' => 'Safe,Risk Identified', 'req' => 1],
            ['name' => 'ventilation_status', 'label' => 'Ventilation Check Passed', 'type' => 'select', 'options' => 'Safe,Unsafe', 'req' => 1],
            ['name' => 'gas_leak_prevention', 'label' => 'Gas Leak Seals Intact', 'type' => 'select', 'options' => 'Safe,Leaking', 'req' => 1],
            ['name' => 'ppe_compliant', 'label' => 'Full PPE Worn', 'type' => 'select', 'options' => 'Yes,No', 'req' => 1],
            ['name' => 'exclusion_zone_set', 'label' => 'Exclusion Zone Barrier Set', 'type' => 'select', 'options' => 'Yes,No', 'req' => 1],
            ['name' => 'risk_level', 'label' => 'Overall Risk Level', 'type' => 'select', 'options' => 'Low,Medium,High', 'req' => 1],
            ['name' => 'approval_status', 'label' => 'Approval Status', 'type' => 'select', 'options' => 'Approved,Pending Clearance,Rejected', 'req' => 1],
            ['name' => 'notes', 'label' => 'Safety Inspection Notes', 'type' => 'textarea', 'req' => 0],
        ]
    ],
    [
        'id' => 'frm_fumigation_logs',
        'title' => 'Fumigation & ROF Clearance Log',
        'table' => 'ap_fumigation_logs',
        'icon' => 'fas fa-smog',
        'fields' => [
            ['name' => 'job_id', 'label' => 'Job Order ID', 'type' => 'lookup', 'options' => 'ap_job_orders,job_id,job_number', 'req' => 1],
            ['name' => 'fumigant_chemical', 'label' => 'Fumigant Chemical', 'type' => 'select', 'options' => 'Methyl Bromide (CH3Br),Phosphine (PH3),Sulfuryl Fluoride', 'req' => 1],
            ['name' => 'dosage_rate', 'label' => 'Dosage Rate (g/m³)', 'type' => 'text', 'req' => 1],
            ['name' => 'exposure_period_hrs', 'label' => 'Exposure Period (Hours)', 'type' => 'number', 'req' => 1],
            ['name' => 'initial_gas_conc', 'label' => 'Initial Gas Conc. (g/m³)', 'type' => 'text', 'req' => 1],
            ['name' => 'final_gas_conc', 'label' => 'Final Gas Conc. (g/m³)', 'type' => 'text', 'req' => 1],
            ['name' => 'ambient_temp_c', 'label' => 'Ambient Temp (°C)', 'type' => 'number', 'req' => 1],
            ['name' => 'ventilation_start_time', 'label' => 'Ventilation Start Time', 'type' => 'datetime', 'req' => 1],
            ['name' => 'clearance_time', 'label' => 'ROF Clearance Time', 'type' => 'datetime', 'req' => 1],
            ['name' => 'rof_status', 'label' => 'ROF Clearance Status', 'type' => 'select', 'options' => 'Pending Clearance,Clearance Issued,Failed Test', 'req' => 1],
            ['name' => 'baf_officer_name', 'label' => 'Authorized BAF Officer', 'type' => 'text', 'req' => 1],
            ['name' => 'baf_officer_license', 'label' => 'BAF Accreditation License #', 'type' => 'text', 'req' => 1],
            ['name' => 'digital_signature_token', 'label' => 'Digital Encrypted Sign Token', 'type' => 'text', 'req' => 0],
            ['name' => 'gas_readings_subform', 'label' => 'Gas Dosing Hourly Monitoring Log', 'type' => 'subform', 'subform_frm_id' => 'frm_gas_readings', 'subform_fk' => 'log_id', 'req' => 0],
        ]
    ],
    [
        'id' => 'frm_gas_readings',
        'title' => 'Gas Dosing Hourly Log',
        'table' => 'ap_gas_readings',
        'icon' => 'fas fa-vial',
        'fields' => [
            ['name' => 'reading_timestamp', 'label' => 'Reading Timestamp', 'type' => 'datetime', 'req' => 1],
            ['name' => 'sampling_location', 'label' => 'Sampling Point / Port', 'type' => 'text', 'req' => 1],
            ['name' => 'target_conc_g_m3', 'label' => 'Target Conc (g/m³)', 'type' => 'number', 'req' => 1],
            ['name' => 'measured_conc_g_m3', 'label' => 'Measured Conc (g/m³)', 'type' => 'number', 'req' => 1],
            ['name' => 'temp_c', 'label' => 'Temp (°C)', 'type' => 'number', 'req' => 1],
            ['name' => 'passed', 'label' => 'Reading Passed', 'type' => 'select', 'options' => '1:Yes,0:No', 'req' => 1],
        ]
    ],
    [
        'id' => 'frm_certificates',
        'title' => 'Certificates & Compliance',
        'table' => 'ap_certificates',
        'icon' => 'fas fa-certificate',
        'fields' => [
            ['name' => 'cert_number', 'label' => 'Certificate #', 'type' => 'text', 'req' => 1],
            ['name' => 'job_id', 'label' => 'Job Order ID', 'type' => 'lookup', 'options' => 'ap_job_orders,job_id,job_number', 'req' => 1],
            ['name' => 'cert_type', 'label' => 'Certificate Type', 'type' => 'select', 'options' => 'Official Fumigation Certificate,Release of Fumigant (ROF) Log,Pest Free Clearance', 'req' => 1],
            ['name' => 'client_name', 'label' => 'Client / Exporter Name', 'type' => 'text', 'req' => 1],
            ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'req' => 1],
            ['name' => 'expiry_date', 'label' => 'Validity Expiry Date', 'type' => 'date', 'req' => 0],
            ['name' => 'baf_accreditation_code', 'label' => 'BAF Accreditation Code', 'type' => 'text', 'req' => 1],
            ['name' => 'status', 'label' => 'Certificate Status', 'type' => 'select', 'options' => 'Active,Revoked,Expired', 'req' => 1],
            ['name' => 'pdf_signature_hash', 'label' => 'Digital Signature Hash', 'type' => 'text', 'req' => 0],
        ]
    ],
    [
        'id' => 'frm_clients',
        'title' => 'Client / Exporter Directory',
        'table' => 'ap_clients',
        'icon' => 'fas fa-address-book',
        'fields' => [
            ['name' => 'client_name', 'label' => 'Company Name', 'type' => 'text', 'req' => 1],
            ['name' => 'exporter_code', 'label' => 'BAF Exporter Code', 'type' => 'text', 'req' => 1],
            ['name' => 'contact_person', 'label' => 'Primary Contact', 'type' => 'text', 'req' => 1],
            ['name' => 'email', 'label' => 'Email Address', 'type' => 'text', 'req' => 1],
            ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'text', 'req' => 0],
            ['name' => 'port_location', 'label' => 'Port / Depot Station', 'type' => 'select', 'options' => 'Lautoka Wharf Station,Suva Port Terminal,Nadi Cargo Depot', 'req' => 1],
            ['name' => 'address', 'label' => 'Business Address', 'type' => 'textarea', 'req' => 0],
            ['name' => 'status', 'label' => 'Account Status', 'type' => 'select', 'options' => 'Active,Suspended,Pending BAF Sync', 'req' => 1],
        ]
    ],
    [
        'id' => 'frm_proposals',
        'title' => 'Treatment & Service Proposals',
        'table' => 'ap_proposals',
        'icon' => 'fas fa-file-contract',
        'fields' => [
            ['name' => 'proposal_no', 'label' => 'Proposal #', 'type' => 'text', 'req' => 1],
            ['name' => 'client_id', 'label' => 'Client Company', 'type' => 'lookup', 'options' => 'ap_clients,client_id,client_name', 'req' => 1],
            ['name' => 'target_pest', 'label' => 'Target Pest Species', 'type' => 'text', 'req' => 1],
            ['name' => 'proposed_chemical', 'label' => 'Proposed Chemical Treatment', 'type' => 'text', 'req' => 1],
            ['name' => 'service_frequency', 'label' => 'Service Frequency', 'type' => 'select', 'options' => 'One-time Export Job,Monthly Contract,Quarterly Audit', 'req' => 1],
            ['name' => 'estimated_cost', 'label' => 'Estimated Total (FJD $)', 'type' => 'number', 'req' => 1],
            ['name' => 'valid_until', 'label' => 'Proposal Valid Until', 'type' => 'date', 'req' => 1],
            ['name' => 'status', 'label' => 'Proposal Status', 'type' => 'select', 'options' => 'Draft,Sent to Client,Accepted,Declined', 'req' => 1],
        ]
    ],
    [
        'id' => 'frm_inspection_reports',
        'title' => 'Pest Inspection & Field Reports',
        'table' => 'ap_inspection_reports',
        'icon' => 'fas fa-bug',
        'fields' => [
            ['name' => 'report_no', 'label' => 'Report #', 'type' => 'text', 'req' => 1],
            ['name' => 'job_id', 'label' => 'Job Order ID', 'type' => 'lookup', 'options' => 'ap_job_orders,job_id,job_number', 'req' => 1],
            ['name' => 'inspection_date', 'label' => 'Field Inspection Date', 'type' => 'date', 'req' => 1],
            ['name' => 'technician_name', 'label' => 'Lead Field Technician', 'type' => 'text', 'req' => 1],
            ['name' => 'overall_infestation_level', 'label' => 'Infestation Severity', 'type' => 'select', 'options' => 'None Detected,Low,Moderate,High / Severe', 'req' => 1],
            ['name' => 'pest_findings', 'label' => 'Inspection Findings', 'type' => 'textarea', 'req' => 0],
            ['name' => 'recommendations', 'label' => 'Corrective Actions & Recommendations', 'type' => 'textarea', 'req' => 0],
            ['name' => 'next_reinspection_date', 'label' => 'Next Re-Inspection Date', 'type' => 'date', 'req' => 0],
            ['name' => 'inspection_zones_subform', 'label' => 'Facility Zones & Treatment Applied', 'type' => 'subform', 'subform_frm_id' => 'frm_inspection_zones', 'subform_fk' => 'report_id', 'req' => 0],
        ]
    ],
    [
        'id' => 'frm_inspection_zones',
        'title' => 'Inspection Zones & Treatments',
        'table' => 'ap_inspection_zones',
        'icon' => 'fas fa-map-marked-alt',
        'fields' => [
            ['name' => 'zone_name', 'label' => 'Zone / Room Name', 'type' => 'text', 'req' => 1],
            ['name' => 'pest_type', 'label' => 'Pest Identified', 'type' => 'text', 'req' => 1],
            ['name' => 'activity_level', 'label' => 'Activity Level', 'type' => 'select', 'options' => 'None,Trace,Active Nests', 'req' => 1],
            ['name' => 'chemical_applied', 'label' => 'Chemical / Bait Applied', 'type' => 'text', 'req' => 0],
            ['name' => 'qty_applied', 'label' => 'Quantity Applied', 'type' => 'text', 'req' => 0],
        ]
    ],
    [
        'id' => 'frm_invoices',
        'title' => 'Billing & Invoices',
        'table' => 'ap_invoices',
        'icon' => 'fas fa-file-invoice-dollar',
        'fields' => [
            ['name' => 'invoice_no', 'label' => 'Invoice #', 'type' => 'text', 'req' => 1],
            ['name' => 'job_id', 'label' => 'Job Order ID', 'type' => 'lookup', 'options' => 'ap_job_orders,job_id,job_number', 'req' => 1],
            ['name' => 'client_name', 'label' => 'Client Name', 'type' => 'text', 'req' => 1],
            ['name' => 'issue_date', 'label' => 'Invoice Date', 'type' => 'date', 'req' => 1],
            ['name' => 'due_date', 'label' => 'Payment Due Date', 'type' => 'date', 'req' => 1],
            ['name' => 'subtotal', 'label' => 'Subtotal (FJD $)', 'type' => 'number', 'req' => 1],
            ['name' => 'vat_amount', 'label' => 'VAT Tax 15% (FJD $)', 'type' => 'number', 'req' => 1],
            ['name' => 'total_amount', 'label' => 'Grand Total (FJD $)', 'type' => 'number', 'req' => 1],
            ['name' => 'payment_status', 'label' => 'Payment Status', 'type' => 'select', 'options' => 'Pending,Paid,Overdue,Partially Paid', 'req' => 1],
            ['name' => 'invoice_items_subform', 'label' => 'Invoice Detailed Line Items', 'type' => 'subform', 'subform_frm_id' => 'frm_invoice_items', 'subform_fk' => 'invoice_id', 'req' => 0],
        ]
    ],
    [
        'id' => 'frm_invoice_items',
        'title' => 'Invoice Line Items',
        'table' => 'ap_invoice_items',
        'icon' => 'fas fa-list',
        'fields' => [
            ['name' => 'description', 'label' => 'Service / Item Description', 'type' => 'text', 'req' => 1],
            ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'number', 'req' => 1],
            ['name' => 'unit_price', 'label' => 'Unit Price ($)', 'type' => 'number', 'req' => 1],
            ['name' => 'amount', 'label' => 'Total Amount ($)', 'type' => 'number', 'req' => 1],
        ]
    ],
    [
        'id' => 'frm_schedules',
        'title' => 'Service Schedules & Timetable',
        'table' => 'ap_schedules',
        'icon' => 'fas fa-calendar-alt',
        'fields' => [
            ['name' => 'title', 'label' => 'Schedule Event Title', 'type' => 'text', 'req' => 1],
            ['name' => 'job_id', 'label' => 'Associated Job Order', 'type' => 'lookup', 'options' => 'ap_job_orders,job_id,job_number', 'req' => 0],
            ['name' => 'assigned_tech', 'label' => 'Assigned Fumigator/Tech', 'type' => 'text', 'req' => 1],
            ['name' => 'port_station', 'label' => 'Port / Terminal Station', 'type' => 'select', 'options' => 'Lautoka Wharf Station,Suva Port Terminal,Nadi Cargo Depot', 'req' => 1],
            ['name' => 'start_time', 'label' => 'Start DateTime', 'type' => 'datetime', 'req' => 1],
            ['name' => 'end_time', 'label' => 'End DateTime', 'type' => 'datetime', 'req' => 1],
            ['name' => 'status', 'label' => 'Schedule Status', 'type' => 'select', 'options' => 'Scheduled,En Route,In Progress,Completed,Rescheduled', 'req' => 1],
        ]
    ],
    [
        'id' => 'frm_inventory',
        'title' => 'Chemical & Stock Inventory',
        'table' => 'ap_chemical_inventory',
        'icon' => 'fas fa-flask',
        'fields' => [
            ['name' => 'chemical_name', 'label' => 'Chemical Product Name', 'type' => 'text', 'req' => 1],
            ['name' => 'active_ingredient', 'label' => 'Active Ingredient', 'type' => 'text', 'req' => 1],
            ['name' => 'batch_number', 'label' => 'Batch / Lot Number', 'type' => 'text', 'req' => 1],
            ['name' => 'quantity_on_hand', 'label' => 'Stock Quantity', 'type' => 'number', 'req' => 1],
            ['name' => 'unit_of_measure', 'label' => 'Unit (Cylinders/Liters/Kg)', 'type' => 'select', 'options' => '50kg Cylinders,100kg Cylinders,Liters,Kg,Packs', 'req' => 1],
            ['name' => 'expiry_date', 'label' => 'Expiration Date', 'type' => 'date', 'req' => 1],
            ['name' => 'reorder_level', 'label' => 'Reorder Threshold', 'type' => 'number', 'req' => 1],
            ['name' => 'sds_document_url', 'label' => 'Safety Data Sheet (SDS) Link', 'type' => 'text', 'req' => 0],
        ]
    ],
    [
        'id' => 'frm_audit_logs',
        'title' => 'Compliance & System Audit Logs',
        'table' => 'ap_audit_logs',
        'icon' => 'fas fa-history',
        'fields' => [
            ['name' => 'user_name', 'label' => 'User Name', 'type' => 'text', 'req' => 1],
            ['name' => 'action_type', 'label' => 'Action Type', 'type' => 'select', 'options' => 'CREATE,UPDATE,DELETE,SIGN_OFF,ROF_RELEASE,LOGIN', 'req' => 1],
            ['name' => 'entity_affected', 'label' => 'Entity / Module Affected', 'type' => 'text', 'req' => 1],
            ['name' => 'details', 'label' => 'Action Details & Changes', 'type' => 'textarea', 'req' => 1],
            ['name' => 'ip_address', 'label' => 'IP Address', 'type' => 'text', 'req' => 0],
        ]
    ],
];

// Insert forms and fields
$formStmt = $pdo->prepare("INSERT INTO nu_forms (frm_id, project_id, frm_title, frm_table, frm_icon, frm_description) VALUES (?, ?, ?, ?, ?, ?)");
$fieldStmt = $pdo->prepare("INSERT INTO nu_form_fields (fld_id, frm_id, fld_name, fld_label, fld_type, fld_options, fld_required, fld_order, fld_subform_frm_id, fld_subform_fk) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($forms as $f) {
    $frmId = $f['id'];
    $formStmt->execute([$frmId, $projectId, $f['title'], $f['table'], $f['icon'], "Form for " . $f['title']]);

    $order = 1;
    foreach ($f['fields'] as $fld) {
        $fldId = uuidv4();
        $fieldStmt->execute([
            $fldId,
            $frmId,
            $fld['name'],
            $fld['label'],
            $fld['type'],
            $fld['options'] ?? null,
            $fld['req'] ?? 0,
            $order++,
            $fld['subform_frm_id'] ?? null,
            $fld['subform_fk'] ?? null
        ]);
    }
}

echo "Registered " . count($forms) . " forms and fields in Nuvis Studio!\n";

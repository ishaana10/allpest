<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

$pdo = new PDO('sqlite:' . __DIR__ . '/nuvis.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$domainTables = [
    // 1. Dashboard / Workspace KPIs
    "CREATE TABLE IF NOT EXISTS ap_dashboard_kpis (
        kpi_id VARCHAR(36) PRIMARY KEY,
        kpi_title VARCHAR(100),
        kpi_value VARCHAR(50),
        kpi_subtext VARCHAR(150),
        kpi_category VARCHAR(50),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // 2. Job Orders
    "CREATE TABLE IF NOT EXISTS ap_job_orders (
        job_id VARCHAR(36) PRIMARY KEY,
        job_number VARCHAR(50) UNIQUE,
        client_name VARCHAR(150),
        client_exporter_code VARCHAR(50),
        port_of_origin VARCHAR(100),
        port_of_destination VARCHAR(100),
        vessel_aircraft VARCHAR(100),
        voyage_flight_no VARCHAR(50),
        target_pest VARCHAR(100),
        commodity_description TEXT,
        treatment_type VARCHAR(100),
        scheduled_date DATE,
        status VARCHAR(50) DEFAULT 'Scheduled',
        assigned_technician VARCHAR(100),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // Container / Cargo Manifest (Subform for Job Orders)
    "CREATE TABLE IF NOT EXISTS ap_cargo_manifest (
        item_id VARCHAR(36) PRIMARY KEY,
        job_id VARCHAR(36),
        container_no VARCHAR(50),
        seal_no VARCHAR(50),
        cargo_type VARCHAR(100),
        volume_m3 DECIMAL(10,2),
        weight_kg DECIMAL(10,2),
        status VARCHAR(50) DEFAULT 'Pending Dosing',
        FOREIGN KEY (job_id) REFERENCES ap_job_orders(job_id) ON DELETE CASCADE
    )",

    // 3. Site Risk Assessment
    "CREATE TABLE IF NOT EXISTS ap_risk_assessments (
        assessment_id VARCHAR(36) PRIMARY KEY,
        job_id VARCHAR(36),
        site_location VARCHAR(150),
        inspector_name VARCHAR(100),
        assessment_date DATE,
        structural_integrity VARCHAR(20),
        ventilation_status VARCHAR(20),
        gas_leak_prevention VARCHAR(20),
        ppe_compliant VARCHAR(20),
        exclusion_zone_set VARCHAR(20),
        risk_level VARCHAR(20) DEFAULT 'Low',
        approval_status VARCHAR(50) DEFAULT 'Approved',
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // 4. Fumigation & ROF Clearance Logs
    "CREATE TABLE IF NOT EXISTS ap_fumigation_logs (
        log_id VARCHAR(36) PRIMARY KEY,
        job_id VARCHAR(36),
        fumigant_chemical VARCHAR(100),
        dosage_rate VARCHAR(50),
        exposure_period_hrs DECIMAL(5,2),
        initial_gas_conc VARCHAR(50),
        final_gas_conc VARCHAR(50),
        ambient_temp_c DECIMAL(5,2),
        ventilation_start_time DATETIME,
        clearance_time DATETIME,
        rof_status VARCHAR(50) DEFAULT 'Pending Clearance',
        baf_officer_name VARCHAR(100),
        baf_officer_license VARCHAR(100),
        digital_signature_token VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // Gas Dosing Readings (Subform for Fumigation Logs)
    "CREATE TABLE IF NOT EXISTS ap_gas_readings (
        reading_id VARCHAR(36) PRIMARY KEY,
        log_id VARCHAR(36),
        reading_timestamp DATETIME,
        sampling_location VARCHAR(100),
        target_conc_g_m3 DECIMAL(8,2),
        measured_conc_g_m3 DECIMAL(8,2),
        temp_c DECIMAL(5,2),
        passed INTEGER DEFAULT 1,
        FOREIGN KEY (log_id) REFERENCES ap_fumigation_logs(log_id) ON DELETE CASCADE
    )",

    // 5. Certificates & Compliance
    "CREATE TABLE IF NOT EXISTS ap_certificates (
        cert_id VARCHAR(36) PRIMARY KEY,
        cert_number VARCHAR(50) UNIQUE,
        job_id VARCHAR(36),
        cert_type VARCHAR(100),
        client_name VARCHAR(150),
        issue_date DATE,
        expiry_date DATE,
        baf_accreditation_code VARCHAR(100),
        status VARCHAR(50) DEFAULT 'Active',
        pdf_signature_hash VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // 6. Client / Exporter Directory
    "CREATE TABLE IF NOT EXISTS ap_clients (
        client_id VARCHAR(36) PRIMARY KEY,
        client_name VARCHAR(150),
        exporter_code VARCHAR(50) UNIQUE,
        contact_person VARCHAR(100),
        email VARCHAR(150),
        phone VARCHAR(50),
        address TEXT,
        port_location VARCHAR(100),
        status VARCHAR(20) DEFAULT 'Active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // 7. Treatment & Service Proposals
    "CREATE TABLE IF NOT EXISTS ap_proposals (
        proposal_id VARCHAR(36) PRIMARY KEY,
        proposal_no VARCHAR(50) UNIQUE,
        client_id VARCHAR(36),
        target_pest VARCHAR(100),
        proposed_chemical VARCHAR(100),
        service_frequency VARCHAR(50),
        estimated_cost DECIMAL(12,2),
        status VARCHAR(50) DEFAULT 'Draft',
        valid_until DATE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // 8. Pest Inspection & Treatment Reports
    "CREATE TABLE IF NOT EXISTS ap_inspection_reports (
        report_id VARCHAR(36) PRIMARY KEY,
        report_no VARCHAR(50) UNIQUE,
        job_id VARCHAR(36),
        inspection_date DATE,
        technician_name VARCHAR(100),
        pest_findings TEXT,
        overall_infestation_level VARCHAR(50),
        recommendations TEXT,
        next_reinspection_date DATE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // Field Inspection Matrix (Subform for Inspection Reports)
    "CREATE TABLE IF NOT EXISTS ap_inspection_zones (
        zone_id VARCHAR(36) PRIMARY KEY,
        report_id VARCHAR(36),
        zone_name VARCHAR(100),
        pest_type VARCHAR(100),
        activity_level VARCHAR(50),
        chemical_applied VARCHAR(100),
        qty_applied VARCHAR(50),
        FOREIGN KEY (report_id) REFERENCES ap_inspection_reports(report_id) ON DELETE CASCADE
    )",

    // 9. Billing & Invoicing
    "CREATE TABLE IF NOT EXISTS ap_invoices (
        invoice_id VARCHAR(36) PRIMARY KEY,
        invoice_no VARCHAR(50) UNIQUE,
        job_id VARCHAR(36),
        client_name VARCHAR(150),
        issue_date DATE,
        due_date DATE,
        subtotal DECIMAL(12,2),
        vat_amount DECIMAL(12,2),
        total_amount DECIMAL(12,2),
        payment_status VARCHAR(50) DEFAULT 'Pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // Invoice Line Items (Subform for Invoicing)
    "CREATE TABLE IF NOT EXISTS ap_invoice_items (
        item_id VARCHAR(36) PRIMARY KEY,
        invoice_id VARCHAR(36),
        description TEXT,
        quantity DECIMAL(10,2),
        unit_price DECIMAL(10,2),
        amount DECIMAL(12,2),
        FOREIGN KEY (invoice_id) REFERENCES ap_invoices(invoice_id) ON DELETE CASCADE
    )",

    // 10. Service Schedules & Maintenance Calendar
    "CREATE TABLE IF NOT EXISTS ap_schedules (
        schedule_id VARCHAR(36) PRIMARY KEY,
        job_id VARCHAR(36),
        title VARCHAR(150),
        assigned_tech VARCHAR(100),
        start_time DATETIME,
        end_time DATETIME,
        status VARCHAR(50) DEFAULT 'Scheduled',
        port_station VARCHAR(100),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // 11. Inventory & Chemical Stock
    "CREATE TABLE IF NOT EXISTS ap_chemical_inventory (
        item_id VARCHAR(36) PRIMARY KEY,
        chemical_name VARCHAR(100),
        active_ingredient VARCHAR(100),
        batch_number VARCHAR(50),
        quantity_on_hand DECIMAL(10,2),
        unit_of_measure VARCHAR(20),
        expiry_date DATE,
        reorder_level DECIMAL(10,2),
        sds_document_url VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // 12. Settings & Compliance Audit Trail
    "CREATE TABLE IF NOT EXISTS ap_audit_logs (
        log_id VARCHAR(36) PRIMARY KEY,
        user_name VARCHAR(100),
        action_type VARCHAR(50),
        entity_affected VARCHAR(100),
        details TEXT,
        ip_address VARCHAR(50),
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($domainTables as $sql) {
    $pdo->exec($sql);
}

// Register project in nu_projects if not exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM nu_projects WHERE prj_code = 'allpest_portal'");
$stmt->execute();
if ($stmt->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO nu_projects (prj_name, prj_code, prj_description, prj_provider_name, prj_provider_id)
        VALUES ('All pest Online Portal', 'allpest_portal', 'Complete Pest Control & Biosecurity Service Management System for Fiji Ports & Exporters', 'Allpest & Hygiene Solutions Pte Ltd', 'FJ0025MB')");
}

echo "Allpest Online Portal schema created and registered successfully.\n";

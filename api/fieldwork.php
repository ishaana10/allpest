<?php
/**
 * api/fieldwork.php - API Handler for Fieldwork Fumigation Manager
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../core/Database.php';
    require_once __DIR__ . '/../core/Auth.php';

    $auth = NuAuth::getInstance();
    if (!$auth->checkAuth()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    $db = NuDatabase::getInstance();
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    if ($action === 'list_jobs') {
        $status = trim((string)($_GET['status'] ?? ''));
        $search = trim((string)($_GET['search'] ?? ''));

        $sql = "SELECT j.*, r.record_ref, r.status AS record_status
                FROM fw_job_orders j
                LEFT JOIN fw_fumigation_records r ON j.job_id = r.job_id WHERE 1=1";
        $params = [];

        if ($status !== '' && strtolower($status) !== 'all' && strtolower($status) !== 'all jobs') {
            $sql .= " AND j.status = ?";
            $params[] = $status;
        }

        if ($search !== '') {
            $sql .= " AND (j.job_ref LIKE ? OR j.client_name LIKE ? OR j.container_number LIKE ?)";
            $term = "%$search%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY j.job_id DESC";
        $jobs = $db->fetchAll($sql, $params);

        // Calculate KPIs
        $kpis = [
            'total' => $db->fetchOne("SELECT COUNT(*) as c FROM fw_job_orders")['c'] ?? 0,
            'draft' => $db->fetchOne("SELECT COUNT(*) as c FROM fw_job_orders WHERE status = 'Draft'")['c'] ?? 0,
            'in_progress' => $db->fetchOne("SELECT COUNT(*) as c FROM fw_job_orders WHERE status = 'In progress'")['c'] ?? 0,
            'successful' => $db->fetchOne("SELECT COUNT(*) as c FROM fw_job_orders WHERE status = 'Successful'")['c'] ?? 0,
            'unsuccessful' => $db->fetchOne("SELECT COUNT(*) as c FROM fw_job_orders WHERE status = 'Unsuccessful'")['c'] ?? 0,
        ];

        echo json_encode(['success' => true, 'jobs' => $jobs, 'kpis' => $kpis]);
        exit;
    }

    if ($action === 'get_job') {
        $jobId = (int)($_GET['id'] ?? 0);
        $job = $db->fetchOne("SELECT * FROM fw_job_orders WHERE job_id = ?", [$jobId]);
        if (!$job) {
            echo json_encode(['success' => false, 'error' => 'Job order not found']);
            exit;
        }
        $record = $db->fetchOne("SELECT * FROM fw_fumigation_records WHERE job_id = ?", [$jobId]);
        $cert = $db->fetchOne("SELECT * FROM fw_certificates WHERE job_id = ?", [$jobId]);

        echo json_encode([
            'success' => true,
            'job' => $job,
            'record' => $record,
            'certificate' => $cert
        ]);
        exit;
    }

    if ($action === 'create_job') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $nextId = ((int)($db->fetchOne("SELECT MAX(job_id) as m FROM fw_job_orders")['m'] ?? 0)) + 1;
        $jobRef = 'JO-2026-' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);

        $db->insert('fw_job_orders', [
            'job_ref' => $jobRef,
            'treatment_provider' => $data['treatment_provider'] ?? 'Allpest and hygiene solutions Pte ltd',
            'provider_id' => $data['provider_id'] ?? 'FJ0025MB',
            'planned_treatment_date' => $data['planned_treatment_date'] ?? date('Y-m-d'),
            'client_name' => $data['client_name'] ?? '',
            'contact_person' => $data['contact_person'] ?? '',
            'contact_email' => $data['contact_email'] ?? '',
            'client_details' => $data['client_details'] ?? '',
            'street_address' => $data['street_address'] ?? '',
            'suburb_city' => $data['suburb_city'] ?? '',
            'country' => $data['country'] ?? 'Fiji',
            'postcode' => $data['postcode'] ?? '—',
            'consignment_desc' => $data['consignment_desc'] ?? '',
            'target_fumigation' => $data['target_fumigation'] ?? '',
            'container_number' => $data['container_number'] ?? '',
            'status' => 'Draft'
        ]);

        $createdJob = $db->fetchOne("SELECT * FROM fw_job_orders WHERE job_ref = ?", [$jobRef]);
        $recordRef = 'RF-2026-' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);

        $db->insert('fw_fumigation_records', [
            'record_ref' => $recordRef,
            'job_id' => $createdJob['job_id'],
            'job_ref' => $jobRef,
            'fumigator_name' => 'Mere Naidu',
            'accreditation_number' => '—',
            'status' => 'Draft'
        ]);

        echo json_encode(['success' => true, 'job_id' => $createdJob['job_id'], 'job_ref' => $jobRef]);
        exit;
    }

    if ($action === 'save_record') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $recordId = (int)($data['record_id'] ?? 0);

        if (!$recordId) {
            echo json_encode(['success' => false, 'error' => 'Record ID required']);
            exit;
        }

        $fields = [
            'fumigator_name' => $data['fumigator_name'] ?? 'Mere Naidu',
            'accreditation_number' => $data['accreditation_number'] ?? '—',
            'specified_treatment_schedule' => $data['specified_treatment_schedule'] ?? '',
            'dose_rate' => $data['dose_rate'] ?? '',
            'exposure_period' => $data['exposure_period'] ?? '',
            'consignment_suitable' => $data['consignment_suitable'] ?? 'Yes',
            'remedial_action' => $data['remedial_action'] ?? '',
            'fumigation_type' => $data['fumigation_type'] ?? 'Ambient temperature',
            'forecast_min_temp' => $data['forecast_min_temp'] ?? '',
            'min_enclosure_temp' => $data['min_enclosure_temp'] ?? '',
            'temp_unit' => $data['temp_unit'] ?? '°C',
            'heaters_used' => $data['heaters_used'] ?? 'No',
            'enclosure_type' => $data['enclosure_type'] ?? 'Sheeted enclosure',
            'other_enclosure_type' => $data['other_enclosure_type'] ?? '',
            'total_volume_m3' => $data['total_volume_m3'] ?? '',
            'dose_rate_used' => $data['dose_rate_used'] ?? '',
            'calculated_dose_g' => $data['calculated_dose_g'] ?? '',
            'amount_ch3br_applied_g' => $data['amount_ch3br_applied_g'] ?? '',
            'device_serial' => $data['device_serial'] ?? '',
            'other_device_serial' => $data['other_device_serial'] ?? '',
            'readings_json' => is_array($data['readings_json'] ?? null) ? json_encode($data['readings_json']) : ($data['readings_json'] ?? ''),
            'declaration_accepted' => !empty($data['declaration_accepted']) ? 1 : 0,
            'declaration_date' => $data['declaration_date'] ?? date('d/m/Y'),
            'status' => !empty($data['submit_review']) ? 'Completed' : 'Draft'
        ];

        $db->update('fw_fumigation_records', $fields, 'record_id = ?', [$recordId]);

        if (!empty($data['submit_review'])) {
            $record = $db->fetchOne("SELECT job_id FROM fw_fumigation_records WHERE record_id = ?", [$recordId]);
            if ($record) {
                $db->update('fw_job_orders', ['status' => 'In progress'], 'job_id = ?', [$record['job_id']]);
            }
        }

        echo json_encode(['success' => true, 'message' => 'Fumigation record saved successfully']);
        exit;
    }

    if ($action === 'record_decision') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $jobId = (int)($data['job_id'] ?? 0);
        $decision = $data['decision'] ?? 'Successful';

        if (!$jobId) {
            echo json_encode(['success' => false, 'error' => 'Job ID required']);
            exit;
        }

        $db->update('fw_job_orders', [
            'status' => $decision,
            'decision_by' => 'Mere Naidu',
            'decision_role' => 'Authorized fumigator-In-charge',
            'decision_timestamp' => date('Y-m-d H:i:s'),
            'accreditation_number' => '—'
        ], 'job_id = ?', [$jobId]);

        if ($decision === 'Successful') {
            $job = $db->fetchOne("SELECT * FROM fw_job_orders WHERE job_id = ?", [$jobId]);
            $record = $db->fetchOne("SELECT * FROM fw_fumigation_records WHERE job_id = ?", [$jobId]);
            $existingCert = $db->fetchOne("SELECT * FROM fw_certificates WHERE job_id = ?", [$jobId]);

            if (!$existingCert && $job && $record) {
                $db->insert('fw_certificates', [
                    'cert_ref' => 'PREVIEW-' . $job['job_ref'],
                    'job_id' => $jobId,
                    'job_ref' => $job['job_ref'],
                    'record_ref' => $record['record_ref'],
                    'status' => 'Preview',
                    'issue_date' => date('Y-m-d')
                ]);
            }
        }

        echo json_encode(['success' => true, 'status' => $decision]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Invalid action']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

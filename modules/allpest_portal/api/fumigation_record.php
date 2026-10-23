<?php
declare(strict_types=1);
/**
 * modules/allpest_portal/api/fumigation_record.php
 * REST API for Methyl Bromide Record of Fumigation & Concentration Monitoring Readings.
 */

if (!defined('NU_ROOT')) {
    define('NU_ROOT', dirname(__DIR__, 3));
}

require_once NU_ROOT . '/config.php';
require_once NU_ROOT . '/core/Database.php';

header('Content-Type: application/json; charset=utf-8');

$db = NuDatabase::getInstance();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

function fum_json(array $data): void {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Read JSON POST payload
$rawInput = file_get_contents('php://input');
$body = json_decode($rawInput, true);
if (!is_array($body)) {
    $body = $_POST;
}

try {
    // ── 1. LIST ORDERS (for Order Picker) ──────────────────────────────────
    if ($action === 'list_orders') {
        $search = trim((string)($_GET['q'] ?? ''));
        $sql = "SELECT job_id, job_number, client_name, client_exporter_code, port_of_origin, port_of_destination, target_pest, treatment_type, status, commodity_description FROM ap_job_orders";
        $params = [];
        if ($search !== '') {
            $sql .= " WHERE job_number LIKE :q OR client_name LIKE :q OR commodity_description LIKE :q";
            $params[':q'] = '%' . $search . '%';
        }
        $sql .= " ORDER BY created_at DESC LIMIT 20";
        $orders = $db->fetchAll($sql, $params) ?? [];
        fum_json(['success' => true, 'orders' => $orders]);
    }

    // ── 2. GET ORDER DETAILS ──────────────────────────────────────────────
    if ($action === 'get_order') {
        $jobId = trim((string)($_GET['job_id'] ?? $body['job_id'] ?? ''));
        if (!$jobId) {
            fum_json(['success' => false, 'message' => 'Job Order ID is required']);
        }
        $order = $db->fetchOne("SELECT * FROM ap_job_orders WHERE job_id = :job_id LIMIT 1", [':job_id' => $jobId]);
        if (!$order) {
            fum_json(['success' => false, 'message' => 'Job Order not found']);
        }
        fum_json(['success' => true, 'order' => $order]);
    }

    // ── 3. LIST RECORDS OF FUMIGATION ────────────────────────────────────
    if ($action === 'list_records') {
        $records = $db->fetchAll("SELECT r.*, j.job_number as linked_job_number FROM ap_fumigation_records r LEFT JOIN ap_job_orders j ON r.job_id = j.job_id ORDER BY r.created_at DESC") ?? [];
        fum_json(['success' => true, 'records' => $records]);
    }

    // ── 4. GET SINGLE RECORD OF FUMIGATION WITH READINGS ─────────────────
    if ($action === 'get_record') {
        $recordId = trim((string)($_GET['record_id'] ?? ''));
        if (!$recordId) {
            fum_json(['success' => false, 'message' => 'Record ID required']);
        }
        $record = $db->fetchOne("SELECT * FROM ap_fumigation_records WHERE record_id = :id LIMIT 1", [':id' => $recordId]);
        if (!$record) {
            fum_json(['success' => false, 'message' => 'Record of Fumigation not found']);
        }
        $readings = $db->fetchAll("SELECT * FROM ap_fumigation_readings WHERE record_id = :id ORDER BY reading_date ASC, reading_time ASC, created_at ASC", [':id' => $recordId]) ?? [];
        fum_json(['success' => true, 'record' => $record, 'readings' => $readings]);
    }

    // ── 5. SAVE / UPDATE RECORD OF FUMIGATION (SECTIONS A - E) ─────────────
    if ($action === 'save_record') {
        $recordId = trim((string)($body['record_id'] ?? ''));
        $isNew = false;
        if (!$recordId) {
            $recordId = 'fum_rec_' . uniqid();
            $isNew = true;
        }

        $fields = [
            'record_id'                  => $recordId,
            'job_id'                     => $body['job_id'] ?? null,
            'job_number'                 => $body['job_number'] ?? null,
            'client_name'                => $body['client_name'] ?? null,
            'treatment_provider_id'      => $body['treatment_provider_id'] ?? 'FJ0025MB',
            'fumigator_name'             => $body['fumigator_name'] ?? null,
            'fumigator_accreditation'    => $body['fumigator_accreditation'] ?? null,
            'street_address'             => $body['street_address'] ?? null,
            'suburb_town_city'           => $body['suburb_town_city'] ?? null,
            'postcode'                   => $body['postcode'] ?? null,
            'country'                    => $body['country'] ?? 'Fiji',
            'consignment_description'    => $body['consignment_description'] ?? null,
            'target_of_fumigation'       => $body['target_of_fumigation'] ?? null,
            'container_numbers'          => $body['container_numbers'] ?? null,
            'specified_dose_rate'        => isset($body['specified_dose_rate']) && $body['specified_dose_rate'] !== '' ? (float)$body['specified_dose_rate'] : null,
            'specified_exposure_period'  => isset($body['specified_exposure_period']) && $body['specified_exposure_period'] !== '' ? (float)$body['specified_exposure_period'] : null,
            'enclosure_type'             => $body['enclosure_type'] ?? 'Sheeted enclosure',
            'enclosure_type_other'       => $body['enclosure_type_other'] ?? null,
            'consignment_suitable'       => isset($body['consignment_suitable']) ? (int)$body['consignment_suitable'] : 1,
            'remedial_action_taken'      => $body['remedial_action_taken'] ?? null,
            'enclosure_length'           => isset($body['enclosure_length']) && $body['enclosure_length'] !== '' ? (float)$body['enclosure_length'] : null,
            'enclosure_width'            => isset($body['enclosure_width']) && $body['enclosure_width'] !== '' ? (float)$body['enclosure_width'] : null,
            'enclosure_height'           => isset($body['enclosure_height']) && $body['enclosure_height'] !== '' ? (float)$body['enclosure_height'] : null,
            'total_volume_m3'            => isset($body['total_volume_m3']) && $body['total_volume_m3'] !== '' ? (float)$body['total_volume_m3'] : null,
            'fumigation_temp_type'      => $body['fumigation_temp_type'] ?? 'Ambient',
            'min_temperature'            => isset($body['min_temperature']) && $body['min_temperature'] !== '' ? (float)$body['min_temperature'] : null,
            'temp_unit'                  => $body['temp_unit'] ?? '°C',
            'heaters_used'               => isset($body['heaters_used']) ? (int)$body['heaters_used'] : 0,
            'dose_rate_used'             => isset($body['dose_rate_used']) && $body['dose_rate_used'] !== '' ? (float)$body['dose_rate_used'] : null,
            'calculated_dose_g'          => isset($body['calculated_dose_g']) && $body['calculated_dose_g'] !== '' ? (float)$body['calculated_dose_g'] : null,
            'dose_percentage'            => isset($body['dose_percentage']) && $body['dose_percentage'] !== '' ? (float)$body['dose_percentage'] : null,
            'amount_ch3br_applied_g'     => isset($body['amount_ch3br_applied_g']) && $body['amount_ch3br_applied_g'] !== '' ? (float)$body['amount_ch3br_applied_g'] : null,
            'device_serial_numbers'      => $body['device_serial_numbers'] ?? null,
            'injection_finished_at'      => $body['injection_finished_at'] ?? null,
            'ventilation_start_at'       => $body['ventilation_start_at'] ?? null,
            'final_tlv_ppm1'             => isset($body['final_tlv_ppm1']) && $body['final_tlv_ppm1'] !== '' ? (float)$body['final_tlv_ppm1'] : null,
            'final_tlv_ppm2'             => isset($body['final_tlv_ppm2']) && $body['final_tlv_ppm2'] !== '' ? (float)$body['final_tlv_ppm2'] : null,
            'final_tlv_ppm3'             => isset($body['final_tlv_ppm3']) && $body['final_tlv_ppm3'] !== '' ? (float)$body['final_tlv_ppm3'] : null,
            'topup_amount_g_m3'          => isset($body['topup_amount_g_m3']) && $body['topup_amount_g_m3'] !== '' ? (float)$body['topup_amount_g_m3'] : null,
            'topup_time'                 => $body['topup_time'] ?? null,
            'topup_concentration'        => isset($body['topup_concentration']) && $body['topup_concentration'] !== '' ? (float)$body['topup_concentration'] : null,
            'signature_gas_in'           => $body['signature_gas_in'] ?? null,
            'signature_gas_out'          => $body['signature_gas_out'] ?? null,
            'declaration_date'           => $body['declaration_date'] ?? date('Y-m-d'),
            'gov_officer_name'           => $body['gov_officer_name'] ?? null,
            'gov_officer_signature'      => $body['gov_officer_signature'] ?? null,
            'status'                     => $body['status'] ?? 'Completed'
        ];

        if ($isNew) {
            $db->insert('ap_fumigation_records', $fields);
        } else {
            $db->update('ap_fumigation_records', $fields, ['record_id' => $recordId]);
        }

        // Auto-update job order status if linked
        if (!empty($body['job_id'])) {
            $db->update('ap_job_orders', ['status' => 'Fumigation Active'], ['job_id' => $body['job_id']]);
        }

        fum_json(['success' => true, 'record_id' => $recordId, 'message' => 'Record of Fumigation saved successfully']);
    }

    // ── 6. SAVE / UPDATE CONCENTRATION READING (MODAL HANDLER) ─────────────
    if ($action === 'save_reading') {
        $readingId = trim((string)($body['reading_id'] ?? ''));
        $recordId  = trim((string)($body['record_id'] ?? ''));

        if (!$recordId) {
            fum_json(['success' => false, 'message' => 'Record ID is required for readings']);
        }

        $isNew = false;
        if (!$readingId) {
            $readingId = 'read_' . uniqid();
            $isNew = true;
        }

        $loc1 = isset($body['loc_1_fb']) && $body['loc_1_fb'] !== '' ? (float)$body['loc_1_fb'] : null;
        $loc2 = isset($body['loc_2_mc']) && $body['loc_2_mc'] !== '' ? (float)$body['loc_2_mc'] : null;
        $loc3 = isset($body['loc_3_tb']) && $body['loc_3_tb'] !== '' ? (float)$body['loc_3_tb'] : null;
        $loc4 = isset($body['loc_4']) && $body['loc_4'] !== '' ? (float)$body['loc_4'] : null;
        $loc5 = isset($body['loc_5']) && $body['loc_5'] !== '' ? (float)$body['loc_5'] : null;

        // Auto-calculate equilibrium result (%) = 100 - (((max - min) / max) * 100)
        $validLocs = array_filter([$loc1, $loc2, $loc3, $loc4, $loc5], function($v) { return $v !== null; });
        $equilibriumPct = null;
        if (count($validLocs) > 1) {
            $maxLoc = max($validLocs);
            $minLoc = min($validLocs);
            if ($maxLoc > 0) {
                $equilibriumPct = round(100.0 - ((($maxLoc - $minLoc) / $maxLoc) * 100.0), 2);
            }
        } elseif (count($validLocs) === 1) {
            $equilibriumPct = 100.00;
        }

        $readingFields = [
            'reading_id'             => $readingId,
            'record_id'              => $recordId,
            'stage_type'             => $body['stage_type'] ?? 'During',
            'reading_date'           => $body['reading_date'] ?? date('Y-m-d'),
            'reading_time'           => $body['reading_time'] ?? date('H:i'),
            'loc_1_fb'               => $loc1,
            'loc_2_mc'               => $loc2,
            'loc_3_tb'               => $loc3,
            'loc_4'                  => $loc4,
            'loc_5'                  => $loc5,
            'equilibrium_result_pct' => $equilibriumPct,
            'standard_g_m3'          => isset($body['standard_g_m3']) && $body['standard_g_m3'] !== '' ? (float)$body['standard_g_m3'] : null,
            'fumigator_initials'     => $body['fumigator_initials'] ?? null
        ];

        if ($isNew) {
            $db->insert('ap_fumigation_readings', $readingFields);
        } else {
            $db->update('ap_fumigation_readings', $readingFields, ['reading_id' => $readingId]);
        }

        fum_json([
            'success'     => true,
            'reading_id'  => $readingId,
            'equilibrium' => $equilibriumPct,
            'message'     => 'Concentration reading updated successfully'
        ]);
    }

    // ── 7. DELETE CONCENTRATION READING ───────────────────────────────────
    if ($action === 'delete_reading') {
        $readingId = trim((string)($body['reading_id'] ?? ''));
        if (!$readingId) {
            fum_json(['success' => false, 'message' => 'Reading ID required']);
        }
        $db->query("DELETE FROM ap_fumigation_readings WHERE reading_id = :id", [':id' => $readingId]);
        fum_json(['success' => true, 'message' => 'Reading deleted']);
    }

    fum_json(['success' => false, 'message' => 'Invalid action parameter']);

} catch (Throwable $e) {
    error_log('[fumigation_record api error] ' . $e->getMessage());
    fum_json(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
}

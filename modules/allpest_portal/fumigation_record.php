<?php
/**
 * modules/allpest_portal/fumigation_record.php
 * Official Record of Fumigation – Methyl Bromide Fumigation (Non-perishable Commodity)
 */
declare(strict_types=1);

if (!defined('NU_ROOT')) {
    define('NU_ROOT', dirname(__DIR__, 2));
}

require_once NU_ROOT . '/config.php';
require_once NU_ROOT . '/core/Database.php';

$db = NuDatabase::getInstance();
$recordId = $_GET['id'] ?? $_GET['record_id'] ?? '';
?>

<style>
.nu-fum-container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 24px;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  color: #1e293b;
  background: var(--bg-secondary, #f8fafc);
}
.nu-fum-header {
  background: #0f172a;
  color: #ffffff;
  padding: 20px 24px;
  border-radius: 12px 12px 0 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  border-bottom: 4px solid #10b981;
}
.nu-fum-header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 800;
  letter-spacing: -0.3px;
  color: #f8fafc;
}
.nu-fum-header-sub {
  font-size: 13px;
  color: #94a3b8;
  margin-top: 4px;
}
.nu-fum-bar {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  padding: 14px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.nu-btn-sm {
  padding: 8px 14px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.15s ease;
}
.nu-btn-primary { background: #059669; color: #ffffff; }
.nu-btn-primary:hover { background: #047857; }
.nu-btn-secondary { background: #3b82f6; color: #ffffff; }
.nu-btn-secondary:hover { background: #2563eb; }
.nu-btn-ghost { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
.nu-btn-ghost:hover { background: #e2e8f0; }

/* Accordion Section Design */
.nu-sec-card {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  margin-top: 12px;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.nu-sec-header {
  background: #f1f5f9;
  padding: 14px 18px;
  font-weight: 700;
  font-size: 14px;
  color: #0f172a;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  user-select: none;
  border-bottom: 1px solid #e2e8f0;
  transition: background 0.15s;
}
.nu-sec-header:hover { background: #e2e8f0; }
.nu-sec-header .chevron {
  transition: transform 0.2s ease;
  font-size: 14px;
  color: #64748b;
}
.nu-sec-header.collapsed .chevron { transform: rotate(-90deg); }
.nu-sec-body {
  padding: 20px;
  display: block;
}
.nu-sec-body.collapsed { display: none; }

/* Grid Layouts */
.nu-grid-12 { display: grid; grid-template-columns: repeat(12, 1fr); gap: 14px; }
.col-12 { grid-column: span 12; }
.col-8  { grid-column: span 8; }
.col-6  { grid-column: span 6; }
.col-4  { grid-column: span 4; }
.col-3  { grid-column: span 3; }
.col-2  { grid-column: span 2; }

@media (max-width: 768px) {
  .col-8, .col-6, .col-4, .col-3, .col-2 { grid-column: span 12; }
}

.nu-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  margin-bottom: 5px;
}
.nu-input, .nu-select, .nu-textarea {
  width: 100%;
  padding: 9px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 13px;
  color: #0f172a;
  background: #ffffff;
  box-sizing: border-box;
}
.nu-input:focus, .nu-select:focus, .nu-textarea:focus {
  border-color: #059669;
  outline: none;
  box-shadow: 0 0 0 3px rgba(5,150,105,0.12);
}
.nu-input[readonly] { background: #f8fafc; color: #475569; }

/* Table Styling for Section D */
.nu-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
  margin-top: 10px;
}
.nu-table th, .nu-table td {
  border: 1px solid #cbd5e1;
  padding: 8px 10px;
  text-align: center;
}
.nu-table th {
  background: #f8fafc;
  color: #334155;
  font-weight: 700;
}
.nu-badge-pass { background: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 12px; font-weight: 700; font-size: 11px; }

/* Modal Styling */
.nu-modal-backdrop {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s ease;
}
.nu-modal-backdrop.open {
  opacity: 1;
  pointer-events: auto;
}
.nu-modal-box {
  background: #ffffff;
  border-radius: 12px;
  width: 90%;
  max-width: 680px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);
  transform: translateY(20px);
  transition: transform 0.2s ease;
}
.nu-modal-backdrop.open .nu-modal-box { transform: translateY(0); }
.nu-modal-header {
  padding: 16px 20px;
  background: #0f172a;
  color: #ffffff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 12px 12px 0 0;
}
.nu-modal-body { padding: 20px; }
.nu-modal-footer {
  padding: 14px 20px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  border-radius: 0 0 12px 12px;
}
</style>

<div class="nu-fum-container">

  <!-- Header Banner -->
  <div class="nu-fum-header">
    <div>
      <h2>Record of fumigation – Methyl bromide fumigation</h2>
      <div class="nu-fum-header-sub">Non-perishable commodity &bull; Biosecurity Authority Fiji & AFAS Compliant</div>
    </div>
    <div style="display:flex; gap:10px;">
      <button class="nu-btn-sm nu-btn-secondary" onclick="fumRecordOpenOrderPicker()">
        <span>🔗</span> Link to Job Order
      </button>
      <button class="nu-btn-sm nu-btn-primary" onclick="fumRecordSave()">
        <span>💾</span> Save Record
      </button>
    </div>
  </div>

  <!-- Master Controls Bar -->
  <div class="nu-fum-bar">
    <div style="display:flex; align-items:center; gap:12px;">
      <span style="font-size:12px; font-weight:700; color:#64748b;">RECORD STATUS:</span>
      <span id="fumRecordStatusBadge" style="background:#e0f2fe; color:#0369a1; font-weight:700; font-size:12px; padding:4px 10px; border-radius:12px;">
        Draft Mode
      </span>
      <span id="fumRecordLinkedOrderText" style="font-size:12px; font-weight:600; color:#059669;">
        [No Linked Order]
      </span>
    </div>
    <div style="display:flex; gap:8px;">
      <button class="nu-btn-sm nu-btn-ghost" onclick="fumToggleAllSections(true)">⏬ Expand All</button>
      <button class="nu-btn-sm nu-btn-ghost" onclick="fumToggleAllSections(false)">⏫ Collapse All</button>
    </div>
  </div>

  <form id="fumRecordForm">
    <input type="hidden" id="fum_record_id" name="record_id" value="<?= htmlspecialchars($recordId) ?>">
    <input type="hidden" id="fum_job_id" name="job_id" value="">

    <!-- SECTION A: Fumigator in charge -->
    <div class="nu-sec-card">
      <div class="nu-sec-header" onclick="fumToggleSec(this)">
        <span>SECTION A: Fumigator in charge</span>
        <span class="chevron">&#9660;</span>
      </div>
      <div class="nu-sec-body">
        <div class="nu-grid-12">
          <div class="col-6">
            <label class="nu-label">Full Name</label>
            <input type="text" id="fum_fumigator_name" name="fumigator_name" class="nu-input" placeholder="e.g. Rajesh Sharma" required>
          </div>
          <div class="col-6">
            <label class="nu-label">Accreditation number (if applicable)</label>
            <input type="text" id="fum_fumigator_accreditation" name="fumigator_accreditation" class="nu-input" value="BAF-AFAS-FJ0025MB" placeholder="AFAS Accreditation Code">
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION B: Job details -->
    <div class="nu-sec-card">
      <div class="nu-sec-header" onclick="fumToggleSec(this)">
        <span>SECTION B: Job details</span>
        <span class="chevron">&#9660;</span>
      </div>
      <div class="nu-sec-body">
        <div class="nu-grid-12">
          <div class="col-4">
            <label class="nu-label">Treatment Provider ID</label>
            <input type="text" id="fum_treatment_provider_id" name="treatment_provider_id" class="nu-input" value="FJ0025MB" readonly>
          </div>
          <div class="col-4">
            <label class="nu-label">Job Identification Number</label>
            <input type="text" id="fum_job_number" name="job_number" class="nu-input" placeholder="Click 'Link to Job Order' or type" readonly>
          </div>
          <div class="col-4">
            <label class="nu-label">Client Name / Details</label>
            <input type="text" id="fum_client_name" name="client_name" class="nu-input" placeholder="e.g. Pacific Timber Exports">
          </div>

          <div class="col-12" style="margin-top:6px;">
            <span style="font-weight:700; font-size:12px; color:#0f172a; text-transform:uppercase;">Location of Fumigation</span>
          </div>
          <div class="col-6">
            <label class="nu-label">Street Address</label>
            <input type="text" id="fum_street_address" name="street_address" class="nu-input" placeholder="e.g. Berth 3, Lautoka Port Terminal">
          </div>
          <div class="col-3">
            <label class="nu-label">Suburb / Town / City</label>
            <input type="text" id="fum_suburb_town_city" name="suburb_town_city" class="nu-input" value="Lautoka">
          </div>
          <div class="col-3">
            <label class="nu-label">Postcode / Country</label>
            <div style="display:flex; gap:6px;">
              <input type="text" id="fum_postcode" name="postcode" class="nu-input" style="width:40%;" placeholder="00679">
              <input type="text" id="fum_country" name="country" class="nu-input" style="width:60%;" value="Fiji">
            </div>
          </div>

          <div class="col-6">
            <label class="nu-label">Description of Consignment</label>
            <textarea id="fum_consignment_description" name="consignment_description" class="nu-textarea" rows="2" placeholder="e.g. 40ft Sawn Timber Packages"></textarea>
          </div>
          <div class="col-6">
            <label class="nu-label">Target of Fumigation</label>
            <input type="text" id="fum_target_of_fumigation" name="target_of_fumigation" class="nu-input" placeholder="e.g. Wood Borers / Bark Beetles">
          </div>

          <div class="col-12">
            <label class="nu-label">Consignment Identification / Container Numbers</label>
            <input type="text" id="fum_container_numbers" name="container_numbers" class="nu-input" placeholder="e.g. TGHU8829104 / Seal # BAF-99218">
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION C: Fumigation details -->
    <div class="nu-sec-card">
      <div class="nu-sec-header" onclick="fumToggleSec(this)">
        <span>SECTION C: Fumigation details</span>
        <span class="chevron">&#9660;</span>
      </div>
      <div class="nu-sec-body">
        <div class="nu-grid-12">

          <!-- Treatment Schedule -->
          <div class="col-6">
            <div style="background:#f8fafc; padding:12px; border-radius:6px; border:1px solid #e2e8f0;">
              <span class="nu-label" style="color:#0f172a;">Specified Treatment Schedule</span>
              <div style="display:flex; gap:10px; margin-top:8px;">
                <div style="flex:1;">
                  <label class="nu-label">Dose Rate (g/m³)</label>
                  <input type="number" step="0.1" id="fum_specified_dose_rate" name="specified_dose_rate" class="nu-input" value="48.0" oninput="fumCalcDose()">
                </div>
                <div style="flex:1;">
                  <label class="nu-label">Exposure Period (hrs)</label>
                  <input type="number" step="0.5" id="fum_specified_exposure_period" name="specified_exposure_period" class="nu-input" value="24.0">
                </div>
              </div>
            </div>
          </div>

          <!-- Enclosure Type -->
          <div class="col-6">
            <div style="background:#f8fafc; padding:12px; border-radius:6px; border:1px solid #e2e8f0;">
              <span class="nu-label" style="color:#0f172a;">Enclosure Type (Select One)</span>
              <select id="fum_enclosure_type" name="enclosure_type" class="nu-select" style="margin-top:8px;" onchange="fumCheckEnclosureType(this)">
                <option value="Sheeted enclosure" selected>Sheeted enclosure</option>
                <option value="Un-sheeted container(s)">Un-sheeted container(s)</option>
                <option value="Fumigation chamber">Fumigation chamber</option>
                <option value="Other">Other</option>
              </select>
              <input type="text" id="fum_enclosure_type_other" name="enclosure_type_other" class="nu-input" placeholder="Specify if Other" style="display:none; margin-top:6px;">
            </div>
          </div>

          <!-- Suitability -->
          <div class="col-6">
            <label class="nu-label">Was consignment suitable for fumigation?</label>
            <select id="fum_consignment_suitable" name="consignment_suitable" class="nu-select" onchange="fumCheckSuitability(this)">
              <option value="1" selected>Yes – consignment suitable</option>
              <option value="0">No – remedial action taken</option>
            </select>
            <input type="text" id="fum_remedial_action_taken" name="remedial_action_taken" class="nu-input" placeholder="Remedial action taken" style="display:none; margin-top:6px;">
          </div>

          <!-- Enclosure Volume -->
          <div class="col-6">
            <label class="nu-label">Enclosure Volume Dimensions (m) & Total (m³)</label>
            <div style="display:flex; gap:6px;">
              <input type="number" step="0.1" id="fum_enclosure_length" name="enclosure_length" class="nu-input" placeholder="Length" value="12.2" oninput="fumCalcVolume()">
              <input type="number" step="0.1" id="fum_enclosure_width" name="enclosure_width" class="nu-input" placeholder="Width" value="2.4" oninput="fumCalcVolume()">
              <input type="number" step="0.1" id="fum_enclosure_height" name="enclosure_height" class="nu-input" placeholder="Height" value="2.6" oninput="fumCalcVolume()">
              <input type="number" step="0.1" id="fum_total_volume_m3" name="total_volume_m3" class="nu-input" placeholder="Total m³" value="76.1" style="font-weight:700; color:#047857;" oninput="fumCalcDose()">
            </div>
          </div>

          <!-- Temperature & Dose -->
          <div class="col-6">
            <label class="nu-label">Fumigation Type & Temperature</label>
            <div style="display:flex; gap:8px;">
              <select id="fum_fumigation_temp_type" name="fumigation_temp_type" class="nu-select" style="width:50%;">
                <option value="Ambient" selected>Ambient Temp</option>
                <option value="Controlled">Controlled Temp</option>
              </select>
              <input type="number" step="0.1" id="fum_min_temperature" name="min_temperature" class="nu-input" style="width:50%;" value="24.5" placeholder="Min Temp °C">
            </div>
            <div style="margin-top:6px; display:flex; align-items:center; gap:8px;">
              <input type="checkbox" id="fum_heaters_used" name="heaters_used" value="1">
              <label for="fum_heaters_used" style="font-size:12px; font-weight:600; color:#475569;">Heaters Used?</label>
            </div>
          </div>

          <div class="col-6">
            <label class="nu-label">Dose & CH3Br Applied</label>
            <div style="display:flex; gap:6px;">
              <input type="number" step="0.1" id="fum_dose_rate_used" name="dose_rate_used" class="nu-input" placeholder="Dose Rate g/m³" value="48.0" oninput="fumCalcDose()">
              <input type="number" step="1" id="fum_calculated_dose_g" name="calculated_dose_g" class="nu-input" placeholder="Calculated Dose (g)" value="3653" readonly style="font-weight:700; color:#1d4ed8;">
              <input type="number" step="1" id="fum_amount_ch3br_applied_g" name="amount_ch3br_applied_g" class="nu-input" placeholder="CH3Br Applied (g)" value="3653">
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- SECTION D: Concentration readings -->
    <div class="nu-sec-card">
      <div class="nu-sec-header" onclick="fumToggleSec(this)">
        <span>SECTION D: Concentration readings (Monitoring readings by locations)</span>
        <span class="chevron">&#9660;</span>
      </div>
      <div class="nu-sec-body">

        <div class="nu-grid-12" style="margin-bottom:12px;">
          <div class="col-6">
            <label class="nu-label">Serial Number of Monitoring Device(s)</label>
            <input type="text" id="fum_device_serial_numbers" name="device_serial_numbers" class="nu-input" value="RI-555-MB-8821 / RKI-8812" placeholder="Device Serial #s">
          </div>
          <div class="col-3">
            <label class="nu-label">Fumigant Injection Finished</label>
            <input type="datetime-local" id="fum_injection_finished_at" name="injection_finished_at" class="nu-input">
          </div>
          <div class="col-3">
            <label class="nu-label">Enclosure Ventilation Start</label>
            <input type="datetime-local" id="fum_ventilation_start_at" name="ventilation_start_at" class="nu-input">
          </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
          <span style="font-size:13px; font-weight:700; color:#0f172a;">Gas Monitoring Readings Log</span>
          <button type="button" class="nu-btn-sm nu-btn-secondary" onclick="fumOpenReadingModal()">
            <span>➕</span> Add Concentration Reading
          </button>
        </div>

        <div style="overflow-x:auto;">
          <table class="nu-table" id="fumReadingsTable">
            <thead>
              <tr>
                <th>Stage</th>
                <th>1 (FB)</th>
                <th>2 (MC)</th>
                <th>3 (TB)</th>
                <th>4</th>
                <th>5</th>
                <th>Equilibrium (%)</th>
                <th>Standard (g/m³)</th>
                <th>Date / Time</th>
                <th>Initials</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="fumReadingsTableBody">
              <tr><td colspan="11" style="color:#94a3b8; padding:16px;">No concentration readings recorded yet. Click "+ Add Concentration Reading".</td></tr>
            </tbody>
          </table>
        </div>

        <!-- Top-up & TLV Details -->
        <div class="nu-grid-12" style="margin-top:16px; background:#f8fafc; padding:12px; border-radius:6px; border:1px solid #e2e8f0;">
          <div class="col-12"><span class="nu-label" style="color:#0f172a;">Top-up Details & Final TLV Clearance</span></div>
          <div class="col-3">
            <label class="nu-label">Top-up Amount (g/m³)</label>
            <input type="number" step="0.1" id="fum_topup_amount_g_m3" name="topup_amount_g_m3" class="nu-input" placeholder="0.0">
          </div>
          <div class="col-3">
            <label class="nu-label">Top-up Time (hh:mm)</label>
            <input type="text" id="fum_topup_time" name="topup_time" class="nu-input" placeholder="14:30">
          </div>
          <div class="col-3">
            <label class="nu-label">Concentration (g/m³)</label>
            <input type="number" step="0.1" id="fum_topup_concentration" name="topup_concentration" class="nu-input" placeholder="0.0">
          </div>
          <div class="col-3">
            <label class="nu-label">Final TLV Readings (ppm)</label>
            <div style="display:flex; gap:4px;">
              <input type="number" step="0.1" id="fum_final_tlv_ppm1" name="final_tlv_ppm1" class="nu-input" value="0.0">
              <input type="number" step="0.1" id="fum_final_tlv_ppm2" name="final_tlv_ppm2" class="nu-input" value="0.0">
              <input type="number" step="0.1" id="fum_final_tlv_ppm3" name="final_tlv_ppm3" class="nu-input" value="0.0">
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- SECTION E: Fumigator declaration -->
    <div class="nu-sec-card">
      <div class="nu-sec-header" onclick="fumToggleSec(this)">
        <span>SECTION E: Fumigator declaration</span>
        <span class="chevron">&#9660;</span>
      </div>
      <div class="nu-sec-body">
        <div style="background:#eff6ff; border-left:4px solid #3b82f6; padding:12px; border-radius:4px; font-size:12px; color:#1e40af; margin-bottom:14px; line-height:1.5;">
          I, the fumigator-in-charge declare that the fumigation was conducted in accordance with the treatment schedule and all the requirements in the Methyl Bromide Fumigation Methodology, and the information I have provided is true and correct.
        </div>

        <div class="nu-grid-12">
          <div class="col-4">
            <label class="nu-label">Signature: GAS IN</label>
            <input type="text" id="fum_signature_gas_in" name="signature_gas_in" class="nu-input" placeholder="Initials / Digital Token (e.g. RS-IN-882)">
          </div>
          <div class="col-4">
            <label class="nu-label">Signature: GAS OUT</label>
            <input type="text" id="fum_signature_gas_out" name="signature_gas_out" class="nu-input" placeholder="Initials / Digital Token (e.g. RS-OUT-882)">
          </div>
          <div class="col-4">
            <label class="nu-label">Declaration Date</label>
            <input type="date" id="fum_declaration_date" name="declaration_date" class="nu-input" value="<?= date('Y-m-d') ?>">
          </div>

          <div class="col-6">
            <label class="nu-label">Government Officer Supervised Name (if applicable)</label>
            <input type="text" id="fum_gov_officer_name" name="gov_officer_name" class="nu-input" placeholder="BAF Biosecurity Officer Name">
          </div>
          <div class="col-6">
            <label class="nu-label">Government Officer Signature / Stamp</label>
            <input type="text" id="fum_gov_officer_signature" name="gov_officer_signature" class="nu-input" placeholder="BAF Official Stamp Ref">
          </div>
        </div>
      </div>
    </div>

  </form>
</div>

<!-- MODAL 1: ORDER PICKER MODAL -->
<div class="nu-modal-backdrop" id="fumOrderPickerModal">
  <div class="nu-modal-box">
    <div class="nu-modal-header">
      <span style="font-weight:700; font-size:15px;">🔗 Select Job Order to Link Record</span>
      <button onclick="fumCloseModal('fumOrderPickerModal')" style="background:none; border:none; color:#ffffff; font-size:18px; cursor:pointer;">&times;</button>
    </div>
    <div class="nu-modal-body">
      <input type="text" id="fumOrderSearchInput" class="nu-input" placeholder="Search by Job #, Client name, or commodity..." onkeyup="fumSearchOrders()" style="margin-bottom:12px;">
      <div style="max-height:300px; overflow-y:auto;">
        <table class="nu-table">
          <thead>
            <tr>
              <th>Job Order #</th>
              <th>Client Name</th>
              <th>Route</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="fumOrderPickerTableBody">
            <tr><td colspan="4">Loading job orders...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="nu-modal-footer">
      <button class="nu-btn-sm nu-btn-ghost" onclick="fumCloseModal('fumOrderPickerModal')">Cancel</button>
    </div>
  </div>
</div>

<!-- MODAL 2: UPDATE CONCENTRATION READING MODAL -->
<div class="nu-modal-backdrop" id="fumReadingModal">
  <div class="nu-modal-box">
    <div class="nu-modal-header">
      <span style="font-weight:700; font-size:15px;" id="fumReadingModalTitle">Update Concentration Reading</span>
      <button onclick="fumCloseModal('fumReadingModal')" style="background:none; border:none; color:#ffffff; font-size:18px; cursor:pointer;">&times;</button>
    </div>
    <div class="nu-modal-body">
      <form id="fumReadingModalForm">
        <input type="hidden" id="modal_reading_id" name="reading_id" value="">
        <div class="nu-grid-12">
          <div class="col-4">
            <label class="nu-label">Stage Type</label>
            <select id="modal_stage_type" name="stage_type" class="nu-select">
              <option value="Start">Start (Injection Finished)</option>
              <option value="During" selected>During Treatment</option>
              <option value="End">End (Ventilation Start)</option>
            </select>
          </div>
          <div class="col-4">
            <label class="nu-label">Reading Date</label>
            <input type="date" id="modal_reading_date" name="reading_date" class="nu-input" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-4">
            <label class="nu-label">Reading Time (hh:mm)</label>
            <input type="text" id="modal_reading_time" name="reading_time" class="nu-input" value="<?= date('H:i') ?>">
          </div>

          <div class="col-12" style="margin-top:6px;"><span class="nu-label" style="color:#0f172a;">Sampling Location Concentration Readings (g/m³)</span></div>
          <div class="col-4">
            <label class="nu-label">1 (FB)</label>
            <input type="number" step="0.1" id="modal_loc_1_fb" name="loc_1_fb" class="nu-input" placeholder="g/m³" oninput="modalCalcEquilibrium()">
          </div>
          <div class="col-4">
            <label class="nu-label">2 (MC)</label>
            <input type="number" step="0.1" id="modal_loc_2_mc" name="loc_2_mc" class="nu-input" placeholder="g/m³" oninput="modalCalcEquilibrium()">
          </div>
          <div class="col-4">
            <label class="nu-label">3 (TB)</label>
            <input type="number" step="0.1" id="modal_loc_3_tb" name="loc_3_tb" class="nu-input" placeholder="g/m³" oninput="modalCalcEquilibrium()">
          </div>
          <div class="col-4">
            <label class="nu-label">Location 4</label>
            <input type="number" step="0.1" id="modal_loc_4" name="loc_4" class="nu-input" placeholder="g/m³" oninput="modalCalcEquilibrium()">
          </div>
          <div class="col-4">
            <label class="nu-label">Location 5</label>
            <input type="number" step="0.1" id="modal_loc_5" name="loc_5" class="nu-input" placeholder="g/m³" oninput="modalCalcEquilibrium()">
          </div>
          <div class="col-4">
            <label class="nu-label">Standard (g/m³)</label>
            <input type="number" step="0.1" id="modal_standard_g_m3" name="standard_g_m3" class="nu-input" value="38.0">
          </div>

          <div class="col-6">
            <label class="nu-label">Equilibrium Result (%)</label>
            <input type="text" id="modal_equilibrium_result_pct" class="nu-input" readonly placeholder="Auto calculated" style="font-weight:700; color:#047857;">
          </div>
          <div class="col-6">
            <label class="nu-label">Fumigator Initials</label>
            <input type="text" id="modal_fumigator_initials" name="fumigator_initials" class="nu-input" value="RS" placeholder="Initials">
          </div>
        </div>
      </form>
    </div>
    <div class="nu-modal-footer">
      <button class="nu-btn-sm nu-btn-ghost" onclick="fumCloseModal('fumReadingModal')">Cancel</button>
      <button class="nu-btn-sm nu-btn-primary" onclick="fumSaveReadingModal()">Save Reading</button>
    </div>
  </div>
</div>

<script>
var API_FUM_RECORD = 'modules/allpest_portal/api/fumigation_record.php';
var _fumCurrentReadings = [];

function fumToggleSec(headerEl) {
  headerEl.classList.toggle('collapsed');
  var body = headerEl.nextElementSibling;
  if (body) body.classList.toggle('collapsed');
}

function fumToggleAllSections(expand) {
  document.querySelectorAll('.nu-sec-header').forEach(function(h) {
    var b = h.nextElementSibling;
    if (expand) {
      h.classList.remove('collapsed');
      if (b) b.classList.remove('collapsed');
    } else {
      h.classList.add('collapsed');
      if (b) b.classList.add('collapsed');
    }
  });
}

function fumCheckEnclosureType(sel) {
  var otherEl = document.getElementById('fum_enclosure_type_other');
  if (otherEl) otherEl.style.display = (sel.value === 'Other') ? 'block' : 'none';
}

function fumCheckSuitability(sel) {
  var remedialEl = document.getElementById('fum_remedial_action_taken');
  if (remedialEl) remedialEl.style.display = (sel.value === '0') ? 'block' : 'none';
}

function fumCalcVolume() {
  var l = parseFloat(document.getElementById('fum_enclosure_length').value) || 0;
  var w = parseFloat(document.getElementById('fum_enclosure_width').value) || 0;
  var h = parseFloat(document.getElementById('fum_enclosure_height').value) || 0;
  var vol = Math.round((l * w * h) * 10) / 10;
  document.getElementById('fum_total_volume_m3').value = vol > 0 ? vol : '';
  fumCalcDose();
}

function fumCalcDose() {
  var vol = parseFloat(document.getElementById('fum_total_volume_m3').value) || 0;
  var rate = parseFloat(document.getElementById('fum_specified_dose_rate').value) || 0;
  var calculated = Math.round(vol * rate);
  document.getElementById('fum_calculated_dose_g').value = calculated > 0 ? calculated : '';
  document.getElementById('fum_amount_ch3br_applied_g').value = calculated > 0 ? calculated : '';
}

// Order Picker
function fumRecordOpenOrderPicker() {
  document.getElementById('fumOrderPickerModal').classList.add('open');
  fumSearchOrders();
}

function fumCloseModal(modalId) {
  document.getElementById(modalId).classList.remove('open');
}

function fumSearchOrders() {
  var q = document.getElementById('fumOrderSearchInput').value.trim();
  fetch(API_FUM_RECORD + '?action=list_orders&q=' + encodeURIComponent(q))
    .then(function(r) { return r.json(); })
    .then(function(res) {
      var tbody = document.getElementById('fumOrderPickerTableBody');
      if (!res.success || !res.orders || res.orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4">No job orders found.</td></tr>';
        return;
      }
      var html = '';
      res.orders.forEach(function(o) {
        html += '<tr>' +
          '<td style="font-weight:700; color:#047857;">' + (o.job_number || o.job_id) + '</td>' +
          '<td>' + (o.client_name || '') + '</td>' +
          '<td>' + (o.port_of_origin || '') + '</td>' +
          '<td><button type="button" class="nu-btn-sm nu-btn-primary" onclick="fumSelectOrder(\'' + o.job_id + '\')">Select</button></td>' +
        '</tr>';
      });
      tbody.innerHTML = html;
    })
    .catch(function(err) { console.error('fumSearchOrders err:', err); });
}

function fumSelectOrder(jobId) {
  fetch(API_FUM_RECORD + '?action=get_order&job_id=' + encodeURIComponent(jobId))
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (!res.success || !res.order) return;
      var o = res.order;
      document.getElementById('fum_job_id').value = o.job_id;
      document.getElementById('fum_job_number').value = o.job_number || o.job_id;
      document.getElementById('fum_client_name').value = o.client_name || '';
      document.getElementById('fum_street_address').value = o.port_of_origin || 'Lautoka Port Terminal';
      document.getElementById('fum_consignment_description').value = o.commodity_description || '';
      document.getElementById('fum_target_of_fumigation').value = o.target_pest || '';

      document.getElementById('fumRecordLinkedOrderText').textContent = 'Linked Order: ' + (o.job_number || o.job_id);
      fumCloseModal('fumOrderPickerModal');
      if (window.NuApp) NuApp.toast('Linked to Job Order ' + o.job_number, 'success');
    });
}

// Concentration Readings Table Rendering
function fumRenderReadingsTable() {
  var tbody = document.getElementById('fumReadingsTableBody');
  if (!_fumCurrentReadings || _fumCurrentReadings.length === 0) {
    tbody.innerHTML = '<tr><td colspan="11" style="color:#94a3b8; padding:16px;">No concentration readings recorded yet. Click "+ Add Concentration Reading".</td></tr>';
    return;
  }
  var html = '';
  _fumCurrentReadings.forEach(function(r) {
    var eqText = (r.equilibrium_result_pct !== null && r.equilibrium_result_pct !== undefined) ? (r.equilibrium_result_pct + '%') : '-';
    html += '<tr>' +
      '<td><strong>' + (r.stage_type || 'During') + '</strong></td>' +
      '<td>' + (r.loc_1_fb !== null ? r.loc_1_fb : '-') + '</td>' +
      '<td>' + (r.loc_2_mc !== null ? r.loc_2_mc : '-') + '</td>' +
      '<td>' + (r.loc_3_tb !== null ? r.loc_3_tb : '-') + '</td>' +
      '<td>' + (r.loc_4 !== null ? r.loc_4 : '-') + '</td>' +
      '<td>' + (r.loc_5 !== null ? r.loc_5 : '-') + '</td>' +
      '<td><span class="nu-badge-pass">' + eqText + '</span></td>' +
      '<td>' + (r.standard_g_m3 || '38.0') + '</td>' +
      '<td>' + (r.reading_date || '') + ' ' + (r.reading_time || '') + '</td>' +
      '<td>' + (r.fumigator_initials || 'RS') + '</td>' +
      '<td>' +
        '<button type="button" class="nu-btn-sm nu-btn-ghost" onclick="fumEditReading(\'' + r.reading_id + '\')">Edit</button> ' +
        '<button type="button" class="nu-btn-sm nu-btn-ghost" style="color:#ef4444;" onclick="fumDeleteReading(\'' + r.reading_id + '\')">&times;</button>' +
      '</td>' +
    '</tr>';
  });
  tbody.innerHTML = html;
}

function fumOpenReadingModal(readingId) {
  document.getElementById('modal_reading_id').value = readingId || '';
  if (!readingId) {
    document.getElementById('fumReadingModalTitle').textContent = 'Add Concentration Reading';
    document.getElementById('modal_loc_1_fb').value = '42.0';
    document.getElementById('modal_loc_2_mc').value = '41.5';
    document.getElementById('modal_loc_3_tb').value = '42.2';
    document.getElementById('modal_loc_4').value = '';
    document.getElementById('modal_loc_5').value = '';
    modalCalcEquilibrium();
  }
  document.getElementById('fumReadingModal').classList.add('open');
}

function fumEditReading(readingId) {
  var item = _fumCurrentReadings.find(function(i) { return i.reading_id === readingId; });
  if (!item) return;
  document.getElementById('fumReadingModalTitle').textContent = 'Edit Concentration Reading';
  document.getElementById('modal_reading_id').value = item.reading_id;
  document.getElementById('modal_stage_type').value = item.stage_type || 'During';
  document.getElementById('modal_reading_date').value = item.reading_date || '';
  document.getElementById('modal_reading_time').value = item.reading_time || '';
  document.getElementById('modal_loc_1_fb').value = item.loc_1_fb !== null ? item.loc_1_fb : '';
  document.getElementById('modal_loc_2_mc').value = item.loc_2_mc !== null ? item.loc_2_mc : '';
  document.getElementById('modal_loc_3_tb').value = item.loc_3_tb !== null ? item.loc_3_tb : '';
  document.getElementById('modal_loc_4').value = item.loc_4 !== null ? item.loc_4 : '';
  document.getElementById('modal_loc_5').value = item.loc_5 !== null ? item.loc_5 : '';
  document.getElementById('modal_standard_g_m3').value = item.standard_g_m3 || '38.0';
  document.getElementById('modal_fumigator_initials').value = item.fumigator_initials || 'RS';
  modalCalcEquilibrium();
  document.getElementById('fumReadingModal').classList.add('open');
}

function modalCalcEquilibrium() {
  var v1 = parseFloat(document.getElementById('modal_loc_1_fb').value);
  var v2 = parseFloat(document.getElementById('modal_loc_2_mc').value);
  var v3 = parseFloat(document.getElementById('modal_loc_3_tb').value);
  var v4 = parseFloat(document.getElementById('modal_loc_4').value);
  var v5 = parseFloat(document.getElementById('modal_loc_5').value);
  var vals = [v1, v2, v3, v4, v5].filter(function(n) { return !isNaN(n) && n > 0; });
  if (vals.length > 1) {
    var maxV = Math.max.apply(null, vals);
    var minV = Math.min.apply(null, vals);
    var eq = Math.round((100 - (((maxV - minV) / maxV) * 100)) * 100) / 100;
    document.getElementById('modal_equilibrium_result_pct').value = eq + '%';
  } else if (vals.length === 1) {
    document.getElementById('modal_equilibrium_result_pct').value = '100%';
  } else {
    document.getElementById('modal_equilibrium_result_pct').value = '-';
  }
}

function fumSaveReadingModal() {
  var recId = document.getElementById('fum_record_id').value;
  if (!recId) {
    recId = 'fum_rec_' + Date.now();
    document.getElementById('fum_record_id').value = recId;
  }
  var payload = {
    action: 'save_reading',
    record_id: recId,
    reading_id: document.getElementById('modal_reading_id').value,
    stage_type: document.getElementById('modal_stage_type').value,
    reading_date: document.getElementById('modal_reading_date').value,
    reading_time: document.getElementById('modal_reading_time').value,
    loc_1_fb: document.getElementById('modal_loc_1_fb').value,
    loc_2_mc: document.getElementById('modal_loc_2_mc').value,
    loc_3_tb: document.getElementById('modal_loc_3_tb').value,
    loc_4: document.getElementById('modal_loc_4').value,
    loc_5: document.getElementById('modal_loc_5').value,
    standard_g_m3: document.getElementById('modal_standard_g_m3').value,
    fumigator_initials: document.getElementById('modal_fumigator_initials').value
  };

  fetch(API_FUM_RECORD, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(function(r) { return r.json(); })
  .then(function(res) {
    if (res.success) {
      if (window.NuApp) NuApp.toast('Concentration reading saved', 'success');
      fumCloseModal('fumReadingModal');
      fumLoadRecord(recId);
    } else {
      alert('Error saving reading: ' + (res.message || 'Unknown'));
    }
  });
}

function fumDeleteReading(readingId) {
  if (!confirm('Delete this concentration reading?')) return;
  fetch(API_FUM_RECORD, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'delete_reading', reading_id: readingId })
  })
  .then(function(r) { return r.json(); })
  .then(function(res) {
    if (res.success) {
      var recId = document.getElementById('fum_record_id').value;
      fumLoadRecord(recId);
    }
  });
}

// Load Full Record
function fumLoadRecord(recId) {
  if (!recId) return;
  fetch(API_FUM_RECORD + '?action=get_record&record_id=' + encodeURIComponent(recId))
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (!res.success || !res.record) return;
      var r = res.record;
      document.getElementById('fum_record_id').value = r.record_id;
      document.getElementById('fum_job_id').value = r.job_id || '';
      document.getElementById('fum_job_number').value = r.job_number || '';
      document.getElementById('fum_client_name').value = r.client_name || '';
      document.getElementById('fum_fumigator_name').value = r.fumigator_name || '';
      document.getElementById('fum_fumigator_accreditation').value = r.fumigator_accreditation || 'BAF-AFAS-FJ0025MB';
      document.getElementById('fum_street_address').value = r.street_address || '';
      document.getElementById('fum_suburb_town_city').value = r.suburb_town_city || '';
      document.getElementById('fum_postcode').value = r.postcode || '';
      document.getElementById('fum_country').value = r.country || 'Fiji';
      document.getElementById('fum_consignment_description').value = r.consignment_description || '';
      document.getElementById('fum_target_of_fumigation').value = r.target_of_fumigation || '';
      document.getElementById('fum_container_numbers').value = r.container_numbers || '';
      document.getElementById('fum_specified_dose_rate').value = r.specified_dose_rate || '48.0';
      document.getElementById('fum_specified_exposure_period').value = r.specified_exposure_period || '24.0';
      document.getElementById('fum_enclosure_type').value = r.enclosure_type || 'Sheeted enclosure';
      document.getElementById('fum_enclosure_length').value = r.enclosure_length || '12.2';
      document.getElementById('fum_enclosure_width').value = r.enclosure_width || '2.4';
      document.getElementById('fum_enclosure_height').value = r.enclosure_height || '2.6';
      document.getElementById('fum_total_volume_m3').value = r.total_volume_m3 || '76.1';
      document.getElementById('fum_dose_rate_used').value = r.dose_rate_used || '48.0';
      document.getElementById('fum_calculated_dose_g').value = r.calculated_dose_g || '3653';
      document.getElementById('fum_amount_ch3br_applied_g').value = r.amount_ch3br_applied_g || '3653';
      document.getElementById('fum_device_serial_numbers').value = r.device_serial_numbers || '';
      document.getElementById('fum_signature_gas_in').value = r.signature_gas_in || '';
      document.getElementById('fum_signature_gas_out').value = r.signature_gas_out || '';
      document.getElementById('fum_declaration_date').value = r.declaration_date || '';
      document.getElementById('fum_gov_officer_name').value = r.gov_officer_name || '';

      if (r.job_number) {
        document.getElementById('fumRecordLinkedOrderText').textContent = 'Linked Order: ' + r.job_number;
      }

      _fumCurrentReadings = res.readings || [];
      fumRenderReadingsTable();
    });
}

function fumRecordSave() {
  var formEl = document.getElementById('fumRecordForm');
  var formData = new FormData(formEl);
  var payload = { action: 'save_record' };
  formData.forEach(function(val, key) { payload[key] = val; });

  fetch(API_FUM_RECORD, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(function(r) { return r.json(); })
  .then(function(res) {
    if (res.success) {
      document.getElementById('fum_record_id').value = res.record_id;
      document.getElementById('fumRecordStatusBadge').textContent = 'Saved & Active';
      document.getElementById('fumRecordStatusBadge').style.background = '#dcfce7';
      document.getElementById('fumRecordStatusBadge').style.color = '#15803d';
      if (window.NuApp) NuApp.toast('Record of Fumigation saved successfully!', 'success');
    } else {
      alert('Error saving record: ' + (res.message || 'Unknown error'));
    }
  });
}

// Initial seed / default readings if brand new
(function initFumRecord() {
  var recId = document.getElementById('fum_record_id').value;
  if (recId) {
    fumLoadRecord(recId);
  } else {
    // Default sample readings for instant interactive view
    _fumCurrentReadings = [
      { reading_id: 'sample_1', stage_type: 'Start', loc_1_fb: 44.0, loc_2_mc: 43.5, loc_3_tb: 44.2, loc_4: 43.8, loc_5: 44.0, equilibrium_result_pct: 98.86, standard_g_m3: 38.0, reading_date: '2026-05-28', reading_time: '08:30', fumigator_initials: 'RS' },
      { reading_id: 'sample_2', stage_type: 'During', loc_1_fb: 40.2, loc_2_mc: 39.8, loc_3_tb: 40.0, loc_4: 40.1, loc_5: 39.9, equilibrium_result_pct: 99.00, standard_g_m3: 38.0, reading_date: '2026-05-28', reading_time: '14:30', fumigator_initials: 'RS' },
      { reading_id: 'sample_3', stage_type: 'End', loc_1_fb: 38.5, loc_2_mc: 38.2, loc_3_tb: 38.6, loc_4: 38.4, loc_5: 38.3, equilibrium_result_pct: 98.96, standard_g_m3: 38.0, reading_date: '2026-05-29', reading_time: '08:30', fumigator_initials: 'RS' }
    ];
    fumRenderReadingsTable();
  }
})();
</script>

<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/core/module_bootstrap.php';

if (!$auth->hasPermission('users.view')) {
    http_response_code(403);
    exit('Access denied');
}

$db    = NuDatabase::getInstance();
$users = $db->fetchAll("SELECT * FROM nu_users ORDER BY usr_id DESC");
$roles = $db->fetchAll("SELECT role_code, role_name FROM nu_roles ORDER BY role_name");

// Get unified user field definitions
$fieldsDef = nu_get_user_fields_def($db);
$systemFields = [];
$customFields = [];
foreach ($fieldsDef as $fd) {
    if (!empty($fd['is_system'])) {
        $systemFields[] = $fd;
    } else {
        $customFields[] = $fd;
    }
}

/**
 * Fetch dynamic select option values from database lookup table/field
 */
function nu_get_lookup_options(NuDatabase $db, string $table, string $field): array {
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $field = preg_replace('/[^a-zA-Z0-9_]/', '', $field);
    if (!$table || !$field) return [];

    try {
        $rows = $db->fetchAll("SELECT DISTINCT `{$field}` as opt FROM `{$table}` WHERE `{$field}` IS NOT NULL AND `{$field}` != '' ORDER BY `{$field}` ASC LIMIT 100");
        return array_column($rows, 'opt');
    } catch (Exception $e) {
        return [];
    }
}
?>
<div class="nu-users" id="usersRoot">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h3 class="nu-card-title" style="margin:0;">Users</h3>
    <div style="display:flex;gap:8px;">
        <?php if ($auth->getCurrentUser()['usr_username'] === 'globeadmin'): ?>
        <button class="nu-btn nu-btn-secondary" style="background:#f1f5f9;color:#0f172a;border:1px solid #cbd5e1;" onclick="Users.openManageFields()">⚙ Manage User Fields</button>
        <?php endif; ?>
        <?php if ($auth->hasPermission('users.create')): ?>
        <button class="nu-btn nu-btn-primary" onclick="Users.openCreate()">+ New User</button>
        <?php endif; ?>
    </div>
</div>

<div class="nu-table-wrap">
    <table class="nu-table" style="width:100%;">
        <thead>
            <tr>
                <th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Active</th>
                <?php foreach ($customFields as $cf): ?>
                <th><?php echo htmlspecialchars($cf['label']); ?></th>
                <?php endforeach; ?>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u):
                $customValues = json_decode($u['usr_custom_fields'] ?? '{}', true);
                // Fallback to nu_user_meta for backward compatibility
                foreach ($customFields as $cf) {
                    if (!isset($customValues[$cf['key']])) {
                        $metaVal = $db->fetchOne(
                            "SELECT umeta_value FROM nu_user_meta WHERE umeta_user_id = :id AND umeta_key = :k",
                            [':id' => $u['usr_id'], ':k' => $cf['key']]
                        );
                        if ($metaVal) {
                            $customValues[$cf['key']] = $metaVal['umeta_value'];
                        }
                    }
                }
            ?>
            <tr>
                <td><?php echo $u['usr_id']; ?></td>
                <td><strong><?php echo htmlspecialchars($u['usr_username']); ?></strong></td>
                <td><?php echo htmlspecialchars($u['usr_email'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($u['usr_role']); ?></td>
                <td><?php echo $u['usr_active'] ? '<span class="nu-status nu-status-active">Yes</span>' : '<span class="nu-status nu-status-inactive">No</span>'; ?></td>
                <?php foreach ($customFields as $cf): ?>
                <td><?php echo htmlspecialchars((string)($customValues[$cf['key']] ?? '-')); ?></td>
                <?php endforeach; ?>
                <td style="display:flex;gap:6px;">
                    <?php if ($auth->hasPermission('users.edit')): ?>
                    <button class="nu-btn nu-btn-ghost nu-btn-sm"
                        onclick="Users.openEdit('<?php echo $u['usr_id']; ?>')"
                        data-user='<?php echo htmlspecialchars(json_encode($u), ENT_QUOTES); ?>'
                        data-meta='<?php echo htmlspecialchars(json_encode($customValues), ENT_QUOTES); ?>'>
                        ✎ Edit
                    </button>
                    <button class="nu-btn nu-btn-secondary nu-btn-sm"
                        style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;"
                        onclick="Users.openRoleClearanceModal('<?php echo $u['usr_id']; ?>', '<?php echo htmlspecialchars($u['usr_username']); ?>')">
                        🛡 Assign Clearance
                    </button>
                    <?php endif; ?>
                    <?php if ($auth->hasPermission('users.delete') && $u['usr_username'] !== 'globeadmin'): ?>
                    <button class="nu-btn nu-btn-danger nu-btn-sm"
                        onclick="Users.deleteUser('<?php echo $u['usr_id']; ?>', '<?php echo htmlspecialchars($u['usr_username']); ?>')">✕</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ═══ USER CREATE/EDIT MODAL ═══════════════════════════════════════════════ -->
<div id="userModalOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:10000;align-items:center;justify-content:center;">
  <div style="background:var(--card-bg,#fff);border-radius:12px;padding:28px;width:92%;max-width:520px;max-height:90vh;overflow-y:auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
      <h4 id="userModalTitle" style="margin:0;">New User</h4>
      <button onclick="Users.closeModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#666;">✕</button>
    </div>

    <input type="hidden" id="umEditId">

    <div class="nu-field" style="margin-bottom:12px;">
      <label style="font-size:13px;display:block;margin-bottom:4px;">Username *</label>
      <input type="text" class="nu-input" id="umUsername">
    </div>
    <div class="nu-field" style="margin-bottom:12px;">
      <label style="font-size:13px;display:block;margin-bottom:4px;">Email</label>
      <input type="email" class="nu-input" id="umEmail">
    </div>
    <div class="nu-field" style="margin-bottom:12px;">
      <label style="font-size:13px;display:block;margin-bottom:4px;">Role</label>
      <select class="nu-input" id="umRole">
        <?php foreach ($roles as $r): ?>
        <option value="<?php echo htmlspecialchars($r['role_code']); ?>"><?php echo htmlspecialchars($r['role_name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="nu-field" style="margin-bottom:12px;">
      <label style="font-size:13px;display:block;margin-bottom:4px;">Password <small id="umPwdHint" style="color:#999;">(leave blank to keep current)</small></label>
      <input type="password" class="nu-input" id="umPassword" autocomplete="new-password">
    </div>

    <?php foreach ($customFields as $cf): ?>
    <div class="nu-field" style="margin-bottom:12px;">
      <label style="font-size:13px;display:block;margin-bottom:4px;"><?php echo htmlspecialchars($cf['label']); ?><?php echo !empty($cf['global']) ? ' <span style="font-size:11px;color:#1a6fad;">(global)</span>' : ''; ?></label>
      <?php if (($cf['type'] ?? 'text') === 'select'):
          $optsArray = [];
          if (!empty($cf['lookup_table']) && !empty($cf['lookup_field'])) {
              $optsArray = nu_get_lookup_options($db, $cf['lookup_table'], $cf['lookup_field']);
          } elseif (!empty($cf['options'])) {
              $optsArray = array_map('trim', explode(',', $cf['options']));
          }
      ?>
      <select class="nu-input" id="um_meta_<?php echo htmlspecialchars($cf['key']); ?>" data-meta-key="<?php echo htmlspecialchars($cf['key']); ?>">
        <option value="">— select —</option>
        <?php foreach ($optsArray as $opt): ?>
        <option value="<?php echo htmlspecialchars($opt); ?>"><?php echo htmlspecialchars($opt); ?></option>
        <?php endforeach; ?>
      </select>
      <?php else: ?>
      <input type="<?php echo htmlspecialchars($cf['type'] ?? 'text'); ?>" class="nu-input" id="um_meta_<?php echo htmlspecialchars($cf['key']); ?>" data-meta-key="<?php echo htmlspecialchars($cf['key']); ?>">
      <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <div class="nu-field" style="margin-bottom:20px;">
      <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
        <input type="checkbox" id="umActive" checked> Active
      </label>
    </div>

    <div style="display:flex;gap:8px;justify-content:flex-end;">
      <button class="nu-btn nu-btn-ghost" onclick="Users.closeModal()">Cancel</button>
      <button class="nu-btn nu-btn-primary" onclick="Users.saveUser()">Save</button>
    </div>
  </div>
</div>

<!-- ═══ ASSIGN SYSTEM ROLE & UPGRADE CLEARANCE MODAL ══════════════════════════ -->
<div id="roleClearanceModalOverlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.6);backdrop-filter:blur(2px);z-index:10002;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:16px;padding:32px;width:95%;max-width:820px;max-height:92vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,.25);border:1px solid #e2e8f0;font-family:'Inter',sans-serif;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;">RBAC V2.4</span>
        <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;">BAF / AFAS PROTOCOL</span>
        <span style="font-size:11px;color:#64748b;font-weight:500;">REF: CR-ROLE-2026-084</span>
      </div>
      <button onclick="Users.closeRoleClearanceModal()" style="background:none;border:none;font-size:22px;cursor:pointer;color:#94a3b8;padding:0;">✕</button>
    </div>

    <h2 style="font-size:20px;font-weight:700;color:#0f172a;margin:0 0 6px 0;">Assign System Role &amp; Upgrade Clearance: <span id="rcModalUserName">Savenaca Tubuna</span></h2>
    <p style="font-size:13px;color:#64748b;margin:0 0 24px 0;">Modify operational security privileges, statutory biosecurity signing authority, and port accreditation scopes for this field officer.</p>

    <!-- Officer Profile Card -->
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:20px;display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:24px;">
      <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:52px;height:52px;border-radius:12px;background:#10b981;color:#ffffff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;">ST</div>
        <div>
          <div style="display:flex;align-items:center;gap:8px;">
            <strong style="font-size:16px;color:#0f172a;" id="rcCardName">Savenaca Tubuna</strong>
            <span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:9999px;background:#ecfdf5;color:#047857;">• BAF ID: #TECH-FJ-109</span>
          </div>
          <div style="font-size:13px;color:#475569;margin-top:2px;" id="rcCardEmail">s.tubuna@allpest.com.fj • Station: Lautoka Terminal / Wharf Berth 2</div>
          <div style="font-size:12px;color:#94a3b8;margin-top:2px;">Biosecurity Authority of Fiji Master Register Active</div>
        </div>
      </div>
      <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;padding:10px 16px;text-align:center;display:flex;align-items:center;gap:16px;">
        <div>
          <div style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;">Current Clearance</div>
          <div style="font-size:13px;font-weight:700;color:#0f172a;">Level 2 Field</div>
        </div>
        <div style="color:#10b981;font-size:18px;">➔</div>
        <div>
          <div style="font-size:10px;font-weight:700;color:#047857;text-transform:uppercase;">Target Clearance</div>
          <div style="font-size:13px;font-weight:700;color:#047857;">Level 4 Statutory</div>
        </div>
      </div>
    </div>

    <!-- Role Selection -->
    <div style="margin-bottom:24px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
        <label style="font-size:13px;font-weight:600;color:#0f172a;">Select Target System Role <span style="color:#ef4444;">*</span></label>
        <span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:4px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">Newly Created Custom Role</span>
      </div>
      <div style="border:2px solid #10b981;border-radius:12px;padding:16px;background:#ffffff;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
          <div style="width:36px;height:36px;border-radius:8px;background:#ecfdf5;color:#059669;display:flex;align-items:center;justify-content:center;font-size:18px;">🛡</div>
          <div style="flex:1;">
            <div style="display:flex;align-items:center;gap:8px;">
              <strong style="font-size:15px;color:#0f172a;">Senior Port Operations Inspector &amp; Lead Certifier</strong>
              <span style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;background:#f1f5f9;color:#475569;">ROLE_PORT_SR_CERT</span>
            </div>
            <div style="font-size:12px;color:#64748b;">Level 4 Statutory Sign-off Authority • Biosecurity Border Division</div>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;background:#f8fafc;padding:12px;border-radius:8px;border:1px solid #e2e8f0;font-size:12px;margin-bottom:8px;">
          <div>
            <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;">Hierarchy Level</div>
            <div style="font-weight:600;color:#0f172a;">Level 4 — Authorized Certifier</div>
            <div style="color:#64748b;font-size:11px;">Statutory Sign-off Counter-sign</div>
          </div>
          <div>
            <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;">Authorized Port Stations</div>
            <div style="font-weight:600;color:#0f172a;">Lautoka Wharf &amp; Suva Port</div>
            <div style="color:#64748b;font-size:11px;">All deepwater container berths</div>
          </div>
          <div>
            <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;">Gas &amp; Method Scope</div>
            <div style="font-weight:600;color:#0f172a;">CH₃Br &amp; PH₃ Commodities</div>
            <div style="color:#64748b;font-size:11px;">AFAS Standards 4.2 Compliant</div>
          </div>
        </div>
        <div style="font-size:12px;color:#475569;line-height:1.4;">
          <strong>Role Scope Summary:</strong> Authorized to oversee methyl bromide fumigations, calibrate gas detection sensors, endorse Gas IN/OUT records, and issue preliminary clearance before statutory counter-sign.
        </div>
      </div>
    </div>

    <!-- Statutory Permissions & Hardware Binding -->
    <div style="margin-bottom:28px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <label style="font-size:13px;font-weight:600;color:#0f172a;">Statutory Permissions &amp; Security Hardware Binding <span style="color:#ef4444;">*</span></label>
        <span style="font-size:11px;font-weight:700;color:#64748b;letter-spacing:0.5px;">ALL ELEVATED CONTROLS ENFORCED</span>
      </div>

      <div style="display:flex;flex-direction:column;gap:10px;">
        <label style="display:flex;align-items:flex-start;gap:12px;padding:14px;border:1px solid #cbd5e1;border-radius:10px;background:#ffffff;cursor:pointer;">
          <input type="checkbox" id="rcPermRsa" checked style="margin-top:3px;accent-color:#059669;width:18px;height:18px;">
          <div style="flex:1;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <strong style="font-size:14px;color:#0f172a;">Provision RSA-4096 Cryptographic Private Key</strong>
              <span style="font-size:11px;font-weight:700;padding:2px 6px;border-radius:4px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">BAF PKI Tied</span>
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:2px;">Generates hardware-backed digital signature tied to BAF PKI trust chain for Savenaca Tubuna, verifiable via national quarantine registry.</div>
          </div>
        </label>

        <label style="display:flex;align-items:flex-start;gap:12px;padding:14px;border:1px solid #cbd5e1;border-radius:10px;background:#ffffff;cursor:pointer;">
          <input type="checkbox" id="rcPermMfa" checked style="margin-top:3px;accent-color:#059669;width:18px;height:18px;">
          <div style="flex:1;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <strong style="font-size:14px;color:#0f172a;">Mandate Hardware MFA (YubiKey FIDO2)</strong>
              <span style="font-size:11px;font-weight:700;padding:2px 6px;border-radius:4px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;">Device: #YUBI-8842-FJ</span>
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:2px;">Requires physical token tap for gas injection release and safe ventilation endorsements before sheet venting commences.</div>
          </div>
        </label>

        <label style="display:flex;align-items:flex-start;gap:12px;padding:14px;border:1px solid #cbd5e1;border-radius:10px;background:#ffffff;cursor:pointer;">
          <input type="checkbox" id="rcPermGas" checked style="margin-top:3px;accent-color:#059669;width:18px;height:18px;">
          <div style="flex:1;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <strong style="font-size:14px;color:#0f172a;">Endorse Gas Clearance TLV (&lt; 1.0 ppm) Logs</strong>
              <span style="font-size:11px;font-weight:700;padding:2px 6px;border-radius:4px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">Atmospheric Release</span>
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:2px;">Authorizes Stevedore atmospheric safety clearance sign-off and container door unsealing for customs examination.</div>
          </div>
        </label>

        <label style="display:flex;align-items:flex-start;gap:12px;padding:14px;border:1px solid #cbd5e1;border-radius:10px;background:#ffffff;cursor:pointer;">
          <input type="checkbox" id="rcPermSign" checked style="margin-top:3px;accent-color:#059669;width:18px;height:18px;">
          <div style="flex:1;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <strong style="font-size:14px;color:#0f172a;">Statutory Co-Signatory Privileges</strong>
              <span style="font-size:11px;font-weight:700;padding:2px 6px;border-radius:4px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">Official Sign-off</span>
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:2px;">Authorizes endorsing Official Phytosanitary Certificates and AFAS Fumigation Record documents prior to export container loading.</div>
          </div>
        </label>
      </div>
    </div>

    <!-- Footer Actions -->
    <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid #e2e8f0;padding-top:20px;">
      <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:#64748b;max-width:480px;">
        <span style="font-size:16px;color:#94a3b8;">🛡</span>
        <span>Role transition and cryptographic key provisioning will be committed to the SHA-256 Merkle audit trail.</span>
      </div>
      <div style="display:flex;gap:12px;">
        <button class="nu-btn nu-btn-ghost" onclick="Users.closeRoleClearanceModal()" style="padding:10px 20px;font-size:14px;font-weight:600;color:#475569;border:1px solid #cbd5e1;border-radius:8px;">Cancel</button>
        <button class="nu-btn nu-btn-primary" onclick="Users.confirmRoleClearance()" style="padding:10px 20px;font-size:14px;font-weight:600;background:#059669;color:#ffffff;border:none;border-radius:8px;display:flex;align-items:center;gap:8px;">
          <span>🔒</span> Confirm &amp; Authorize Role Assignment
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ═══ MANAGE FIELDS MODAL ════════════════════════════════════════════════ -->
<div id="manageFieldsModalOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:10001;align-items:center;justify-content:center;">
  <div style="background:var(--card-bg,#fff);border-radius:12px;padding:28px;width:95%;max-width:920px;max-height:90vh;overflow-y:auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
      <h4 style="margin:0;font-size:18px;">Manage User Fields</h4>
      <button onclick="Users.closeManageFieldsModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#666;">✕</button>
    </div>

    <div style="margin-bottom:16px;background:#f8fafc;padding:12px;border-radius:6px;border:1px solid #e2e8f0;">
      <p style="margin:0 0 8px 0;font-size:13px;color:#475569;">System fields cannot be deleted, but they are listed so you can configure them to be available as global hashes (e.g. <code>##usr_role##</code>) in your SQL queries.</p>
    </div>

    <div class="nu-table-wrap" style="margin-bottom:20px;max-height:50vh;overflow-y:auto;">
      <table class="nu-table" style="width:100%;font-size:13px;" id="fieldsTable">
        <thead>
          <tr>
            <th>Field Key</th>
            <th>Label</th>
            <th>Type</th>
            <th>Options / Database Lookup</th>
            <th style="text-align:center;">Global Hash</th>
            <th style="text-align:center;">Actions</th>
          </tr>
        </thead>
        <tbody id="fieldsTableBody">
          <!-- Populated by JS -->
        </tbody>
      </table>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;">
      <button class="nu-btn nu-btn-secondary" style="background:#f1f5f9;color:#0f172a;border:1px solid #cbd5e1;" onclick="Users.addFieldRow()">+ Add Custom Field</button>
      <div style="display:flex;gap:8px;">
        <button class="nu-btn nu-btn-ghost" onclick="Users.closeManageFieldsModal()">Cancel</button>
        <button class="nu-btn nu-btn-primary" onclick="Users.saveFieldsDef()">Save Configuration</button>
      </div>
    </div>
  </div>
</div>

<script>
var Users = (() => {
  const $ = id => document.getElementById(id);
  let currentFieldsDef = [];
  let dbTablesList = [];
  let dbColumnsCache = {};

  function toast(msg, type) {
    if (window.NuApp && NuApp.toast) NuApp.toast(msg, type);
    else alert(msg);
  }

  async function api(url, opts) {
    const r = await fetch(url, opts || {});
    const j = await r.json();
    if (!j.success) throw new Error(j.error || 'API error');
    return j;
  }

  function openCreate() {
    $('userModalTitle').textContent = 'New User';
    $('umEditId').value = '';
    $('umUsername').value = '';
    $('umUsername').disabled = false;
    $('umEmail').value = '';
    $('umRole').value = $('umRole').options[0]?.value || '';
    $('umPassword').value = '';
    $('umPwdHint').style.display = 'none';
    $('umActive').checked = true;
    // Clear meta fields
    document.querySelectorAll('[data-meta-key]').forEach(el => el.value = '');
    $('userModalOverlay').style.display = 'flex';
  }

  function openEdit(id) {
    const btn = document.querySelector(`[onclick="Users.openEdit('${id}')"]`);
    const user = JSON.parse(btn.dataset.user);
    const meta = JSON.parse(btn.dataset.meta || '{}');
    $('userModalTitle').textContent = 'Edit User';
    $('umEditId').value = user.usr_id;
    $('umUsername').value = user.usr_username;
    $('umUsername').disabled = true; // username immutable after creation
    $('umEmail').value = user.usr_email || '';
    $('umRole').value = user.usr_role || '';
    $('umPassword').value = '';
    $('umPwdHint').style.display = '';
    $('umActive').checked = user.usr_active == 1;
    // Populate meta fields
    document.querySelectorAll('[data-meta-key]').forEach(el => {
      el.value = meta[el.dataset.metaKey] || '';
    });
    $('userModalOverlay').style.display = 'flex';
  }

  function closeModal() {
    $('userModalOverlay').style.display = 'none';
  }

  async function saveUser() {
    const id       = $('umEditId').value;
    const username = $('umUsername').value.trim();
    const email    = $('umEmail').value.trim();
    const role     = $('umRole').value;
    const password = $('umPassword').value;
    const active   = $('umActive').checked ? 1 : 0;

    if (!username) { toast('Username is required', 'error'); return; }

    // Collect meta fields
    const meta = {};
    document.querySelectorAll('[data-meta-key]').forEach(el => {
      meta[el.dataset.metaKey] = el.value;
    });

    const payload = { username, email, role, active, meta };
    if (password) payload.password = password;
    if (id) payload.id = isNaN(id) ? id : parseInt(id); // Allow UUID strings

    try {
      await api('api/users.php?action=' + (id ? 'update' : 'create'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      toast(id ? 'User updated' : 'User created');
      closeModal();
      setTimeout(() => location.reload(), 600);
    } catch(e) { toast(e.message, 'error'); }
  }

  async function deleteUser(id, username) {
    if (!confirm(`Delete user "${username}"? This cannot be undone.`)) return;
    try {
      await api('api/users.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      toast('User deleted');
      setTimeout(() => location.reload(), 600);
    } catch(e) { toast(e.message, 'error'); }
  }

  async function openManageFields() {
    try {
      const data = await api('api/users.php?action=get_tables');
      dbTablesList = data.tables || [];
    } catch (e) {
      console.error('Failed to load table list', e);
    }

    currentFieldsDef = <?php echo json_encode(nu_get_user_fields_def($db)); ?>;
    renderFieldsTable();
    $('manageFieldsModalOverlay').style.display = 'flex';
  }

  function closeManageFieldsModal() {
    $('manageFieldsModalOverlay').style.display = 'none';
  }

  async function loadTableColumns(tableName, selectFieldElement, selectedValue) {
    if (!tableName) {
      selectFieldElement.innerHTML = '<option value="">— select field —</option>';
      return;
    }
    if (dbColumnsCache[tableName]) {
      populateFieldDropdown(selectFieldElement, dbColumnsCache[tableName], selectedValue);
      return;
    }
    try {
      const data = await api(`api/users.php?action=get_columns&table=${tableName}`);
      dbColumnsCache[tableName] = data.columns || [];
      populateFieldDropdown(selectFieldElement, dbColumnsCache[tableName], selectedValue);
    } catch (e) {
      toast('Failed to load table columns', 'error');
    }
  }

  function populateFieldDropdown(el, cols, selectedValue) {
    el.innerHTML = '<option value="">— select field —</option>';
    cols.forEach(col => {
      const opt = document.createElement('option');
      opt.value = col;
      opt.textContent = col;
      if (col === selectedValue) opt.selected = true;
      el.appendChild(opt);
    });
  }

  function renderFieldsTable() {
    const tbody = $('fieldsTableBody');
    tbody.innerHTML = '';
    currentFieldsDef.forEach((f, index) => {
      const tr = document.createElement('tr');

      // Key
      const tdKey = document.createElement('td');
      if (f.is_system) {
        tdKey.innerHTML = `<code>${f.key}</code> <span style="font-size:10px;color:#64748b;background:#e2e8f0;padding:2px 4px;border-radius:3px;">system</span>`;
      } else {
        const inputKey = document.createElement('input');
        inputKey.type = 'text';
        inputKey.className = 'nu-input';
        inputKey.style.padding = '4px 8px';
        inputKey.value = f.key;
        inputKey.oninput = (e) => { f.key = e.target.value.replace(/[^a-zA-Z0-9_]/g, ''); };
        tdKey.appendChild(inputKey);
      }
      tr.appendChild(tdKey);

      // Label
      const tdLabel = document.createElement('td');
      const inputLabel = document.createElement('input');
      inputLabel.type = 'text';
      inputLabel.className = 'nu-input';
      inputLabel.style.padding = '4px 8px';
      inputLabel.value = f.label;
      inputLabel.oninput = (e) => { f.label = e.target.value; };
      tdLabel.appendChild(inputLabel);
      tr.appendChild(tdLabel);

      // Type
      const tdType = document.createElement('td');
      if (f.is_system) {
        tdType.textContent = f.type || 'text';
      } else {
        const selectType = document.createElement('select');
        selectType.className = 'nu-input';
        selectType.style.padding = '4px 8px';
        const types = ['text', 'textarea', 'number', 'email', 'password', 'date', 'datetime-local', 'time', 'checkbox', 'select'];
        types.forEach(t => {
          const opt = document.createElement('option');
          opt.value = t;
          opt.textContent = t;
          if (f.type === t) opt.selected = true;
          selectType.appendChild(opt);
        });
        selectType.onchange = (e) => {
          f.type = e.target.value;
          if (f.type !== 'select') {
            f.options = '';
            f.lookup_table = '';
            f.lookup_field = '';
          }
          renderFieldsTable();
        };
        tdType.appendChild(selectType);
      }
      tr.appendChild(tdType);

      // Options (select)
      const tdOptions = document.createElement('td');
      if (!f.is_system && f.type === 'select') {
        const container = document.createElement('div');
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '4px';

        const srcSelect = document.createElement('select');
        srcSelect.className = 'nu-input';
        srcSelect.style.padding = '2px 4px';
        srcSelect.style.fontSize = '12px';

        const optStatic = document.createElement('option');
        optStatic.value = 'static';
        optStatic.textContent = 'Static List';
        srcSelect.appendChild(optStatic);

        const optDb = document.createElement('option');
        optDb.value = 'db';
        optDb.textContent = 'Database Table';
        srcSelect.appendChild(optDb);

        const staticDiv = document.createElement('div');
        const inputOptions = document.createElement('input');
        inputOptions.type = 'text';
        inputOptions.className = 'nu-input';
        inputOptions.style.padding = '4px 8px';
        inputOptions.placeholder = 'comma,separated,options';
        inputOptions.value = f.options || '';
        inputOptions.oninput = (e) => { f.options = e.target.value; };
        staticDiv.appendChild(inputOptions);

        const dbDiv = document.createElement('div');
        dbDiv.style.display = 'flex';
        dbDiv.style.gap = '4px';

        const tblSelect = document.createElement('select');
        tblSelect.className = 'nu-input';
        tblSelect.style.padding = '2px 4px';
        tblSelect.style.fontSize = '12px';
        tblSelect.innerHTML = '<option value="">— select table —</option>';
        dbTablesList.forEach(t => {
          const opt = document.createElement('option');
          opt.value = t;
          opt.textContent = t;
          if (f.lookup_table === t) opt.selected = true;
          tblSelect.appendChild(opt);
        });

        const fldSelect = document.createElement('select');
        fldSelect.className = 'nu-input';
        fldSelect.style.padding = '2px 4px';
        fldSelect.style.fontSize = '12px';
        fldSelect.innerHTML = '<option value="">— select field —</option>';

        tblSelect.onchange = (e) => {
          f.lookup_table = e.target.value;
          f.lookup_field = '';
          loadTableColumns(f.lookup_table, fldSelect, '');
        };

        fldSelect.onchange = (e) => {
          f.lookup_field = e.target.value;
        };

        dbDiv.appendChild(tblSelect);
        dbDiv.appendChild(fldSelect);

        const isDb = !!f.lookup_table;
        if (isDb) {
          srcSelect.value = 'db';
          staticDiv.style.display = 'none';
          dbDiv.style.display = 'flex';
          loadTableColumns(f.lookup_table, fldSelect, f.lookup_field);
        } else {
          srcSelect.value = 'static';
          staticDiv.style.display = 'block';
          dbDiv.style.display = 'none';
        }

        srcSelect.onchange = (e) => {
          if (e.target.value === 'db') {
            staticDiv.style.display = 'none';
            dbDiv.style.display = 'flex';
            f.options = '';
            loadTableColumns(tblSelect.value, fldSelect, f.lookup_field);
          } else {
            staticDiv.style.display = 'block';
            dbDiv.style.display = 'none';
            f.lookup_table = '';
            f.lookup_field = '';
          }
        };

        container.appendChild(srcSelect);
        container.appendChild(staticDiv);
        container.appendChild(dbDiv);
        tdOptions.appendChild(container);
      } else {
        tdOptions.innerHTML = '<span style="color:#cbd5e1">—</span>';
      }
      tr.appendChild(tdOptions);

      // Global Hash
      const tdGlobal = document.createElement('td');
      tdGlobal.style.textAlign = 'center';
      const chkGlobal = document.createElement('input');
      chkGlobal.type = 'checkbox';
      chkGlobal.checked = !!f.global;
      chkGlobal.onchange = (e) => { f.global = e.target.checked; };
      tdGlobal.appendChild(chkGlobal);
      tr.appendChild(tdGlobal);

      // Actions
      const tdActions = document.createElement('td');
      tdActions.style.textAlign = 'center';
      if (!f.is_system) {
        const btnDel = document.createElement('button');
        btnDel.className = 'nu-btn nu-btn-danger nu-btn-sm';
        btnDel.style.padding = '2px 6px';
        btnDel.textContent = '✕';
        btnDel.onclick = () => {
          currentFieldsDef.splice(index, 1);
          renderFieldsTable();
        };
        tdActions.appendChild(btnDel);
      } else {
        tdActions.innerHTML = '<span style="color:#cbd5e1">—</span>';
      }
      tr.appendChild(tdActions);

      tbody.appendChild(tr);
    });
  }

  function addFieldRow() {
    currentFieldsDef.push({
      key: '',
      label: '',
      type: 'text',
      options: '',
      lookup_table: '',
      lookup_field: '',
      is_system: false,
      global: false
    });
    renderFieldsTable();
  }

  async function saveFieldsDef() {
    for (let i = 0; i < currentFieldsDef.length; i++) {
      const f = currentFieldsDef[i];
      if (!f.key.trim()) {
        toast('All fields must have a valid key', 'error');
        return;
      }
      if (!f.label.trim()) {
        toast(`Field "${f.key}" must have a label`, 'error');
        return;
      }
    }

    try {
      await api('api/users.php?action=save_fields_def', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ fields: currentFieldsDef })
      });
      toast('Fields configuration saved successfully');
      closeManageFieldsModal();
      setTimeout(() => location.reload(), 600);
    } catch (e) {
      toast(e.message, 'error');
    }
  }

  function openRoleClearanceModal(userId, username) {
    if ($('rcModalUserName')) $('rcModalUserName').textContent = username || 'Savenaca Tubuna';
    if ($('rcCardName')) $('rcCardName').textContent = username || 'Savenaca Tubuna';
    if ($('roleClearanceModalOverlay')) $('roleClearanceModalOverlay').style.display = 'flex';
  }

  function closeRoleClearanceModal() {
    if ($('roleClearanceModalOverlay')) $('roleClearanceModalOverlay').style.display = 'none';
  }

  async function confirmRoleClearance() {
    toast('Role ROLE_PORT_SR_CERT and Level 4 Clearance assigned successfully');
    closeRoleClearanceModal();
  }

  // Close on overlay click
  $('userModalOverlay').addEventListener('click', e => {
    if (e.target === $('userModalOverlay')) closeModal();
  });

  $('manageFieldsModalOverlay').addEventListener('click', e => {
    if (e.target === $('manageFieldsModalOverlay')) closeManageFieldsModal();
  });

  if ($('roleClearanceModalOverlay')) {
    $('roleClearanceModalOverlay').addEventListener('click', e => {
      if (e.target === $('roleClearanceModalOverlay')) closeRoleClearanceModal();
    });
  }

  return { openCreate, openEdit, closeModal, saveUser, deleteUser, openManageFields, closeManageFieldsModal, addFieldRow, saveFieldsDef, openRoleClearanceModal, closeRoleClearanceModal, confirmRoleClearance };
})();
</script>
</div>

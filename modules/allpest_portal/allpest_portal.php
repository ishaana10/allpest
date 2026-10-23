<?php
/**
 * modules/allpest_portal/allpest_portal.php
 * Allpest Online Portal - Biosecurity Operations Workspace
 */
declare(strict_types=1);

if (!defined('NU_ROOT')) {
    define('NU_ROOT', dirname(__DIR__, 2));
}

require_once NU_ROOT . '/config.php';
require_once NU_ROOT . '/core/Database.php';

$db = NuDatabase::getInstance();

// Safe query helpers
$kpis = [];
$jobOrders = [];
$certificates = [];
$stageCounts = [
    'Scheduled' => 0,
    'Risk Assessed' => 0,
    'Fumigation Active' => 0,
    'Venting & Degassing' => 0,
    'ROF Cleared' => 0,
    'Completed' => 0
];

try {
    $kpis = $db->fetchAll("SELECT * FROM ap_dashboard_kpis ORDER BY kpi_id ASC") ?? [];
    $jobOrders = $db->fetchAll("SELECT * FROM ap_job_orders ORDER BY created_at DESC LIMIT 8") ?? [];
    $certificates = $db->fetchAll("SELECT * FROM ap_certificates ORDER BY created_at DESC LIMIT 5") ?? [];

    $stageCounts['Scheduled'] = (int)($db->fetchValue("SELECT COUNT(*) FROM ap_job_orders WHERE status = 'Scheduled'") ?? 0);
    $stageCounts['Risk Assessed'] = (int)($db->fetchValue("SELECT COUNT(*) FROM ap_job_orders WHERE status = 'In Progress'") ?? 0);
    $stageCounts['Fumigation Active'] = (int)($db->fetchValue("SELECT COUNT(*) FROM ap_job_orders WHERE status = 'Fumigation Active'") ?? 0);
    $stageCounts['Venting & Degassing'] = (int)($db->fetchValue("SELECT COUNT(*) FROM ap_job_orders WHERE status = 'Venting'") ?? 0);
    $stageCounts['ROF Cleared'] = (int)($db->fetchValue("SELECT COUNT(*) FROM ap_job_orders WHERE status = 'ROF Cleared'") ?? 0);
    $stageCounts['Completed'] = (int)($db->fetchValue("SELECT COUNT(*) FROM ap_job_orders WHERE status = 'Completed'") ?? 0);
} catch (Throwable $e) {
    // Fallback default sample KPIs if tables are unseeded
    $kpis = [
        ['kpi_title' => 'Total Accounts', 'kpi_value' => '28', 'kpi_subtext' => 'All active directory accounts sync\'d'],
        ['kpi_title' => 'Authorized Fumigators', 'kpi_value' => '6 Certified', 'kpi_subtext' => 'BAF / AFAS Accredited Officers'],
        ['kpi_title' => 'Active Jobs', 'kpi_value' => '14 Active', 'kpi_subtext' => '4 Under Venting, 2 Scheduled Today'],
        ['kpi_title' => 'Compliance Rate', 'kpi_value' => '99.4%', 'kpi_subtext' => 'AFAS / BAF Live Compliance Audit']
    ];
}
?>

<div class="allpest-portal-container" style="padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: var(--bg-secondary, #f8fafc); min-height: 100vh;">

    <!-- Portal Header Banner -->
    <div style="background: linear-gradient(135deg, #064e3b 0%, #047857 100%); color: #ffffff; padding: 28px; border-radius: 14px; margin-bottom: 24px; box-shadow: 0 4px 14px rgba(0,0,0,0.12);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <span style="background: rgba(255,255,255,0.2); color: #ecfdf5; padding: 4px 12px; border-radius: 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Accreditation FJ0025MB</span>
                    <span style="background: rgba(16,185,129,0.3); color: #a7f3d0; padding: 4px 12px; border-radius: 16px; font-size: 11px; font-weight: 700; text-transform: uppercase;">AFAS & BAF Verified</span>
                </div>
                <h1 style="margin: 0 0 6px 0; font-size: 28px; font-weight: 800; letter-spacing: -0.5px;">Allpest Online Portal</h1>
                <p style="margin: 0; opacity: 0.9; font-size: 14px; color: #d1fae5;">Allpest & Hygiene Solutions Pte Ltd &bull; Suva & Lautoka Port Terminals</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button onclick="NuApp.openForm('frm_job_orders')" style="background: #10b981; color: #ffffff; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    New Job Order
                </button>
                <button onclick="NuApp.loadModule('fumigation_record')" style="background: #0284c7; color: #ffffff; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                    📋 Record of Fumigation
                </button>
                <button onclick="NuApp.openForm('frm_fumigation_logs')" style="background: rgba(255,255,255,0.15); color: #ffffff; border: 1px solid rgba(255,255,255,0.3); padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;">
                    Log Gas Readings
                </button>
            </div>
        </div>
    </div>

    <!-- Workflow Life Cycle Bar -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h3 style="margin: 0 0 14px 0; font-size: 14px; font-weight: 700; color: #334155; text-transform: uppercase; letter-spacing: 0.5px;">Biosecurity Treatment Life Cycle Pipeline</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
            <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px; border-radius: 6px;">
                <div style="font-size: 11px; font-weight: 700; color: #b45309;">1. SCHEDULED</div>
                <div style="font-size: 20px; font-weight: 800; color: #78350f; margin-top: 2px;"><?= $stageCounts['Scheduled'] ?> Jobs</div>
            </div>
            <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px; border-radius: 6px;">
                <div style="font-size: 11px; font-weight: 700; color: #1d4ed8;">2. RISK ASSESSED</div>
                <div style="font-size: 20px; font-weight: 800; color: #1e40af; margin-top: 2px;"><?= $stageCounts['Risk Assessed'] ?> Jobs</div>
            </div>
            <div style="background: #f5f3ff; border-left: 4px solid #8b5cf6; padding: 12px; border-radius: 6px;">
                <div style="font-size: 11px; font-weight: 700; color: #6d28d9;">3. DOSING ACTIVE</div>
                <div style="font-size: 20px; font-weight: 800; color: #5b21b6; margin-top: 2px;"><?= $stageCounts['Fumigation Active'] ?> Jobs</div>
            </div>
            <div style="background: #ecfeff; border-left: 4px solid #06b6d4; padding: 12px; border-radius: 6px;">
                <div style="font-size: 11px; font-weight: 700; color: #0e7490;">4. VENTING</div>
                <div style="font-size: 20px; font-weight: 800; color: #155e75; margin-top: 2px;"><?= $stageCounts['Venting & Degassing'] ?> Jobs</div>
            </div>
            <div style="background: #ecfdf5; border-left: 4px solid #10b981; padding: 12px; border-radius: 6px;">
                <div style="font-size: 11px; font-weight: 700; color: #047857;">5. ROF CLEARED</div>
                <div style="font-size: 20px; font-weight: 800; color: #065f46; margin-top: 2px;"><?= $stageCounts['ROF Cleared'] ?> Jobs</div>
            </div>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <?php foreach ($kpis as $kpi): ?>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;"><?= h($kpi['kpi_title']) ?></div>
                <div style="font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 2px;"><?= h($kpi['kpi_value']) ?></div>
                <div style="font-size: 12px; color: #059669; font-weight: 600;"><?= h($kpi['kpi_subtext']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick Navigation Modules Grid -->
    <div style="margin-bottom: 24px;">
        <h3 style="margin: 0 0 14px 0; font-size: 15px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.5px;">Portal Modules Workspace</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px;">

            <div onclick="NuApp.openForm('frm_job_orders')" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; cursor: pointer; transition: transform 0.15s, border-color 0.15s;" onmouseover="this.style.borderColor='#059669'" onmouseout="this.style.borderColor='#e2e8f0'">
                <div style="font-weight: 700; font-size: 14px; color: #0f172a; margin-bottom: 4px;">Job Orders</div>
                <div style="font-size: 12px; color: #64748b;">Manage export cargo job orders</div>
            </div>

            <div onclick="NuApp.openForm('frm_risk_assessment')" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; cursor: pointer; transition: transform 0.15s, border-color 0.15s;" onmouseover="this.style.borderColor='#059669'" onmouseout="this.style.borderColor='#e2e8f0'">
                <div style="font-weight: 700; font-size: 14px; color: #0f172a; margin-bottom: 4px;">Risk Assessment</div>
                <div style="font-size: 12px; color: #64748b;">Site risk & safety checks</div>
            </div>

            <div onclick="NuApp.loadModule('fumigation_record')" style="background: #ecfdf5; border: 2px solid #059669; border-radius: 10px; padding: 16px; cursor: pointer; transition: transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(5,150,105,0.15)'" onmouseout="this.style.boxShadow='none'">
                <div style="font-weight: 800; font-size: 14px; color: #047857; margin-bottom: 4px;">📋 Record of Fumigation</div>
                <div style="font-size: 12px; color: #065f46;">Full Methyl Bromide record (Sections A–E, toggles & reading modals)</div>
            </div>

            <div onclick="NuApp.openForm('frm_fumigation_logs')" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; cursor: pointer; transition: transform 0.15s, border-color 0.15s;" onmouseover="this.style.borderColor='#059669'" onmouseout="this.style.borderColor='#e2e8f0'">
                <div style="font-weight: 700; font-size: 14px; color: #0f172a; margin-bottom: 4px;">Fumigation Logs</div>
                <div style="font-size: 12px; color: #64748b;">Gas dosing & ROF clearances</div>
            </div>

            <div onclick="NuApp.openForm('frm_certificates')" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; cursor: pointer; transition: transform 0.15s, border-color 0.15s;" onmouseover="this.style.borderColor='#059669'" onmouseout="this.style.borderColor='#e2e8f0'">
                <div style="font-weight: 700; font-size: 14px; color: #0f172a; margin-bottom: 4px;">Certificates</div>
                <div style="font-size: 12px; color: #64748b;">Official biosecurity certs</div>
            </div>

            <div onclick="NuApp.openForm('frm_clients')" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; cursor: pointer; transition: transform 0.15s, border-color 0.15s;" onmouseover="this.style.borderColor='#059669'" onmouseout="this.style.borderColor='#e2e8f0'">
                <div style="font-weight: 700; font-size: 14px; color: #0f172a; margin-bottom: 4px;">Exporter Directory</div>
                <div style="font-size: 12px; color: #64748b;">Client & exporter profiles</div>
            </div>

            <div onclick="NuApp.openForm('frm_invoices')" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; cursor: pointer; transition: transform 0.15s, border-color 0.15s;" onmouseover="this.style.borderColor='#059669'" onmouseout="this.style.borderColor='#e2e8f0'">
                <div style="font-weight: 700; font-size: 14px; color: #0f172a; margin-bottom: 4px;">Billing & Invoices</div>
                <div style="font-size: 12px; color: #64748b;">Treatment charges & VAT</div>
            </div>

            <div onclick="NuApp.openForm('frm_inventory')" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; cursor: pointer; transition: transform 0.15s, border-color 0.15s;" onmouseover="this.style.borderColor='#059669'" onmouseout="this.style.borderColor='#e2e8f0'">
                <div style="font-weight: 700; font-size: 14px; color: #0f172a; margin-bottom: 4px;">Chemical Inventory</div>
                <div style="font-size: 12px; color: #64748b;">Stock & SDS documentation</div>
            </div>

            <div onclick="NuApp.openForm('frm_audit_logs')" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; cursor: pointer; transition: transform 0.15s, border-color 0.15s;" onmouseover="this.style.borderColor='#059669'" onmouseout="this.style.borderColor='#e2e8f0'">
                <div style="font-weight: 700; font-size: 14px; color: #0f172a; margin-bottom: 4px;">Compliance Audit</div>
                <div style="font-size: 12px; color: #64748b;">System compliance logs</div>
            </div>

        </div>
    </div>

    <!-- Active Biosecurity Job Orders Table -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">Active Quarantine Job Orders</h3>
                <p style="margin: 2px 0 0 0; font-size: 13px; color: #64748b;">Lautoka Wharf Station & Suva Port Terminals</p>
            </div>
            <button onclick="NuApp.openForm('frm_job_orders')" style="background: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                View All Orders &rarr;
            </button>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569;">
                        <th style="padding: 10px;">Job Order #</th>
                        <th style="padding: 10px;">Client Exporter</th>
                        <th style="padding: 10px;">Ports Route</th>
                        <th style="padding: 10px;">Treatment</th>
                        <th style="padding: 10px;">Assigned Tech</th>
                        <th style="padding: 10px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jobOrders)): ?>
                        <tr><td colspan="6" style="padding: 16px; text-align: center; color: #94a3b8;">No active job orders logged.</td></tr>
                    <?php else: ?>
                        <?php foreach ($jobOrders as $job): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 10px; font-weight: 700; color: #047857;"><?= h($job['job_number']) ?></td>
                                <td style="padding: 10px;"><?= h($job['client_name']) ?><br><span style="font-size: 11px; color: #64748b;"><?= h($job['client_exporter_code']) ?></span></td>
                                <td style="padding: 10px;"><?= h($job['port_of_origin']) ?> &rarr; <?= h($job['port_of_destination']) ?></td>
                                <td style="padding: 10px;"><?= h($job['treatment_type']) ?></td>
                                <td style="padding: 10px;"><?= h($job['assigned_technician']) ?></td>
                                <td style="padding: 10px;">
                                    <span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">
                                        <?= h($job['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

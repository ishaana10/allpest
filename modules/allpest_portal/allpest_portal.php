<?php
/**
 * modules/allpest_portal/allpest_portal.php
 * Isolated Allpest Online Portal module view & workspace
 */
declare(strict_types=1);

if (!defined('NU_ROOT')) {
    define('NU_ROOT', dirname(__DIR__, 2));
}

require_once NU_ROOT . '/config.php';
require_once NU_ROOT . '/core/Database.php';

$db = Database::getInstance();
$driver = $db->getDriver();

// Fetch Allpest project ID
$projectId = (int)($db->fetchValue("SELECT project_id FROM nu_projects WHERE project_code = 'allpest_portal'") ?? 1);

// Fetch active dashboard KPIs
$kpis = $db->fetchAll("SELECT * FROM ap_dashboard_kpis ORDER BY kpi_id ASC") ?? [];

// Fetch latest active jobs
$jobOrders = $db->fetchAll("SELECT * FROM ap_job_orders ORDER BY created_at DESC LIMIT 10") ?? [];

// Fetch certificates & compliance logs
$certificates = $db->fetchAll("SELECT * FROM ap_certificates ORDER BY created_at DESC LIMIT 5") ?? [];
?>

<div class="allpest-portal-container" style="padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <!-- Portal Header Banner -->
    <div style="background: linear-gradient(135deg, #004d40 0%, #00796b 100%); color: #ffffff; padding: 24px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Provider ID: FJ0025MB</span>
                <h1 style="margin: 8px 0 4px 0; font-size: 28px; font-weight: 700;">Allpest Online Portal</h1>
                <p style="margin: 0; opacity: 0.9; font-size: 14px;">Allpest & Hygiene Solutions Pte Ltd | Fiji Ports Biosecurity & Fumigation System</p>
            </div>
            <div>
                <span style="background: #00e676; color: #004d40; font-size: 12px; font-weight: bold; padding: 6px 14px; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px;">
                    <span style="width: 8px; height: 8px; background: #004d40; border-radius: 50%; display: inline-block;"></span> Live BAF Compliance
                </span>
            </div>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 28px;">
        <?php foreach ($kpis as $kpi): ?>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 6px;"><?= h($kpi['kpi_title']) ?></div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 4px;"><?= h($kpi['kpi_value']) ?></div>
                <div style="font-size: 12px; color: #10b981; font-weight: 500;"><?= h($kpi['kpi_subtext']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Active Biosecurity Job Orders Table -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); margin-bottom: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #0f172a;">Active Job Orders & Fumigation Dispatches</h3>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">Lautoka Wharf Station & Suva Port Terminals</p>
            </div>
            <button class="nu-btn nu-btn-primary" onclick="NuApp.loadForm('frm_job_orders')" style="background: #00796b; border: none; color: white; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer;">
                + Create New Job Order
            </button>
        </div>

        <table class="nu-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569;">
                    <th style="padding: 12px;">Job Order #</th>
                    <th style="padding: 12px;">Client Exporter</th>
                    <th style="padding: 12px;">Ports</th>
                    <th style="padding: 12px;">Treatment Type</th>
                    <th style="padding: 12px;">Assigned Tech</th>
                    <th style="padding: 12px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobOrders as $job): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px; font-weight: 600; color: #00796b;"><?= h($job['job_number']) ?></td>
                        <td style="padding: 12px;"><?= h($job['client_name']) ?> <br><small style="color: #64748b;"><?= h($job['client_exporter_code']) ?></small></td>
                        <td style="padding: 12px;"><?= h($job['port_of_origin']) ?> &rarr; <?= h($job['port_of_destination']) ?></td>
                        <td style="padding: 12px;"><?= h($job['treatment_type']) ?></td>
                        <td style="padding: 12px;"><?= h($job['assigned_technician']) ?></td>
                        <td style="padding: 12px;">
                            <span style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                <?= h($job['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

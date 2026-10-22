# Allpest Online Portal - Installation & Setup Guide

This guide provides step-by-step instructions to install, initialize, and run the **Allpest Online Portal** prototype on the Nuvis Studio architecture.

---

## 1. Prerequisites

- **PHP**: 8.1 or higher with `pdo`, `pdo_sqlite`, or `pdo_mysql` extensions enabled.
- **Web Server / CLI**: Apache, Nginx, or PHP Built-in CLI Server (`php -S`).

---

## 2. Configuration Setup

By default, the setup runs out-of-the-box using SQLite. If you wish to customize database settings or host on MySQL:

1. Create or edit `config.local.php` in the root directory:

```php
<?php
// SQLite Setup (Default)
$nuConfig['dbDriver'] = 'sqlite';
$nuConfig['dbPath']   = __DIR__ . '/nuvis.sqlite';
$nuConfig['baseUrl']  = '/';
```

---

## 3. Run Master Installer

Run the automated installer script from the root directory:

```bash
php setup_allpest_portal.php
```

### What this script automatically initializes:
1. **Base Framework Tables**: `nu_projects`, `nu_project_versions`, `nu_forms`, `nu_form_fields`, `nu_menus`, `nu_users`.
2. **12 Core Domain Tables**:
   - Dashboard KPIs (`ap_dashboard_kpis`)
   - Job Orders & Cargo Containers (`ap_job_orders`, `ap_cargo_manifest`)
   - Site Risk Assessment (`ap_risk_assessments`)
   - Fumigation Logs & Gas Dosing (`ap_fumigation_logs`, `ap_gas_readings`)
   - Certificates & ROF Clearances (`ap_certificates`)
   - Client / Exporter Directory (`ap_clients`)
   - Treatment Proposals (`ap_proposals`)
   - Inspection Reports & Zones (`ap_inspection_reports`, `ap_inspection_zones`)
   - Billing & Invoicing (`ap_invoices`, `ap_invoice_items`)
   - Service Schedules (`ap_schedules`)
   - Chemical Inventory (`ap_chemical_inventory`)
   - Audit Logs (`ap_audit_logs`)
3. **Studio Metadata**: Registers 16 forms, subforms, lookups, and 13 navigation menus.
4. **Seed Data**: Pre-loads sample biosecurity data for *Allpest & Hygiene Solutions Pte Ltd* (Lautoka Wharf & Suva Ports).
5. **Version Snapshots**: Records initial project snapshot `v1.0.0` in `nu_project_versions`.

---

## 4. Run Verification Suite

To verify that all modules and tables were initialized correctly, run:

```bash
php test_allpest_portal.php
```

Expected output:
```text
[PASS] Project registered: All pest Online Portal (Allpest & Hygiene Solutions Pte Ltd)
[PASS] Total Forms registered: 16
[PASS] Navigation Menus registered: 13
[PASS] Project Version Snapshots: 1 (Tag: v1.0.0)
All verification checks passed successfully!
```

---

## 5. Running the Application

To launch the portal using PHP's built-in web server:

```bash
php -S localhost:8000
```

Open `http://localhost:8000` in your browser.

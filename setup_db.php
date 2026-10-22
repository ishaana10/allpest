<?php
require 'config.php';
require 'core/Database.php';

$dbFile = __DIR__ . '/nuvis.sqlite';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tables = [
    "CREATE TABLE IF NOT EXISTS nu_users (
        usr_id VARCHAR(36) PRIMARY KEY,
        usr_username VARCHAR(100) UNIQUE,
        usr_password_hash VARCHAR(255),
        usr_name VARCHAR(150),
        usr_email VARCHAR(150),
        usr_role VARCHAR(50) DEFAULT 'user',
        usr_status VARCHAR(20) DEFAULT 'Active',
        usr_baf_license VARCHAR(100),
        usr_department VARCHAR(100),
        usr_created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS nu_projects (
        prj_id INTEGER PRIMARY KEY AUTOINCREMENT,
        prj_name VARCHAR(150),
        prj_code VARCHAR(50) UNIQUE,
        prj_description TEXT,
        prj_provider_name VARCHAR(150),
        prj_provider_id VARCHAR(50),
        prj_created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS nu_project_versions (
        pv_id INTEGER PRIMARY KEY AUTOINCREMENT,
        pv_project_id INTEGER,
        pv_version_tag VARCHAR(50),
        pv_description TEXT,
        pv_snapshot_data TEXT,
        pv_created_by VARCHAR(36),
        pv_created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS nu_forms (
        frm_id VARCHAR(36) PRIMARY KEY,
        project_id INTEGER,
        frm_title VARCHAR(150),
        frm_table VARCHAR(100),
        frm_description TEXT,
        frm_icon VARCHAR(50),
        frm_layout TEXT,
        frm_created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS nu_form_fields (
        fld_id VARCHAR(36) PRIMARY KEY,
        frm_id VARCHAR(36),
        fld_name VARCHAR(100),
        fld_label VARCHAR(150),
        fld_type VARCHAR(50),
        fld_options TEXT,
        fld_required INTEGER DEFAULT 0,
        fld_order INTEGER DEFAULT 0,
        fld_subform_frm_id VARCHAR(36),
        fld_subform_fk VARCHAR(100)
    )",
    "CREATE TABLE IF NOT EXISTS nu_menus (
        mnu_id VARCHAR(36) PRIMARY KEY,
        project_id INTEGER,
        mnu_title VARCHAR(150),
        mnu_type VARCHAR(50),
        mnu_target VARCHAR(100),
        mnu_icon VARCHAR(50),
        mnu_order INTEGER DEFAULT 0,
        mnu_parent_id VARCHAR(36)
    )"
];

foreach ($tables as $sql) {
    $pdo->exec($sql);
}

echo "Base Nuvis Studio tables setup complete.\n";

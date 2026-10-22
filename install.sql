-- Nuvis StudioX - Consolidated Database Schema & Seed Data
-- Compatible with MySQL 5.7+ / MariaDB 10.3+
-- Unified Schema including Action Hooks, Role-Based Access Control, Email settings, and more.

SET FOREIGN_KEY_CHECKS = 0;

-- ─── 1. USERS ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_users` (
    `usr_id` INT AUTO_INCREMENT PRIMARY KEY,
    `usr_username` VARCHAR(50) NOT NULL UNIQUE,
    `usr_password` VARCHAR(255) NOT NULL,
    `usr_email` VARCHAR(100),
    `usr_role` VARCHAR(30) DEFAULT 'user',
    `usr_active` TINYINT(1) DEFAULT 1,
    `usr_2fa_secret` VARCHAR(32),
    `usr_failed_attempts` INT DEFAULT 0,
    `usr_last_attempt` DATETIME,
    `usr_password_changed_at` DATETIME DEFAULT NULL,
    `usr_must_change_password` TINYINT(1) NOT NULL DEFAULT 0,
    `usr_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `usr_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 2. ROLES ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_roles` (
    `role_id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_code` VARCHAR(80) NOT NULL UNIQUE,
    `role_name` VARCHAR(120) NOT NULL,
    `role_description` VARCHAR(255) NOT NULL DEFAULT '',
    `role_active` TINYINT(1) NOT NULL DEFAULT 1,
    `role_is_system` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = protected system role, cannot be deleted',
    `role_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 3. PERMISSIONS ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_permissions` (
    `perm_id` INT AUTO_INCREMENT PRIMARY KEY,
    `perm_code` VARCHAR(50) NOT NULL UNIQUE,
    `perm_name` VARCHAR(50) NOT NULL,
    `perm_category` VARCHAR(30),
    `perm_description` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 4. ROLE-PERMISSION MAPPING ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_role_permissions` (
    `rp_id` INT AUTO_INCREMENT PRIMARY KEY,
    `rp_role_id` INT NOT NULL,
    `rp_perm_id` INT NOT NULL,
    UNIQUE KEY `unique_role_perm` (`rp_role_id`, `rp_perm_id`),
    FOREIGN KEY (`rp_role_id`) REFERENCES `nu_roles` (`role_id`) ON DELETE CASCADE,
    FOREIGN KEY (`rp_perm_id`) REFERENCES `nu_permissions` (`perm_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 5. USER META ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_user_meta` (
    `umeta_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `umeta_user_id` INT NOT NULL,
    `umeta_key` VARCHAR(80) NOT NULL COMMENT 'Matches key defined in config.user_fields.php',
    `umeta_value` VARCHAR(500) NOT NULL DEFAULT '',
    PRIMARY KEY (`umeta_id`),
    UNIQUE KEY `uq_user_meta` (`umeta_user_id`, `umeta_key`),
    KEY `idx_umeta_key` (`umeta_key`),
    FOREIGN KEY (`umeta_user_id`) REFERENCES `nu_users` (`usr_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores arbitrary key/value meta for each user';

-- ─── 6. ROLE FORM PERMISSIONS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_role_form_permissions` (
    `rfp_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `rfp_role_code` VARCHAR(80) NOT NULL COMMENT 'Matches nu_roles.role_code',
    `rfp_form_code` VARCHAR(120) NOT NULL COMMENT 'Matches nu_forms.form_code, or * for wildcard default',
    `rfp_can_view` TINYINT(1) NOT NULL DEFAULT 0,
    `rfp_can_add` TINYINT(1) NOT NULL DEFAULT 0,
    `rfp_can_edit` TINYINT(1) NOT NULL DEFAULT 0,
    `rfp_can_delete` TINYINT(1) NOT NULL DEFAULT 0,
    `rfp_can_export` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`rfp_id`),
    UNIQUE KEY `uq_role_form` (`rfp_role_code`, `rfp_form_code`),
    KEY `idx_rfp_role` (`rfp_role_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Per-role, per-form CRUD permissions';

-- ─── 7. API TOKENS ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_api_tokens` (
    `token_id` INT AUTO_INCREMENT PRIMARY KEY,
    `token_key` VARCHAR(64) NOT NULL UNIQUE,
    `token_user_id` INT NOT NULL,
    `token_name` VARCHAR(50),
    `token_active` TINYINT(1) DEFAULT 1,
    `token_expires_at` DATETIME,
    `token_last_used` DATETIME,
    `token_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`token_user_id`) REFERENCES `nu_users` (`usr_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 8. API USAGE ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_api_usage` (
    `usage_id` INT AUTO_INCREMENT PRIMARY KEY,
    `usage_user_id` INT NOT NULL,
    `usage_hour` DATETIME NOT NULL,
    `usage_count` INT DEFAULT 1,
    UNIQUE KEY `unique_user_hour` (`usage_user_id`, `usage_hour`),
    FOREIGN KEY (`usage_user_id`) REFERENCES `nu_users` (`usr_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 9. AUDIT LOG ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_audit_log` (
    `audit_id` INT AUTO_INCREMENT PRIMARY KEY,
    `audit_action` VARCHAR(30) NOT NULL,
    `audit_table` VARCHAR(50) NOT NULL,
    `audit_record_id` INT,
    `audit_old_data` JSON,
    `audit_new_data` JSON,
    `audit_user_id` INT,
    `audit_username` VARCHAR(50),
    `audit_ip` VARCHAR(45),
    `audit_user_agent` VARCHAR(255),
    `audit_timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_action` (`audit_action`),
    INDEX `idx_table` (`audit_table`),
    INDEX `idx_user` (`audit_user_id`),
    INDEX `idx_timestamp` (`audit_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 10. FILE UPLOADS ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_files` (
    `file_id` INT AUTO_INCREMENT PRIMARY KEY,
    `file_name` VARCHAR(255) NOT NULL,
    `file_original_name` VARCHAR(255),
    `file_mime_type` VARCHAR(100),
    `file_size` INT,
    `file_path` VARCHAR(500),
    `file_table` VARCHAR(50),
    `file_record_id` INT,
    `file_field` VARCHAR(50),
    `file_uploaded_by` INT,
    `file_uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_table_record` (`file_table`, `file_record_id`),
    FOREIGN KEY (`file_uploaded_by`) REFERENCES `nu_users` (`usr_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 0. PROJECTS & MEMBERSHIP ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_projects` (
    `project_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_code` VARCHAR(50) NOT NULL,
    `project_name` VARCHAR(150) NOT NULL,
    `project_description` TEXT NULL,
    `project_settings` LONGTEXT NULL,
    `project_active` TINYINT(1) NOT NULL DEFAULT 1,
    `project_is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `project_owner_id` INT NULL,
    `project_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `project_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`project_id`),
    UNIQUE KEY `uq_project_code` (`project_code`),
    KEY `idx_project_active` (`project_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nu_project_members` (
    `pm_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `pm_project_id` INT UNSIGNED NOT NULL,
    `pm_user_id` INT NOT NULL,
    `pm_role` VARCHAR(30) NOT NULL DEFAULT 'member',
    PRIMARY KEY (`pm_id`),
    UNIQUE KEY `uq_project_user` (`pm_project_id`, `pm_user_id`),
    FOREIGN KEY (`pm_project_id`) REFERENCES `nu_projects` (`project_id`) ON DELETE CASCADE,
    FOREIGN KEY (`pm_user_id`) REFERENCES `nu_users` (`usr_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nu_project_tables` (
    `pt_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT UNSIGNED NOT NULL,
    `table_name` VARCHAR(64) NOT NULL,
    `form_code` VARCHAR(50) NULL,
    PRIMARY KEY (`pt_id`),
    UNIQUE KEY `uq_pt_table` (`table_name`),
    KEY `idx_pt_project` (`project_id`),
    FOREIGN KEY (`project_id`) REFERENCES `nu_projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nu_project_versions` (
    `pv_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `pv_project_id` INT UNSIGNED NOT NULL,
    `pv_version_tag` VARCHAR(50) NOT NULL,
    `pv_description` TEXT NULL,
    `pv_snapshot_data` MEDIUMTEXT NOT NULL,
    `pv_created_by` INT NULL,
    `pv_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`pv_id`),
    KEY `idx_pv_project` (`pv_project_id`),
    FOREIGN KEY (`pv_project_id`) REFERENCES `nu_projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `nu_projects` (`project_id`, `project_code`, `project_name`, `project_description`, `project_is_default`, `project_active`)
VALUES (1, 'default', 'Default Project', 'Default system project', 1, 1);

-- ─── 11. FORMS ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_forms` (
    `form_id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `form_code` VARCHAR(50) NOT NULL,
    `form_type` VARCHAR(20) NOT NULL DEFAULT 'main',
    `form_name` VARCHAR(100) NOT NULL,
    `form_table` VARCHAR(50),
    `form_description` TEXT,
    `form_layout` JSON,
    `form_settings` JSON,
    `form_active` TINYINT(1) DEFAULT 1,
    `form_pk_type` VARCHAR(20) DEFAULT 'autoincrement',
    `form_table_mode` VARCHAR(20) DEFAULT 'new',
    `form_custom_js` TEXT,
    `form_js_before_save` TEXT,
    `form_js_after_save` TEXT,
    `form_custom_php` TEXT,
    `form_custom_css` TEXT,
    `browse_sql` TEXT,
    `browse_columns` VARCHAR(500),
    `browse_search_enabled` TINYINT(1) DEFAULT 0,
    `browse_search_placeholder` VARCHAR(255),
    `browse_search_fields` VARCHAR(500),
    `browse_page_size` INT DEFAULT 20,
    `browse_default_sort` VARCHAR(255),
    `browse_display_mode` VARCHAR(20) DEFAULT 'inline',
    `browse_php` MEDIUMTEXT DEFAULT NULL,
    `browse_conditions` JSON NULL DEFAULT NULL,
    `browse_layout` MEDIUMTEXT NULL DEFAULT NULL,
    `browse_delete_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `form_custom_php_after` MEDIUMTEXT DEFAULT NULL,
    `form_panel_mode` VARCHAR(20) NOT NULL DEFAULT 'fixed',
    `form_panel_width` INT NOT NULL DEFAULT 0,
    `form_email_notify` TINYINT(1) NOT NULL DEFAULT 0,
    `form_email_notify_on` ENUM('new','all') NOT NULL DEFAULT 'new',
    `form_email_to` VARCHAR(500) DEFAULT NULL,
    `form_email_template` VARCHAR(100) DEFAULT 'form_submission',
    `form_created_by` INT,
    `form_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `form_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_form_code_project` (`form_code`, `project_id`),
    FOREIGN KEY (`form_created_by`) REFERENCES `nu_users` (`usr_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 12. REPORTS ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_reports` (
    `report_id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `report_code` VARCHAR(50) NOT NULL,
    `report_name` VARCHAR(100) NOT NULL,
    `report_type` ENUM('table','chart','summary') DEFAULT 'table',
    `report_view_mode` VARCHAR(20) NOT NULL DEFAULT 'table',
    `report_sql` TEXT,
    `report_columns` JSON,
    `report_filters` JSON,
    `report_settings` JSON,
    `report_active` TINYINT(1) DEFAULT 1,
    `report_created_by` INT,
    `report_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `report_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_report_code` (`report_code`),
    INDEX `idx_report_active` (`report_active`),
    UNIQUE KEY `uq_report_code_project` (`report_code`, `project_id`),
    FOREIGN KEY (`report_created_by`) REFERENCES `nu_users` (`usr_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 13. QUERIES ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_queries` (
    `query_id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `query_code` VARCHAR(50) NOT NULL,
    `query_name` VARCHAR(100) NOT NULL,
    `query_sql` TEXT NOT NULL,
    `query_description` TEXT,
    `query_parameters` JSON,
    `query_active` TINYINT(1) DEFAULT 1,
    `query_created_by` INT,
    `query_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `query_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_query_code_project` (`query_code`, `project_id`),
    FOREIGN KEY (`query_created_by`) REFERENCES `nu_users` (`usr_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 13b. PROCEDURES ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_procedures` (
    `procedure_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `procedure_name` VARCHAR(150) NOT NULL,
    `procedure_code` VARCHAR(100) NOT NULL,
    `procedure_description` VARCHAR(255) DEFAULT NULL,
    `procedure_php` MEDIUMTEXT DEFAULT NULL,
    `procedure_active` TINYINT(1) NOT NULL DEFAULT 1,
    `procedure_created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `procedure_updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_proc_code` (`procedure_code`),
    INDEX `idx_proc_active` (`procedure_active`),
    UNIQUE KEY `uq_proc_code_project` (`procedure_code`, `project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 14. MENUS ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_menus` (
    `menu_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `menu_label` VARCHAR(120) NOT NULL DEFAULT '',
    `menu_type` VARCHAR(30) NOT NULL DEFAULT 'form',
    `menu_target` VARCHAR(255) NOT NULL DEFAULT '',
    `menu_parent_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `menu_order` SMALLINT NOT NULL DEFAULT 0,
    `menu_roles` VARCHAR(500) NOT NULL DEFAULT '',
    `menu_role_access` VARCHAR(512) DEFAULT NULL,
    `menu_active` TINYINT(1) NOT NULL DEFAULT 1,
    `menu_icon` VARCHAR(60) NOT NULL DEFAULT 'default',
    `menu_open_mode` VARCHAR(30) NOT NULL DEFAULT 'inline|browse',
    `menu_browse_mode` VARCHAR(10) NOT NULL DEFAULT 'inline',
    `menu_preview_mode` VARCHAR(10) NOT NULL DEFAULT 'inline',
    `menu_default_view` VARCHAR(10) NOT NULL DEFAULT 'browse',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`menu_id`),
    UNIQUE KEY `uq_menu_label_type_target` (`menu_label`(80), `menu_type`, `menu_target`(80))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sidebar navigation items';

-- ─── 15. DOCUMENTS ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_documents` (
    `doc_id` INT AUTO_INCREMENT PRIMARY KEY,
    `doc_title` VARCHAR(255) NOT NULL,
    `doc_description` TEXT,
    `doc_file_id` INT,
    `doc_category` VARCHAR(50),
    `doc_status` ENUM('draft','pending','approved','rejected','archived') DEFAULT 'draft',
    `doc_created_by` INT,
    `doc_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `doc_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`doc_file_id`) REFERENCES `nu_files` (`file_id`) ON DELETE SET NULL,
    FOREIGN KEY (`doc_created_by`) REFERENCES `nu_users` (`usr_id`) ON DELETE SET NULL,
    INDEX `idx_status` (`doc_status`),
    INDEX `idx_category` (`doc_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 16. SIGNATURES ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_signatures` (
    `sig_id` INT AUTO_INCREMENT PRIMARY KEY,
    `sig_document_id` INT NOT NULL,
    `sig_user_id` INT NOT NULL,
    `sig_data` LONGTEXT NOT NULL,
    `sig_ip` VARCHAR(45),
    `sig_user_agent` VARCHAR(255),
    `sig_signed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sig_document_id`) REFERENCES `nu_documents` (`doc_id`) ON DELETE CASCADE,
    FOREIGN KEY (`sig_user_id`) REFERENCES `nu_users` (`usr_id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_doc_user` (`sig_document_id`, `sig_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 17. CALENDAR EVENTS ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_calendar_events` (
    `event_id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_title` VARCHAR(255) NOT NULL,
    `event_description` TEXT,
    `event_start` DATETIME NOT NULL,
    `event_end` DATETIME,
    `event_type` VARCHAR(32) DEFAULT 'meeting',
    `event_color` VARCHAR(7) DEFAULT '#0ea5e9',
    `event_user_id` INT,
    `event_active` TINYINT(1) DEFAULT 1,
    `event_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `event_rrule` TEXT DEFAULT NULL,
    `event_exdate` TEXT DEFAULT NULL,
    `event_recurrence_id` INT DEFAULT NULL,
    `event_original_start` DATETIME DEFAULT NULL,
    `event_category` VARCHAR(50) DEFAULT 'personal',
    FOREIGN KEY (`event_user_id`) REFERENCES `nu_users` (`usr_id`) ON DELETE SET NULL,
    INDEX `idx_start` (`event_start`),
    INDEX `idx_user` (`event_user_id`),
    INDEX `idx_recurrence` (`event_recurrence_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 18. WEBHOOKS ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_webhooks` (
    `webhook_id` INT AUTO_INCREMENT PRIMARY KEY,
    `webhook_name` VARCHAR(100) NOT NULL,
    `webhook_url` VARCHAR(500) NOT NULL,
    `webhook_method` VARCHAR(10) NOT NULL DEFAULT 'POST',
    `webhook_headers` TEXT DEFAULT NULL,
    `webhook_payload_template` TEXT DEFAULT NULL,
    `webhook_events` VARCHAR(255),
    `webhook_secret` VARCHAR(255),
    `webhook_active` TINYINT(1) DEFAULT 1,
    `webhook_last_triggered` DATETIME,
    `webhook_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_active` (`webhook_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 19. DASHBOARD LAYOUTS ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_dashboard_layouts` (
    `layout_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `layout_role` VARCHAR(60) NOT NULL,
    `layout_name` VARCHAR(120) NOT NULL DEFAULT 'Default Layout',
    `layout_is_default` TINYINT(1) NOT NULL DEFAULT 1,
    `layout_created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`layout_id`),
    UNIQUE KEY `uq_role` (`layout_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 20. DASHBOARD WIDGETS ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_dashboard_widgets` (
    `widget_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `widget_user_id` INT UNSIGNED DEFAULT NULL,
    `widget_role` VARCHAR(60) DEFAULT NULL,
    `widget_type` VARCHAR(30) NOT NULL,
    `widget_title` VARCHAR(120) NOT NULL DEFAULT '',
    `widget_icon` VARCHAR(120) DEFAULT NULL,
    `widget_config` JSON NOT NULL,
    `widget_width` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `widget_height` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `widget_position` SMALLINT NOT NULL DEFAULT 0,
    `widget_active` TINYINT(1) NOT NULL DEFAULT 1,
    `widget_created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `widget_updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`widget_id`),
    INDEX `idx_user` (`widget_user_id`, `widget_active`),
    INDEX `idx_role` (`widget_role`, `widget_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 21. EMAIL SETTINGS ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_email_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NOT NULL,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 22. EMAIL TEMPLATES ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_email_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Machine-readable key used in code: e.g. form_submission',
    `description` TEXT,
    `subject` VARCHAR(255) NOT NULL,
    `body` LONGTEXT NOT NULL COMMENT 'HTML body, supports {{variable}} placeholders',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 23. EMAIL LOG ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_email_log` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `recipient` VARCHAR(500) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `status` ENUM('SENT','FAIL') NOT NULL DEFAULT 'SENT',
    `error_message` VARCHAR(1000) DEFAULT NULL,
    `template_slug` VARCHAR(100) DEFAULT NULL,
    `sent_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 24. ERROR LOG ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_error_log` (
    `errlog_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `errlog_type` ENUM('PHP','SQL','JS','APP') NOT NULL DEFAULT 'PHP' COMMENT 'Error source type',
    `errlog_severity` ENUM('debug','info','warning','error','fatal') NOT NULL DEFAULT 'error',
    `errlog_message` VARCHAR(2000) NOT NULL DEFAULT '',
    `errlog_context` TEXT DEFAULT NULL COMMENT 'JSON: extra data, SQL, params, etc.',
    `errlog_trace` TEXT DEFAULT NULL COMMENT 'Stack trace or JS stack string',
    `errlog_file` VARCHAR(500) DEFAULT NULL COMMENT 'Source file (NU_ROOT stripped)',
    `errlog_line` SMALLINT UNSIGNED DEFAULT NULL,
    `errlog_request_uri` VARCHAR(500) DEFAULT NULL,
    `errlog_request_method` VARCHAR(10) DEFAULT NULL,
    `errlog_user_id` INT UNSIGNED DEFAULT NULL,
    `errlog_user_name` VARCHAR(100) DEFAULT NULL,
    `errlog_created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`errlog_id`),
    KEY `idx_type` (`errlog_type`),
    KEY `idx_severity` (`errlog_severity`),
    KEY `idx_created` (`errlog_created_at`),
    KEY `idx_type_sev` (`errlog_type`, `errlog_severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Centralised PHP / SQL / JS / APP error log';

-- ─── 25. PASSWORD POLICY ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_password_policy` (
    `policy_id` INT NOT NULL DEFAULT 1,
    `policy_min_length` INT NOT NULL DEFAULT 8,
    `policy_require_uppercase` TINYINT(1) NOT NULL DEFAULT 1,
    `policy_require_lowercase` TINYINT(1) NOT NULL DEFAULT 1,
    `policy_require_number` TINYINT(1) NOT NULL DEFAULT 1,
    `policy_require_special` TINYINT(1) NOT NULL DEFAULT 0,
    `policy_disallow_username` TINYINT(1) NOT NULL DEFAULT 1,
    `policy_history_count` INT NOT NULL DEFAULT 5,
    `policy_expiry_days` INT NOT NULL DEFAULT 0,
    `policy_expiry_warning_days` INT NOT NULL DEFAULT 7,
    `policy_force_change_on_first_login` TINYINT(1) NOT NULL DEFAULT 1,
    `policy_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`policy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 26. PASSWORD HISTORY ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_password_history` (
    `ph_id` INT AUTO_INCREMENT PRIMARY KEY,
    `ph_user_id` INT NOT NULL,
    `ph_hash` VARCHAR(255) NOT NULL,
    `ph_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ph_user` (`ph_user_id`),
    FOREIGN KEY (`ph_user_id`) REFERENCES `nu_users` (`usr_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 27. WORKFLOW DEFINITIONS ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_workflows` (
    `wf_id` INT NOT NULL AUTO_INCREMENT,
    `project_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `wf_code` VARCHAR(64) NOT NULL,
    `wf_name` VARCHAR(128) NOT NULL,
    `wf_description` TEXT,
    `wf_form_code` VARCHAR(64) DEFAULT NULL COMMENT 'optional: bind to a form',
    `wf_active` TINYINT(1) NOT NULL DEFAULT 1,
    `wf_created_by` INT DEFAULT NULL,
    `wf_created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `wf_updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`wf_id`),
    UNIQUE KEY `uq_wf_code_project` (`wf_code`, `project_id`),
    KEY `idx_wf_active` (`wf_active`),
    FOREIGN KEY (`wf_created_by`) REFERENCES `nu_users` (`usr_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 28. WORKFLOW STAGES ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_workflow_stages` (
    `wfs_id` INT NOT NULL AUTO_INCREMENT,
    `project_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `wfs_wf_id` INT NOT NULL,
    `wfs_code` VARCHAR(64)   NOT NULL,
    `wfs_name` VARCHAR(128)  NOT NULL,
    `wfs_description` TEXT,
    `wfs_color` VARCHAR(16) NOT NULL DEFAULT '#6366f1',
    `wfs_is_start` TINYINT(1) NOT NULL DEFAULT 0,
    `wfs_is_end` TINYINT(1) NOT NULL DEFAULT 0,
    `wfs_order` SMALLINT NOT NULL DEFAULT 0,
    `wfs_sla_hours` SMALLINT DEFAULT NULL COMMENT 'SLA in hours, NULL = no limit',
    `wfs_role` VARCHAR(64) DEFAULT NULL COMMENT 'role that acts on this stage',
    `wfs_created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`wfs_id`),
    KEY `idx_wfs_wf_id` (`wfs_wf_id`),
    KEY `idx_wfs_order` (`wfs_wf_id`, `wfs_order`),
    CONSTRAINT `fk_wfs_wf` FOREIGN KEY (`wfs_wf_id`) REFERENCES `nu_workflows` (`wf_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 29. WORKFLOW TRANSITIONS ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_workflow_transitions` (
    `wft_id` INT NOT NULL AUTO_INCREMENT,
    `project_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `wft_wf_id` INT NOT NULL,
    `wft_from_id` INT NOT NULL,
    `wft_to_id` INT NOT NULL,
    `wft_action` VARCHAR(64) NOT NULL DEFAULT 'advance' COMMENT 'advance|reject|escalate|return',
    `wft_label` VARCHAR(128) NOT NULL DEFAULT 'Advance',
    `wft_condition` TEXT DEFAULT NULL COMMENT 'optional JSON condition',
    `wft_hook` TEXT DEFAULT NULL COMMENT 'JSON configuration for custom transition Action Hooks (send_email, call_webhook, update_record)',
    `wft_created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`wft_id`),
    KEY `idx_wft_wf` (`wft_wf_id`),
    KEY `idx_wft_from` (`wft_from_id`),
    CONSTRAINT `fk_wft_wf` FOREIGN KEY (`wft_wf_id`) REFERENCES `nu_workflows` (`wf_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_wft_from` FOREIGN KEY (`wft_from_id`) REFERENCES `nu_workflow_stages` (`wfs_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_wft_to` FOREIGN KEY (`wft_to_id`) REFERENCES `nu_workflow_stages` (`wfs_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 30. WORKFLOW INSTANCES ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_workflow_instances` (
    `wfi_id` INT NOT NULL AUTO_INCREMENT,
    `wfi_wf_id` INT NOT NULL,
    `wfi_stage_id` INT NOT NULL COMMENT 'current stage',
    `wfi_record_table` VARCHAR(64) DEFAULT NULL,
    `wfi_record_id` VARCHAR(64) DEFAULT NULL,
    `wfi_status` ENUM('active','completed','rejected','cancelled') NOT NULL DEFAULT 'active',
    `wfi_started_by` INT DEFAULT NULL,
    `wfi_started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `wfi_completed_at` DATETIME DEFAULT NULL,
    `wfi_meta` TEXT DEFAULT NULL COMMENT 'JSON metadata (TEXT for broad MariaDB compat)',
    PRIMARY KEY (`wfi_id`),
    KEY `idx_wfi_wf` (`wfi_wf_id`),
    KEY `idx_wfi_stage` (`wfi_stage_id`),
    KEY `idx_wfi_status` (`wfi_status`),
    KEY `idx_wfi_record` (`wfi_record_table`, `wfi_record_id`),
    CONSTRAINT `fk_wfi_wf` FOREIGN KEY (`wfi_wf_id`) REFERENCES `nu_workflows` (`wf_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_wfi_stage` FOREIGN KEY (`wfi_stage_id`) REFERENCES `nu_workflow_stages` (`wfs_id`),
    FOREIGN KEY (`wfi_started_by`) REFERENCES `nu_users` (`usr_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 31. WORKFLOW HISTORY ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_workflow_history` (
    `wfh_id` INT NOT NULL AUTO_INCREMENT,
    `wfh_wfi_id` INT NOT NULL,
    `wfh_from_id` INT DEFAULT NULL,
    `wfh_to_id` INT NOT NULL,
    `wfh_action` VARCHAR(64) NOT NULL,
    `wfh_actor_id` INT DEFAULT NULL,
    `wfh_comment` TEXT DEFAULT NULL,
    `wfh_acted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`wfh_id`),
    KEY `idx_wfh_wfi` (`wfh_wfi_id`),
    KEY `idx_wfh_actor` (`wfh_actor_id`),
    CONSTRAINT `fk_wfh_wfi` FOREIGN KEY (`wfh_wfi_id`) REFERENCES `nu_workflow_instances` (`wfi_id`) ON DELETE CASCADE,
    FOREIGN KEY (`wfh_actor_id`) REFERENCES `nu_users` (`usr_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── SEED DATA ───────────────────────────────────────────────────────────────
INSERT IGNORE INTO `nu_roles` (`role_code`, `role_name`, `role_description`, `role_is_system`) VALUES
('globeadmin', 'Globe Admin', 'System administrator - full access, cannot be deleted', 1),
('admin', 'Admin', 'Administrative access', 0),
('user', 'User', 'Standard user access', 0),
('viewer', 'Viewer', 'Read-only access', 0);

INSERT IGNORE INTO `nu_permissions` (`perm_code`, `perm_name`, `perm_category`) VALUES
('users.view', 'View Users', 'Users'),
('users.create', 'Create Users', 'Users'),
('users.edit', 'Edit Users', 'Users'),
('users.delete', 'Delete Users', 'Users'),
('roles.view', 'View Roles', 'Roles'),
('roles.manage', 'Manage Roles', 'Roles'),
('forms.view', 'View Forms', 'Forms'),
('forms.build', 'Build Forms', 'Forms'),
('reports.view', 'View Reports', 'Reports'),
('reports.build', 'Build Reports', 'Reports'),
('queries.view', 'View Queries', 'Queries'),
('queries.build', 'Build Queries', 'Queries'),
('audit.view', 'View Audit Trail', 'Audit'),
('files.view', 'View Files', 'Files'),
('files.upload', 'Upload Files', 'Files'),
('api.view', 'View API Tokens', 'API'),
('api.manage', 'Manage API Tokens', 'API'),
('system.config', 'System Configuration', 'System'),
('password.change', 'Change Own Password', 'Users');

INSERT IGNORE INTO `nu_role_permissions` (`rp_role_id`, `rp_perm_id`)
SELECT r.role_id, p.perm_id FROM `nu_roles` r CROSS JOIN `nu_permissions` p
WHERE r.role_code = 'globeadmin';

INSERT IGNORE INTO `nu_role_permissions` (`rp_role_id`, `rp_perm_id`)
SELECT r.role_id, p.perm_id FROM `nu_roles` r CROSS JOIN `nu_permissions` p
WHERE r.role_code = 'admin';

-- Default password: password (change immediately after first login)
INSERT IGNORE INTO `nu_users` (`usr_username`, `usr_password`, `usr_email`, `usr_role`, `usr_active`, `usr_must_change_password`) VALUES
('globeadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@nuvistechnologies.com.fj', 'globeadmin', 1, 1);

UPDATE `nu_users` SET `usr_must_change_password` = 1 WHERE `usr_username` = 'globeadmin' OR LENGTH(`usr_password`) < 60;

-- Default Password Policy
INSERT IGNORE INTO `nu_password_policy` (`policy_id`) VALUES (1);

-- Consolidated nu_menus Seed
INSERT IGNORE INTO `nu_menus`
  (`menu_label`, `menu_type`, `menu_target`, `menu_parent_id`, `menu_order`, `menu_roles`, `menu_active`, `menu_icon`, `menu_open_mode`, `menu_browse_mode`, `menu_preview_mode`, `menu_default_view`)
VALUES
  ('Main', 'group', '', 0, 10, '', 1, 'group', 'inline|browse', 'inline', 'inline', 'browse');

SET @main_group = LAST_INSERT_ID();

SELECT @main_group := `menu_id`
FROM   `nu_menus`
WHERE  `menu_label` = 'Main'
  AND  `menu_type`  = 'group'
  AND  `menu_target` = ''
LIMIT 1;

INSERT IGNORE INTO `nu_menus`
  (`menu_label`, `menu_type`, `menu_target`, `menu_parent_id`, `menu_order`, `menu_roles`, `menu_active`, `menu_icon`, `menu_open_mode`, `menu_browse_mode`, `menu_preview_mode`, `menu_default_view`)
VALUES
  ('Dashboard',    'form', 'dashboard',    @main_group, 10, '', 1, 'dashboard', 'inline|browse', 'inline', 'inline', 'browse'),
  ('Forms',        'form', 'forms',         @main_group, 20, '', 1, 'forms', 'inline|browse', 'inline', 'inline', 'browse'),
  ('Reports',      'form', 'reports',       @main_group, 30, '', 1, 'reports', 'inline|browse', 'inline', 'inline', 'browse'),
  ('Queries',      'form', 'queries',       @main_group, 40, '', 1, 'queries', 'inline|browse', 'inline', 'inline', 'browse'),
  ('Calendar',     'form', 'calendar',      @main_group, 50, '', 1, 'calendar', 'inline|browse', 'inline', 'inline', 'browse'),
  ('AI Assistant', 'form', 'ai',            @main_group, 60, '', 1, 'ai', 'inline|browse', 'inline', 'inline', 'browse'),
  ('Webhooks',     'form', 'integrations',  @main_group, 70, '', 1, 'link', 'inline|browse', 'inline', 'inline', 'browse');

INSERT IGNORE INTO `nu_menus`
  (`menu_label`, `menu_type`, `menu_target`, `menu_parent_id`, `menu_order`, `menu_roles`, `menu_active`, `menu_icon`, `menu_open_mode`, `menu_browse_mode`, `menu_preview_mode`, `menu_default_view`)
VALUES
  ('Admin Tools', 'group', '', 0, 80, 'globeadmin,admin', 1, 'group', 'inline|browse', 'inline', 'inline', 'browse');

SET @admin_group = LAST_INSERT_ID();

SELECT @admin_group := `menu_id`
FROM   `nu_menus`
WHERE  `menu_label` = 'Admin Tools'
  AND  `menu_type`  = 'group'
  AND  `menu_target` = ''
LIMIT 1;

INSERT IGNORE INTO `nu_menus`
  (`menu_label`, `menu_type`, `menu_target`, `menu_parent_id`, `menu_order`, `menu_roles`, `menu_active`, `menu_icon`, `menu_open_mode`, `menu_browse_mode`, `menu_preview_mode`, `menu_default_view`)
VALUES
  ('Projects',              'module', 'projects',        @admin_group,    5,   'globeadmin,admin',  1, 'folder', 'inline|browse', 'inline', 'inline', 'browse'),
  ('Menus',                 'form', 'menus',             @admin_group,    10,  'globeadmin,admin',  1, 'menus', 'inline|browse', 'inline', 'inline', 'browse'),
  ('Users',                 'form', 'users',             @admin_group,    20,  'globeadmin,admin',  1, 'users', 'inline|browse', 'inline', 'inline', 'browse'),
  ('Roles',                 'form', 'roles',             @admin_group,    25,  'globeadmin,admin',  1, 'roles', 'inline|browse', 'inline', 'inline', 'browse'),
  ('Audit Trail',           'form', 'audit',             @admin_group,    28,  'globeadmin,admin',  1, 'audit', 'inline|browse', 'inline', 'inline', 'browse'),
  ('Files',                 'form', 'files',             @admin_group,    30,  'globeadmin,admin',  1, 'files', 'inline|browse', 'inline', 'inline', 'browse'),
  ('Workflow',              'form', 'workflow',          @admin_group,    40,  'globeadmin,admin',  1, 'workflow', 'inline|browse', 'inline', 'inline', 'browse'),
  ('DB & Server Inspector', 'form', 'inspector',         @admin_group,    50,  'globeadmin,admin',  1, 'inspector', 'inline|browse', 'inline', 'inline', 'browse'),
  ('Error Log',             'form', 'errorlog',          @admin_group,    60,  'globeadmin,admin',  1, 'alert', 'inline|browse', 'inline', 'inline', 'browse'),
  ('Password Policy',       'form', 'password_policy',   @admin_group,    70,  'globeadmin,admin',  1, 'shield', 'inline|browse', 'inline', 'inline', 'browse'),
  ('App Cloner',            'form', 'appcloner',         @admin_group,    80,  'globeadmin,admin',  1, 'copy', 'inline|browse', 'inline', 'inline', 'browse'),
  ('Import / Export',       'form', 'import_export',     @admin_group,    85,  'globeadmin,admin',  1, 'clipboard', 'inline|browse', 'inline', 'inline', 'browse'),
  ('System Updater',        'form', 'updater',           @admin_group,    90,  'globeadmin,admin',  1, 'refresh', 'inline|browse', 'inline', 'inline', 'browse');

INSERT IGNORE INTO `nu_menus`
  (`menu_label`, `menu_type`, `menu_target`, `menu_parent_id`, `menu_order`, `menu_roles`, `menu_active`, `menu_icon`, `menu_open_mode`, `menu_browse_mode`, `menu_preview_mode`, `menu_default_view`)
VALUES
  ('Personal', 'group', '', 0, 90, '', 1, 'group', 'inline|browse', 'inline', 'inline', 'browse');

SET @personal_group = LAST_INSERT_ID();

SELECT @personal_group := `menu_id`
FROM   `nu_menus`
WHERE  `menu_label` = 'Personal'
  AND  `menu_type`  = 'group'
  AND  `menu_target` = ''
LIMIT 1;

INSERT IGNORE INTO `nu_menus`
  (`menu_label`, `menu_type`, `menu_target`, `menu_parent_id`, `menu_order`, `menu_roles`, `menu_active`, `menu_icon`, `menu_open_mode`, `menu_browse_mode`, `menu_preview_mode`, `menu_default_view`)
VALUES
  ('Change Password', 'form',  'password',  @personal_group,  10,  '',  1, 'password', 'inline|browse', 'inline', 'inline', 'browse');

-- Seed Dashboard Widgets & Layout presets
INSERT IGNORE INTO `nu_dashboard_widgets`
    (`widget_user_id`, `widget_role`, `widget_type`, `widget_title`, `widget_icon`, `widget_config`, `widget_width`, `widget_height`, `widget_position`)
VALUES
-- KPI: available forms
(NULL, 'user', 'stat', 'Available Forms', 'forms',
 '{"source":"query","sql":"SELECT COUNT(*) as value FROM nu_forms WHERE form_active=1","color":"primary"}',
 1, 1, 10),
-- KPI: available reports
(NULL, 'user', 'stat', 'Available Reports', 'reports',
 '{"source":"query","sql":"SELECT COUNT(*) as value FROM nu_reports WHERE report_active=1","color":"success"}',
 1, 1, 20),
-- Quick links list
(NULL, 'user', 'list', 'Quick Links', NULL,
 '{"items":[{"label":"Open Forms","module":"forms"},{"label":"Open Reports","module":"reports"},{"label":"Calendar","module":"calendar"},{"label":"Change Password","module":"password"}]}',
 2, 1, 30),
-- My recent activity table
(NULL, 'user', 'table', 'My Recent Activity', NULL,
 '{"source":"query","sql":"SELECT audit_action AS Action, audit_table AS Area, DATE_FORMAT(audit_timestamp, ''%b %e %l:%i %p'') AS Time FROM nu_audit_log WHERE audit_user_id={{user_id}} ORDER BY audit_timestamp DESC LIMIT 6","columns":["Action","Area","Time"]}',
 4, 2, 40);

INSERT IGNORE INTO `nu_dashboard_widgets`
    (`widget_user_id`, `widget_role`, `widget_type`, `widget_title`, `widget_icon`, `widget_config`, `widget_width`, `widget_height`, `widget_position`)
VALUES
(NULL, 'manager', 'stat', 'Total Users', 'users',
 '{"source":"query","sql":"SELECT COUNT(*) as value FROM nu_users","color":"primary"}',
 1, 1, 10),
(NULL, 'manager', 'stat', 'Active Forms', 'forms',
 '{"source":"query","sql":"SELECT COUNT(*) as value FROM nu_forms WHERE form_active=1","color":"success"}',
 1, 1, 20),
(NULL, 'manager', 'chart_bar', 'Activity This Week', NULL,
 '{"source":"query","sql":"SELECT DATE(audit_timestamp) AS label, COUNT(*) AS value FROM nu_audit_log WHERE audit_timestamp >= DATE_SUB(CURDATE(),INTERVAL 7 DAY) GROUP BY DATE(audit_timestamp) ORDER BY label"}',
 2, 2, 30),
(NULL, 'manager', 'chart_pie', 'Actions Breakdown', NULL,
 '{"source":"query","sql":"SELECT audit_action AS label, COUNT(*) AS value FROM nu_audit_log WHERE audit_timestamp >= DATE_SUB(CURDATE(),INTERVAL 30 DAY) GROUP BY audit_action ORDER BY value DESC LIMIT 6"}',
 2, 2, 40);

INSERT IGNORE INTO `nu_dashboard_layouts` (`layout_role`, `layout_name`) VALUES
('user',       'Default User Layout'),
('manager',    'Manager Layout'),
('supervisor', 'Supervisor Layout'),
('admin',      'Admin Layout');

-- Seed Default SMTP settings
INSERT IGNORE INTO `nu_email_settings` (`setting_key`, `setting_value`) VALUES
  ('driver',        'mail'),
  ('smtp_host',     ''),
  ('smtp_port',     '587'),
  ('smtp_secure',   'tls'),
  ('smtp_auth',     '1'),
  ('smtp_username', ''),
  ('smtp_password', ''),
  ('from_email',    'noreply@example.com'),
  ('from_name',     'nub5-dev');

-- Seed Email templates
INSERT IGNORE INTO `nu_email_templates` (`name`, `slug`, `subject`, `body`, `description`) VALUES
(
  'Form Submission Notification',
  'form_submission',
  'New form submission: {{form_name}}',
  '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;max-width:600px;margin:auto">\n<h2 style="color:#2d7dd2">New Form Submission</h2>\n<p>A new submission was received for the form <strong>{{form_name}}</strong>.</p>\n<table style="width:100%;border-collapse:collapse">\n  <tr><td style="padding:8px;border:1px solid #ddd"><strong>Submitted by</strong></td><td style="padding:8px;border:1px solid #ddd">{{submitted_by}}</td></tr>\n  <tr><td style="padding:8px;border:1px solid #ddd"><strong>Date</strong></td><td style="padding:8px;border:1px solid #ddd">{{submitted_at}}</td></tr>\n  <tr><td style="padding:8px;border:1px solid #ddd"><strong>Record ID</strong></td><td style="padding:8px;border:1px solid #ddd">{{record_id}}</td></tr>\n</table>\n<p style="margin-top:16px"><a href="{{review_url}}" style="background:#2d7dd2;color:#fff;padding:10px 20px;text-decoration:none;border-radius:4px">Review Submission</a></p>\n<hr/><p style="font-size:12px;color:#888">This is an automated notification from nub5-dev.</p>\n</body></html>',
  'Sent when a form is submitted. Variables: {{form_name}}, {{submitted_by}}, {{submitted_at}}, {{record_id}}, {{review_url}}'
),
(
  'Welcome / Account Created',
  'user_welcome',
  'Welcome to {{app_name}}, {{user_name}}!',
  '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;max-width:600px;margin:auto">\n<h2 style="color:#2d7dd2">Welcome, {{user_name}}!</h2>\n<p>Your account on <strong>{{app_name}}</strong> has been created.</p>\n<p><strong>Username:</strong> {{username}}<br/><strong>Temporary Password:</strong> {{temp_password}}</p>\n<p>Please log in and change your password immediately.</p>\n<p><a href="{{login_url}}" style="background:#2d7dd2;color:#fff;padding:10px 20px;text-decoration:none;border-radius:4px">Log In Now</a></p>\n<hr/><p style="font-size:12px;color:#888">nub5-dev Automated Notification</p>\n</body></html>',
  'Sent when a new user account is created. Variables: {{user_name}}, {{username}}, {{app_name}}, {{temp_password}}, {{login_url}}'
),
(
  'Password Reset',
  'password_reset',
  'Password Reset Request - {{app_name}}',
  '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;max-width:600px;margin:auto">\n<h2 style="color:#2d7dd2">Password Reset</h2>\n<p>Hi {{user_name}}, we received a request to reset your password.</p>\n<p><a href="{{reset_url}}" style="background:#e63946;color:#fff;padding:10px 20px;text-decoration:none;border-radius:4px">Reset My Password</a></p>\n<p style="color:#888;font-size:12px">This link expires in 1 hour. If you did not request this, ignore this email.</p>\n<hr/><p style="font-size:12px;color:#888">nub5-dev Automated Notification</p>\n</body></html>',
  'Password reset link email. Variables: {{user_name}}, {{app_name}}, {{reset_url}}'
),
(
  'Workflow Action Notification',
  'workflow_notification',
  'Action Required: {{workflow_name}} - Step {{step_name}}',
  '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;max-width:600px;margin:auto">\n<h2 style="color:#2d7dd2">Workflow Notification</h2>\n<p>Hi {{recipient_name}},</p>\n<p>The workflow <strong>{{workflow_name}}</strong> requires your attention at step: <strong>{{step_name}}</strong>.</p>\n<p><strong>Details:</strong> {{message}}</p>\n<p><a href="{{action_url}}" style="background:#2d7dd2;color:#fff;padding:10px 20px;text-decoration:none;border-radius:4px">Take Action</a></p>\n<hr/><p style="font-size:12px;color:#888">nub5-dev Automated Notification</p>\n</body></html>',
  'Workflow step notification. Variables: {{recipient_name}}, {{workflow_name}}, {{step_name}}, {{message}}, {{action_url}}'
),
(
  'Password Changed Notification',
  'password_changed',
  'Your password has been changed successfully - {{app_name}}',
  '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;max-width:600px;margin:auto\">\n<h2 style="color:#10b981">Password Changed Successfully</h2>\n<p>Hi {{user_name}},</p>\n<p>Your password for your account <strong>{{username}}</strong> has been updated successfully.</p>\n<p>If you did not perform this change, please contact an administrator or reset your password immediately.</p>\n<p><a href="{{login_url}}" style="background:#10b981;color:#fff;padding:10px 20px;text-decoration:none;border-radius:4px">Log In to Your Account</a></p>\n<hr/><p style="font-size:12px;color:#888">nub5-dev Automated Notification</p>\n</body></html>',
  'Sent to notify users when their password is changed. Variables: {{user_name}}, {{username}}, {{app_name}}, {{login_url}}'
),
(
  'Application Cloned / Registered',
  'app_registered',
  'New Application Registered: {{app_name}}',
  '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;max-width:600px;margin:auto\">\n<h2 style="color:#2d7dd2">New Application Cloned</h2>\n<p>Hello,</p>\n<p>A new application template has been cloned or registered successfully:</p>\n<table style="width:100%;border-collapse:collapse\">\n  <tr><td style="padding:8px;border:1px solid #ddd"><strong>App Name</strong></td><td style="padding:8px;border:1px solid #ddd">{{app_name}}</td></tr>\n  <tr><td style="padding:8px;border:1px solid #ddd"><strong>Registered By</strong></td><td style="padding:8px;border:1px solid #ddd">{{registered_by}}</td></tr>\n  <tr><td style="padding:8px;border:1px solid #ddd"><strong>Timestamp</strong></td><td style="padding:8px;border:1px solid #ddd">{{registered_at}}</td></tr>\n</table>\n<hr/><p style="font-size:12px;color:#888">nub5-dev Automated Notification</p>\n</body></html>',
  'Sent to notify admin when a new application is registered or cloned. Variables: {{app_name}}, {{registered_by}}, {{registered_at}}'
),
(
  'Account Lockout Warning',
  'account_lockout',
  'Security Alert: Account Temporarily Locked - {{app_name}}',
  '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;max-width:600px;margin:auto\">\n<h2 style="color:#ef4444\">Security Alert: Account Locked</h2>\n<p>Hi {{user_name}},</p>\n<p>Your account has been temporarily locked due to too many failed login attempts.</p>\n<p><strong>Lockout Duration:</strong> {{lockout_duration}} minutes</p>\n<p>If this was not you, someone may be trying to access your account.</p>\n<hr/><p style="font-size:12px;color:#888">nub5-dev Automated Notification</p>\n</body></html>',
  'Sent when a user account is locked. Variables: {{user_name}}, {{app_name}}, {{lockout_duration}}'
);

-- Seed System Users Form Definition (for GlobeAdmin Customization)
INSERT IGNORE INTO `nu_forms` (
    `form_code`,
    `form_type`,
    `form_name`,
    `form_table`,
    `form_description`,
    `form_active`,
    `form_pk_type`,
    `form_table_mode`
) VALUES (
    'sys_users',
    'main',
    'System Users',
    'nu_users',
    'Admin tool to add custom fields to the users table.',
    1,
    'autoincrement',
    'existing'
);

-- Seed App Cloner Form Definition
INSERT IGNORE INTO `nu_forms` (
    `form_code`,
    `form_type`,
    `form_name`,
    `form_table`,
    `form_description`,
    `form_layout`,
    `form_settings`,
    `form_active`,
    `form_pk_type`,
    `form_table_mode`
) VALUES (
    'appcloner',
     'main',
    'App Cloner',
    NULL,
    'Clone or export the Nuvis StudioX application database and files.',
    NULL,
    '{"type": "custom", "custom_module": "appcloner"}',
    1,
    'autoincrement',
    'existing'
);

-- ─── REPORT DASHBOARDS MODULE ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS nu_report_dashboards (
    dashboard_id INT AUTO_INCREMENT PRIMARY KEY,
    dashboard_code VARCHAR(50) NOT NULL UNIQUE,
    dashboard_name VARCHAR(100) NOT NULL,
    dashboard_description TEXT,
    dashboard_active TINYINT(1) DEFAULT 1,
    dashboard_role_access JSON,
    dashboard_filters JSON,
    dashboard_reports JSON,
    dashboard_created_by INT,
    dashboard_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    dashboard_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS nu_stations (
    station_id INT AUTO_INCREMENT PRIMARY KEY,
    station_name VARCHAR(100) NOT NULL,
    station_code VARCHAR(20) NOT NULL UNIQUE,
    station_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO nu_stations (station_name, station_code) VALUES
('North Station', 'STN_NORTH'),
('South Station', 'STN_SOUTH'),
('East Station', 'STN_EAST'),
('West Station', 'STN_WEST')
ON DUPLICATE KEY UPDATE station_name=VALUES(station_name);

INSERT INTO nu_reports (report_code, report_name, report_type, report_sql, report_columns, report_filters, report_active) VALUES
('revenue_report', 'Revenue Report', 'table',
 'SELECT ''July'' as month, ''2026'' as year, ''STN_NORTH'' as station, 54000.00 as revenue, 1250 as transactions UNION ALL SELECT ''July'' as month, ''2026'' as year, ''STN_SOUTH'' as station, 42000.00 as revenue, 980 as transactions UNION ALL SELECT ''August'' as month, ''2026'' as year, ''STN_EAST'' as station, 61000.00 as revenue, 1400 as transactions UNION ALL SELECT ''August'' as month, ''2026'' as year, ''STN_WEST'' as station, 35000.00 as revenue, 850 as transactions',
 '[{"field":"month","label":"Month"},{"field":"year","label":"Year"},{"field":"station","label":"Station Code"},{"field":"revenue","label":"Revenue"},{"field":"transactions","label":"Transactions"}]',
 '[]', 1)
ON DUPLICATE KEY UPDATE report_name=VALUES(report_name), report_sql=VALUES(report_sql), report_columns=VALUES(report_columns);

-- Seed system modules menus with correct parent_id, label, target, and icons
-- Since @main_group is already resolved above, we can insert them into @main_group
INSERT INTO nu_menus (menu_parent_id, menu_label, menu_type, menu_target, menu_icon, menu_order, menu_open_mode, menu_browse_mode, menu_preview_mode, menu_default_view) VALUES
(@main_group, 'report_dashboards', 'form', 'report_dashboards', 'pie-chart', 100, 'inline|browse', 'inline', 'inline', 'browse'),
(@main_group, 'email_settings', 'form', 'email_settings', 'clipboard', 80, 'inline|browse', 'inline', 'inline', 'browse')
ON DUPLICATE KEY UPDATE menu_parent_id=VALUES(menu_parent_id), menu_order=VALUES(menu_order), menu_target=VALUES(menu_target);

-- ─── BARCODE PRODUCTS MODULE ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `nu_products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `type` VARCHAR(50) NOT NULL DEFAULT 'product', -- 'product', 'good', 'service'
    `barcode` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_product_barcode` (`barcode`),
    INDEX `idx_product_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed initial products, goods, and services
INSERT INTO `nu_products` (`name`, `type`, `barcode`, `description`, `price`) VALUES
('Organic Coffee Beans (Dark Roast)', 'product', '7501031311309', 'Premium single-origin organic arabica coffee beans, slow-roasted to a rich dark finish. 500g bag.', 18.99),
('Ergonomic Wireless Mouse', 'product', '074182285197', 'Rechargeable wireless ergonomic mouse with silent clicking, side-scroll wheel, and adjustable DPI settings up to 4000.', 45.50),
('Eco-Friendly Bamboo Water Bottle', 'product', '8886367301031', 'Double-walled vacuum insulated water bottle made from stainless steel and covered with natural, sustainable bamboo. 750ml.', 24.99),
('Heavy Duty Industrial Pallet', 'good', 'GD-WRH-PAL-02', 'High-density polyethylene structural foam plastic pallet. Ideal for warehouse storage and forklift transport. Rated up to 1500kg.', 79.95),
('Ultra-Soft Microfiber Towel Set', 'good', 'GD-TOWEL-S4', 'Pack of 4 quick-drying, highly absorbent premium microfiber towels. Perfect for home, gym, or automotive detailing.', 15.49),
('Web Application Development Consultation', 'service', 'SVC-WEB-DEV-01', 'One-hour professional architectural and consulting session with a Senior Software Engineer regarding web app stacks and cloud hosting.', 120.00),
('Enterprise IT Security Audit', 'service', 'SVC-IT-SEC-05', 'Comprehensive end-to-end network penetration testing, software vulnerability scan, and infrastructure compliance audit report.', 1500.00)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `type`=VALUES(`type`), `description`=VALUES(`description`), `price`=VALUES(`price`);


-- ─── DEVELOPER SYSTEM DEMO FILES TABLES ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS `demo_service_types` (
    `service_type_id` VARCHAR(36) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (`service_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `demo_service_types` (`service_type_id`, `name`, `description`, `price`) VALUES
('a1b2c3d4-e5f6-7a8b-9c0d-1e2f3a4b5c6d', 'Plumbing Maintenance', 'General plumbing repairs, leak detection, and pipe maintenance.', 150.00),
('b2c3d4e5-f6a7-8b9c-0d1e-2f3a4b5c6d7e', 'Electrical Inspection', 'Safety audit of wiring, outlets, and panel inspection.', 120.00),
('c3d4e5f6-a7b8-9c0d-1e2f-3a4b5c6d7e8f', 'HVAC System Service', 'Heating, ventilation, and air conditioning diagnostic and filter change.', 200.00);

CREATE TABLE IF NOT EXISTS `demo_customer_requests` (
    `request_id` VARCHAR(36) NOT NULL,
    `customer_name` VARCHAR(150) NOT NULL,
    `service_type_id` VARCHAR(36) NOT NULL,
    `request_details` TEXT DEFAULT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'Pending',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `demo_customer_requests` (`request_id`, `customer_name`, `service_type_id`, `request_details`, `status`) VALUES
('d4e5f6a7-b8c9-0d1e-2f3a-4b5c6d7e8f9a', 'Alice Smith', 'a1b2c3d4-e5f6-7a8b-9c0d-1e2f3a4b5c6d', 'Main drain is running extremely slow and backing up.', 'Pending'),
('e5f6a7b8-c9d0-1e2f-3a4b-5c6d7e8f9a0b', 'Bob Johnson', 'b2c3d4e5-f6a7-8b9c-0d1e-2f3a4b5c6d7e', 'Living room outlets have no power. Breaker is not tripped.', 'Pending');

CREATE TABLE IF NOT EXISTS `demo_staff_services` (
    `service_log_id` VARCHAR(36) NOT NULL,
    `customer_request_id` VARCHAR(36) NOT NULL,
    `staff_notes` TEXT DEFAULT NULL,
    `service_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`service_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─── SEED DEMO FORMS ─────────────────────────────────────────────────────────
INSERT IGNORE INTO `nu_forms` (
    `form_code`, `form_type`, `form_name`, `form_table`, `form_description`,
    `form_layout`, `browse_layout`, `form_custom_php_after`, `form_active`, `form_pk_type`, `form_table_mode`
) VALUES
(
    'demo_service_types', 'main', 'Service Types', 'demo_service_types', 'Manage service types and pricing.',
    '[{"type": "row", "children": [{"name": "name", "label": "Service Name", "type": "text", "required": true, "col": 6}, {"name": "price", "label": "Price ($)", "type": "number", "required": true, "col": 6}]}, {"type": "row", "children": [{"name": "description", "label": "Description", "type": "textarea", "col": 12, "rows": 4}]}]',
    '[{"fieldname": "name", "fieldlabel": "Service Name", "width": "200px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "price", "fieldlabel": "Price", "width": "120px", "align": "right", "formatter": "currency", "sortable": true}, {"fieldname": "description", "fieldlabel": "Description", "width": "300px", "align": "left", "formatter": "text", "sortable": true}]',
    NULL, 1, 'uuid', 'existing'
),
(
    'demo_customer_requests', 'main', 'Customer Requests', 'demo_customer_requests', 'Manage customer requests.',
    '[{"type": "row", "children": [{"name": "customer_name", "label": "Customer Name", "type": "text", "required": true, "col": 6}, {"name": "service_type_id", "label": "Service Type", "type": "select", "required": true, "col": 6, "options_source": "table", "options_table": "demo_service_types", "options_value_col": "service_type_id", "options_label_col": "name", "join_sql": "LEFT JOIN demo_service_types ON demo_service_types.service_type_id = demo_customer_requests.service_type_id", "join_display_field": "demo_service_types.name"}]}, {"type": "row", "children": [{"name": "request_details", "label": "Request Details", "type": "textarea", "col": 12, "rows": 4}]}, {"type": "row", "children": [{"name": "status", "label": "Status", "type": "text", "required": true, "col": 6, "default_value": "Pending", "readonly": true}]}]',
    '[{"fieldname": "customer_name", "fieldlabel": "Customer Name", "width": "180px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "service_type_id", "fieldlabel": "Service Type", "width": "200px", "align": "left", "formatter": "text", "sortable": true, "join_sql": "LEFT JOIN demo_service_types ON demo_service_types.service_type_id = demo_customer_requests.service_type_id", "join_display_field": "demo_service_types.name"}, {"fieldname": "request_details", "fieldlabel": "Details", "width": "280px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "status", "fieldlabel": "Status", "width": "120px", "align": "center", "formatter": "badge", "sortable": true}]',
    NULL, 1, 'uuid', 'existing'
),
(
    'demo_staff_services', 'main', 'Staff Services', 'demo_staff_services', 'Log staff service actions.',
    '[{"type": "row", "children": [{"name": "customer_request_id", "label": "Customer Request", "type": "select", "required": true, "col": 6, "options_source": "table", "options_table": "demo_customer_requests", "options_value_col": "request_id", "options_label_col": "customer_name", "options_filter": "status != \'Completed\'", "join_sql": "LEFT JOIN demo_customer_requests ON demo_customer_requests.request_id = demo_staff_services.customer_request_id", "join_display_field": "demo_customer_requests.customer_name"}, {"name": "service_date", "label": "Service Date", "type": "datetime", "required": true, "col": 6}]}, {"type": "row", "children": [{"name": "staff_notes", "label": "Staff Service Notes", "type": "textarea", "required": true, "col": 12, "rows": 4}]}]',
    '[{"fieldname": "customer_request_id", "fieldlabel": "Customer", "width": "180px", "align": "left", "formatter": "text", "sortable": true, "join_sql": "LEFT JOIN demo_customer_requests ON demo_customer_requests.request_id = demo_staff_services.customer_request_id", "join_display_field": "demo_customer_requests.customer_name"}, {"fieldname": "staff_notes", "fieldlabel": "Notes", "width": "300px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "service_date", "fieldlabel": "Date & Time", "width": "160px", "align": "left", "formatter" : "text", "sortable" : true}]',
    '$db = NuDatabase::getInstance();\n$reqId = $customer_request_id;\nif ($reqId) {\n    $db->update("demo_customer_requests", ["status" => "Completed"], "request_id = ?", [$reqId]);\n}', 1, 'uuid', 'existing'
);


-- ─── SEED DEMO WORKFLOW ──────────────────────────────────────────────────────
INSERT IGNORE INTO `nu_workflows` (`wf_id`, `wf_code`, `wf_name`, `wf_description`, `wf_form_code`, `wf_active`) VALUES
(100, 'customer_request_wf', 'Customer Request Workflow', 'A demo workflow tracking customer service requests through life cycle stage progressions.', 'demo_customer_requests', 1);

INSERT IGNORE INTO `nu_workflow_stages` (`wfs_id`, `wfs_wf_id`, `wfs_code`, `wfs_name`, `wfs_description`, `wfs_color`, `wfs_is_start`, `wfs_is_end`, `wfs_order`) VALUES
(101, 100, 'Pending', 'Pending Service', 'The service request is created and is waiting for assignment/commencement.', '#f59e0b', 1, 0, 1),
(102, 100, 'In Progress', 'Service In Progress', 'The staff has commenced providing the requested service.', '#3b82f6', 0, 0, 2),
(103, 100, 'Completed', 'Completed', 'The requested service has been successfully completed.', '#10b981', 0, 1, 3);

INSERT IGNORE INTO `nu_workflow_transitions` (`wft_id`, `wft_wf_id`, `wft_from_id`, `wft_to_id`, `wft_action`, `wft_label`, `wft_hook`) VALUES
(101, 100, 101, 102, 'advance', 'Start Providing Service', 'update_record'),
(102, 100, 102, 103, 'advance', 'Mark Service Completed', 'update_record');


SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================

-- ==============================================================================
-- ALLPEST ONLINE PORTAL - DOMAIN SCHEMA, METADATA & BISECURITY SEED DATA
-- ==============================================================================

-- 1. Project Registration
INSERT IGNORE INTO `nu_projects` (`project_id`, `project_code`, `project_name`, `project_description`, `project_is_default`, `project_active`) VALUES
(100, 'allpest_portal', 'All pest Online Portal', 'Complete Pest Control & Biosecurity Service Management System for Fiji Ports & Exporters (Allpest & Hygiene Solutions Pte Ltd, FJ0025MB)', 0, 1);

-- 2. Domain Schema Tables
CREATE TABLE IF NOT EXISTS `ap_dashboard_kpis` (
    `kpi_id` VARCHAR(36) PRIMARY KEY,
    `kpi_title` VARCHAR(100),
    `kpi_value` VARCHAR(50),
    `kpi_subtext` VARCHAR(150),
    `kpi_category` VARCHAR(50),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ap_job_orders` (
    `job_id` VARCHAR(36) PRIMARY KEY,
    `job_number` VARCHAR(50) UNIQUE,
    `client_name` VARCHAR(150),
    `client_exporter_code` VARCHAR(50),
    `port_of_origin` VARCHAR(100),
    `port_of_destination` VARCHAR(100),
    `vessel_aircraft` VARCHAR(100),
    `voyage_flight_no` VARCHAR(50),
    `target_pest` VARCHAR(100),
    `commodity_description` TEXT,
    `treatment_type` VARCHAR(100),
    `scheduled_date` DATE,
    `status` VARCHAR(50) DEFAULT 'Scheduled',
    `assigned_technician` VARCHAR(100),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ap_cargo_manifest` (
    `item_id` VARCHAR(36) PRIMARY KEY,
    `job_id` VARCHAR(36),
    `container_no` VARCHAR(50),
    `seal_no` VARCHAR(50),
    `cargo_type` VARCHAR(100),
    `volume_m3` DECIMAL(10,2),
    `weight_kg` DECIMAL(10,2),
    `status` VARCHAR(50) DEFAULT 'Pending Dosing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ap_risk_assessments` (
    `assessment_id` VARCHAR(36) PRIMARY KEY,
    `job_id` VARCHAR(36),
    `site_location` VARCHAR(150),
    `inspector_name` VARCHAR(100),
    `assessment_date` DATE,
    `structural_integrity` VARCHAR(20),
    `ventilation_status` VARCHAR(20),
    `gas_leak_prevention` VARCHAR(20),
    `ppe_compliant` VARCHAR(20),
    `exclusion_zone_set` VARCHAR(20),
    `risk_level` VARCHAR(20) DEFAULT 'Low',
    `approval_status` VARCHAR(50) DEFAULT 'Approved',
    `notes` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ap_fumigation_logs` (
    `log_id` VARCHAR(36) PRIMARY KEY,
    `job_id` VARCHAR(36),
    `fumigant_chemical` VARCHAR(100),
    `dosage_rate` VARCHAR(50),
    `exposure_period_hrs` DECIMAL(5,2),
    `initial_gas_conc` VARCHAR(50),
    `final_gas_conc` VARCHAR(50),
    `ambient_temp_c` DECIMAL(5,2),
    `ventilation_start_time` DATETIME,
    `clearance_time` DATETIME,
    `rof_status` VARCHAR(50) DEFAULT 'Pending Clearance',
    `baf_officer_name` VARCHAR(100),
    `baf_officer_license` VARCHAR(100),
    `digital_signature_token` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ap_gas_readings` (
    `reading_id` VARCHAR(36) PRIMARY KEY,
    `log_id` VARCHAR(36),
    `reading_timestamp` DATETIME,
    `sampling_location` VARCHAR(100),
    `target_conc_g_m3` DECIMAL(8,2),
    `measured_conc_g_m3` DECIMAL(8,2),
    `temp_c` DECIMAL(5,2),
    `passed` INT DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ap_certificates` (
    `cert_id` VARCHAR(36) PRIMARY KEY,
    `cert_number` VARCHAR(50) UNIQUE,
    `job_id` VARCHAR(36),
    `cert_type` VARCHAR(100),
    `client_name` VARCHAR(150),
    `issue_date` DATE,
    `expiry_date` DATE,
    `baf_accreditation_code` VARCHAR(100),
    `status` VARCHAR(50) DEFAULT 'Active',
    `pdf_signature_hash` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ap_clients` (
    `client_id` VARCHAR(36) PRIMARY KEY,
    `client_name` VARCHAR(150),
    `exporter_code` VARCHAR(50) UNIQUE,
    `contact_person` VARCHAR(100),
    `email` VARCHAR(150),
    `phone` VARCHAR(50),
    `address` TEXT,
    `port_location` VARCHAR(100),
    `status` VARCHAR(20) DEFAULT 'Active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ap_proposals` (
    `proposal_id` VARCHAR(36) PRIMARY KEY,
    `proposal_no` VARCHAR(50) UNIQUE,
    `client_id` VARCHAR(36),
    `target_pest` VARCHAR(100),
    `proposed_chemical` VARCHAR(100),
    `service_frequency` VARCHAR(50),
    `estimated_cost` DECIMAL(12,2),
    `status` VARCHAR(50) DEFAULT 'Draft',
    `valid_until` DATE,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ap_inspection_reports` (
    `report_id` VARCHAR(36) PRIMARY KEY,
    `report_no` VARCHAR(50) UNIQUE,
    `job_id` VARCHAR(36),
    `inspection_date` DATE,
    `technician_name` VARCHAR(100),
    `pest_findings` TEXT,
    `overall_infestation_level` VARCHAR(50),
    `recommendations` TEXT,
    `next_reinspection_date` DATE,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ap_inspection_zones` (
    `zone_id` VARCHAR(36) PRIMARY KEY,
    `report_id` VARCHAR(36),
    `zone_name` VARCHAR(100),
    `pest_type` VARCHAR(100),
    `activity_level` VARCHAR(50),
    `chemical_applied` VARCHAR(100),
    `qty_applied` VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ap_invoices` (
    `invoice_id` VARCHAR(36) PRIMARY KEY,
    `invoice_no` VARCHAR(50) UNIQUE,
    `job_id` VARCHAR(36),
    `client_name` VARCHAR(150),
    `issue_date` DATE,
    `due_date` DATE,
    `subtotal` DECIMAL(12,2),
    `vat_amount` DECIMAL(12,2),
    `total_amount` DECIMAL(12,2),
    `payment_status` VARCHAR(50) DEFAULT 'Pending',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ap_invoice_items` (
    `item_id` VARCHAR(36) PRIMARY KEY,
    `invoice_id` VARCHAR(36),
    `description` TEXT,
    `quantity` DECIMAL(10,2),
    `unit_price` DECIMAL(10,2),
    `amount` DECIMAL(12,2)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ap_schedules` (
    `schedule_id` VARCHAR(36) PRIMARY KEY,
    `job_id` VARCHAR(36),
    `title` VARCHAR(150),
    `assigned_tech` VARCHAR(100),
    `start_time` DATETIME,
    `end_time` DATETIME,
    `status` VARCHAR(50) DEFAULT 'Scheduled',
    `port_station` VARCHAR(100),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ap_chemical_inventory` (
    `item_id` VARCHAR(36) PRIMARY KEY,
    `chemical_name` VARCHAR(100),
    `active_ingredient` VARCHAR(100),
    `batch_number` VARCHAR(50),
    `quantity_on_hand` DECIMAL(10,2),
    `unit_of_measure` VARCHAR(20),
    `expiry_date` DATE,
    `reorder_level` DECIMAL(10,2),
    `sds_document_url` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ap_audit_logs` (
    `log_id` VARCHAR(36) PRIMARY KEY,
    `user_name` VARCHAR(100),
    `action_type` VARCHAR(50),
    `entity_affected` VARCHAR(100),
    `details` TEXT,
    `ip_address` VARCHAR(50),
    `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Studio Forms Registration with full layouts
INSERT IGNORE INTO `nu_forms` (`project_id`, `form_code`, `form_name`, `form_table`, `form_description`, `form_type`, `form_layout`, `browse_layout`, `form_active`, `form_pk_type`, `form_table_mode`) VALUES
(100, 'frm_dashboard', 'Dashboard & KPIs', 'ap_dashboard_kpis', 'Form for Dashboard & KPIs', 'main', '[{"type": "row", "children": [{"name": "kpi_title", "label": "KPI Title", "type": "text", "required": true, "col": 6}, {"name": "kpi_category", "label": "Category", "type": "select", "required": true, "col": 6, "options": ["Fumigation", "Certificates", "Billing", "Compliance"]}]}, {"type": "row", "children": [{"name": "kpi_value", "label": "KPI Value", "type": "text", "required": true, "col": 6}, {"name": "kpi_subtext", "label": "Subtext / Secondary Info", "type": "text", "col": 6}]}]', '[{"fieldname": "kpi_title", "fieldlabel": "Title", "width": "200px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "kpi_category", "fieldlabel": "Category", "width": "150px", "align": "left", "formatter": "badge", "sortable": true}, {"fieldname": "kpi_value", "fieldlabel": "Value", "width": "120px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "kpi_subtext", "fieldlabel": "Subtext", "width": "250px", "align": "left", "formatter": "text", "sortable": true}]', 1, 'uuid', 'existing'),
(100, 'frm_job_orders', 'Job Orders Management', 'ap_job_orders', 'Form for Job Orders Management', 'main', '[{"type": "row", "children": [{"name": "job_number", "label": "Job Order Number", "type": "text", "required": true, "col": 4}, {"name": "client_name", "label": "Client / Exporter Name", "type": "text", "required": true, "col": 4}, {"name": "client_exporter_code", "label": "BAF Exporter Code", "type": "text", "col": 4}]}, {"type": "row", "children": [{"name": "port_of_origin", "label": "Port of Origin", "type": "text", "required": true, "col": 6}, {"name": "port_of_destination", "label": "Port of Destination", "type": "text", "required": true, "col": 6}]}, {"type": "row", "children": [{"name": "vessel_aircraft", "label": "Vessel / Aircraft Carrier", "type": "text", "col": 6}, {"name": "voyage_flight_no", "label": "Voyage / Flight No.", "type": "text", "col": 6}]}, {"type": "row", "children": [{"name": "target_pest", "label": "Target Pest Species", "type": "text", "required": true, "col": 4}, {"name": "treatment_type", "label": "Treatment Method", "type": "select", "required": true, "col": 4, "options": ["Methyl Bromide Fumigation", "Sulfuryl Fluoride", "Heat Treatment", "General Pest Control", "Container Dosing"]}, {"name": "scheduled_date", "label": "Scheduled Date", "type": "date", "required": true, "col": 4}]}, {"type": "row", "children": [{"name": "status", "label": "Job Status", "type": "select", "required": true, "col": 6, "options": ["Scheduled", "In Progress", "Fumigation Active", "Venting", "ROF Cleared", "Completed", "Cancelled"]}, {"name": "assigned_technician", "label": "Assigned Lead Technician", "type": "text", "required": true, "col": 6}]}, {"type": "row", "children": [{"name": "commodity_description", "label": "Commodity & Cargo Description", "type": "textarea", "col": 12, "rows": 3}]}, {"type": "row", "children": [{"type": "subform", "subform_code": "frm_cargo_manifest", "parent_field": "job_id", "label": "Cargo Container Manifest", "col": 12}]}]', '[{"fieldname": "job_number", "fieldlabel": "Job No", "width": "140px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "client_name", "fieldlabel": "Client Name", "width": "180px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "treatment_type", "fieldlabel": "Treatment", "width": "160px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "port_of_origin", "fieldlabel": "Origin", "width": "120px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "scheduled_date", "fieldlabel": "Scheduled Date", "width": "130px", "align": "center", "formatter": "date", "sortable": true}, {"fieldname": "status", "fieldlabel": "Status", "width": "130px", "align": "center", "formatter": "badge", "sortable": true}]', 1, 'uuid', 'existing'),
(100, 'frm_cargo_manifest', 'Cargo Container Manifest', 'ap_cargo_manifest', 'Form for Cargo Container Manifest', 'subform', '[{"type": "row", "children": [{"name": "container_no", "label": "Container No.", "type": "text", "required": true, "col": 6}, {"name": "seal_no", "label": "Seal No.", "type": "text", "required": true, "col": 6}]}, {"type": "row", "children": [{"name": "cargo_type", "label": "Cargo Type", "type": "text", "col": 6}, {"name": "status", "label": "Status", "type": "select", "col": 6, "options": ["Pending Dosing", "Gas Dosinated", "Clearance Approved"]}]}, {"type": "row", "children": [{"name": "volume_m3", "label": "Volume (m\u00b3)", "type": "number", "col": 6}, {"name": "weight_kg", "label": "Weight (kg)", "type": "number", "col": 6}]}]', '[{"fieldname": "container_no", "fieldlabel": "Container No", "width": "150px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "seal_no", "fieldlabel": "Seal No", "width": "130px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "cargo_type", "fieldlabel": "Cargo Type", "width": "150px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "volume_m3", "fieldlabel": "Volume (m\u00b3)", "width": "110px", "align": "right", "formatter": "number", "sortable": true}, {"fieldname": "status", "fieldlabel": "Status", "width": "130px", "align": "center", "formatter": "badge", "sortable": true}]', 1, 'uuid', 'existing'),
(100, 'frm_risk_assessment', 'Site Risk Assessment', 'ap_risk_assessments', 'Form for Site Risk Assessment', 'main', '[{"type": "row", "children": [{"name": "job_id", "label": "Associated Job Order", "type": "text", "required": true, "col": 4}, {"name": "site_location", "label": "Site / Port Location", "type": "text", "required": true, "col": 4}, {"name": "inspector_name", "label": "Safety Inspector Name", "type": "text", "required": true, "col": 4}]}, {"type": "row", "children": [{"name": "assessment_date", "label": "Assessment Date", "type": "date", "required": true, "col": 4}, {"name": "risk_level", "label": "Overall Risk Level", "type": "select", "required": true, "col": 4, "options": ["Low", "Medium", "High", "Critical"]}, {"name": "approval_status", "label": "Approval Status", "type": "select", "required": true, "col": 4, "options": ["Draft", "Pending Approval", "Approved", "Rejected"]}]}, {"type": "row", "children": [{"name": "structural_integrity", "label": "Structural Enclosure Check", "type": "select", "col": 4, "options": ["Pass", "Fail", "N/A"]}, {"name": "ventilation_status", "label": "Ventilation Equipment Check", "type": "select", "col": 4, "options": ["Pass", "Fail", "N/A"]}, {"name": "gas_leak_prevention", "label": "Gas Leak Prevention Check", "type": "select", "col": 4, "options": ["Pass", "Fail", "N/A"]}]}, {"type": "row", "children": [{"name": "ppe_compliant", "label": "Full PPE Compliance", "type": "select", "col": 6, "options": ["Yes", "No"]}, {"name": "exclusion_zone_set", "label": "Exclusion Zone Warning Signs Posted", "type": "select", "col": 6, "options": ["Yes", "No"]}]}, {"type": "row", "children": [{"name": "notes", "label": "Risk Assessment Notes & Mitigation Steps", "type": "textarea", "col": 12, "rows": 3}]}]', '[{"fieldname": "site_location", "fieldlabel": "Site Location", "width": "180px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "inspector_name", "fieldlabel": "Inspector", "width": "150px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "assessment_date", "fieldlabel": "Date", "width": "120px", "align": "center", "formatter": "date", "sortable": true}, {"fieldname": "risk_level", "fieldlabel": "Risk Level", "width": "120px", "align": "center", "formatter": "badge", "sortable": true}, {"fieldname": "approval_status", "fieldlabel": "Status", "width": "130px", "align": "center", "formatter": "badge", "sortable": true}]', 1, 'uuid', 'existing'),
(100, 'frm_fumigation_logs', 'Fumigation & ROF Clearance Log', 'ap_fumigation_logs', 'Form for Fumigation & ROF Clearance Log', 'main', '[{"type": "row", "children": [{"name": "job_id", "label": "Job Order ID", "type": "text", "required": true, "col": 4}, {"name": "fumigant_chemical", "label": "Fumigant Chemical", "type": "select", "required": true, "col": 4, "options": ["Methyl Bromide (CH3Br)", "Sulfuryl Fluoride (SO2F2)", "Phosphine (PH3)"]}, {"name": "dosage_rate", "label": "Dosage Rate (g/m\u00b3)", "type": "text", "required": true, "col": 4}]}, {"type": "row", "children": [{"name": "exposure_period_hrs", "label": "Exposure Period (Hours)", "type": "number", "col": 4}, {"name": "initial_gas_conc", "label": "Initial Gas Concentration", "type": "text", "col": 4}, {"name": "final_gas_conc", "label": "Final Gas Concentration", "type": "text", "col": 4}]}, {"type": "row", "children": [{"name": "ambient_temp_c", "label": "Ambient Temp (\u00b0C)", "type": "number", "col": 4}, {"name": "ventilation_start_time", "label": "Ventilation Start Time", "type": "datetime", "col": 4}, {"name": "clearance_time", "label": "Final ROF Clearance Time", "type": "datetime", "col": 4}]}, {"type": "row", "children": [{"name": "rof_status", "label": "ROF Clearance Status", "type": "select", "required": true, "col": 4, "options": ["Pending Clearance", "Active Treatment", "Venting", "ROF Issued", "On Hold"]}, {"name": "baf_officer_name", "label": "BAF Biosecurity Officer Name", "type": "text", "required": true, "col": 4}, {"name": "baf_officer_license", "label": "BAF License / Stamp No.", "type": "text", "required": true, "col": 4}]}, {"type": "row", "children": [{"name": "digital_signature_token", "label": "Digital Signature Token / Hash", "type": "text", "col": 12}]}, {"type": "row", "children": [{"type": "subform", "subform_code": "frm_gas_readings", "parent_field": "log_id", "label": "Gas Dosing Hourly Log", "col": 12}]}]', '[{"fieldname": "fumigant_chemical", "fieldlabel": "Chemical", "width": "160px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "dosage_rate", "fieldlabel": "Dosage Rate", "width": "120px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "rof_status", "fieldlabel": "ROF Status", "width": "140px", "align": "center", "formatter": "badge", "sortable": true}, {"fieldname": "baf_officer_name", "fieldlabel": "BAF Officer", "width": "160px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "clearance_time", "fieldlabel": "Clearance Time", "width": "160px", "align": "center", "formatter": "text", "sortable": true}]', 1, 'uuid', 'existing'),
(100, 'frm_gas_readings', 'Gas Dosing Hourly Log', 'ap_gas_readings', 'Form for Gas Dosing Hourly Log', 'subform', '[{"type": "row", "children": [{"name": "reading_timestamp", "label": "Timestamp", "type": "datetime", "required": true, "col": 6}, {"name": "sampling_location", "label": "Sampling Location Point", "type": "text", "required": true, "col": 6}]}, {"type": "row", "children": [{"name": "target_conc_g_m3", "label": "Target Conc (g/m\u00b3)", "type": "number", "col": 4}, {"name": "measured_conc_g_m3", "label": "Measured Conc (g/m\u00b3)", "type": "number", "required": true, "col": 4}, {"name": "temp_c", "label": "Temperature (\u00b0C)", "type": "number", "col": 4}]}, {"type": "row", "children": [{"name": "passed", "label": "Reading Threshold Met", "type": "select", "col": 12, "options": ["1 - Pass", "0 - Fail"]}]}]', '[{"fieldname": "reading_timestamp", "fieldlabel": "Timestamp", "width": "160px", "align": "center", "formatter": "text", "sortable": true}, {"fieldname": "sampling_location", "fieldlabel": "Location", "width": "150px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "measured_conc_g_m3", "fieldlabel": "Measured (g/m\u00b3)", "width": "130px", "align": "right", "formatter": "number", "sortable": true}, {"fieldname": "passed", "fieldlabel": "Status", "width": "100px", "align": "center", "formatter": "badge", "sortable": true}]', 1, 'uuid', 'existing'),
(100, 'frm_certificates', 'Certificates & Compliance', 'ap_certificates', 'Form for Certificates & Compliance', 'main', '[{"type": "row", "children": [{"name": "cert_number", "label": "Certificate Number", "type": "text", "required": true, "col": 4}, {"name": "cert_type", "label": "Certificate Type", "type": "select", "required": true, "col": 4, "options": ["Biosecurity Fumigation Certificate", "ROF Clearance Certificate", "Pest Control Treatment Certificate"]}, {"name": "client_name", "label": "Client / Recipient Name", "type": "text", "required": true, "col": 4}]}, {"type": "row", "children": [{"name": "job_id", "label": "Associated Job Order ID", "type": "text", "col": 4}, {"name": "issue_date", "label": "Issue Date", "type": "date", "required": true, "col": 4}, {"name": "expiry_date", "label": "Expiry Date", "type": "date", "col": 4}]}, {"type": "row", "children": [{"name": "baf_accreditation_code", "label": "BAF Accreditation Code", "type": "text", "required": true, "col": 6}, {"name": "status", "label": "Certificate Status", "type": "select", "required": true, "col": 6, "options": ["Active", "Revoked", "Expired"]}]}, {"type": "row", "children": [{"name": "pdf_signature_hash", "label": "PDF Security Hash", "type": "text", "col": 12}]}]', '[{"fieldname": "cert_number", "fieldlabel": "Cert No.", "width": "150px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "cert_type", "fieldlabel": "Type", "width": "220px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "client_name", "fieldlabel": "Client", "width": "180px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "issue_date", "fieldlabel": "Issue Date", "width": "120px", "align": "center", "formatter": "date", "sortable": true}, {"fieldname": "status", "fieldlabel": "Status", "width": "110px", "align": "center", "formatter": "badge", "sortable": true}]', 1, 'uuid', 'existing'),
(100, 'frm_clients', 'Client / Exporter Directory', 'ap_clients', 'Form for Client Directory', 'main', '[{"type": "row", "children": [{"name": "client_name", "label": "Company / Exporter Name", "type": "text", "required": true, "col": 6}, {"name": "exporter_code", "label": "BAF Exporter Registration Code", "type": "text", "required": true, "col": 6}]}, {"type": "row", "children": [{"name": "contact_person", "label": "Contact Person", "type": "text", "required": true, "col": 4}, {"name": "email", "label": "Email Address", "type": "email", "required": true, "col": 4}, {"name": "phone", "label": "Phone Number", "type": "text", "required": true, "col": 4}]}, {"type": "row", "children": [{"name": "port_location", "label": "Primary Port / Facility Station", "type": "select", "required": true, "col": 6, "options": ["Suva Port Station", "Lautoka Port Station", "Nadi Airport Cargo Terminal"]}, {"name": "status", "label": "Account Status", "type": "select", "required": true, "col": 6, "options": ["Active", "Inactive", "Suspended"]}]}, {"type": "row", "children": [{"name": "address", "label": "Physical Facility Address", "type": "textarea", "col": 12, "rows": 3}]}]', '[{"fieldname": "client_name", "fieldlabel": "Company Name", "width": "200px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "exporter_code", "fieldlabel": "Exporter Code", "width": "140px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "contact_person", "fieldlabel": "Contact Person", "width": "150px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "phone", "fieldlabel": "Phone", "width": "130px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "port_location", "fieldlabel": "Port Location", "width": "160px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "status", "fieldlabel": "Status", "width": "110px", "align": "center", "formatter": "badge", "sortable": true}]', 1, 'uuid', 'existing'),
(100, 'frm_proposals', 'Treatment & Service Proposals', 'ap_proposals', 'Form for Proposals', 'main', '[{"type": "row", "children": [{"name": "proposal_no", "label": "Proposal Number", "type": "text", "required": true, "col": 4}, {"name": "client_id", "label": "Client / Exporter ID", "type": "text", "required": true, "col": 4}, {"name": "target_pest", "label": "Target Pest Species", "type": "text", "required": true, "col": 4}]}, {"type": "row", "children": [{"name": "proposed_chemical", "label": "Proposed Treatment / Chemical", "type": "text", "required": true, "col": 4}, {"name": "service_frequency", "label": "Service Frequency", "type": "select", "required": true, "col": 4, "options": ["One-time Quarantine", "Weekly Port Audit", "Monthly Commercial Contract", "Quarterly Warehouse Spray"]}, {"name": "estimated_cost", "label": "Estimated Cost (FJD $)", "type": "number", "required": true, "col": 4}]}, {"type": "row", "children": [{"name": "status", "label": "Proposal Status", "type": "select", "required": true, "col": 6, "options": ["Draft", "Sent", "Accepted", "Rejected"]}, {"name": "valid_until", "label": "Valid Until Date", "type": "date", "required": true, "col": 6}]}]', '[{"fieldname": "proposal_no", "fieldlabel": "Proposal No", "width": "140px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "target_pest", "fieldlabel": "Target Pest", "width": "160px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "service_frequency", "fieldlabel": "Frequency", "width": "160px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "estimated_cost", "fieldlabel": "Est Cost ($)", "width": "130px", "align": "right", "formatter": "currency", "sortable": true}, {"fieldname": "status", "fieldlabel": "Status", "width": "120px", "align": "center", "formatter": "badge", "sortable": true}]', 1, 'uuid', 'existing'),
(100, 'frm_inspection_reports', 'Pest Inspection & Field Reports', 'ap_inspection_reports', 'Form for Inspection Reports', 'main', '[{"type": "row", "children": [{"name": "report_no", "label": "Inspection Report No.", "type": "text", "required": true, "col": 4}, {"name": "job_id", "label": "Job Order ID", "type": "text", "col": 4}, {"name": "inspection_date", "label": "Inspection Date", "type": "date", "required": true, "col": 4}]}, {"type": "row", "children": [{"name": "technician_name", "label": "Field Technician Name", "type": "text", "required": true, "col": 6}, {"name": "overall_infestation_level", "label": "Overall Infestation Level", "type": "select", "required": true, "col": 6, "options": ["None Detected", "Low", "Moderate", "Severe", "Critical Quarantine Action Required"]}]}, {"type": "row", "children": [{"name": "pest_findings", "label": "Detailed Field Pest Findings", "type": "textarea", "col": 12, "rows": 3}]}, {"type": "row", "children": [{"name": "recommendations", "label": "Corrective Recommendations", "type": "textarea", "col": 12, "rows": 3}]}, {"type": "row", "children": [{"name": "next_reinspection_date", "label": "Next Re-inspection Date", "type": "date", "col": 6}]}, {"type": "row", "children": [{"type": "subform", "subform_code": "frm_inspection_zones", "parent_field": "report_id", "label": "Inspection Zones & Treatments", "col": 12}]}]', '[{"fieldname": "report_no", "fieldlabel": "Report No", "width": "140px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "inspection_date", "fieldlabel": "Date", "width": "120px", "align": "center", "formatter": "date", "sortable": true}, {"fieldname": "technician_name", "fieldlabel": "Technician", "width": "160px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "overall_infestation_level", "fieldlabel": "Infestation Level", "width": "180px", "align": "center", "formatter": "badge", "sortable": true}]', 1, 'uuid', 'existing'),
(100, 'frm_inspection_zones', 'Inspection Zones & Treatments', 'ap_inspection_zones', 'Form for Inspection Zones', 'subform', '[{"type": "row", "children": [{"name": "zone_name", "label": "Zone / Location Area", "type": "text", "required": true, "col": 6}, {"name": "pest_type", "label": "Pest Type Identified", "type": "text", "required": true, "col": 6}]}, {"type": "row", "children": [{"name": "activity_level", "label": "Activity Level", "type": "select", "col": 4, "options": ["None", "Low", "Moderate", "High"]}, {"name": "chemical_applied", "label": "Chemical Applied", "type": "text", "col": 4}, {"name": "qty_applied", "label": "Quantity Applied", "type": "text", "col": 4}]}]', '[{"fieldname": "zone_name", "fieldlabel": "Zone Name", "width": "160px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "pest_type", "fieldlabel": "Pest Type", "width": "140px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "activity_level", "fieldlabel": "Activity Level", "width": "130px", "align": "center", "formatter": "badge", "sortable": true}, {"fieldname": "chemical_applied", "fieldlabel": "Chemical", "width": "150px", "align": "left", "formatter": "text", "sortable": true}]', 1, 'uuid', 'existing'),
(100, 'frm_invoices', 'Billing & Invoices', 'ap_invoices', 'Form for Invoicing', 'main', '[{"type": "row", "children": [{"name": "invoice_no", "label": "Invoice Number", "type": "text", "required": true, "col": 4}, {"name": "client_name", "label": "Client / Billed To", "type": "text", "required": true, "col": 4}, {"name": "job_id", "label": "Job Order ID", "type": "text", "col": 4}]}, {"type": "row", "children": [{"name": "issue_date", "label": "Issue Date", "type": "date", "required": true, "col": 4}, {"name": "due_date", "label": "Due Date", "type": "date", "required": true, "col": 4}, {"name": "payment_status", "label": "Payment Status", "type": "select", "required": true, "col": 4, "options": ["Pending", "Paid", "Overdue", "Cancelled"]}]}, {"type": "row", "children": [{"name": "subtotal", "label": "Subtotal (FJD $)", "type": "number", "required": true, "col": 4}, {"name": "vat_amount", "label": "VAT (15%) Amount", "type": "number", "required": true, "col": 4}, {"name": "total_amount", "label": "Total Amount (FJD $)", "type": "number", "required": true, "col": 4}]}, {"type": "row", "children": [{"type": "subform", "subform_code": "frm_invoice_items", "parent_field": "invoice_id", "label": "Invoice Line Items", "col": 12}]}]', '[{"fieldname": "invoice_no", "fieldlabel": "Invoice No", "width": "140px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "client_name", "fieldlabel": "Client Name", "width": "180px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "issue_date", "fieldlabel": "Issue Date", "width": "120px", "align": "center", "formatter": "date", "sortable": true}, {"fieldname": "total_amount", "fieldlabel": "Total ($)", "width": "130px", "align": "right", "formatter": "currency", "sortable": true}, {"fieldname": "payment_status", "fieldlabel": "Status", "width": "120px", "align": "center", "formatter": "badge", "sortable": true}]', 1, 'uuid', 'existing'),
(100, 'frm_invoice_items', 'Invoice Line Items', 'ap_invoice_items', 'Form for Line Items', 'subform', '[{"type": "row", "children": [{"name": "description", "label": "Service Line Item Description", "type": "text", "required": true, "col": 12}]}, {"type": "row", "children": [{"name": "quantity", "label": "Quantity", "type": "number", "required": true, "col": 4}, {"name": "unit_price", "label": "Unit Price ($)", "type": "number", "required": true, "col": 4}, {"name": "amount", "label": "Line Total ($)", "type": "number", "required": true, "col": 4}]}]', '[{"fieldname": "description", "fieldlabel": "Description", "width": "250px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "quantity", "fieldlabel": "Qty", "width": "90px", "align": "right", "formatter": "number", "sortable": true}, {"fieldname": "unit_price", "fieldlabel": "Unit Price ($)", "width": "120px", "align": "right", "formatter": "currency", "sortable": true}, {"fieldname": "amount", "fieldlabel": "Total ($)", "width": "120px", "align": "right", "formatter": "currency", "sortable": true}]', 1, 'uuid', 'existing'),
(100, 'frm_schedules', 'Service Schedules & Timetable', 'ap_schedules', 'Form for Schedules', 'main', '[{"type": "row", "children": [{"name": "title", "label": "Schedule Task Title", "type": "text", "required": true, "col": 6}, {"name": "job_id", "label": "Associated Job Order ID", "type": "text", "col": 6}]}, {"type": "row", "children": [{"name": "assigned_tech", "label": "Assigned Lead Technician", "type": "text", "required": true, "col": 6}, {"name": "port_station", "label": "Port Station Location", "type": "select", "required": true, "col": 6, "options": ["Suva Port Station", "Lautoka Port Station", "Nadi Airport Cargo Terminal"]}]}, {"type": "row", "children": [{"name": "start_time", "label": "Start Date & Time", "type": "datetime", "required": true, "col": 4}, {"name": "end_time", "label": "End Date & Time", "type": "datetime", "required": true, "col": 4}, {"name": "status", "label": "Schedule Status", "type": "select", "required": true, "col": 4, "options": ["Scheduled", "In Progress", "Completed", "Cancelled"]}]}]', '[{"fieldname": "title", "fieldlabel": "Schedule Title", "width": "200px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "assigned_tech", "fieldlabel": "Technician", "width": "160px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "port_station", "fieldlabel": "Port Station", "width": "160px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "start_time", "fieldlabel": "Start Time", "width": "160px", "align": "center", "formatter": "text", "sortable": true}, {"fieldname": "status", "fieldlabel": "Status", "width": "120px", "align": "center", "formatter": "badge", "sortable": true}]', 1, 'uuid', 'existing'),
(100, 'frm_inventory', 'Chemical & Stock Inventory', 'ap_chemical_inventory', 'Form for Chemical Inventory', 'main', '[{"type": "row", "children": [{"name": "chemical_name", "label": "Chemical / Product Name", "type": "text", "required": true, "col": 6}, {"name": "active_ingredient", "label": "Active Ingredient & Concentration", "type": "text", "required": true, "col": 6}]}, {"type": "row", "children": [{"name": "batch_number", "label": "Manufacturer Batch Number", "type": "text", "required": true, "col": 4}, {"name": "quantity_on_hand", "label": "Quantity On Hand", "type": "number", "required": true, "col": 4}, {"name": "unit_of_measure", "label": "Unit of Measure", "type": "select", "required": true, "col": 4, "options": ["Liters", "Kilograms", "Grams", "Cylinders", "Units"]}]}, {"type": "row", "children": [{"name": "expiry_date", "label": "Expiration Date", "type": "date", "required": true, "col": 4}, {"name": "reorder_level", "label": "Minimum Reorder Level", "type": "number", "col": 4}, {"name": "sds_document_url", "label": "Safety Data Sheet (SDS) URL / Ref", "type": "text", "col": 4}]}]', '[{"fieldname": "chemical_name", "fieldlabel": "Chemical Name", "width": "200px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "active_ingredient", "fieldlabel": "Active Ingredient", "width": "180px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "batch_number", "fieldlabel": "Batch No.", "width": "130px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "quantity_on_hand", "fieldlabel": "Stock Qty", "width": "110px", "align": "right", "formatter": "number", "sortable": true}, {"fieldname": "unit_of_measure", "fieldlabel": "Unit", "width": "90px", "align": "center", "formatter": "text", "sortable": true}, {"fieldname": "expiry_date", "fieldlabel": "Expiry Date", "width": "120px", "align": "center", "formatter": "date", "sortable": true}]', 1, 'uuid', 'existing'),
(100, 'frm_audit_logs', 'Compliance & System Audit Logs', 'ap_audit_logs', 'Form for Audit Logs', 'main', '[{"type": "row", "children": [{"name": "user_name", "label": "User / Operator Name", "type": "text", "required": true, "col": 4}, {"name": "action_type", "label": "Action / Operation Type", "type": "text", "required": true, "col": 4}, {"name": "entity_affected", "label": "Entity / Form Affected", "type": "text", "required": true, "col": 4}]}, {"type": "row", "children": [{"name": "ip_address", "label": "IP Address", "type": "text", "col": 6}, {"name": "timestamp", "label": "Action Timestamp", "type": "datetime", "col": 6}]}, {"type": "row", "children": [{"name": "details", "label": "Full Audit Action Details", "type": "textarea", "col": 12, "rows": 4}]}]', '[{"fieldname": "user_name", "fieldlabel": "User", "width": "150px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "action_type", "fieldlabel": "Action", "width": "150px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "entity_affected", "fieldlabel": "Entity Affected", "width": "180px", "align": "left", "formatter": "text", "sortable": true}, {"fieldname": "timestamp", "fieldlabel": "Timestamp", "width": "160px", "align": "center", "formatter": "text", "sortable": true}]', 1, 'uuid', 'existing');

-- 4. Studio Navigation Menus Registration
INSERT IGNORE INTO `nu_menus` (`project_id`, `menu_label`, `menu_type`, `menu_target`, `menu_icon`, `menu_order`, `menu_active`) VALUES
(100, 'Workspace / Portal', 'module', 'allpest_portal', 'fas fa-th-large', 1, 1),
(100, 'Job Orders', 'form', 'frm_job_orders', 'fas fa-clipboard-list', 2, 1),
(100, 'Risk Assessment', 'form', 'frm_risk_assessment', 'fas fa-shield-alt', 3, 1),
(100, 'Fumigation Records', 'form', 'frm_fumigation_logs', 'fas fa-smog', 4, 1),
(100, 'Certificates', 'form', 'frm_certificates', 'fas fa-certificate', 5, 1),
(100, 'Clients', 'form', 'frm_clients', 'fas fa-users', 6, 1),
(100, 'Proposals', 'form', 'frm_proposals', 'fas fa-file-contract', 7, 1),
(100, 'Inspection Reports', 'form', 'frm_inspection_reports', 'fas fa-bug', 8, 1),
(100, 'Invoicing & Billing', 'form', 'frm_invoices', 'fas fa-file-invoice-dollar', 9, 1),
(100, 'Service Schedules', 'form', 'frm_schedules', 'fas fa-calendar-alt', 10, 1),
(100, 'Chemical Stock', 'form', 'frm_inventory', 'fas fa-flask', 11, 1),
(100, 'Audit Logs', 'form', 'frm_audit_logs', 'fas fa-history', 12, 1);

-- 5. Seed Biosecurity Sample Data
INSERT IGNORE INTO `ap_dashboard_kpis` (`kpi_id`, `kpi_title`, `kpi_value`, `kpi_subtext`, `kpi_category`) VALUES
('kpi_1', 'Total Directory / Accounts', '28', 'All active directory accounts sync''d', 'Jobs'),
('kpi_2', 'Authorized Fumigators', '6 Certified', 'BAF / AFAS Accredited Officers', 'Fumigation'),
('kpi_3', 'Field Technicians', '11 Active', 'Stationed in Lautoka & Suva Ports', 'Jobs'),
('kpi_4', 'Client Exporters', '9 Exporters', 'Restricted Portal Access Only', 'Compliance'),
('kpi_5', 'Active Jobs', '14 Active', '4 Under Venting, 2 Scheduled Today', 'Jobs'),
('kpi_6', 'Compliance Rate', '99.4%', 'AFAS / BAF Live Compliance Audit', 'Compliance');

INSERT IGNORE INTO `ap_job_orders` (`job_id`, `job_number`, `client_name`, `client_exporter_code`, `port_of_origin`, `port_of_destination`, `vessel_aircraft`, `voyage_flight_no`, `target_pest`, `treatment_type`, `scheduled_date`, `status`, `assigned_technician`, `commodity_description`) VALUES
('job_101', 'JOB-2026-0841', 'Pacific Timber Exports Pte Ltd', 'EXP-FJ-8821', 'Lautoka Port, Fiji', 'Port of Sydney, Australia', 'MV Pacific Trader', 'V.2026-12', 'Wood Borers', 'Fumigation (Methyl Bromide)', '2026-05-28', 'In Progress', 'Rajesh Sharma', '40ft Sawn Hardwood Timber Packages (AFAS Compliant Stacking)');

INSERT IGNORE INTO `ap_certificates` (`cert_id`, `cert_number`, `job_id`, `cert_type`, `client_name`, `issue_date`, `expiry_date`, `baf_accreditation_code`, `status`, `pdf_signature_hash`) VALUES
('cert_101', 'CERT-FUM-2026-084', 'job_101', 'Official Fumigation Certificate', 'Pacific Timber Exports Pte Ltd', '2026-05-28', '2026-11-28', 'BAF-AFAS-FJ0025MB', 'Active', 'RSA-4096-Encrypted-Sig-Token');

-- 6. Initial Version Snapshot Tracking
INSERT IGNORE INTO `nu_project_versions` (`pv_id`, `pv_project_id`, `pv_version_tag`, `pv_description`, `pv_snapshot_data`, `pv_created_at`) VALUES
(100, 100, 'v1.0.0', 'Initial release of Allpest Online Portal natively integrated in Nuvis Studio with 12 modules, forms, menus, and biosecurity sample data.', '{"version":"v1.0.0","modules":12}', NOW());
-- 7. Allpest Biosecurity Workflow Registration
INSERT IGNORE INTO `nu_workflows` (`wf_id`, `project_id`, `wf_code`, `wf_name`, `wf_description`, `wf_form_code`, `wf_active`) VALUES
(200, 100, 'ap_biosecurity_job_wf', 'Allpest Biosecurity Job Life Cycle', 'Life cycle progression for Quarantine Fumigation & Biosecurity Treatment (BAF FJ0025MB Accredited Operations)', 'frm_job_orders', 1);

INSERT IGNORE INTO `nu_workflow_stages` (`wfs_id`, `project_id`, `wfs_wf_id`, `wfs_code`, `wfs_name`, `wfs_description`, `wfs_color`, `wfs_is_start`, `wfs_is_end`, `wfs_order`) VALUES
(201, 100, 200, 'wfs_scheduled', 'Scheduled / Intention Received', 'Job order logged and scheduled for treatment.', '#f59e0b', 1, 0, 1),
(202, 100, 200, 'wfs_risk_assessed', 'Site Risk Assessed', 'Safety & structural integrity risk assessment completed and passed.', '#3b82f6', 0, 0, 2),
(203, 100, 200, 'wfs_dosing_active', 'Fumigation Dosing Active', 'Gas dosing injected into container or enclosure; concentration monitored.', '#8b5cf6', 0, 0, 3),
(204, 100, 200, 'wfs_venting', 'Venting & Degassing', 'Aeration in progress to clear chemical residues below safety thresholds.', '#06b6d4', 0, 0, 4),
(205, 100, 200, 'wfs_rof_cleared', 'ROF Clearance Issued', 'Release of Fumigation (ROF) certificate authorized by BAF officer.', '#10b981', 0, 0, 5),
(206, 100, 200, 'wfs_completed', 'Job Order Completed', 'Treatment complete, certificate issued, and cargo cleared for shipping.', '#059669', 0, 1, 6);

INSERT IGNORE INTO `nu_workflow_transitions` (`wft_id`, `project_id`, `wft_wf_id`, `wft_from_id`, `wft_to_id`, `wft_action`, `wft_label`, `wft_hook`) VALUES
(201, 100, 200, 201, 202, 'advance', 'Perform Risk Assessment', 'update_record'),
(202, 100, 200, 202, 203, 'advance', 'Commence Fumigation Dosing', 'update_record'),
(203, 100, 200, 203, 204, 'advance', 'Initiate Venting & Degassing', 'update_record'),
(204, 100, 200, 204, 205, 'advance', 'Issue ROF Clearance Certificate', 'update_record'),
(205, 100, 200, 205, 206, 'advance', 'Close & Finalize Job Order', 'update_record');

-- 8. Allpest Saved Queries Registration
INSERT IGNORE INTO `nu_queries` (`project_id`, `query_code`, `query_name`, `query_sql`, `query_description`, `query_active`) VALUES
(100, 'qry_ap_active_jobs', 'Active Quarantine Operations', 'SELECT job_number, client_name, treatment_type, port_of_origin, status, scheduled_date FROM ap_job_orders WHERE status IN (\'Scheduled\', \'In Progress\', \'Fumigation Active\', \'Venting\') ORDER BY scheduled_date DESC', 'List of all live active biosecurity fumigations across ports.', 1),
(100, 'qry_ap_baf_clearance_log', 'BAF ROF Clearance Audit Log', 'SELECT l.log_id, j.job_number, j.client_name, l.fumigant_chemical, l.dosage_rate, l.rof_status, l.baf_officer_name, l.clearance_time FROM ap_fumigation_logs l LEFT JOIN ap_job_orders j ON j.job_id = l.job_id ORDER BY l.created_at DESC', 'Complete ROF clearance logs for Biosecurity Authority Fiji audit verification.', 1),
(100, 'qry_ap_chemical_stock', 'Chemical Inventory & Low Stock Alert', 'SELECT chemical_name, active_ingredient, batch_number, quantity_on_hand, unit_of_measure, reorder_level, expiry_date FROM ap_chemical_inventory ORDER BY expiry_date ASC', 'Chemical inventory stock monitoring and expiry alerts.', 1);

-- 9. Allpest Compliance Reports Registration
INSERT IGNORE INTO `nu_reports` (`project_id`, `report_code`, `report_name`, `report_type`, `report_sql`, `report_columns`, `report_active`) VALUES
(100, 'rpt_ap_baf_audit_manifest', 'BAF Monthly Biosecurity Audit Manifest', 'table', 'SELECT j.job_number, j.client_name, j.client_exporter_code, j.vessel_aircraft, j.treatment_type, c.cert_number, c.status as cert_status, c.issue_date FROM ap_job_orders j LEFT JOIN ap_certificates c ON c.job_id = j.job_id', '[{"field":"job_number","label":"Job No"},{"field":"client_name","label":"Exporter Name"},{"field":"client_exporter_code","label":"BAF Exporter Code"},{"field":"vessel_aircraft","label":"Vessel"},{"field":"treatment_type","label":"Treatment"},{"field":"cert_number","label":"Cert No"},{"field":"cert_status","label":"Cert Status"},{"field":"issue_date","label":"Issue Date"}]', 1),
(100, 'rpt_ap_container_dosing_summary', 'Container Fumigation & Dosing Summary', 'table', 'SELECT j.job_number, m.container_no, m.seal_no, m.volume_m3, m.status as container_status FROM ap_cargo_manifest m LEFT JOIN ap_job_orders j ON j.job_id = m.job_id', '[{"field":"job_number","label":"Job No"},{"field":"container_no","label":"Container No"},{"field":"seal_no","label":"Seal No"},{"field":"volume_m3","label":"Volume m3"},{"field":"container_status","label":"Status"}]', 1);

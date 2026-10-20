-- Fieldwork Fumigation Manager Schema & Seed Data

CREATE TABLE IF NOT EXISTS `fw_clients` (
  `client_id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_name` VARCHAR(255) NOT NULL,
  `contact_person` VARCHAR(255) DEFAULT NULL,
  `contact_email` VARCHAR(255) DEFAULT NULL,
  `client_details` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `fw_job_orders` (
  `job_id` INT AUTO_INCREMENT PRIMARY KEY,
  `job_ref` VARCHAR(100) NOT NULL UNIQUE,
  `treatment_provider` VARCHAR(255) DEFAULT 'Allpest and hygiene solutions Pte ltd',
  `provider_id` VARCHAR(100) DEFAULT 'FJ0025MB',
  `planned_treatment_date` DATE DEFAULT NULL,
  `client_id` INT DEFAULT NULL,
  `client_name` VARCHAR(255) NOT NULL,
  `contact_person` VARCHAR(255) DEFAULT NULL,
  `contact_email` VARCHAR(255) DEFAULT NULL,
  `client_details` TEXT DEFAULT NULL,
  `street_address` VARCHAR(255) DEFAULT NULL,
  `suburb_city` VARCHAR(255) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT 'Fiji',
  `postcode` VARCHAR(50) DEFAULT NULL,
  `consignment_desc` TEXT DEFAULT NULL,
  `treatment_type` VARCHAR(255) DEFAULT 'Methyl bromide • Non-perishable commodity',
  `target_fumigation` VARCHAR(255) DEFAULT NULL,
  `container_number` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('Draft', 'In progress', 'Successful', 'Unsuccessful') DEFAULT 'Draft',
  `decision_by` VARCHAR(255) DEFAULT NULL,
  `decision_role` VARCHAR(255) DEFAULT 'Authorized fumigator-in-charge',
  `decision_timestamp` DATETIME DEFAULT NULL,
  `accreditation_number` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `fw_fumigation_records` (
  `record_id` INT AUTO_INCREMENT PRIMARY KEY,
  `record_ref` VARCHAR(100) NOT NULL UNIQUE,
  `job_id` INT NOT NULL,
  `job_ref` VARCHAR(100) NOT NULL,
  `fumigator_name` VARCHAR(255) DEFAULT 'Mere Naidu',
  `accreditation_number` VARCHAR(100) DEFAULT NULL,
  `specified_treatment_schedule` TEXT DEFAULT NULL,
  `dose_rate` VARCHAR(50) DEFAULT NULL,
  `exposure_period` VARCHAR(50) DEFAULT NULL,
  `consignment_suitable` ENUM('Yes', 'No') DEFAULT 'Yes',
  `remedial_action` TEXT DEFAULT NULL,
  `fumigation_type` VARCHAR(100) DEFAULT 'Ambient temperature',
  `forecast_min_temp` VARCHAR(50) DEFAULT NULL,
  `min_enclosure_temp` VARCHAR(50) DEFAULT NULL,
  `temp_unit` VARCHAR(10) DEFAULT '°C',
  `heaters_used` ENUM('Yes', 'No') DEFAULT 'No',
  `enclosure_type` VARCHAR(100) DEFAULT 'Sheeted enclosure',
  `other_enclosure_type` VARCHAR(255) DEFAULT NULL,
  `length_m` VARCHAR(50) DEFAULT NULL,
  `height_m` VARCHAR(50) DEFAULT NULL,
  `width_m` VARCHAR(50) DEFAULT NULL,
  `total_volume_m3` VARCHAR(50) DEFAULT NULL,
  `dose_rate_used` VARCHAR(50) DEFAULT NULL,
  `calculated_dose_g` VARCHAR(50) DEFAULT NULL,
  `amount_ch3br_applied_g` VARCHAR(50) DEFAULT NULL,
  `device_serial` VARCHAR(255) DEFAULT NULL,
  `other_device_serial` VARCHAR(255) DEFAULT NULL,
  `readings_json` LONGTEXT DEFAULT NULL,
  `injection_finished_date` VARCHAR(50) DEFAULT NULL,
  `injection_finished_time` VARCHAR(50) DEFAULT NULL,
  `ventilation_start_date` VARCHAR(50) DEFAULT NULL,
  `ventilation_start_time` VARCHAR(50) DEFAULT NULL,
  `final_reading_1` VARCHAR(50) DEFAULT NULL,
  `final_reading_2` VARCHAR(50) DEFAULT NULL,
  `final_reading_3` VARCHAR(50) DEFAULT NULL,
  `topup_amount` VARCHAR(50) DEFAULT NULL,
  `topup_time` VARCHAR(50) DEFAULT NULL,
  `topup_concentration` VARCHAR(50) DEFAULT NULL,
  `declaration_accepted` TINYINT(1) DEFAULT 0,
  `gas_in_signature` VARCHAR(255) DEFAULT 'Recorded',
  `gas_out_signature` VARCHAR(255) DEFAULT 'Recorded',
  `declaration_date` VARCHAR(50) DEFAULT NULL,
  `government_supervision` VARCHAR(255) DEFAULT 'Not recorded • Optional',
  `supervising_officer_name` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Draft', 'Completed') DEFAULT 'Draft',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `fw_certificates` (
  `cert_id` INT AUTO_INCREMENT PRIMARY KEY,
  `cert_ref` VARCHAR(100) NOT NULL UNIQUE,
  `job_id` INT NOT NULL,
  `job_ref` VARCHAR(100) NOT NULL,
  `record_ref` VARCHAR(100) NOT NULL,
  `status` ENUM('Preview', 'Issued') DEFAULT 'Preview',
  `issue_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Sample Clients
INSERT INTO `fw_clients` (`client_name`, `contact_person`, `contact_email`, `client_details`) VALUES
('Pacific Timber Exports Pte Ltd', 'Ana Kumar', 'operations@pacific-timber.example', 'Export operations • Primary contact for this consignment'),
('Rewa Export Traders', 'Dev Singh', 'logistics@rewa-export.example', 'General timber exporter'),
('Coral Coast Logistics', 'Savenaca Vasi', 'info@coralcoast.example', 'Freight and container handling'),
('Viti Freight Services', 'Litiana Rotu', 'dispatch@vitifreight.example', 'Crate and pallet shipments');

-- Seed Sample Job Orders
INSERT INTO `fw_job_orders`
(`job_id`, `job_ref`, `treatment_provider`, `provider_id`, `planned_treatment_date`, `client_name`, `contact_person`, `contact_email`, `client_details`, `street_address`, `suburb_city`, `country`, `postcode`, `consignment_desc`, `treatment_type`, `target_fumigation`, `container_number`, `status`, `decision_by`, `decision_role`, `decision_timestamp`, `accreditation_number`)
VALUES
(1, 'JO-2026-0142', 'Allpest and hygiene solutions Pte ltd', 'FJ0025MB', '2026-10-19', 'Pacific Timber Exports Pte Ltd', 'Ana Kumar', 'operations@pacific-timber.example', 'Export operations • Primary contact for this consignment', '18 Industrial Road', 'Lautoka', 'Fiji', '—', 'Sawn timber • 1 × 20 ft container', 'Methyl bromide • Non-perishable commodity', 'Timber pests', 'FCIU 482671-3', 'Successful', 'Mere Naidu', 'Authorized fumigator-In-charge', '2026-10-19 16:45:00', '—'),
(2, 'JO-2026-0141', 'Allpest and hygiene solutions Pte ltd', 'FJ0025MB', '2026-10-19', 'Rewa Export Traders', 'Dev Singh', 'logistics@rewa-export.example', 'Timber packaging', '22 Marine Drive', 'Suva', 'Fiji', '—', 'Timber packaging • 1 × 20 ft container', 'Methyl bromide', 'Timber pests', 'TLLU 591832-0', 'In progress', NULL, 'Authorized fumigator-In-charge', NULL, NULL),
(3, 'JO-2026-0140', 'Allpest and hygiene solutions Pte ltd', 'FJ0025MB', '2026-10-18', 'Coral Coast Logistics', 'Savenaca Vasi', 'info@coralcoast.example', 'Wooden pallets', '5 Queens Road', 'Nadi', 'Fiji', '—', 'Wooden pallets • 1 × 20 ft container', 'Methyl bromide', 'Storage pests', 'MSCU 273905-1', 'Draft', NULL, 'Authorized fumigator-In-charge', NULL, NULL),
(4, 'JO-2026-0139', 'Allpest and hygiene solutions Pte ltd', 'FJ0025MB', '2026-10-18', 'Pacific Timber Exports Pte Ltd', 'Ana Kumar', 'operations@pacific-timber.example', 'Export operations', '18 Industrial Road', 'Lautoka', 'Fiji', '—', 'Sawn timber • 1 × 20 ft container', 'Methyl bromide', 'Timber pests', 'TEMU 638104-2', 'Successful', 'Mere Naidu', 'Authorized fumigator-In-charge', '2026-10-18 15:30:00', '—'),
(5, 'JO-2026-0138', 'Allpest and hygiene solutions Pte ltd', 'FJ0025MB', '2026-10-17', 'Viti Freight Services', 'Litiana Rotu', 'dispatch@vitifreight.example', 'Wooden crates', '12 Wharf Street', 'Suva', 'Fiji', '—', 'Wooden crates • 1 × 20 ft container', 'Methyl bromide', 'Wood borers', 'CMAU 184230-7', 'Unsuccessful', 'Mere Naidu', 'Authorized fumigator-In-charge', '2026-10-17 14:10:00', '—'),
(6, 'JO-2026-0137', 'Allpest and hygiene solutions Pte ltd', 'FJ0025MB', '2026-10-17', 'Rewa Export Traders', 'Dev Singh', 'logistics@rewa-export.example', 'Timber packaging', '22 Marine Drive', 'Suva', 'Fiji', '—', 'Timber packaging • 1 × 20 ft container', 'Methyl bromide', 'Timber pests', 'TCKU 307162-4', 'Draft', NULL, 'Authorized fumigator-In-charge', NULL, NULL);

-- Seed Sample Fumigation Record
INSERT INTO `fw_fumigation_records`
(`record_id`, `record_ref`, `job_id`, `job_ref`, `fumigator_name`, `accreditation_number`, `specified_treatment_schedule`, `dose_rate`, `exposure_period`, `consignment_suitable`, `fumigation_type`, `forecast_min_temp`, `min_enclosure_temp`, `temp_unit`, `heaters_used`, `enclosure_type`, `total_volume_m3`, `dose_rate_used`, `calculated_dose_g`, `amount_ch3br_applied_g`, `declaration_accepted`, `gas_in_signature`, `gas_out_signature`, `declaration_date`, `status`)
VALUES
(1, 'RF-2026-0142', 1, 'JO-2026-0142', 'Mere Naidu', '—', '24g/m³ for 24 hrs at 21°C+', '24', '24', 'Yes', 'Ambient temperature', '22', '22', '°C', 'No', 'Sheeted enclosure', '38.5', '24', '924', '924', 1, 'Mere Naidu • Recorded', 'Mere Naidu • Recorded', '19/10/2026', 'Completed');

-- Seed Sample Certificate
INSERT INTO `fw_certificates`
(`cert_id`, `cert_ref`, `job_id`, `job_ref`, `record_ref`, `status`, `issue_date`)
VALUES
(1, 'PREVIEW-JO-2026-0142', 1, 'JO-2026-0142', 'RF-2026-0142', 'Preview', '2026-10-19');

<?php
/**
 * Allpest Online Portal - Master Setup & Initialization Script
 * Runs all database schema creation, form definitions, menu configuration,
 * sample biosecurity data seeding, and version snapshotting.
 */

// Initialize base DB tables if needed
require_once __DIR__ . '/setup_db.php';

// Execute sub-initializers
exec('php ' . escapeshellarg(__DIR__ . '/create_allpest_schema.php'), $out1, $ret1);
exec('php ' . escapeshellarg(__DIR__ . '/create_allpest_forms.php'), $out2, $ret2);
exec('php ' . escapeshellarg(__DIR__ . '/create_allpest_menus.php'), $out3, $ret3);
exec('php ' . escapeshellarg(__DIR__ . '/seed_allpest_data.php'), $out4, $ret4);
exec('php ' . escapeshellarg(__DIR__ . '/create_allpest_version.php'), $out5, $ret5);

echo implode("\n", array_merge($out1, $out2, $out3, $out4, $out5)) . "\n";
echo "\n=======================================================\n";
echo "Allpest Online Portal Setup completed successfully!\n";
echo "=======================================================\n";

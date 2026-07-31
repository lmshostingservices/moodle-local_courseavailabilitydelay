<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Course Availability Delay — upgrade steps.
 *
 * @package    local_courseavailabilitydelay
 * @copyright  2026 Essay Grader AI
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_courseavailabilitydelay_upgrade($oldversion) {

    // v1.0.1: FIX-CAD-NUMERIC-VERSION — Corrects the 10-digit $plugin->version (2026040901)
    //   used in the initial v1.0.0 release. The mandatory format is 13-digit YYYYMMDD00XXX.
    //   The 10-digit value is numerically correct for upgrades from v1.0.0 (since
    //   2026040900001 > 2026040901 as integers), but violates the 13-digit convention
    //   required for long-term upgrade safety across all installed sites.
    //   No DB schema changes. version.php → 2026040900001.
    if ($oldversion < 2026040900001) {
        upgrade_plugin_savepoint(true, 2026040900001, 'local', 'courseavailabilitydelay');
    }

    // v1.0.2: BUG-CAD-001 — credits-gate notification changed from error to warning (advisory only, rules persist).
    //         BUG-CAD-002 — negative delay_days values now rejected in CSV import with row-level error message.
    //         No DB schema changes.
    if ($oldversion < 2026040900002) {
        upgrade_plugin_savepoint(true, 2026040900002, 'local', 'courseavailabilitydelay');
    }

    // v1.0.3: FIX-CAD-003 — Switched unlock_verifier from raw PHP curl_init() to Moodle's \curl class
    //         (require_once filelib.php). Raw cURL bypasses Moodle's SSL certificate bundle and HTTP
    //         settings, causing verify API calls to fail silently on many Moodle hosting environments.
    //         Plugin now unlocks correctly when site has sufficient credits and valid credentials.
    //         No DB schema changes.
    if ($oldversion < 2026041000003) {
        upgrade_plugin_savepoint(true, 2026041000003, 'local', 'courseavailabilitydelay');
    }

    // v1.0.4: FIX-CAD-LANG-CAPABILITIES — Added missing capability language strings
    //         'courseavailabilitydelay:manage' and 'courseavailabilitydelay:viewreports'.
    //         Without these strings Moodle's capabilities admin page showed raw
    //         [[key]] placeholders instead of readable labels. No DB schema changes.
    if ($oldversion < 2026042400004) {
        upgrade_plugin_savepoint(true, 2026042400004, 'local', 'courseavailabilitydelay');
    }

    if ($oldversion < 2026072300209) {
        // FIX-API-DOMAIN: Updated all API endpoint URLs from lms-labs.com to lms-labs.com.
        // lms-labs.com has no DNS resolution from Moodle server side; lms-labs.com is the
        // correct working domain. All ajax.php, api_client, unlock_verifier, lib.php calls updated.
        if (function_exists('opcache_invalidate')) {
            $_pluginDir = realpath(__DIR__ . '/..');
            foreach (['version.php', 'db/upgrade.php'] as $_f) {
                $_full = $_pluginDir . '/' . $_f;
                if (file_exists($_full)) {
                    opcache_invalidate($_full, true);
                }
            }
        } elseif (function_exists('opcache_reset')) {
            opcache_reset();
        }
        upgrade_plugin_savepoint(true, 2026072300209, 'local', 'courseavailabilitydelay');
    }

    if ($oldversion < 2026072300210) {
        // FIX-API-DOMAIN: Reverted API endpoint to lms-labs.com (correct domain).
        // essaygraderai.app was the original single-plugin domain; lms-labs.com is correct.
        if (function_exists('opcache_invalidate')) {
            $_pluginDir = realpath(__DIR__ . '/..');
            foreach (['version.php', 'db/upgrade.php'] as $_f) {
                $_full = $_pluginDir . '/' . $_f;
                if (file_exists($_full)) { opcache_invalidate($_full, true); }
            }
        } elseif (function_exists('opcache_reset')) { opcache_reset(); }
        upgrade_plugin_savepoint(true, 2026072300210, 'local', 'courseavailabilitydelay');
    }

    if ($oldversion < 2026072300211) {
        // Domain update: lms-labs.com → lms-labs.com
        if (function_exists('opcache_invalidate')) {
            $_pluginDir = realpath(__DIR__ . '/..');
            foreach (['version.php', 'lib.php', 'db/upgrade.php'] as $_f) {
                $_full = $_pluginDir . '/' . $_f;
                if (file_exists($_full)) { opcache_invalidate($_full, true); }
            }
        } elseif (function_exists('opcache_reset')) { opcache_reset(); }
        upgrade_plugin_savepoint(true, 2026072300211, 'local', 'courseavailabilitydelay');
    }

    return true;
}
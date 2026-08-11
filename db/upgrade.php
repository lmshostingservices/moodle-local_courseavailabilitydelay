<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Course Availability Delay — upgrade steps.
 *
 * @package    local_courseavailabilitydelay
 * @copyright  2026 LMS-Labs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute local_courseavailabilitydelay upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_courseavailabilitydelay_upgrade($oldversion) {

    // v1.0.1: FIX-CAD-NUMERIC-VERSION — Corrects the 10-digit $plugin->version (2026040901)
    //   used in the initial v1.0.0 release to the mandatory YYYYMMDDXX format.
    //   Creates db/upgrade.php with savepoint. No functional or DB schema changes.
    if ($oldversion < 2026040900) {
        upgrade_plugin_savepoint(true, 2026040900, 'local', 'courseavailabilitydelay');
    }

    // v1.0.3: FIX-CAD-003 — Switched unlock_verifier from raw PHP curl_init() to Moodle's
    //   \curl class (filelib.php). Raw cURL bypasses Moodle's SSL certificate bundle causing
    //   verify API calls to fail silently on most Moodle hosting environments.
    //   No DB schema changes.
    if ($oldversion < 2026041000) {
        upgrade_plugin_savepoint(true, 2026041000, 'local', 'courseavailabilitydelay');
    }

    // v1.0.4: FIX-CAD-LANG-CAPABILITIES — Added missing capability language strings
    //   'courseavailabilitydelay:manage' and 'courseavailabilitydelay:viewreports'.
    //   Without these strings Moodle's capabilities admin page showed raw
    //   [[key]] placeholders instead of readable labels. No DB schema changes.
    if ($oldversion < 2026042400) {
        upgrade_plugin_savepoint(true, 2026042400, 'local', 'courseavailabilitydelay');
    }

    // v1.0.5–v1.0.8: FIX-API-DOMAIN series — Updated all API endpoint URLs to the correct
    //   production domain. No DB schema changes.
    if ($oldversion < 2026072300) {
        upgrade_plugin_savepoint(true, 2026072300, 'local', 'courseavailabilitydelay');
    }

    return true;
}

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
 * Course Availability Delay - Version information.
 *
 * v1.0.4: FIX-CAD-LANG-CAPABILITIES — Added missing capability language strings
 *         'courseavailabilitydelay:manage' and 'courseavailabilitydelay:viewreports'
 *         to lang/en/local_courseavailabilitydelay.php. Without these strings,
 *         Moodle's capability admin page displayed raw keys [[courseavailabilitydelay:manage]]
 *         and [[courseavailabilitydelay:viewreports]] instead of readable labels.
 *         No DB schema changes. version.php → 2026042400004.
 *
 * v1.0.0: Initial release. Per-course availability delay for enrolled students.
 *         Students cannot see a course on their My Courses dashboard until the
 *         configured delay period (days since enrolment) has elapsed. Supports
 *         per-user custom start-date overrides and bulk CSV import. Credit-gated
 *         unlock (1,000 credits) with Central Config priority. Admin pages:
 *         manage.php (CSV bulk import) and assign.php (manual per-user overrides).
 *
 * @package    local_courseavailabilitydelay
 * @copyright  2026 Essay Grader AI
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_courseavailabilitydelay';
$plugin->version   = 2026072300212;
$plugin->requires  = 2022041900;
$plugin->supported = [400, 500];
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '1.0.8';

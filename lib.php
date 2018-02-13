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
 * Comillasppi method library.
 *
 * @package    local_comillasppi
 * @copyright  2018 Planificacion de Entornos Tecnologicos SL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/pagelib.php');

/**
 * This is an Output function to load a js module in order to
 * show a privacy policy pop-up when the user is i nedit mode
 * inside a course
 *
 * @return void
 */
function local_comillasppi_before_footer() {
    global $PAGE;
    global $USER;
    global $CFG;

    // Check is the user has the capability to manage activities and the turn editing on option is activated.
    if (has_capability('moodle/course:manageactivities', $PAGE->context)
            && ($USER->editing == 1)
            && $PAGE->context->contextlevel == CONTEXT_COURSE) {
        // Recover the warning text to show on pop-up.
        $text = $CFG->local_comillasppi_text;

        // Recover the url and replace the placeholder with the current course's idnumber.
        $url = str_replace('idnumber', $PAGE->course->idnumber, $CFG->local_comillasppi_url);

        // Call to the js module responsible of creating the pop-up.
        $PAGE->requires->js_call_amd("local_comillasppi/comillasppi", "main", array($text, $url));
    }
}

/**
 * This is an Output function to load a css stylesheet to chance
 * the visual appearance of the pop-up
 *
 * @return void
 */
function local_comillasppi_before_http_headers() {
    global $PAGE;

    // Call to the stylesheet for the pop-up.
    $PAGE->requires->css("/local/comillasppi/style/comillasppi_style.css");
}

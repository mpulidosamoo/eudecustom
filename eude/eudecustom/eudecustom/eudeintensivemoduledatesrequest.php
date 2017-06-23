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
 * Moodle request php for intensivemoduledates ajax petitions.
 *
 * @package    local_eudecustom
 * @copyright  2017 Planificacion de Entornos Tecnologicos SL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/utils.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->dirroot . '/tag/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->libdir . '/filelib.php');

require_login(null, false, null, false, true);

global $DB;

if (optional_param('category', 0, PARAM_INT)) {
    $category = optional_param('category', 0, PARAM_INT);
    $sql = "SELECT *
              FROM {course}
             WHERE category = :category
               AND shortname LIKE 'MI.%'
          ORDER BY shortname ASC";
    $data = $DB->get_records_sql($sql, array('category' => $category));
    $response = "";
    $moduletitle = get_string('module', 'local_eudecustom');
    $date1title = get_string('date1', 'local_eudecustom');
    $date2title = get_string('date2', 'local_eudecustom');
    $date3title = get_string('date3', 'local_eudecustom');
    $date4title = get_string('date4', 'local_eudecustom');
    $savebuttontitle = get_string('savechanges', 'local_eudecustom');
    $resetbuttontitle = get_string('reset', 'local_eudecustom');

    if ($data) {
        $response = "<TABLE class='table'><TR><TH class='moduletitle'>$moduletitle</TH>"
                . "<TH  class='calldatecell'>$date1title</TH> <TH class='calldatecell'>$date2title</TH>"
                . "<TH class='calldatecell'>$date3title</TH><TH class='calldatecell'>$date4title</TH></TR>";

        foreach ($data as $course) {
            $fecha1 = '';
            $fecha2 = '';
            $fecha3 = '';
            $fecha4 = '';
            if ($record = $DB->get_record('local_eudecustom_call_date', array('courseid' => $course->id))) {
                if ($record->fecha1 !== 0) {
                    $fecha1 = date('d/m/Y', $record->fecha1);
                }
                if ($record->fecha1 !== 0) {
                    $fecha2 = date('d/m/Y', $record->fecha2);
                }
                if ($record->fecha1 !== 0) {
                    $fecha3 = date('d/m/Y', $record->fecha3);
                }
                if ($record->fecha1 !== 0) {
                    $fecha4 = date('d/m/Y', $record->fecha4);
                }
            }

            $response .= "<TR><TD><span class='shortname' title='$course->fullname'>$course->shortname</span>"
                    . "<input type='hidden' id='$course->shortname-shortname' class='shortname' "
                    . "name='$course->shortname-shortname' value='$course->shortname' readonly></TD>"
                    . "<TD><input type='text' id='date1-$course->id' class='date1 inputdate' name='date1-$course->id'"
                    . " value='$fecha1' placeholder='dd/mm/aaaa'></TD>"
                    . "<TD><input type='text' id='date2-$course->id' class='date2 inputdate' name='date2-$course->id'"
                    . " value='$fecha2' placeholder='dd/mm/aaaa'></TD>"
                    . "<TD><input type='text' id='date3-$course->id' class='date3 inputdate' name='date3-$course->id'"
                    . " value='$fecha3' placeholder='dd/mm/aaaa'></TD>"
                    . "<TD><input type='text' id='date4-$course->id' class='date4 inputdate' name='date4-$course->id'"
                    . " value='$fecha4' placeholder='dd/mm/aaaa'></TD>"
                    . "</TR> ";
        }

        $response .= "</TABLE>"
                . "<button type='submit' id='savedates'  class='btn btn-default' name='savedates' "
                . "value='savedates'>$savebuttontitle</button>"
                . "<button type='submit' id='resetfechas'  class='btn btn-default' name='resetfechas' "
                . "value='resetfechas'>$resetbuttontitle</button>";

        echo json_encode($response);
    }
}
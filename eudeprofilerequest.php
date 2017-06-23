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
 * Moodle academic management teacher page.
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
require_once(__DIR__ . "/classes/models/local_eudecustom_eudeprofile.class.php");

require_login(null, false, null, false, true);

global $DB;
global $CFG;
global $USER;

$PAGE->requires->jquery();
$PAGE->requires->js_call_amd("local_eudecustom/eude", "profile");
$PAGE->requires->js_call_amd("local_eudecustom/eude", "menu");

$newdata = configureprofiledata($USER->id);
// Request of the category.
if (optional_param('profilecat', 0, PARAM_INT)) {
    $category = optional_param('profilecat', 0, PARAM_INT);
    $response['student'] = '';
    $response['table'] = '';
    $data = $DB->get_records('course', array('category' => $category));
    $studentvalrole = get_shortname_courses_by_category($USER->id, 'studentval', $category);
    // If the request includes the id of a student we return data for the tables.
    if (optional_param('profilestudent', 0, PARAM_INT)) {
        $studentid = optional_param('profilestudent', 0, PARAM_INT);
        $table = new \html_table();
            $table->width = '100%';
            $table->head = array(get_string('module', 'local_eudecustom'), get_string('actions', 'local_eudecustom'),
                get_string('attemps', 'local_eudecustom'),
                get_string('provisionalgrades', 'local_eudecustom'), get_string('finalgrades', 'local_eudecustom'));
            $table->align = array('left', 'center', 'center', 'center', 'center');
            $table->size = array('45%', '15%', '10%', '15%', '15%');
        foreach ($data as $course) {
            if (substr($course->shortname, 0, 3) !== 'MI.') {
                $row = get_intensivecourse_data($course, $studentid);
                if ($row) {
                    $actiondata = html_writer::tag('span', $row->actions, array('class' => 'eudeprofilespan'));
                    $tr = new \html_table_row();
                    $tr->attributes['class'] = "cat" . $category . " mod" . $course->id;
                    $cell = new \html_table_cell($row->name);
                    $cell->attributes['title'] = $course->fullname;
                    $tr->cells[] = $cell;
                    if ($USER->id == $studentid && !$studentvalrole) {
                        $ok = false;
                        foreach ($newdata as $newd) {
                            $html = get_intensive_action($newd);
                            $cell = new \html_table_cell($html);
                        }
                        if ($ok == false) {
                            $cell = new \html_table_cell('');
                        }
                    } else {
                        $cell = new \html_table_cell($actiondata);
                    }
                    $tr->cells[] = $cell;
                    $html = html_writer::tag('span', $row->attempts, array('class' => 'attempts'));
                    if ($row->attempts > 0) {
                        $html .= html_writer::empty_tag('i',
                                        array('id' => 'info', 'class' => 'fa fa-info-circle',
                                            'title' => $row->info,
                                            'aria-hidden' => 'true'));
                    }
                    $cell = new \html_table_cell($html);
                    $tr->cells[] = $cell;
                    $cell = new \html_table_cell($row->provgrades);
                    $tr->cells[] = $cell;
                    $cell = new \html_table_cell($row->finalgrades);
                    $tr->cells[] = $cell;
                    $table->data[] = $tr;
                }
            }
        }
        $html = html_writer::table($table);
        $response = $html;
        // If the request is only for the category we return the select to choose a student.
    } else {
        $testeditingteacherrole = get_shortname_courses_by_category($USER->id, 'editingteacher', $category);
        $testteacherrole = get_shortname_courses_by_category($USER->id, 'teacher', $category);
        $testmanagerrole = get_shortname_courses_by_category($USER->id, 'manager', $category);
        $students = array();
        if (has_capability('moodle/site:config',
                context_system::instance()) || $testeditingteacherrole || $testteacherrole || $testmanagerrole) {
            foreach ($data as $course) {
                $students += get_course_students($course->id);
            }
            if (count($students)) {
                $html = '<label>' . get_string('choosestudent', 'local_eudecustom') . '</label>';
                $html .= "<select id='menucategoryname' class='select custom-select menucategoryname' name='categoryname'>";
                $html .= "<option value=''>-- Alumno --</option>";

                foreach ($students as $student) {
                    $html .= "<option value=$student->id>$student->lastname, $student->firstname</option>";
                }

                $html .= '</select>';

                $response['student'] .= $html;
            }
        } else {
            $data = get_user_all_courses($USER->id);
            $table = new \html_table();
            $table->width = '100%';
            $table->head = array(get_string('module', 'local_eudecustom'), get_string('actions', 'local_eudecustom'),
                get_string('attemps', 'local_eudecustom'),
                get_string('provisionalgrades', 'local_eudecustom'), get_string('finalgrades', 'local_eudecustom'));
            $table->align = array('left', 'center', 'center', 'center', 'center');
            $table->size = array('45%', '15%', '10%', '15%', '15%');
            foreach ($data as $course) {
                if (substr($course->shortname, 0, 3) !== 'MI.' && $course->category == $category) {
                    $row = get_intensivecourse_data($course, $USER->id);
                    if ($row) {
                        $actiondata = html_writer::tag('span', $row->actions, array('class' => 'eudeprofilespan'));
                        $tr = new \html_table_row();
                        $tr->attributes['class'] = "cat" . $category . " mod" . $course->id;
                        $cell = new \html_table_cell($row->name);
                        $cell->attributes['title'] = $course->fullname;
                        $tr->cells[] = $cell;
                        $ok = false;
                        foreach ($newdata as $newd) {
                            $html = get_intensive_action($newd);
                            $cell = new \html_table_cell($html);
                        }
                        if ($ok == false) {
                            $cell = new \html_table_cell('');
                        }
                        $tr->cells[] = $cell;
                        $html = html_writer::tag('span', $row->attempts, array('class' => 'attempts'));
                        if ($row->attempts > 0) {
                            $html .= html_writer::empty_tag('i',
                                            array('id' => 'info', 'class' => 'fa fa-info-circle',
                                                'title' => $newd->info,
                                                'aria-hidden' => 'true'));
                        }
                        $cell = new \html_table_cell($html);
                        $tr->cells[] = $cell;
                        $cell = new \html_table_cell($row->provgrades);
                        $tr->cells[] = $cell;
                        $cell = new \html_table_cell($row->finalgrades);
                        $tr->cells[] = $cell;
                        $table->data[] = $tr;
                    }
                }
            }
            $html = html_writer::table($table);
            $response['table'] = $html;
            $response['student'] = '';
        }
    }
    echo json_encode($response);
}
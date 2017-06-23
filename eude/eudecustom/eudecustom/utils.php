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
 * Eude plugin.
 *
 * This plugin cover specific needs of the plugin.
 *
 * @package    local_eudecustom
 * @copyright  2017 Planificacion de Entornos Tecnologicos SL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/moodlelib.php');

/**
 * This function enrols an user in an intensive eude course.
 *
 * @param string $enrol
 * @param int $courseid
 * @param int $userid
 * @param int $timestart 0 means unknown
 * @param int $timeend 0 means forever
 * @param int $convnum
 * @param int $status default to ENROL_USER_ACTIVE for new enrolments, no change by default in updates
 * @return void
 */
function enrol_intensive_user ($enrol, $courseid, $userid, $timestart = 0, $timeend = 0, $convnum, $status = null) {
    global $DB;

    $data = $DB->get_record('enrol', array('enrol' => $enrol, 'courseid' => $courseid));
    $userdata = $DB->get_record('user', array('id' => $userid));
    $coursedata = $DB->get_record('course', array('id' => $courseid));

    // We make a new entry on table user_enrolments.
    $ue = new stdClass();

    $ue->status = !$status ? ENROL_USER_ACTIVE : $status;
    $ue->enrolid = $data->id;
    $ue->userid = $userid;
    $ue->timestart = $timestart;
    $ue->timeend = $timeend;
    $ue->timecreated = time();
    $ue->timemodified = $ue->timecreated;
    if ($data = $DB->get_record('user_enrolments', array('userid' => $userid, 'enrolid' => $data->id))) {
        $ue->id = $data->id;
        $DB->update_record('user_enrolments', $ue);
    } else {
        $ue->id = $DB->insert_record('user_enrolments', $ue);
    }

    // We make a new entry on table local_eudecustom_mat_int.
    $record = new stdClass();
    $record->user_email = $userdata->email;
    $record->course_shortname = $coursedata->shortname;
    $record->matriculation_date = $timestart;
    $record->conv_number = $convnum;
    $DB->insert_record('local_eudecustom_mat_int', $record);

    // We make a new entry in role_assignments to give the user student role.
    $ra = new stdClass();
    $rid = $DB->get_record('role', array('shortname' => 'student'));
    $rctxid = context_course::instance($courseid);
    $ra->roleid = $rid->id;
    $ra->contextid = $rctxid->id;
    $ra->userid = $userid;
    $ra->timemodified = $timestart;
    $ra->modifierid = 2;
    if ($data = $DB->get_record('role_assignments',
            array(
        'userid' => $userid,
        'contextid' => $rctxid->id,
        'roleid' => $rid->id))) {
        $ra->id = $data->id;
        $DB->update_record('role_assignments', $ra);
    } else {
        $ra->id = $DB->insert_record('role_assignments', $ra);
    }

    // Check if a record exists in table local_eudecustom_user.
    $record = $DB->get_record('local_eudecustom_user',
            array('user_email' => $userdata->email, 'course_category' => $coursedata->category));
    if ($record) {
        // If record exists we make an update in local_eudecustom_user.
        $record->num_intensive = $record->num_intensive + 1;
        $DB->update_record('local_eudecustom_user', $record);

        // Reset the previous attempts on quizs for that course.
        reset_attemps_from_course($userid, $courseid);
    } else {
        // If not exists we make a new entry in local_eudecustom_user.
        $record = new stdClass();
        $record->user_email = $userdata->email;
        $record->course_category = $coursedata->category;
        $record->num_intensive = 1;
        $DB->insert_record('local_eudecustom_user', $record);
    }
}

/**
 * This function resets the attemps of each activity in a course for a given user.
 *
 * @param int $userid
 * @param int $courseid
 * @return void
 */
function reset_attemps_from_course ($userid, $courseid) {
    global $DB;
    $deleted = false;
    // We recover the quizs of the given course.
    if ($records = $DB->get_records('quiz', array('course' => $courseid))) {
        // For each quiz we delete the attempts of the given user.
        foreach ($records as $record) {
            $quizid = $record->id;
            $deleted = $DB->delete_records('quiz_attempts', array('userid' => $userid, 'quiz' => $quizid));
        }
    }
    return $deleted;
}

/**
 * This function returns an array with the different subjects of a message.
 *
 * @return array $data associative aray with subjects value=>description
 */
function get_samoo_subjects () {
    $data = array('Calificaciones' => get_string('califications', 'local_eudecustom'),
        'Foro' => get_string('forum', 'local_eudecustom'),
        'Duda' => get_string('doubt', 'local_eudecustom'),
        'Incidencia' => get_string('problem', 'local_eudecustom'),
        'Petición' => get_string('request', 'local_eudecustom'));

    return $data;
}

/**
 * This function returns all the categories with intensive courses.
 *
 * @return array $data associative array with id->name of course categories.
 */
function get_categories_with_intensive_modules () {
    global $DB;
    $data = array();
    $sql = "SELECT cc.id, cc.name
              FROM {course_categories} cc
             WHERE cc.id IN  (SELECT DISTINCT c.category
                                FROM {course} c
                               WHERE c.shortname LIKE 'MI.%')";
    $records = $DB->get_records_sql($sql, array());
    foreach ($records as $record) {
        $data[$record->name] = $record->id;
    }
    return $data;
}

/**
 * This function counts the number of matriculations in a given course.
 *
 * @param int $userid
 * @param int $courseid
 * @return int $attempts number of attempts made
 */
function count_course_matriculations ($userid, $courseid) {
    global $DB;
    $userdata = $DB->get_record('user', array('id' => $userid));
    $coursedata = $DB->get_record('course', array('id' => $courseid));
    // We recover the attempts of the given course.
    if ($record = $DB->get_records('local_eudecustom_mat_int',
            array('user_email' => $userdata->email, 'course_shortname' => $coursedata->shortname))) {
        return count($record);
    } else {
        return 0;
    }
}

/**
 * This function checks the number of enrolments in intensive courses of an user in a given category.
 *
 * @param int $userid
 * @param int $categoryid
 * @return int $courses number of enroled courses
 */
function count_total_intensives ($userid, $categoryid) {
    global $DB;

    $userdata = $DB->get_record('user', array('id' => $userid));
    // We recover the intensive courses of the given course.
    if ($record = $DB->get_record('local_eudecustom_user',
            array('user_email' => $userdata->email, 'course_category' => $categoryid))) {
        return $record->num_intensive;
    } else {
        return 0;
    }
}

/**
 * This function returns the name of the categories where an user has an enrolment in one or more courses and has a specific role.
 *
 * @param int $userid
 * @param string $role
 * @return array $categories
 */
function get_name_categories_by_role ($userid, $role) {
    global $DB;
    if ($role == 'manager') {
        $sql = 'SELECT distinct cc.name, cc.id
              FROM {role_assignments} ra
              JOIN {role} r ON r.id = ra.roleid
              JOIN {context} c ON c.id = ra.contextid
              JOIN {course_categories} cc ON cc.id = c.instanceid
             WHERE userid = :userid
               AND r.shortname = :role
               AND c.contextlevel = :context';
        $records = $DB->get_records_sql($sql,
                array(
            'userid' => $userid,
            'role' => $role,
            'context' => CONTEXT_COURSECAT
        ));
    } else {
        $sql = 'SELECT distinct cc.name, cc.id
              FROM {role_assignments} ra
              JOIN {role} r ON r.id = ra.roleid
              JOIN {context} c ON c.id = ra.contextid
              JOIN {course} co ON co.id = c.instanceid
              JOIN {course_categories} cc ON cc.id = co.category
             WHERE userid = :userid
               AND r.shortname = :role
               AND c.contextlevel = :context';
        $records = $DB->get_records_sql($sql,
                array(
            'userid' => $userid,
            'role' => $role,
            'context' => CONTEXT_COURSE
        ));
    }

    $categories = array();

    foreach ($records as $record) {
        $categories[$record->name] = $record->id;
    }

    return $categories;
}

/**
 * This function returns the users with the shortname student in a given course.
 *
 * @param int $courseid
 * @return array $data array of users
 */
function get_course_students ($courseid) {
    global $DB;
    $sql = 'SELECT u.*
              FROM {role_assignments} ra
              JOIN {user} u ON u.id = ra.userid
              JOIN {role} r ON r.id = ra.roleid
              JOIN {context} cxt ON cxt.id = ra.contextid
              JOIN {course} c ON c.id = cxt.instanceid
             WHERE ra.userid = u.id
               AND ra.contextid = cxt.id
               AND cxt.contextlevel = :contextlevel
               AND cxt.instanceid = :course
               AND  r.shortname = :shortname';
    $data = $DB->get_records_sql($sql,
            array(
        'contextlevel' => CONTEXT_COURSE,
        'shortname' => 'student',
        'course' => $courseid,
    ));

    return $data;
}

/**
 * This function returns the name of the enroled course categories of an user.
 *
 * @param int $userid
 * @return array $categories
 */
function get_user_categories ($userid) {
    global $DB;

    $sql = 'SELECT distinct (cc.name), cc.id
              FROM {role_assignments} ra
              JOIN {role} r ON r.id = ra.roleid
              JOIN {context} c ON c.id = ra.contextid
              JOIN {course} co ON co.id = c.instanceid
              JOIN {course_categories} cc ON cc.id = co.category
             WHERE userid = :userid
               AND c.contextlevel = :context';
    $records = $DB->get_records_sql($sql, array(
        'userid' => $userid,
        'context' => CONTEXT_COURSE
    ));

    $categories = array();

    foreach ($records as $record) {
        $categories[$record->name] = $record->id;
    }

    $managercategories = get_name_categories_by_role($userid, 'manager');
    foreach ($managercategories as $key => $value) {
        $categories[$key] = $value;
    }

    return $categories;
}

/**
 * This function returns the name of the courses of an user in a category depending of the user role in that courses.
 *
 * @param int $userid
 * @param string $role
 * @param int $category
 * @return array $courses
 */
function get_shortname_courses_by_category ($userid, $role, $category) {
    global $DB;
    // If the user is manager return all the courses in that category.
    if (check_role_manager($userid, $category)) {
        $sql = 'SELECT co.shortname, co.id
              FROM {course} co
             WHERE category = :category';
        $records = $DB->get_records_sql($sql, array(
            'category' => $category
        ));
    } else {
        $sql = 'SELECT co.shortname, co.id
              FROM {role_assignments} ra
              JOIN {role} r ON r.id = ra.roleid
              JOIN {context} c ON c.id = ra.contextid
              JOIN {course} co ON co.id = c.instanceid
              JOIN {course_categories} cc ON cc.id = co.category
             WHERE userid = :userid
               AND r.shortname = :role
               AND c.contextlevel = :context
               AND co.category = :category';
        $records = $DB->get_records_sql($sql,
                array(
            'userid' => $userid,
            'role' => $role,
            'context' => CONTEXT_COURSE,
            'category' => $category
        ));
    }
    return $records;
}

/**
 * This function check is the user is enroled in a category with role manager.
 *
 * @param int $userid
 * @param int $categoryid
 * @return boolean
 */
function check_role_manager ($userid, $categoryid) {
    global $DB;

    $sql = 'SELECT ra.userid
              FROM {role_assignments} ra
              JOIN {role} r ON r.id = ra.roleid
              JOIN {context} cxt ON cxt.id = ra.contextid
             WHERE cxt.instanceid = :categoryid
               AND cxt.contextlevel = :context
               AND r.shortname = :role';
    $record = $DB->get_record_sql($sql,
            array(
        'categoryid' => $categoryid,
        'context' => CONTEXT_COURSECAT,
        'role' => 'manager',
    ));

    if ($record && ($record->userid == $userid)) {
        return true;
    } else {
        return false;
    }
}

/**
 * This function returns the user enroled as a manager in a category if exists.
 *
 * @param int $categoryid
 * @return array $record
 */
function get_role_manager ($categoryid) {
    global $DB;

    $sql = 'SELECT u.*
              FROM {role_assignments} ra
              JOIN {role} r ON r.id = ra.roleid
              JOIN {context} cxt ON cxt.id = ra.contextid
              JOIN {user} u ON u.id = ra.userid
             WHERE cxt.instanceid = :categoryid
               AND cxt.contextlevel = :context
               AND r.shortname = :role';
    $record = $DB->get_record_sql($sql,
            array(
        'categoryid' => $categoryid,
        'context' => CONTEXT_COURSECAT,
        'role' => 'manager',
    ));

    return $record;
}

/**
 * This function returns the name of the enroled courses of an user of a specific category.
 *
 * @param int $userid
 * @param int $category
 * @return array $courses
 */
function get_user_shortname_courses ($userid, $category) {
    global $DB;

    if (check_role_manager($userid, $category)) {
        $records = $DB->get_records('course', array('category' => $category));
    } else {
        $sql = 'SELECT co.shortname, co.id
              FROM {role_assignments} ra
              JOIN {role} r ON r.id = ra.roleid
              JOIN {context} c ON c.id = ra.contextid
              JOIN {course} co ON co.id = c.instanceid
              JOIN {course_categories} cc ON cc.id = co.category
             WHERE userid = :userid
               AND c.contextlevel = :context
               AND co.category = :category';
        $records = $DB->get_records_sql($sql,
                array(
            'userid' => $userid,
            'context' => CONTEXT_COURSE,
            'category' => $category
        ));
    }
    return $records;
}

/**
 * This function stores in the table eude_fecha_convocatoria the matriculation dates of intensive courses.
 *
 * @param array $data array of stdClass ready to be inserted in the db
 * @return boolean $saved return true if all the records were saved properly
 */
function save_matriculation_dates ($data) {

    global $DB;
    $saved = false;
    foreach ($data as $course) {
        $record = new stdClass();
        $record->courseid = $course->courseid;
        $record->fecha1 = $course->fecha1;
        $record->fecha2 = $course->fecha2;
        $record->fecha3 = $course->fecha3;
        $record->fecha4 = $course->fecha4;
        if ($entry = $DB->get_record('local_eudecustom_call_date', array('courseid' => $course->courseid))) {
            $record->id = $entry->id;
            $saved = $DB->update_record('local_eudecustom_call_date', $record);
        } else {
            $DB->insert_record('local_eudecustom_call_date', $record, false);
            $saved = true;
        }
    }
    return $saved;
}

/**
 * This function updates the start date of an intensive course for an user.
 *
 * @param int $convnum number of call date (there are 4 different dates to choose)
 * @param int $cid id of a non intensive course
 * @param int $userid id of the user to update
 * @return void
 */
function update_intensive_dates ($convnum, $cid, $userid) {
    global $DB;
    $course = $DB->get_record('course', array('id' => $cid));
    $intensive = $DB->get_record('course', array('shortname' => 'MI.' . $course->shortname));
    $enrol = $DB->get_record('enrol', array('courseid' => $intensive->id, 'enrol' => 'manual'));
    $userdata = $DB->get_record('user', array('id' => $userid));
    if ($DB->get_record('user_enrolments', array('enrolid' => $enrol->id, 'userid' => $userid))) {
        $enrol = $DB->get_record('enrol', array('courseid' => $intensive->id, 'enrol' => 'manual'));
    } else {
        $enrol = $DB->get_record('enrol', array('courseid' => $intensive->id, 'enrol' => 'conduit'));
    }
    $start = $DB->get_record('user_enrolments', array('enrolid' => $enrol->id, 'userid' => $userid));
    $alldates = $DB->get_record('local_eudecustom_call_date', array('courseid' => $intensive->id));
    switch ($convnum) {
        case 1:
            $newdate = $alldates->fecha1;
            break;
        case 2:
            $newdate = $alldates->fecha2;
            break;
        case 3:
            $newdate = $alldates->fecha3;
            break;
        case 4:
            $newdate = $alldates->fecha4;
            break;
        default:
            break;
    }
    $date = new stdClass();
    $date->id = $start->id;
    $date->timestart = $newdate;
    // Timeend is timestart + a week in seconds.
    $date->timeend = $newdate + 604800;
    $recordupdated = $DB->update_record('user_enrolments', $date);
    $sql = 'SELECT id
               FROM {local_eudecustom_mat_int}
               WHERE course_shortname = :course_shortname AND user_email = :user_email
               ORDER BY matriculation_date DESC
               LIMIT 1';
    $idmatint = $DB->get_record_sql($sql, array('course_shortname' => $intensive->shortname, 'user_email' => $userdata->email));
    $newdata = new stdClass();
    $newdata->id = $idmatint->id;
    $newdata->matriculation_date = $newdate;
    $newdata->conv_number = $convnum;
    if ($recordupdated) {
        $recordupdated = $DB->update_record('local_eudecustom_mat_int', $newdata);
    }
    return $recordupdated;
}

/**
 * This function return the grade of an user in a specific course.
 *
 * @param int $cid id of the course
 * @param int $userid id of the user
 * @return int $finalgrade final grade of the course in 0-10 format
 */
function grades ($cid, $userid) {
    global $DB;
    $finalgrade = 0;
    $item = $DB->get_record('grade_items', array('courseid' => $cid, 'itemtype' => 'course'));
    if ($item && $DB->record_exists('grade_grades', array('itemid' => $item->id, 'userid' => $userid))) {
        $grades = $DB->get_record('grade_grades', array('itemid' => $item->id, 'userid' => $userid));
        // Format the grades to 0-10 numeration.
        $finalgrade = ($grades->finalgrade / $grades->rawgrademax) * 10;
    }
    return $finalgrade;
}

/**
 * This function return all the enroled courses of an user (we need also the still no started courses so we
 * cant use enrol_get_my_courses() function
 *
 * @param int $userid id of the user
 * @return array $data array with course objects
 */
function get_user_all_courses ($userid) {
    global $DB;
    
    $role = $DB->get_record('role', array('shortname' => 'student'));
    $sql = "SELECT  DISTINCT c.*
              FROM {course} c
              JOIN (SELECT DISTINCT e.courseid
                      FROM {enrol} e
                      JOIN {user_enrolments} ue
                      ON (ue.enrolid = e.id AND ue.userid = :userid)
                      ) en
                      ON (en.courseid = c.id)
                      JOIN {context} ctx
                      JOIN {role_assignments} ra
                      ON (ctx.instanceid = c.id
                            AND ctx.contextlevel = :context
                            AND ra.roleid = :role
                            AND ra.contextid = ctx.id
                            AND ra.userid = :user
                            )
             WHERE c.format NOT LIKE 'site' AND ra.id IS NOT NULL
             ORDER BY c.visible DESC, c.sortorder ASC";
    $data = $DB->get_records_sql($sql,
            array(
        'userid' => $userid,
        'user' => $userid,
        'context' => CONTEXT_COURSE,
        'role' => $role->id));
    return $data;
}

/**
 * This function returns data about the grades.
 *
 * @param integer $courseid
 * @param integer $userid
 * @return string $record->feedback string with the title show on the attempts hover.
 */
function get_info_grades ($courseid, $userid) {
    global $DB;

    $sql = "SELECT GG.feedback
                FROM {grade_grades} GG
                JOIN {grade_items} GI ON GG.itemid = GI.id
                JOIN {course} GC ON GI.courseid = GC.id
                WHERE GI.itemtype = 'course'
                AND GC.id = :course
                AND GG.userid = :userid";
    if ($DB->get_record_sql($sql, array('course' => $courseid, 'userid' => $userid))) {
        $record = $DB->get_record_sql($sql, array('course' => $courseid, 'userid' => $userid));
        if ($record->feedback == null || $record->feedback == "") {
            return 'No hay notas disponibles.';
        } else {
            return $record->feedback;
        }
    } else {
        return 'No hay notas disponibles.';
    }
}

/**
 * This function returns data required in the render of eudeprofile page.
 *
 * @param integer $userid
 * @return array $date array of instances of local_eudecustom_eudeprofile class
 */
function configureprofiledata ($userid) {
    global $DB;
    global $USER;
    global $CFG;

    $data = array();
    $daytoday = time();
    $weekinseconds = 604800;
    if ($userid == $USER->id) {
        $owner = true;
    } else {
        $owner = false;
    }
    // Get the enroled courses of the current user.
    if ($mycourses = get_user_all_courses($userid)) {
        foreach ($mycourses as $mycourse) {
            // If the course is not intensive type.
            if (substr($mycourse->shortname, 0, 3) !== 'MI.') {
                $object = new local_eudecustom_eudeprofile();
                $object->actionid = '';
                $object->desc = $mycourse->fullname;
                if ($mycourse->category) {
                    context_helper::preload_from_record($mycourse);
                    $ccontext = context_course::instance($mycourse->id);
                    $linkattributes = null;
                    if ($mycourse->visible == 0) {
                        if (!has_capability('moodle/course:viewhiddencourses', $ccontext)) {
                            continue;
                        }
                        $linkattributes['class'] = 'dimmed';
                    }
                    // Add scores for each course.
                    $mygrades = grades($mycourse->id, $userid);
                    // Print list of not intensive modules.
                    // Intensive module data.
                    if ($modint = $DB->get_record('course', array('shortname' => 'MI.' . $mycourse->shortname))) {
                        // Add intensive module grades.
                        $mygradesint = grades($modint->id, $userid);
                        $object->name = $mycourse->shortname;
                        $object->cat = ' cat' . $mycourse->category;
                        $object->id = ' mod' . $mycourse->id;

                        $sql = 'SELECT FROM_UNIXTIME(u.timestart,"%d/%m/%Y") AS time, u.timestart
                                  FROM {user_enrolments} u
                                  JOIN {enrol} e
                                 WHERE u.enrolid = e.id
                                   AND e.courseid = :courseid
                                   AND u.userid = :userid
                              ORDER BY u.timestart DESC
                                 LIMIT 1';
                        $time = $DB->get_record_sql($sql, array('courseid' => $modint->id, 'userid' => $userid));

                        $sql = 'SELECT FROM_UNIXTIME(fecha1,"%d/%m/%Y") AS f1, FROM_UNIXTIME(fecha2,"%d/%m/%Y") AS f2,
                            FROM_UNIXTIME(fecha3,"%d/%m/%Y") AS f3, FROM_UNIXTIME(fecha4,"%d/%m/%Y") AS f4
                            FROM {local_eudecustom_call_date}
                            WHERE courseid = :courseid';
                        $convoc = $DB->get_record_sql($sql, array('courseid' => $modint->id));
                        $matriculado = false;
                        if ($time) {
                            if ($daytoday < ($time->timestart + $weekinseconds)) {

                                $object->action = 'insideweek';
                                $matriculado = true;
                                $object->actiontitle = $time->time;
                                $object->actionclass = 'abrirFechas';
                                switch ($time->time) {
                                    case $convoc->f1:
                                        $date = 'fecha1';
                                        break;
                                    case $convoc->f2:
                                        $date = 'fecha2';
                                        break;
                                    case $convoc->f3:
                                        $date = 'fecha3';
                                        break;
                                    case $convoc->f4:
                                        $date = 'fecha4';
                                        break;
                                    default:
                                        $date = 'fecha1';
                                        break;
                                }

                                $sql = "SELECT $date AS fecha
                                          FROM {local_eudecustom_call_date} f
                                          JOIN {course} c
                                         WHERE f.courseid = c.id AND c.category = :category
                                         ORDER BY fecha ASC
                                         LIMIT 1";
                                $startconv = $DB->get_record_sql($sql, array('category' => $modint->category));

                                if ($startconv->fecha > ($daytoday + $weekinseconds) && $owner == true) {
                                    $object->actionid = 'abrirFechas(' . $mycourse->id . ',2,3)';
                                    $object->action = 'outweek';
                                }
                            }
                        }
                        $intentos = count_course_matriculations($userid, $modint->id);
                        if (!$matriculado) {
                            $object->action = 'notenroled';
                            $object->actionid = '';
                            $userdata = $DB->get_record('user', array('id' => $userid));
                            $numint = $DB->get_record('local_eudecustom_user',
                                    array(
                                'user_email' => $userdata->email,
                                'course_category' => $mycourse->category));
                            if (!$numint) {
                                $numint = new StdClass();
                                $numint->num_intensive = 0;
                            }
                            if ($owner == true) {
                                // Print action button.
                                if (gettype($mygrades) != 'double' && $intentos == 0) {
                                    $object->actiontitle = get_string('bringforward', 'local_eudecustom');
                                    $object->actionid = 'abrir(' . $mycourse->id . ',0,0)';
                                    $object->actionclass = 'abrir';
                                } else if ($mygradesint) {
                                    if ($mygradesint < 5) {
                                        if ($numint &&
                                                $numint->num_intensive < $CFG->local_eudecustom_intensivemodulechecknumber &&
                                                $intentos < $CFG->local_eudecustom_totalenrolsinincurse) {
                                            $object->actiontitle = get_string('retest', 'local_eudecustom');
                                            $object->actionid = 'abrirFechas(' . $mycourse->id . ',1,1)';
                                            $object->actionclass = 'abrirFechas';
                                        } else {
                                            $object->actiontitle = get_string('retest', 'local_eudecustom');
                                            $object->actionid = 'abrir(' . $mycourse->id . ',0,1)';
                                            $object->actionclass = 'abrir';
                                        }
                                    } else if ($mygradesint == 10) {
                                        $object->action = 'insideweek';
                                    } else {
                                        $object->actiontitle = get_string('increasegrades', 'local_eudecustom');
                                        $object->actionid = 'abrir(' . $mycourse->id . ',0,2)';
                                        $object->actionclass = 'abrir';
                                    }
                                } else {
                                    if ($mygrades < 5) {
                                        if ($numint &&
                                                $numint->num_intensive < $CFG->local_eudecustom_intensivemodulechecknumber &&
                                                $intentos < $CFG->local_eudecustom_totalenrolsinincurse) {
                                            $object->actiontitle = get_string('retest', 'local_eudecustom');
                                            $object->actionid = 'abrirFechas(' . $mycourse->id . ',1,1)';
                                            $object->actionclass = 'abrirFechas';
                                        } else {
                                            $object->actiontitle = get_string('retest', 'local_eudecustom');
                                            $object->actionid = 'abrir(' . $mycourse->id . ',0,1)';
                                            $object->actionclass = 'abrir';
                                        }
                                    } else if ($mygrades == 10) {
                                        $object->action = 'insideweek';
                                    } else {
                                        $object->actiontitle = get_string('increasegrades', 'local_eudecustom');
                                        $object->actionid = 'abrir(' . $mycourse->id . ',0,2)';
                                        $object->actionclass = 'abrir';
                                    }
                                }
                            } else {
                                $object->actiontitle = '-';
                                $object->action = 'insideweek';
                            }
                        }
                        // Print attemps.
                        $object->attempts = $intentos;
                        $object->info = get_info_grades($mycourse->id, $userid);

                        // Format grades to display.
                        if (gettype($mygrades) == 'double') {
                            $object->grades = number_format($mygrades, 2, '.', '');
                        } else {
                            $object->grades = '-';
                        }
                        if (gettype($mygradesint) == 'double') {
                            $object->gradesint = number_format($mygradesint, 2, '.', '');
                        } else {
                            if (gettype($mygrades) == 'double') {
                                $object->gradesint = number_format($mygrades, 2, '.', '');
                            } else {
                                $object->gradesint = '-';
                            }
                        };
                        array_push($data, $object);
                    }
                }
            }
        }
    }
    return $data;
}

/**
 * This function adds hidden inputs required in the tpv actions of the plugin
 *
 * @param string $response
 * @return string $response input string with added fields
 */
function add_tpv_hidden_inputs ($response) {
    global $USER;
    global $CFG;
    $response .= html_writer::empty_tag('input',
                    array(
                'type' => 'hidden',
                'id' => 'user',
                'name' => 'user',
                'class' => 'form-control',
                'value' => $USER->id));
    $response .= html_writer::empty_tag('input',
                    array(
                'type' => 'hidden',
                'id' => 'course',
                'name' => 'course',
                'class' => 'form-control'));
    $response .= html_writer::empty_tag('input',
                    array('type' => 'hidden',
                'id' => 'amount',
                'name' => 'amount',
                'class' => 'form-control',
                'value' => $CFG->local_eudecustom_intensivemoduleprice));
    $response .= html_writer::empty_tag('input',
                    array('type' => 'hidden',
                'id' => 'sesskey',
                'name' => 'sesskey',
                'class' => 'form-control',
                'value' => sesskey()));
    $response .= html_writer::end_div();
    $response .= html_writer::end_div();
    $response .= html_writer::empty_tag('input',
                    array(
                'type' => 'submit',
                'name' => 'abrirFechas',
                'class' => 'btn btn-lg btn-primary btn-block abrirFechas',
                'value' => get_string('continue', 'local_eudecustom')));
    return $response;
}

/**
 * This function returns a list of course id's where the user has a specific rol.
 *
 * @param int $userid
 * @return array $rolcourses
 */
function get_usercourses_by_rol ($userid) {
    global $DB;

    $rolsql = 'SELECT DISTINCT C.id, C.category
                 FROM {role_assignments} RA
                 JOIN {role} R ON R.id = RA.roleid
                 JOIN {context} CTX ON CTX.id = RA.contextid
                 JOIN {course} C ON C.id = CTX.instanceid
                 JOIN {course_categories} CC ON CC.id = C.category
                WHERE userid = :userid
                  AND CTX.contextlevel = :context
                  AND (R.shortname = :role1
                   OR R.shortname = :role2
                   OR R.shortname = :role3)
             ORDER BY C.id';

    $rolrecords = $DB->get_records_sql($rolsql,
            array(
        'userid' => $userid,
        'role1' => 'editingteacher',
        'role2' => 'manager',
        'role3' => 'teacher',
        'context' => CONTEXT_COURSE
    ));
    $rolcourses = [];

    foreach ($rolrecords as $r) {
        $c = ['course' => $r->id, 'category' => $r->category];
        array_push($rolcourses, $c);
    }
    return $rolcourses;
}

/**
 * Checks if the enrolment is for an intensive course
 * Intensive courses will always be named as 'MI.'+normal course shortname
 *
 * @param   String $shortname course shortname
 * @return  bool
 */
function module_is_intensive ($shortname) {

    // Define the intensive tag.
    $tag = 'MI';
    $sub = explode('.', $shortname);

    if ($sub[0] == $tag) {
        return true;
    } else {
        return false;
    }
}

/**
 * Get the course data categorized in actual, prev, and next.
 *
 *
 * @param int $courseid
 * @param int $actualmodule number of module in shortname
 * @return array $res data of course
 */
function get_students_course_data ($courseid, $actualmodule) {
    global $DB;

    $role = $DB->get_record('role', array('shortname' => 'student'))->id;
    $sql = "SELECT C.id, C.shortname, C.fullname, UE.timestart, UE.timeend, UE.userid, CC.name AS 'category name'
                    FROM {course} C
                    JOIN {course_categories} CC ON C.category = CC.id
                    JOIN {context} CTX ON C.id = CTX.instanceid
                    JOIN {role_assignments} RA ON RA.contextid = CTX.id
                    JOIN {user_enrolments} UE ON UE.userid = RA.userid
                    JOIN {enrol} E ON E.id = UE.enrolid AND E.courseid = C.id
                   WHERE RA.roleid = :role
                     AND C.id = :courseid
                   LIMIT 1";
    $res = $DB->get_record_sql($sql, array(
        'role' => $role,
        'courseid' => $courseid
    ));

    if ($res) {
        if (!module_is_intensive($res->shortname)) {
            $module = explode('.M', $res->shortname);

            if ($module[1] == $actualmodule) {
                $res->date = 'actual';
            } else if ($module[1] > $actualmodule) {
                $res->date = 'next';
            } else {
                $res->date = 'prev';
            }
        } else {
            $res->date = 'actual';
        }
    } else {
        $sql2 = "SELECT C.id, C.shortname, C.fullname, UE.timestart, UE.timeend, UE.userid, CC.name AS 'category name'
                    FROM {course} C
                    JOIN {course_categories} CC ON C.category = CC.id
                    JOIN {context} CTX ON C.id = CTX.instanceid
                    JOIN {role_assignments} RA ON RA.contextid = CTX.id
                    JOIN {user_enrolments} UE ON UE.userid = RA.userid
                    JOIN {enrol} E ON E.id = UE.enrolid AND E.courseid = C.id
                   WHERE C.id = :courseid
                   LIMIT 1";
        $res = $DB->get_record_sql($sql2, array(
            'courseid' => $courseid
        ));
        $res->date = 'actual';
    }
    return $res;
}

/**
 * get forums and assignments, and add them to the course array.
 *
 * @param object $record
 * @return object $record
 */
function add_course_activities ($record) {
    global $DB;

    $forumsql = 'SELECT id, course, type, name
                   FROM {forum}
                  WHERE course = :course';

    $forums = $DB->get_records_sql($forumsql, array('course' => $record->id));

    $assignsql = 'SELECT id, course, name
                    FROM {assign}
                   WHERE course = :course';

    $assigns = $DB->get_records_sql($assignsql, array('course' => $record->id));

    $record->forums = [];
    $record->assigns = [];
    if ($forums) {
        foreach ($forums as $forum) {
            if ($forum->type == 'news') {
                $record->notices = $forum;
            } else {
                array_push($record->forums, $forum);
            }
        }
    }
    if ($assigns) {
        foreach ($assigns as $assign) {
            array_push($record->assigns, $assign);
        }
    }

    return $record;
}

/**
 * Sort an array of objects by shortname atribute.
 *
 * @param stdClass $a with shortname attribute
 * @param stdClass $b with shortname attribute
 * @return int $ab
 */
function sort_obj_by_shortname ($a, $b) {
    $ab = strcmp($a->shortname, $b->shortname);
    return $ab;
}

/**
 * This function returns an array of courses categorized by actual prev and next
 * according to enrolment dates.
 *
 * @param int $userid
 * @return array $courses
 */
function get_user_courses ($userid) {

    $records['actual'] = [];
    $records['prev'] = [];
    $records['next'] = [];

    $rolcourses = get_usercourses_by_rol($userid);
    $categories = [];

    // Separate in categories, courses.
    foreach ($rolcourses as $r) {
        $catexists = false;
        foreach ($categories as $category) {
            if ($category->id == $r['category']) {
                $catexists = true;
                $coursexists = false;

                if (!$coursexists) {
                    $course = new stdClass();
                    $course->id = $r['course'];
                    $r = get_students_course_data($course->id, $category->actualmodule);
                    $record = add_course_activities($r);
                    if ($record->date == 'actual') {
                        array_push($records['actual'], $record);
                        usort($records['actual'], "sort_obj_by_shortname");
                    } else if ($record->date == 'next') {
                        array_push($records['next'], $record);
                        usort($records['next'], "sort_obj_by_shortname");
                    } else {
                        array_push($records['prev'], $record);
                        usort($records['prev'], "sort_obj_by_shortname");
                    }
                    array_push($category->courses, $course);
                }
            }
        }
        if (!$catexists) {
            $category = new stdClass();
            $category->id = $r['category'];
            $category->actualmodule = get_actual_module($category->id);
            $course = new stdClass();
            $course->id = $r['course'];
            $r = get_students_course_data($course->id, $category->actualmodule);
            $record = add_course_activities($r);
            if ($record->date == 'actual') {
                array_push($records['actual'], $record);
                usort($records['actual'], "sort_obj_by_shortname");
            } else if ($record->date == 'next') {
                array_push($records['next'], $record);
                usort($records['next'], "sort_obj_by_shortname");
            } else {
                array_push($records['prev'], $record);
                usort($records['prev'], "sort_obj_by_shortname");
            }
            $category->courses = [];
            array_push($category->courses, $course);
            array_push($categories, $category);
        }
    }
    return $records;
}

/**
 * Gets the course module opened for students at the moment (actual enrolment)
 *
 * @param int $catid
 * @return array $actualmodule
 */
function get_actual_module ($catid) {
    global $DB;

    $now = time();
    $role = $DB->get_record('role', array('shortname' => 'student'))->id;
    $actualmodule = 0;
    $sql = "SELECT C.id, C.shortname, C.category
                    FROM {course} C
                    JOIN {course_categories} CC ON C.category = CC.id
                    JOIN {context} CTX ON C.id = CTX.instanceid
                    JOIN {role_assignments} RA ON RA.contextid = CTX.id
                    JOIN {user_enrolments} UE ON UE.userid = RA.userid
                    JOIN {enrol} E ON E.id = UE.enrolid AND E.courseid = C.id
                   WHERE RA.roleid = :role
                     AND C.category = :category
                     AND UE.timestart < :now
                     AND UE.timeend >= :now2
                ORDER BY UE.timestart DESC
                   LIMIT 1";
    $res = $DB->get_record_sql($sql,
            array(
        'role' => $role,
        'category' => $catid,
        'now' => $now,
        'now2' => $now
    ));
    if ($res) {
        $split = explode('.M', $res->shortname, 2);
        $actualmodule = $split[1];
    }
    return $actualmodule;
}

/**
 * This function returns data of the intensive modules of a student to show in eudeprofile view.
 *
 * @param course $course an instance of the normal course to check his intensive course
 * @param int $studentid the id of the user whose data will be shown
 * @return boolean || stdClass $intensivecourse an object with the data required to show in a table cell
 */
function get_intensivecourse_data ($course, $studentid) {
    global $DB;
    // Check if the course has a intensive module related.
    if ($modint = $DB->get_record('course', array('shortname' => 'MI.' . $course->shortname))) {
        $intensivecourse = new stdClass();
        $intensivecourse->name = $course->shortname;
        $intensivecourse->id = $modint->id;
        $userdata = $DB->get_record('user', array('id' => $studentid));
        // Check if the user has enroled in the intensive module to print the last matriculation date.
        $sql = 'SELECT *
                  FROM {local_eudecustom_mat_int}
                 WHERE user_email = :user_email
                   AND course_shortname = :course_shortname
              ORDER BY matriculation_date DESC
                 LIMIT 1';
        if ($intdate = $DB->get_record_sql($sql,
                array('user_email' => $userdata->email, 'course_shortname' => $modint->shortname))) {
            $intensivecourse->actions = date("d/m/o", $intdate->matriculation_date);
        } else {
            $intensivecourse->actions = '-';
        }
        // Count the numbers of enrolments in the intensive module.
        $intensivecourse->attempts = count_course_matriculations($studentid, $modint->id);
        $intensivecourse->info = get_info_grades($course->id, $studentid);
        // Check if the user has grades in the normal and intensive modules or didnt attemp the exams.
        $coursegrades = grades($course->id, $studentid);
        $intensivegrades = grades($modint->id, $studentid);
        if (gettype($coursegrades) == 'double') {
            $intensivecourse->provgrades = number_format($coursegrades, 2, '.', '');
        } else {
            $intensivecourse->provgrades = '-';
        }
        if (gettype($intensivegrades) == 'double') {
            $intensivecourse->finalgrades = number_format($intensivegrades, 2, '.', '');
        } else {
            $intensivecourse->finalgrades = $intensivecourse->provgrades;
        }
        return $intensivecourse;
    } else {
        return $intensivecourse = false;
    }
}

/**
 * This function receives a string with the format username;shortname;date(dd/mm/yyyy);num/n in eachline,
 * and then process the data to insert or update records in the database.
 *
 * @param string $data string with format username;shortname;date(dd/mm/yyyy);num/n
 * @return boolean || Exception true if the transaction process was completed successfully, or exception if
 * the commits had failed.
 */
function integrate_previous_data ($data) {
    global $DB;
    $completed = false;
    try {
        $transaction = $DB->start_delegated_transaction();
        $registers = preg_split('/\r\n|\r|\n/', $data);
        foreach ($registers as $register) {
            // Data entry validation.
            $register = explode(";", $register);
            if (array_key_exists(0, $register) && ($register[0] == 'CREATE' || $register[0] == 'DELETE')) {
                $action = $register[0];
            } else {
                throw new Exception('Error');
            }
            if (array_key_exists(1, $register)) {
                $useremail = $register[1];
            } else {
                throw new Exception('Error');
            }
            if (array_key_exists(2, $register)) {
                $courseshortname = $register[2];
            } else {
                throw new Exception('Error');
            }
            switch ($action) {
                /*
                 * With CREATE action we record a new entry in local_eudecustom_mat_int and
                 * a new entry/update if record exists in local_eudecustom_user.
                 */
                case 'CREATE':

                    if (array_key_exists(3, $register) && validatedate($register[3], 'd/m/Y')) {
                        $unixdate = DateTime::createFromFormat('d/m/Y', $register[3])->getTimestamp();
                    } else {
                        throw new Exception('Error');
                    }
                    if (array_key_exists(4, $register) && is_int((int) $register[4]) && ($register[4] >= 1 && $register[4] <= 4)) {
                        $convnumber = $register[4];
                    } else {
                        throw new Exception('Error');
                    }

                    // New entry in local_eudecustom_mat_int.
                    $record1 = new stdClass();
                    $record1->user_email = $useremail;
                    $record1->course_shortname = $courseshortname;
                    $record1->matriculation_date = $unixdate;
                    $record1->conv_number = $convnumber;
                    $DB->insert_record('local_eudecustom_mat_int', $record1);
                    $course = findcategory($courseshortname);
                    $record2 = $DB->get_record('local_eudecustom_user',
                            array('user_email' => $useremail, 'course_category' => $course->category));
                    // Create/Update entry in local_eudecustom_user.
                    if ($record2) {
                        $record2->num_intensive = $record2->num_intensive + 1;
                        $DB->update_record('local_eudecustom_user', $record2);
                    } else {
                        $record = new stdClass();
                        $record->user_email = $useremail;
                        $record->course_category = $course->category;
                        $record->num_intensive = 1;
                        $DB->insert_record('local_eudecustom_user', $record);
                    }
                    break;
                /*
                 * With DELETE action we delete all the records in local_eudecustom_mat_int of that course related
                 * to the user and delete/update if record exists in local_eudecustom_user.
                 */
                case 'DELETE':
                    // Count the records to delete and delete afterwards.
                    $records = $DB->get_records('local_eudecustom_mat_int',
                            array('user_email' => $useremail, 'course_shortname' => $courseshortname));
                    $DB->delete_records('local_eudecustom_mat_int',
                            array('user_email' => $useremail, 'course_shortname' => $courseshortname));
                    $course = findcategory($courseshortname);
                    // Delete/Update entry in local_eudecustom_user.
                    $record2 = $DB->get_record('local_eudecustom_user',
                            array('user_email' => $useremail, 'course_category' => $course->category));
                    if ($record2) {
                        $record2->num_intensive = $record2->num_intensive - count($records);
                        // If the new number is > 0 we make an update, else we make a delete.
                        if ($record2->num_intensive > 0) {
                            $DB->update_record('local_eudecustom_user', $record2);
                        } else {
                            $DB->delete_records('local_eudecustom_user', array('id' => $record2->id));
                        }
                    }
                    break;

                default:
                    break;
            }
        }
        $transaction->allow_commit();
        $completed = true;
    } catch (Exception $e) {
        $transaction->rollback($e);
        $completed = false;
    } finally {
        return $completed;
    }
}

/**
 * This function validates if a string is a valid date in the specified format
 *
 * @param string $date string with the date
 * @param string $format string with the date format
 * @return boolean
 */
function validatedate($date, $format = 'Y-m-d H:i:s') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

/**
 * This function find the category of a course
 *
 * @param string $courseshortname string with the shortname of the course
 * @return stdClass $course;
 */
function findcategory($courseshortname) {
    global $DB;
    // If the course is not integrated yet, we find the category by the course shortname index.
    if ($DB->record_exists('course', array('shortname' => $courseshortname))) {
        $course = $DB->get_record('course', array('shortname' => $courseshortname));
    } else {
        /*
         * If the course dont exist, we explode the shortname and get the category name (as the shortnames
         * have this format MI.xxxxx.yyyy, so xxxx is the name of the course's category name)
        */
        $coursecategoryname = explode(".", $courseshortname);
        if ($DB->record_exists('course_categories', array('name' => $coursecategoryname[1]))) {
            $coursecategory = $DB->get_record('course_categories', array('name' => $coursecategoryname[1]));
        } else {
            throw new Exception('Error');
        }
        $course = new stdClass();
        $course->category = $coursecategory->id;
    }
    return $course;
}

/**
 * This function get the action print of intensive courses
 *
 * @param object $data object with the course data.
 * @return string $html;
 */
function get_intensive_action($data) {
    if ($data->action == 'notenroled') {
        switch ($data->actiontitle) {
            case get_string('bringforward', 'local_eudecustom'):
                $html = html_writer::tag('button', get_string('bringforward', 'local_eudecustom'),
                                array('class' => $data->actionclass, 'id' => $data->actionid));
                break;
            case get_string('retest', 'local_eudecustom'):
                $html = html_writer::tag('button', get_string('retest', 'local_eudecustom'),
                                array('class' => $data->actionclass, 'id' => $data->actionid));
                break;
            case get_string('increasegrades', 'local_eudecustom'):
                $html = html_writer::tag('button', get_string('increasegrades', 'local_eudecustom'),
                                array('class' => $data->actionclass, 'id' => $data->actionid));
                break;
            default:
                $html = '';
                break;
        }
    } else if ($data->action == 'outweek') {
        $html = html_writer::tag('span', $data->actiontitle, array('class' => 'eudeprofilespan'));
        $html .= html_writer::tag('i', '·', array('id' => $data->actionid,
                        'class' => 'fa fa-pencil-square-o ' . $data->actionclass,
                        'aria-hidden' => 'true'));
    } else {
        $html = html_writer::tag('span', $data->actiontitle, array('class' => 'eudeprofilespan'));
    }
    return $html;
}

/**
 * This function generate html to print the event keys section
 *
 * @return string $html;
 */
function generate_event_keys($modal = '') {
    $html = html_writer::tag('h3', get_string('eventkeytitle', 'local_eudecustom'));
    $html .= html_writer::start_tag('ul', array('class' => 'eventkey'));

    $html .= html_writer::start_div('col-md-4');

    $html .= html_writer::start_tag('li', array('id' => 'eventkeymodulebegin', 'class' => 'eventkey'));
    $html .= html_writer::empty_tag('input',
                    array('type' => 'checkbox', 'id' => 'cb-eventkeymodulebegin',
                'class' => 'cb-eventkey', 'name' => 'modulebegin'.$modal, 'checked' => 'checked'));
    $html .= html_writer::start_tag('div',
                    array('id' => 'cd-eventkeymodulebegin',
                'class' => 'cd-eventkey eventkeymodulebegin'));
    $html .= html_writer::end_tag('div');
    $html .= html_writer::tag('span', get_string('eventkeymodulebegin', 'local_eudecustom'));
    $html .= html_writer::end_tag('li');

    $html .= html_writer::start_tag('li', array('id' => 'eventkeyactivityend', 'class' => 'eventkey'));
    $html .= html_writer::empty_tag('input',
                    array('type' => 'checkbox', 'id' => 'cb-eventkeyactivityend',
                'class' => 'cb-eventkey', 'name' => 'activityend'.$modal, 'checked' => 'checked'));
    $html .= html_writer::start_tag('div',
                    array('id' => 'cd-eventkeyactivityend',
                'class' => 'cd-eventkey eventkeyactivityend'));
    $html .= html_writer::end_tag('div');
    $html .= html_writer::tag('span', get_string('eventkeyactivityend', 'local_eudecustom'));
    $html .= html_writer::end_tag('li');

    $html .= html_writer::start_tag('li', array('id' => 'eventkeyquestionnairedate', 'class' => 'eventkey'));
    $html .= html_writer::empty_tag('input',
                    array('type' => 'checkbox', 'id' => 'cb-eventkeyquestionnairedate',
                'class' => 'cb-eventkey', 'name' => 'questionnairedate'.$modal, 'checked' => 'checked'));
    $html .= html_writer::start_tag('div',
                    array('id' => 'cd-eventkeyquestionnairedate',
                'class' => 'cd-eventkey eventkeyquestionnairedate'));
    $html .= html_writer::end_tag('div');
    $html .= html_writer::tag('span', get_string('eventkeyquestionnaire', 'local_eudecustom'));
    $html .= html_writer::end_tag('li');

    $html .= html_writer::end_tag('div');
    $html .= html_writer::start_div('col-md-4');

    $html .= html_writer::start_tag('li', array('id' => 'eventkeytestdate', 'class' => 'eventkey'));
    $html .= html_writer::empty_tag('input',
                    array('type' => 'checkbox', 'id' => 'cb-eventkeytestdate',
                'class' => 'cb-eventkey', 'name' => 'testdate'.$modal, 'checked' => 'checked'));
    $html .= html_writer::start_tag('div', array('id' => 'cd-eventkeytestdate', 'class' => 'cd-eventkey eventkeytestdate'));
    $html .= html_writer::end_tag('div');
    $html .= html_writer::tag('span', get_string('eventkeytestdate', 'local_eudecustom'));
    $html .= html_writer::end_tag('li');

    $html .= html_writer::start_tag('li', array('id' => 'eventkeyintensivemodulebegin', 'class' => 'eventkey'));
    $html .= html_writer::empty_tag('input',
                    array('type' => 'checkbox', 'id' => 'cb-eventkeyintensivemodulebegin',
                'class' => 'cb-eventkey', 'name' => 'intensivemodulebegin'.$modal, 'checked' => 'checked'));
    $html .= html_writer::start_tag('div',
                    array('id' => 'cd-eventkeyintensivemodulebegin',
                'class' => 'cd-eventkey eventkeyintensivemodulebegin'));
    $html .= html_writer::end_tag('div');
    $html .= html_writer::tag('span', get_string('eventkeyintensivemodulebegin', 'local_eudecustom'));
    $html .= html_writer::end_tag('li');

    $html .= html_writer::end_tag('div');
    $html .= html_writer::start_div('col-md-4');

    $html .= html_writer::start_tag('li', array('id' => 'eventkeyeudeevent', 'class' => 'eventkey'));
    $html .= html_writer::empty_tag('input',
                    array('type' => 'checkbox', 'id' => 'cb-eventkeyeudeevent',
                'class' => 'cb-eventkey', 'name' => 'eudeevent'.$modal, 'checked' => 'checked'));
    $html .= html_writer::start_tag('div', array('id' => 'cd-eventkeyeudeevent', 'class' => 'cd-eventkey eventkeyeudeevent'));
    $html .= html_writer::end_tag('div');
    $html .= html_writer::tag('span', get_string('eventkeyeudeevent', 'local_eudecustom'));
    $html .= html_writer::end_tag('li');

    $html .= html_writer::end_tag('div');

    $html .= html_writer::end_tag('ul');
    return $html;
}
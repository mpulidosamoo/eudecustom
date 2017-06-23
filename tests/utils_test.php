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
 * COMPONENT External functions unit tests
 *
 * @package    local_eudecustom
 * @copyright  2017 Planificacion de Entornos Tecnologicos SL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require($CFG->dirroot . '\local\eudecustom\utils.php');

/**
 * This class is used to run the unit tests
 *
 * @package    local_eudecustom
 * @copyright  2015 Planificacion de Entornos Tecnologicos SL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_eudecustom_testcase extends advanced_testcase {

    /**
     * Enable the manual enrol plugin.
     *
     * @return bool $manualplugin Return true if is enabled.
     */
    public function enable_enrol_plugin () {
        $manualplugin = enrol_get_plugin('manual');
        return $manualplugin;
    }

    /**
     * Get Student object.
     *
     * @return stdClass $studentrole Object student role record.
     */
    public function get_student_role () {
        global $DB;
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        return $studentrole;
    }

    /**
     * Get Teacher object.
     *
     * @return stdClass $teacherrole Object teacher role record.
     */
    public function get_teacher_role () {
        global $DB;
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));
        return $teacherrole;
    }

    /**
     * Create manual instance to enrol in a course.
     * @param int $courseid Course id.
     *
     * @return stdClass $manualinstance Object type of enrol to be enrolled.
     */
    public function create_manual_instance ($courseid) {
        global $DB;
        $manualinstance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
        return $manualinstance;
    }

    /**
     * Tests for phpunit.
     */
    public function test_get_categories_with_intensive_modules () {

        $this->resetAfterTest(true);

        // Creating a few categories.
        $category1 = $this->getDataGenerator()->create_category(
                array('name' => 'Category with normal and intensive courses'));
        $category2 = $this->getDataGenerator()->create_category(
                array('name' => 'Category with only normal courses'));
        $category3 = $this->getDataGenerator()->create_category(
                array('name' => 'Category with only intensive courses'));

        // Creating several courses and assign each to one of the categories above.
        $course1 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Normal course 1', 'category' => $category1->id));
        $course2 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Normal course 2', 'category' => $category1->id));
        $course3 = $this->getDataGenerator()->create_course(
                array('shortname' => 'MI.Normal course 1', 'category' => $category1->id));
        $course4 = $this->getDataGenerator()->create_course(
                array('shortname' => 'MI.Normal course 2', 'category' => $category1->id));
        $course5 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Normal course 3', 'category' => $category2->id));
        $course6 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Normal course 4', 'category' => $category2->id));
        $course7 = $this->getDataGenerator()->create_course(
                array('shortname' => 'MI.Other course 1', 'category' => $category3->id));
        $course8 = $this->getDataGenerator()->create_course(
                array('shortname' => 'MI.Other course 2', 'category' => $category3->id));

        // Get the function response.
        $result = get_categories_with_intensive_modules();
        // Build an array with the expected result.
        $expectedresult = array($category1->name => $category1->id, $category3->name => $category3->id);

        // Test the function response.
        $this->assertEquals($expectedresult, $result);
        $this->assertCount(2, $result);

        // Reset all the data and test with no categories in the database.
        $this->resetAllData();
        $result = get_categories_with_intensive_modules();
        $expectedresult = array();
        $this->assertEquals($expectedresult, $result);
        $this->assertCount(0, $result);
    }

    /**
     * Tests for phpunit.
     */
    public function test_get_samoo_subjects () {

        $this->resetAfterTest(true);

        // Get the function response.
        $result = get_samoo_subjects();
        // Build an array with the expected result.
        $expectedresult = array('Calificaciones' => get_string('califications', 'local_eudecustom'),
            'Foro' => get_string('forum', 'local_eudecustom'),
            'Duda' => get_string('doubt', 'local_eudecustom'),
            'Incidencia' => get_string('problem', 'local_eudecustom'),
            'Petición' => get_string('request', 'local_eudecustom'));

        // Test the function response.
        $this->assertEquals($expectedresult, $result);
    }

    /**
     * Tests for phpunit.
     */
    public function test_count_course_matriculations () {
        global $DB;
        $this->resetAfterTest(true);

        // Creating a few users.
        $user1 = $this->getDataGenerator()->create_user(array('username' => 'user1', 'email' => 'user1@test.com'));
        $user2 = $this->getDataGenerator()->create_user(array('username' => 'user2', 'email' => 'user2@test.com'));
        $user3 = $this->getDataGenerator()->create_user(array('username' => 'user3', 'email' => 'user3@test.com'));

        // Creating several courses to enrol the users.
        $course1 = $this->getDataGenerator()->create_course(array('shortname' => 'Course 1'));
        $course2 = $this->getDataGenerator()->create_course(array('shortname' => 'Course 2'));
        $course3 = $this->getDataGenerator()->create_course(array('shortname' => 'Course 3'));

        // Generating and inserting the records in the db.
        $record1 = new stdClass();
        $record1->user_email = $user1->email;
        $record1->course_shortname = $course1->shortname;
        $record1->matriculation_date = time();
        $record2 = new stdClass();
        $record2->user_email = $user1->email;
        $record2->course_shortname = $course2->shortname;
        $record2->matriculation_date = time();
        // Gonna insert 3 matriculations for user1 course1 and 1 matriculation for user1 course2.
        $lastinsertid = $DB->insert_record('local_eudecustom_mat_int', $record1);
        $lastinsertid = $DB->insert_record('local_eudecustom_mat_int', $record1);
        $lastinsertid = $DB->insert_record('local_eudecustom_mat_int', $record1);
        $lastinsertid = $DB->insert_record('local_eudecustom_mat_int', $record2);

        // Test user1 with course1 (Expected results = 3).
        $result = count_course_matriculations($user1->id, $course1->id);
        $this->assertEquals(3, $result);

        // Test user1 with course2 (Expected result = 1).
        $result = count_course_matriculations($user1->id, $course2->id);
        $this->assertEquals(1, $result);

        // Test user2 with course1 (Expected result = 0).
        $result = count_course_matriculations($user2->id, $course1->id);
        $this->assertEquals(0, $result);

        // Test a nonexistent user and a nonexistent course.
        $result = count_course_matriculations($user3->id, $course1->id);
        $this->assertEquals(0, $result);
        $result = count_course_matriculations($user1->id, $course3->id);
        $this->assertEquals(0, $result);
    }

    /**
     * Tests for phpunit.
     */
    public function test_count_total_intensives () {
        global $DB;
        $this->resetAfterTest(true);

        // Creating a few users.
        $user1 = $this->getDataGenerator()->create_user(array('username' => 'user1', 'email' => 'user1@php.com'));
        $user2 = $this->getDataGenerator()->create_user(array('username' => 'user2', 'email' => 'user2@php.com'));

        // Creating several categories for future use.
        $category1 = $this->getDataGenerator()->create_category(array('name' => 'Category 1'));
        $category2 = $this->getDataGenerator()->create_category(array('name' => 'Category 2'));
        $category3 = $this->getDataGenerator()->create_category(array('name' => 'Category 3'));

        // Generating and inserting the records in the db.
        $record1 = new stdClass();
        $record1->user_email = $user1->email;
        $record1->course_category = $category1->id;
        $record1->num_intensive = 5;
        $record2 = new stdClass();
        $record2->user_email = $user1->email;
        $record2->course_category = $category2->id;
        $record2->num_intensive = 2;

        // Insert data for user1 in the table.
        $lastinsertid = $DB->insert_record('local_eudecustom_user', $record1);
        $lastinsertid = $DB->insert_record('local_eudecustom_user', $record2);

        // Test user1 with course_category1 (Expected results = 5).
        $result = count_total_intensives($user1->id, $category1->id);
        $this->assertEquals(5, $result);

        // Test user1 with course_category2 (Expected result = 2).
        $result = count_total_intensives($user1->id, $category2->id);
        $this->assertEquals(2, $result);

        // Test user1 with course_category3 (Expected result = 0).
        $result = count_total_intensives($user1->id, $category3->id);
        $this->assertEquals(0, $result);

        // Test a nonexistent user and a nonexistent course.
        $result = count_total_intensives($user2->id, $category1->id);
        $this->assertEquals(0, $result);
        $result = count_total_intensives($user1->id, $category3->id);
        $this->assertEquals(0, $result);
    }

    /**
     * Tests for phpunit.
     */
    public function test_get_name_categories_by_role () {
        global $DB;
        $this->resetAfterTest(true);

        // Creating a few users.
        $user1 = $this->getDataGenerator()->create_user(array('username' => 'user1', 'email' => 'user1@php.com'));
        $user2 = $this->getDataGenerator()->create_user(array('username' => 'user2', 'email' => 'user2@php.com'));

        // Creating several categories for future use.
        $category1 = $this->getDataGenerator()->create_category(array('name' => 'Category 1'));
        $category2 = $this->getDataGenerator()->create_category(array('name' => 'Category 2'));
        $category3 = $this->getDataGenerator()->create_category(array('name' => 'Category 3'));

        // Creating courses related to the categories above.
        $course1 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 1', 'category' => $category1->id));
        $course2 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 2', 'category' => $category1->id));
        $course3 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 3', 'category' => $category1->id));
        $course4 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 4', 'category' => $category2->id));
        $course5 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 5', 'category' => $category2->id));
        $course6 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 6', 'category' => $category3->id));
        $course7 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 7', 'category' => $category3->id));
        $course8 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 8', 'category' => $category3->id));

        // Getting the id of the roles.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));
        $editingteacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $managerrole = $DB->get_record('role', array('shortname' => 'manager'));

        // Enrol user 1 as a student in course 1 and course 4.
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course4->id, $studentrole->id, 'manual');

        // Enrol user 1 as a techer in course 2.
        $this->getDataGenerator()->enrol_user($user1->id, $course2->id, $teacherrole->id, 'manual');

        // Enrol user 1 as an editingteacher in course 5 and 6.
        $this->getDataGenerator()->enrol_user($user1->id, $course5->id, $editingteacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course6->id, $editingteacherrole->id, 'manual');

        // Recovering the context of the category 1 and 3.
        $contextcat1 = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $category1->id));
        $contextcat3 = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $category3->id));

        // Enroling user2 in category 1 and category 3 as manager.
        $record1 = new stdClass();
        $record1->roleid = $managerrole->id;
        $record1->contextid = $contextcat1->id;
        $record1->userid = $user2->id;
        $lastinsertid = $DB->insert_record('role_assignments', $record1);
        $record2 = new stdClass();
        $record2->roleid = $managerrole->id;
        $record2->contextid = $contextcat3->id;
        $record2->userid = $user2->id;
        $lastinsertid = $DB->insert_record('role_assignments', $record2);

        // Enrol user 2 as teacher in course 4.
        $this->getDataGenerator()->enrol_user($user2->id, $course4->id, $teacherrole->id, 'manual');

        // Test user1 with role student (Expected results array with cats 1 and 2).
        $result = get_name_categories_by_role($user1->id, 'student');
        $expectedresult = array($category1->name => $category1->id, $category2->name => $category2->id);
        $this->assertEquals($expectedresult, $result);

        // Test user1 with role teacher (Expected results array with cat 1).
        $result = get_name_categories_by_role($user1->id, 'teacher');
        $expectedresult = array($category1->name => $category1->id);
        $this->assertEquals($expectedresult, $result);

        // Test user1 with role editingteacher (Expected results array with cats 2 and 3).
        $result = get_name_categories_by_role($user1->id, 'editingteacher');
        $expectedresult = array($category2->name => $category2->id, $category3->name => $category3->id);
        $this->assertEquals($expectedresult, $result);

        // Test user1 with role manager (Expected results empty array).
        $result = get_name_categories_by_role($user1->id, 'manager');
        $expectedresult = array();
        $this->assertEquals($expectedresult, $result);

        // Test user2 with role student (Expected results empty array).
        $result = get_name_categories_by_role($user2->id, 'student');
        $expectedresult = array();
        $this->assertEquals($expectedresult, $result);

        // Test user2 with role teacher (Expected results array with cat 2).
        $result = get_name_categories_by_role($user2->id, 'teacher');
        $expectedresult = array($category2->name => $category2->id);
        $this->assertEquals($expectedresult, $result);

        // Test user2 with role manager (Expected results array with cat 1 and cat 3).
        $result = get_name_categories_by_role($user2->id, 'manager');
        $expectedresult = array($category1->name => $category1->id, $category3->name => $category3->id);
        $this->assertEquals($expectedresult, $result);
    }

    /**
     * Tests for phpunit.
     */
    public function test_get_course_students () {
        global $DB;
        $this->resetAfterTest(true);

        // Creating a few users.
        $user1 = $this->getDataGenerator()->create_user(array('username' => 'user1'));
        $user2 = $this->getDataGenerator()->create_user(array('username' => 'user2'));
        $user3 = $this->getDataGenerator()->create_user(array('username' => 'user3'));
        $user4 = $this->getDataGenerator()->create_user(array('username' => 'user4'));
        $user5 = $this->getDataGenerator()->create_user(array('username' => 'user5'));

        // Creating a few courses.
        $course1 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Normal course 1'));
        $course2 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Normal course 2'));
        $course3 = $this->getDataGenerator()->create_course(
                array('shortname' => 'MI.Normal course 1'));
        $course4 = $this->getDataGenerator()->create_course(
                array('shortname' => 'MI.Normal course 2'));

        // Getting the id of the roles.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));

        // Course 1 enrols: user1 as teacher user2 to user5 as students.
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user3->id, $course1->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user4->id, $course1->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user5->id, $course1->id, $studentrole->id, 'manual');

        // Course 2 enrols: user1 to user3 as students.
        $this->getDataGenerator()->enrol_user($user1->id, $course2->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user2->id, $course2->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user3->id, $course2->id, $studentrole->id, 'manual');

        // Course 3 enrols: user4 and user5 as teachers and user5 also as a student.
        $this->getDataGenerator()->enrol_user($user4->id, $course3->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user5->id, $course3->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user5->id, $course3->id, $studentrole->id, 'manual');

        // Test the function with course 1 (Expected results array with 4 users: user2, user3, user4 and user5).
        $result = get_course_students($course1->id);
        $expectedresult = array($user2->id => $user2, $user3->id => $user3, $user4->id => $user4, $user5->id => $user5);
        $this->assertEquals($expectedresult, $result);
        $this->assertCount(4, $result);

        // Test the function with course 2 (Expected results array with 3 users: user1, user2 and user3).
        $result = get_course_students($course2->id);
        $expectedresult = array($user1->id => $user1, $user2->id => $user2, $user3->id => $user3);
        $this->assertEquals($expectedresult, $result);
        $this->assertCount(3, $result);

        // Test the function with course 3 (Expected results array with 1 user: user5).
        $result = get_course_students($course3->id);
        $expectedresult = array($user5->id => $user5);
        $this->assertEquals($expectedresult, $result);
        $this->assertCount(1, $result);

        // Test the function with course 4 (Expected results empty array).
        $result = get_course_students($course4->id);
        $expectedresult = array();
        $this->assertEquals($expectedresult, $result);
        $this->assertCount(0, $result);
    }

    /**
     * Tests for phpunit.
     */
    public function test_get_user_categories () {
        global $DB;
        $this->resetAfterTest(true);

        // Creating a few users.
        $user1 = $this->getDataGenerator()->create_user(array('username' => 'user1'));
        $user2 = $this->getDataGenerator()->create_user(array('username' => 'user2'));
        $user3 = $this->getDataGenerator()->create_user(array('username' => 'user3'));
        $user4 = $this->getDataGenerator()->create_user(array('username' => 'user4'));

        // Creating several categories for future use.
        $category1 = $this->getDataGenerator()->create_category(array('name' => 'Category 1'));
        $category2 = $this->getDataGenerator()->create_category(array('name' => 'Category 2'));
        $category3 = $this->getDataGenerator()->create_category(array('name' => 'Category 3'));

        // Creating courses related to the categories above.
        $course1 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 1', 'category' => $category1->id));
        $course2 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 2', 'category' => $category1->id));
        $course3 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 3', 'category' => $category2->id));
        $course4 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 4', 'category' => $category2->id));
        $course5 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 5', 'category' => $category3->id));

        // Getting the id of the roles.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));
        $managerrole = $DB->get_record('role', array('shortname' => 'manager'));

        // Recovering the context of the category 1 and 3.
        $contextcat1 = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $category1->id));
        $contextcat3 = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $category3->id));

        // Enrol user 1 as a student in course 1, course 3 and as a teacher in course 5.
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course3->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course5->id, $teacherrole->id, 'manual');

        // Enrol user 2 as a student in course 1, and as a teacher in course 2 and course 4.
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user2->id, $course2->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user2->id, $course4->id, $teacherrole->id, 'manual');

        // Enroling user 4 in category 1 and category 3 as manager.
        $record1 = new stdClass();
        $record1->roleid = $managerrole->id;
        $record1->contextid = $contextcat1->id;
        $record1->userid = $user4->id;
        $lastinsertid = $DB->insert_record('role_assignments', $record1);
        $record2 = new stdClass();
        $record2->roleid = $managerrole->id;
        $record2->contextid = $contextcat3->id;
        $record2->userid = $user4->id;
        $lastinsertid = $DB->insert_record('role_assignments', $record2);

        // Test user1 (Expected results array with cats 1, 2 and 3).
        $result = get_user_categories($user1->id);
        $expectedresult = array($category1->name => $category1->id, $category2->name => $category2->id,
            $category3->name => $category3->id);
        $this->assertEquals($expectedresult, $result);

        // Test user2 (Expected results array with cats 1 and 2).
        $result = get_user_categories($user2->id);
        $expectedresult = array($category1->name => $category1->id, $category2->name => $category2->id);
        $this->assertEquals($expectedresult, $result);

        // Test user3 (Expected results empty array).
        $result = get_user_categories($user3->id);
        $expectedresult = array();
        $this->assertEquals($expectedresult, $result);

        // Test user4 (Expected results array with cats 1 and 3).
        $result = get_user_categories($user4->id);
        $expectedresult = array($category1->name => $category1->id, $category3->name => $category3->id);
        $this->assertEquals($expectedresult, $result);
    }

    /**
     * Tests for phpunit.
     */
    public function test_get_shortname_courses_by_category () {
        global $DB;
        $this->resetAfterTest(true);

        // Creating a few users.
        $user1 = $this->getDataGenerator()->create_user(array('username' => 'user1'));
        $user2 = $this->getDataGenerator()->create_user(array('username' => 'user2'));

        // Creating several categories for future use.
        $category1 = $this->getDataGenerator()->create_category(array('name' => 'Category 1'));
        $category2 = $this->getDataGenerator()->create_category(array('name' => 'Category 2'));
        $category3 = $this->getDataGenerator()->create_category(array('name' => 'Category 3'));

        // Recovering the context of the category 1.
        $contextcat1 = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $category1->id));

        // Creating courses related to the categories above.
        $course1 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 1', 'category' => $category1->id));
        $course2 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 2', 'category' => $category1->id));
        $course3 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 3', 'category' => $category2->id));
        $course4 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 4', 'category' => $category2->id));
        $course5 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 5', 'category' => $category3->id));
        $course6 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 6', 'category' => $category3->id));

        // Getting the id of the roles.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));
        $managerrole = $DB->get_record('role', array('shortname' => 'manager'));

        // Enrol user 1 as a student in course 1 and as a teacher in course 2, course 3 and course 4.
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course2->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course3->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course4->id, $teacherrole->id, 'manual');

        // Enroling user2 in category 1 as manager.
        $record1 = new stdClass();
        $record1->roleid = $managerrole->id;
        $record1->contextid = $contextcat1->id;
        $record1->userid = $user2->id;
        $lastinsertid = $DB->insert_record('role_assignments', $record1);

        // Test the function with user 1, category 1 and role student (Expected results: array with course 1).
        $result = get_shortname_courses_by_category($user1->id, $studentrole->shortname, $category1->id);
        $c1 = new stdclass();
        $c1->shortname = $course1->shortname;
        $c1->id = $course1->id;
        $expectedresult = array($c1->shortname => $c1);
        $this->assertEquals($expectedresult, $result);
        $this->assertCount(1, $result);

        // Test the function with user 1, category 1 and role teacher (Expected results: array with course 2).
        $result = get_shortname_courses_by_category($user1->id, $teacherrole->shortname, $category1->id);
        $c2 = new stdclass();
        $c2->shortname = $course2->shortname;
        $c2->id = $course2->id;
        $expectedresult = array($c2->shortname => $c2);
        $this->assertEquals($expectedresult, $result);
        $this->assertCount(1, $result);

        // Test the function with user 1, category 2 and role teacher (Expected results: array with course 3 and course 4).
        $result = get_shortname_courses_by_category($user1->id, $teacherrole->shortname, $category2->id);
        $c3 = new stdclass();
        $c3->shortname = $course3->shortname;
        $c3->id = $course3->id;
        $c4 = new stdclass();
        $c4->shortname = $course4->shortname;
        $c4->id = $course4->id;
        $expectedresult = array($c3->shortname => $c3, $c4->shortname => $c4);
        $this->assertEquals($expectedresult, $result);
        $this->assertCount(2, $result);

        // Test the function with user 2, category 1 and role manager (Expected results: array with course 1 and course 2).
        $result = get_shortname_courses_by_category($user2->id, $managerrole->shortname, $category1->id);
        $c1 = new stdclass();
        $c1->shortname = $course1->shortname;
        $c1->id = $course1->id;
        $c2 = new stdclass();
        $c2->shortname = $course2->shortname;
        $c2->id = $course2->id;
        $expectedresult = array($c1->shortname => $c1, $c2->shortname => $c2);
        $this->assertEquals($expectedresult, $result);
        $this->assertCount(2, $result);

        // Test the function with user 2, category 1 and role student (Expected results: array with course 1 and course 2).
        $result = get_shortname_courses_by_category($user2->id, $studentrole->shortname, $category1->id);
        $c1 = new stdclass();
        $c1->shortname = $course1->shortname;
        $c1->id = $course1->id;
        $c2 = new stdclass();
        $c2->shortname = $course2->shortname;
        $c2->id = $course2->id;
        $expectedresult = array($c1->shortname => $c1, $c2->shortname => $c2);
        $this->assertEquals($expectedresult, $result);
        $this->assertCount(2, $result);

        // Test the function with user 2, category 2 and role student (Expected results: empty array).
        $result = get_shortname_courses_by_category($user2->id, $studentrole->shortname, $category2->id);
        $expectedresult = array();
        $this->assertEquals($expectedresult, $result);
        $this->assertCount(0, $result);
    }

    /**
     * Tests for phpunit.
     */
    public function test_check_role_manager () {
        global $DB;
        $this->resetAfterTest(true);

        // Creating a few users.
        $user1 = $this->getDataGenerator()->create_user(array('username' => 'user1'));
        $user2 = $this->getDataGenerator()->create_user(array('username' => 'user2'));

        // Creating several categories for future use.
        $category1 = $this->getDataGenerator()->create_category(array('name' => 'Category 1'));
        $category2 = $this->getDataGenerator()->create_category(array('name' => 'Category 2'));
        $category3 = $this->getDataGenerator()->create_category(array('name' => 'Category 3'));

        // Recovering the manager role data.
        $managerrole = $DB->get_record('role', array('shortname' => 'manager'));

        // Recovering the context of the categories.
        $contextcat1 = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $category1->id));
        $contextcat2 = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $category2->id));
        $contextcat3 = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $category3->id));

        // Enroling user1 in category 1 and category 2 as manager.
        $record1 = new stdClass();
        $record1->roleid = $managerrole->id;
        $record1->contextid = $contextcat1->id;
        $record1->userid = $user1->id;
        $lastinsertid = $DB->insert_record('role_assignments', $record1);
        $record2 = new stdClass();
        $record2->roleid = $managerrole->id;
        $record2->contextid = $contextcat2->id;
        $record2->userid = $user1->id;
        $lastinsertid = $DB->insert_record('role_assignments', $record2);

        // Test the function with user1 and categories 1 and 2 (Expected results: both true).
        $result = check_role_manager($user1->id, $category1->id);
        $this->assertTrue($result);
        $result = check_role_manager($user1->id, $category2->id);
        $this->assertTrue($result);

        // Test the function with user1 in category 3 and user2 in category 1 (Expected results: both false).
        $result = check_role_manager($user1->id, $category3->id);
        $this->assertFalse($result);
        $result = check_role_manager($user2->id, $category1->id);
        $this->assertFalse($result);
    }

    /**
     * Tests for phpunit.
     */
    public function test_get_role_manager () {
        global $DB;
        $this->resetAfterTest(true);

        // Creating a few users.
        $user1 = $this->getDataGenerator()->create_user(array('username' => 'user1'));
        $user2 = $this->getDataGenerator()->create_user(array('username' => 'user2'));

        // Creating several categories for future use.
        $category1 = $this->getDataGenerator()->create_category(array('name' => 'Category 1'));
        $category2 = $this->getDataGenerator()->create_category(array('name' => 'Category 2'));
        $category3 = $this->getDataGenerator()->create_category(array('name' => 'Category 3'));

        // Recovering the manager role data.
        $managerrole = $DB->get_record('role', array('shortname' => 'manager'));

        // Recovering the context of the categories.
        $contextcat1 = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $category1->id));
        $contextcat2 = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $category2->id));
        $contextcat3 = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $category3->id));

        // Enroling user1 in category 1 and category 2.
        $record1 = new stdClass();
        $record1->roleid = $managerrole->id;
        $record1->contextid = $contextcat1->id;
        $record1->userid = $user1->id;
        $lastinsertid = $DB->insert_record('role_assignments', $record1);
        $record2 = new stdClass();
        $record2->roleid = $managerrole->id;
        $record2->contextid = $contextcat2->id;
        $record2->userid = $user1->id;
        $lastinsertid = $DB->insert_record('role_assignments', $record2);

        // Test the function with category 1 (Expected results: user1).
        $result = get_role_manager($category1->id);
        $expectedresult = $user1->id;
        $this->assertEquals($expectedresult, $result->id);

        // Test the function with category 2 (Expected results: user1).
        $result = get_role_manager($category2->id);
        $expectedresult = $user1->id;
        $this->assertEquals($expectedresult, $result->id);

        // Test the function with category 3 (Expected results: empty ).
        $result = get_role_manager($category3->id);
        $expectedresult = false;
        $this->assertEquals($expectedresult, $result);
    }

    /**
     * Tests for phpunit.
     */
    public function test_reset_attemps_from_course () {
        global $DB;
        $this->resetAfterTest(true);

        // Creating a few users.
        $user1 = $this->getDataGenerator()->create_user(array('username' => 'user1'));
        $user2 = $this->getDataGenerator()->create_user(array('username' => 'user2'));

        // Creating a few courses.
        $course1 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Normal course 1'));

        // Getting the id of the role student.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

        // Enrol the user1 in course 1 as a student.
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $studentrole->id, 'manual');

        // Creating a quiz and associate it to the courses.
        $quizgen = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz1 = $quizgen->create_instance(array('course' => $course1->id, 'sumgrades' => 2));

        // Creating a question and attach it to the quiz.
        $questgen = $this->getDataGenerator()->get_plugin_generator('core_question');
        $quizcat = $questgen->create_question_category();
        $question = $questgen->create_question('numerical', null, ['category' => $quizcat->id]);
        quiz_add_quiz_question($question->id, $quiz1);

        // Creating an instance of quiz 1 for user 1.
        $quizobj1a = quiz::create($quiz1->id, $user1->id);

        // Set attempts.
        $quba1a = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj1a->get_context());
        $quba1a->set_preferred_behaviour($quizobj1a->get_quiz()->preferredbehaviour);

        $timenow = time();

        // User 1 passes quiz 1.
        $attempt = quiz_create_attempt($quizobj1a, 1, false, $timenow, false, $user1->id);
        quiz_start_new_attempt($quizobj1a, $quba1a, $attempt, 1, $timenow);
        quiz_attempt_save_started($quizobj1a, $quba1a, $attempt);
        $attemptobj = quiz_attempt::create($attempt->id);
        $attemptobj->process_submitted_actions($timenow, false, [1 => ['answer' => '3.14']]);
        $attemptobj->process_finish($timenow, false);

        // Check for user 1 and quiz 1.
        $attempts = quiz_get_user_attempts($quiz1->id, $user1->id, 'all');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(quiz_attempt::FINISHED, $attempt->state);
        $this->assertEquals($user1->id, $attempt->userid);
        $this->assertEquals($quiz1->id, $attempt->quiz);

        $attempts = quiz_get_user_attempts($quiz1->id, $user1->id, 'finished');
        $this->assertCount(1, $attempts);
        $attempt = array_shift($attempts);
        $this->assertEquals(quiz_attempt::FINISHED, $attempt->state);
        $this->assertEquals($user1->id, $attempt->userid);
        $this->assertEquals($quiz1->id, $attempt->quiz);

        $attempts = quiz_get_user_attempts($quiz1->id, $user1->id, 'unfinished');
        $this->assertCount(0, $attempts);

        // Test the function with user1 and course 1 (Expected result: 0 attempts).
        $result = reset_attemps_from_course($user1->id, $course1->id);
        $this->assertTrue($result);
        $attempts = quiz_get_user_attempts($quiz1->id, $user1->id, 'all');
        $this->assertCount(0, $attempts);

        // Test the function with user2 and course 1 (Expected result: 0 attempts).
        $result = reset_attemps_from_course($user2->id, $course1->id);
        $this->assertTrue($result);
        $attempts = quiz_get_user_attempts($quiz1->id, $user2->id, 'all');
        $this->assertCount(0, $attempts);
    }

    /**
     * Tests for phpunit.
     */
    public function test_save_matriculation_dates () {
        global $DB;
        $this->resetAfterTest(true);

        // Creating a few categories.
        $category1 = $this->getDataGenerator()->create_category(
                array('name' => 'Category with normal and intensive courses'));
        $category2 = $this->getDataGenerator()->create_category(
                array('name' => 'Category with only normal courses'));

        // Creating several courses and assign each to one of the categories above.
        $course1 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Normal course 1', 'category' => $category1->id));
        $course2 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Normal course 2', 'category' => $category1->id));
        $course3 = $this->getDataGenerator()->create_course(
                array('shortname' => 'MI.Normal course 1', 'category' => $category2->id));
        $course4 = $this->getDataGenerator()->create_course(
                array('shortname' => 'MI.Normal course 2', 'category' => $category2->id));
        $course5 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Normal course 3', 'category' => $category2->id));

        // Generating and inserting the initial records in the db.
        $today = time();
        $dayinseconds = 86400;
        $record1 = new stdClass();
        $record1->courseid = $course1->id;
        $record1->fecha1 = $today;
        $record1->fecha2 = $today + $dayinseconds;
        $record1->fecha3 = $today + $dayinseconds * 2;
        $record1->fecha4 = $today + $dayinseconds * 3;

        $record2 = new stdClass();
        $record2->courseid = $course2->id;
        $record2->fecha1 = $today + $dayinseconds * 7;
        $record2->fecha2 = $today + $dayinseconds * 8;
        $record2->fecha3 = $today + $dayinseconds * 9;
        $record2->fecha4 = $today + $dayinseconds * 10;

        $lastinsertid = $DB->insert_record('local_eudecustom_call_date', $record1);
        $lastinsertid = $DB->insert_record('local_eudecustom_call_date', $record2);

        // Creating the entry parameters to test the function.
        $newrecord1 = new stdClass();
        $newrecord1->courseid = $course1->id;
        $newrecord1->fecha1 = $today + $dayinseconds * 30;
        $newrecord1->fecha2 = $today + $dayinseconds * 31;
        $newrecord1->fecha3 = $today + $dayinseconds * 32;
        $newrecord1->fecha4 = $today + $dayinseconds * 33;
        $newrecord2 = new stdClass();
        $newrecord2->courseid = $course2->id;
        $newrecord2->fecha1 = $today + $dayinseconds * 60;
        $newrecord2->fecha2 = $today + $dayinseconds * 61;
        $newrecord2->fecha3 = $today + $dayinseconds * 62;
        $newrecord2->fecha4 = $today + $dayinseconds * 63;
        $newrecord3 = new stdClass();
        $newrecord3->courseid = $course3->id;
        $newrecord3->fecha1 = $today;
        $newrecord3->fecha2 = $today;
        $newrecord3->fecha3 = $today;
        $newrecord3->fecha4 = $today;
        $newrecord4 = new stdClass();
        $newrecord4->courseid = $course4->id;
        $newrecord4->fecha1 = $today;
        $newrecord4->fecha2 = $today;
        $newrecord4->fecha3 = $today;
        $newrecord4->fecha4 = $today;
        $newrecord5 = new stdClass();
        $newrecord5->courseid = $course5->id;
        $newrecord5->fecha1 = $today;
        $newrecord5->fecha2 = $today;
        $newrecord5->fecha3 = $today;
        $newrecord5->fecha4 = $today;

        $updatedata = array($newrecord1, $newrecord2);
        $newdata = array($newrecord3, $newrecord4);
        $mixeddata = array($newrecord3, $newrecord4, $newrecord5);
        $emptydata = array();

        // Test the function with prerecorded data in the db so all the changes are updates (Expected result: true).
        $result = save_matriculation_dates($updatedata);
        $this->assertTrue($result);
        $this->assertTrue($DB->record_exists('local_eudecustom_call_date',
                        array('courseid' => $course1->id, 'fecha1' => $today + $dayinseconds * 30,
                    'fecha2' => $today + $dayinseconds * 31, 'fecha3' => $today + $dayinseconds * 32,
                    'fecha4' => $today + $dayinseconds * 33)));
        $this->assertTrue($DB->record_exists('local_eudecustom_call_date',
                        array('courseid' => $course2->id, 'fecha1' => $today + $dayinseconds * 60,
                    'fecha2' => $today + $dayinseconds * 61, 'fecha3' => $today + $dayinseconds * 62,
                    'fecha4' => $today + $dayinseconds * 63)));

        // Test the function with new data for all entries (Expected result: true).
        $result = save_matriculation_dates($newdata);
        $this->assertTrue($result);
        $this->assertTrue($DB->record_exists('local_eudecustom_call_date',
                        array('courseid' => $course3->id, 'fecha1' => $today,
                    'fecha2' => $today, 'fecha3' => $today, 'fecha4' => $today)));
        $this->assertTrue($DB->record_exists('local_eudecustom_call_date',
                        array('courseid' => $course4->id, 'fecha1' => $today,
                    'fecha2' => $today, 'fecha3' => $today, 'fecha4' => $today)));

        // Test the function with 2 updates and 1 insert (Expected result: true).
        $result = save_matriculation_dates($mixeddata);
        $this->assertTrue($result);
        $this->assertTrue($DB->record_exists('local_eudecustom_call_date',
                        array('courseid' => $course3->id, 'fecha1' => $today,
                    'fecha2' => $today, 'fecha3' => $today, 'fecha4' => $today)));
        $this->assertTrue($DB->record_exists('local_eudecustom_call_date',
                        array('courseid' => $course4->id, 'fecha1' => $today,
                    'fecha2' => $today, 'fecha3' => $today, 'fecha4' => $today)));
        $this->assertTrue($DB->record_exists('local_eudecustom_call_date',
                        array('courseid' => $course5->id, 'fecha1' => $today,
                    'fecha2' => $today, 'fecha3' => $today, 'fecha4' => $today)));

        // Test the function with empty data (Expected result: false).
        $result = save_matriculation_dates($emptydata);
        $this->assertFalse($result);
    }

    /**
     * Tests for phpunit.
     */
    public function test_enrol_intensive_user () {
        global $DB;
        $this->resetAfterTest(true);

        // Creating a few users.
        $user1 = $this->getDataGenerator()->create_user(array('username' => 'user1', 'email' => 'user1@php.com'));
        $user2 = $this->getDataGenerator()->create_user(array('username' => 'user2', 'email' => 'user2@php.com'));

        // Creating a category.
        $category1 = $this->getDataGenerator()->create_category(
                array('name' => 'Category with normal and intensive courses'));

        // Creating a few courses.
        $course1 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 1', 'category' => $category1->id));
        $course2 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Course 2', 'category' => $category1->id));

        // Getting the id of the role student.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

        // Getting the manual enrolment for course 1.
        $maninstance1 = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'manual'), '*', MUST_EXIST);

        // Setting some initial parameters.
        $timestart = time();
        $timeend = time() + 86400;
        $convoc = 2;
        $contextcourse1 = context_course::instance($course1->id);

        // Test the function to enrol user 1 in course 1.
        enrol_intensive_user('manual', $course1->id, $user1->id, $timestart, $timeend, $convoc);
        // Check if the enrolment is created.
        $this->assertTrue($DB->record_exists('user_enrolments', array('userid' => $user1->id, 'enrolid' => $maninstance1->id)));
        // Check new entry in table local_eudecustom_mat_int.
        $this->assertTrue($DB->record_exists('local_eudecustom_mat_int',
                        array('user_email' => $user1->email, 'course_shortname' => $course1->shortname,
                              'matriculation_date' => $timestart, 'conv_number' => $convoc)));
        // Check is the user is enroled as student.
        $this->assertTrue($DB->record_exists('role_assignments',
                        array('userid' => $user1->id, 'contextid' => $contextcourse1->id, 'roleid' => $studentrole->id)));
        // Check the number of enrolments in table local_eudecustom_user.
        $this->assertTrue($DB->record_exists('local_eudecustom_user',
                        array('user_email' => $user1->email, 'course_category' => $course1->category)));
        $data = $DB->get_record('local_eudecustom_user',
                array('user_email' => $user1->email, 'course_category' => $course1->category));
        $this->assertEquals(1, $data->num_intensive);

        // Test the function to enrol user 1 again in course 1.
        $timestart2 = $timestart + 200000;
        $timeend2 = $timeend + 200000;
        enrol_intensive_user('manual', $course1->id, $user1->id, $timestart2, $timeend2, $convoc);
        // Check if the enrolment is created.
        $this->assertTrue($DB->record_exists('user_enrolments', array('userid' => $user1->id, 'enrolid' => $maninstance1->id)));
        $data2 = $DB->get_record('user_enrolments', array('userid' => $user1->id, 'enrolid' => $maninstance1->id));
        $this->assertEquals($timestart2, $data2->timestart);
        // Check new entry in table local_eudecustom_mat_int.
        $this->assertTrue($DB->record_exists('local_eudecustom_mat_int',
                        array('user_email' => $user1->email, 'course_shortname' => $course1->shortname,
                              'matriculation_date' => $timestart, 'conv_number' => $convoc)));
        // Check is the user is enroled as student.
        $this->assertTrue($DB->record_exists('role_assignments',
                        array('userid' => $user1->id, 'contextid' => $contextcourse1->id, 'roleid' => $studentrole->id)));
        // Check the number of enrolments in table local_eudecustom_user.
        $this->assertTrue($DB->record_exists('local_eudecustom_user',
                        array('user_email' => $user1->email, 'course_category' => $course1->category)));
        $data2 = $DB->get_record('local_eudecustom_user',
                array('user_email' => $user1->email, 'course_category' => $course1->category));
        $this->assertEquals(2, $data2->num_intensive);
    }

    /**
     * Tests for phpunit.
     */
    public function test_add_tpv_hidden_inputs () {
        global $CFG;
        global $DB;
        global $USER;

        $this->resetAfterTest(true);

        // Creating a new user.
        $user1 = $this->getDataGenerator()->create_user(array('username' => 'user1'));

        // Logging with user 1.
        $this->setUser($user1);

        // Setting the initial data.
        $initialdata = '';
        // We have to initialize this parameter like this because phpunit didnt map all the settings of the plugins.
        $CFG->local_eudecustom_intensivemoduleprice = 60;
        $expectedresult = html_writer::empty_tag('input',
                        array(
                    'type' => 'hidden',
                    'id' => 'user',
                    'name' => 'user',
                    'class' => 'form-control',
                    'value' => $USER->id));
        $expectedresult .= html_writer::empty_tag('input',
                        array(
                    'type' => 'hidden',
                    'id' => 'course',
                    'name' => 'course',
                    'class' => 'form-control'));
        $expectedresult .= html_writer::empty_tag('input',
                        array('type' => 'hidden',
                    'id' => 'amount',
                    'name' => 'amount',
                    'class' => 'form-control',
                    'value' => '60'));
        $expectedresult .= html_writer::empty_tag('input',
                        array('type' => 'hidden',
                    'id' => 'sesskey',
                    'name' => 'sesskey',
                    'class' => 'form-control',
                    'value' => sesskey()));
        $expectedresult .= html_writer::end_div();
        $expectedresult .= html_writer::end_div();
        $expectedresult .= html_writer::empty_tag('input',
                        array(
                    'type' => 'submit',
                    'name' => 'abrirFechas',
                    'class' => 'btn btn-lg btn-primary btn-block abrirFechas',
                    'value' => get_string('continue', 'local_eudecustom')));

        // Testing the function.
        $result = add_tpv_hidden_inputs($initialdata);
        $this->assertEquals($expectedresult, $result);
    }

    /**
     * Tests for phpunit.
     */
    public function test_get_user_all_courses () {

        $this->resetAfterTest();

        $manualplugin = self::enable_enrol_plugin();
        $this->assertNotEmpty($manualplugin);
        $studentrole = self::get_student_role();

        // Create user.
        $user1 = $this->getDataGenerator()->create_user(array('firstname' => "USUARIO 1"));
        $this->assertNotEmpty($user1);

        // Create courses.
        $course1 = $this->getDataGenerator()->create_course(array('shortname' => "CURSO"));
        $this->assertNotEmpty($course1);
        $course2 = $this->getDataGenerator()->create_course(array('shortname' => "MI.CURSO"));
        $this->assertNotEmpty($course2);
        $course3 = $this->getDataGenerator()->create_course(array('shortname' => "CURSONORMAL"));
        $this->assertNotEmpty($course3);
        $course4 = $this->getDataGenerator()->create_course(array('shortname' => "CURSO2"));
        $this->assertNotEmpty($course4);
        $course5 = $this->getDataGenerator()->create_course(array('shortname' => "MI.CURSO2"));
        $this->assertNotEmpty($course5);

        // Enrol user on courses.
        $manualinstance = self::create_manual_instance($course1->id);
        $manualplugin->enrol_user($manualinstance, $user1->id, $studentrole->id, 1493203999, 1494303999);
        $manualinstance2 = self::create_manual_instance($course2->id);
        $manualplugin->enrol_user($manualinstance2, $user1->id, $studentrole->id, 1493103999, 1494302999);
        $manualinstance3 = self::create_manual_instance($course3->id);
        $manualplugin->enrol_user($manualinstance3, $user1->id, $studentrole->id, 1494103999, 1495102999);
        $manualinstance4 = self::create_manual_instance($course4->id);
        $manualplugin->enrol_user($manualinstance4, $user1->id, $studentrole->id, 1493403999, 1494312999);
        $manualinstance5 = self::create_manual_instance($course5->id);
        $manualplugin->enrol_user($manualinstance5, $user1->id, $studentrole->id, 1493153999, 1494402999);

        // Testing the function.
        $data = get_user_all_courses($user1->id);
        $this->assertNotEmpty($data);

        $this->assertEquals($data[$course5->id]->shortname, "MI.CURSO2");
        $this->assertEquals($data[$course4->id]->shortname, "CURSO2");
        $this->assertEquals($data[$course3->id]->shortname, "CURSONORMAL");
        $this->assertEquals($data[$course2->id]->shortname, "MI.CURSO");
        $this->assertEquals($data[$course1->id]->shortname, "CURSO");
    }

    /**
     * Tests for phpunit.
     */
    public function test_update_intensive_dates () {
        global $DB;
        $this->resetAfterTest();

        $manualplugin = self::enable_enrol_plugin();
        $this->assertNotEmpty($manualplugin);
        $studentrole = self::get_student_role();

        // Create user and courses.
        $user1 = $this->getDataGenerator()->create_user(array('firstname' => "USUARIO 1", 'email' => 'user1@php.com'));
        $this->assertNotEmpty($user1);
        $course1 = $this->getDataGenerator()->create_course(array('shortname' => "CURSO"));
        $this->assertNotEmpty($course1);
        $course2 = $this->getDataGenerator()->create_course(array('shortname' => "MI.CURSO"));
        $this->assertNotEmpty($course2);

        // Add call date data.
        $date = new stdClass();
        $date->id = 10;
        $date->courseid = $course2->id;
        $date->fecha1 = 1495650823;
        $date->fecha2 = 1496150824;
        $date->fecha3 = 1496650825;
        $date->fecha4 = 1497150826;
        $this->assertNotEmpty($date);

        $DB->insert_record('local_eudecustom_call_date', $date, false);

        // Enrol user 1 as a student in course 1 and course 4.
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course2->id, $studentrole->id, 'manual');

        // Initial settings for local_eudecustom_mat_int.
        $matint = new stdClass();
        $matint->user_email = $user1->email;
        $matint->course_shortname = $course2->shortname;
        $matint->matriculation_date = 150000000;
        $DB->insert_record('local_eudecustom_mat_int', $matint, true);

        // Testing the function with the first call date (Expected matriculation date : 1495650823).
        $record = update_intensive_dates(1, $course1->id, $user1->id);
        $this->assertTrue($record);
        $result = $DB->get_record('local_eudecustom_mat_int',
                array('course_shortname' => $course2->shortname, 'user_email' => $user1->email));
        $this->assertEquals($date->fecha1, $result->matriculation_date);

        // Testing the function with the first call date (Expected matriculation date : 1495650823).
        $record = update_intensive_dates(2, $course1->id, $user1->id);
        $this->assertTrue($record);
        $result = $DB->get_record('local_eudecustom_mat_int',
                array('course_shortname' => $course2->shortname, 'user_email' => $user1->email));
        $this->assertEquals($date->fecha2, $result->matriculation_date);

        // Testing the function with the first call date (Expected matriculation date : 1495650823).
        $record = update_intensive_dates(3, $course1->id, $user1->id);
        $this->assertTrue($record);
        $result = $DB->get_record('local_eudecustom_mat_int',
                array('course_shortname' => $course2->shortname, 'user_email' => $user1->email));
        $this->assertEquals($date->fecha3, $result->matriculation_date);

        // Testing the function with the first call date (Expected matriculation date : 1495650823).
        $record = update_intensive_dates(4, $course1->id, $user1->id);
        $this->assertTrue($record);
        $result = $DB->get_record('local_eudecustom_mat_int',
                array('course_shortname' => $course2->shortname, 'user_email' => $user1->email));
        $this->assertEquals($date->fecha4, $result->matriculation_date);
    }

    /**
     * Tests for phpunit.
     */
    public function test_grades () {
        global $DB;
        $this->resetAfterTest();

        $manualplugin = self::enable_enrol_plugin();
        $this->assertNotEmpty($manualplugin);
        $studentrole = self::get_student_role();

        // Create user and courses.
        $user1 = $this->getDataGenerator()->create_user(array('firstname' => "USUARIO 1"));
        $this->assertNotEmpty($user1);
        $course1 = $this->getDataGenerator()->create_course(array('shortname' => "CURSO"));
        $this->assertNotEmpty($course1);
        $course2 = $this->getDataGenerator()->create_course(array('shortname' => "MI.CURSO"));
        $this->assertNotEmpty($course2);
        $course3 = $this->getDataGenerator()->create_course(array('shortname' => "CURSOSINGRADES"));
        $this->assertNotEmpty($course3);

        // Enrol user on courses.
        $manualinstance = self::create_manual_instance($course1->id);
        $manualplugin->enrol_user($manualinstance, $user1->id, $studentrole->id);
        $manualinstance2 = self::create_manual_instance($course2->id);
        $manualplugin->enrol_user($manualinstance2, $user1->id, $studentrole->id);
        $manualinstance3 = self::create_manual_instance($course3->id);
        $manualplugin->enrol_user($manualinstance3, $user1->id, $studentrole->id);

        // Use the function for a course without grades.
        $grade = grades($course1->id, $user1->id);
        $this->assertEmpty($grade);

        // Create grade for course1.
        $grade1 = $this->getDataGenerator()->create_grade_item(array('itemtype' => 'course', 'courseid' => $course1->id));
        $this->assertNotEmpty($grade1);

        $data = new stdClass();
        $data->itemid = $grade1->id;
        $data->finalgrade = 78;
        $data->userid = $user1->id;

        $DB->insert_record('grade_grades', $data, false);

        $gradeprov = grades($course1->id, $user1->id);
        $this->assertNotEmpty($gradeprov);
        $this->assertEquals($gradeprov, 7.8);

        // Create grade for course2.
        $grade2 = $this->getDataGenerator()->create_grade_item(array('itemtype' => 'course', 'courseid' => $course2->id));
        $this->assertNotEmpty($grade2);

        $data2 = new stdClass();
        $data2->itemid = $grade2->id;
        $data2->finalgrade = 82;
        $data2->userid = $user1->id;

        $DB->insert_record('grade_grades', $data2, false);

        $gradefinal = grades($course2->id, $user1->id);
        $this->assertNotEmpty($gradefinal);
        $this->assertEquals($gradefinal, 8.2);

        // Use the function for a course without grades.
        $gradefalse = grades($course3->id, $user1->id);
        $this->assertEmpty($gradefalse);
    }

    /**
     * Tests for phpunit.
     */
    public function test_get_intensivecourse_data () {
        global $DB;
        $this->resetAfterTest();

        $manualplugin = self::enable_enrol_plugin();
        $this->assertNotEmpty($manualplugin);
        $studentrole = self::get_student_role();

        // Create user and courses.
        $user1 = $this->getDataGenerator()->create_user(array('firstname' => "USUARIO 1", 'email' => 'user1@testmail.com'));
        $course1 = $this->getDataGenerator()->create_course(array('shortname' => "CURSO"));
        $course2 = $this->getDataGenerator()->create_course(array('shortname' => "MI.CURSO"));
        $course3 = $this->getDataGenerator()->create_course(array('shortname' => "CURSONORMAL"));
        $course4 = $this->getDataGenerator()->create_course(array('shortname' => "CURSO2"));
        $course5 = $this->getDataGenerator()->create_course(array('shortname' => "MI.CURSO2"));
        $category1 = $this->getDataGenerator()->create_category();
        $course6 = $this->getDataGenerator()->create_course(array('shortname' => "CURSO3"));
        $course7 = $this->getDataGenerator()->create_course(array('shortname' => "MI.CURSO3"));

        // Enrol user on courses.
        $manualinstance = self::create_manual_instance($course1->id);
        $manualplugin->enrol_user($manualinstance, $user1->id, $studentrole->id);
        $manualinstance2 = self::create_manual_instance($course3->id);
        $manualplugin->enrol_user($manualinstance2, $user1->id, $studentrole->id);
        $manualinstance3 = self::create_manual_instance($course4->id);
        $manualplugin->enrol_user($manualinstance3, $user1->id, $studentrole->id);
        $manualinstance4 = self::create_manual_instance($course5->id);
        $manualplugin->enrol_user($manualinstance4, $user1->id, $studentrole->id);
        $manualinstance5 = self::create_manual_instance($course6->id);
        $manualplugin->enrol_user($manualinstance5, $user1->id, $studentrole->id, 1493103999, 1494302999);
        $manualinstance6 = self::create_manual_instance($course7->id);
        $manualplugin->enrol_user($manualinstance6, $user1->id, $studentrole->id, 1493123999, 1494322999);

        // Testing user and courses.
        $this->assertNotEmpty($user1);
        $this->assertNotEmpty($course1);
        $this->assertNotEmpty($course2);
        $this->assertNotEmpty($course3);
        $this->assertNotEmpty($course4);
        $this->assertNotEmpty($course5);
        $this->assertNotEmpty($course6);
        $this->assertNotEmpty($course7);

        // TEST 1: Without grades.
        $data = get_intensivecourse_data($course1, $user1->id);

        $this->assertNotEmpty($data);
        $this->assertEquals("CURSO", $data->name);
        $this->assertEquals("-", $data->actions);
        $this->assertEquals(0, $data->attempts);
        $this->assertEquals("-", $data->provgrades);
        $this->assertEquals("-", $data->finalgrades);

        // TEST 2: With grades on normal module.
        $grade1 = $this->getDataGenerator()->create_grade_item(array('itemtype' => 'course', 'courseid' => $course1->id));
        $this->assertNotEmpty($grade1);

        $grades = new stdClass();
        $grades->itemid = $grade1->id;
        $grades->finalgrade = 78;
        $grades->userid = $user1->id;

        $DB->insert_record('grade_grades', $grades, false);

        $data2 = get_intensivecourse_data($course1, $user1->id);
        $this->assertNotEmpty($data2);
        $this->assertEquals("CURSO", $data->name);
        $this->assertEquals("-", $data->actions);
        $this->assertEquals(7.8, $data2->provgrades);
        $this->assertEquals(7.8, $data2->finalgrades);

        // TEST 3: With intensive module enrollment without grades.
        $data3 = get_intensivecourse_data($course4, $user1->id);

        $this->assertNotEmpty($data3);
        $this->assertEquals("CURSO2", $data3->name);
        $this->assertEquals("-", $data3->actions);
        $this->assertEquals("-", $data3->provgrades);
        $this->assertEquals("-", $data3->finalgrades);

        // TEST 4: With intensive module enrollment with grades only on normal module.
        $grade4 = $this->getDataGenerator()->create_grade_item(array('itemtype' => 'course', 'courseid' => $course4->id));
        $this->assertNotEmpty($grade4);

        $grades = new stdClass();
        $grades->itemid = $grade4->id;
        $grades->finalgrade = 65;
        $grades->userid = $user1->id;

        $DB->insert_record('grade_grades', $grades, false);

        $data4 = get_intensivecourse_data($course4, $user1->id);
        $this->assertNotEmpty($data4);
        $this->assertEquals("CURSO2", $data4->name);
        $this->assertEquals("-", $data4->actions);
        $this->assertEquals(6.5, $data4->provgrades);
        $this->assertEquals(6.5, $data4->finalgrades);

        // TEST 5: With intensive module enrollment with grades on normal module and intensive module.
        $grade5 = $this->getDataGenerator()->create_grade_item(array('itemtype' => 'course', 'courseid' => $course5->id));
        $this->assertNotEmpty($grade5);

        $grades = new stdClass();
        $grades->itemid = $grade5->id;
        $grades->finalgrade = 72;
        $grades->userid = $user1->id;

        $DB->insert_record('grade_grades', $grades, false);

        $data5 = get_intensivecourse_data($course4, $user1->id);
        $this->assertNotEmpty($data5);
        $this->assertEquals("CURSO2", $data5->name);
        $this->assertEquals("-", $data5->actions);
        $this->assertEquals(6.5, $data5->provgrades);
        $this->assertEquals(7.2, $data5->finalgrades);

        // TEST 6: With intensive module enrollment with grades only on intensive module.
        $matint = new stdClass();
        $matint->user_email = $user1->email;
        $matint->course_shortname = $course7->shortname;
        $matint->matriculation_date = 1497302999;
        $DB->insert_record('local_eudecustom_mat_int', $matint, true);

        $grade6 = $this->getDataGenerator()->create_grade_item(array('itemtype' => 'course', 'courseid' => $course7->id));
        $this->assertNotEmpty($grade6);

        $grades = new stdClass();
        $grades->itemid = $grade6->id;
        $grades->finalgrade = 90;
        $grades->userid = $user1->id;

        $DB->insert_record('grade_grades', $grades, false);

        $newdata = new stdClass();
        $newdata->useremail = $user1->email;
        $newdata->course_category = $category1->id;
        $newdata->num_intensive = 1;

        $DB->insert_record('local_eudecustom_user', $newdata, false);

        $data6 = get_intensivecourse_data($course6, $user1->id);
        $this->assertNotEmpty($data6);
        $this->assertEquals("CURSO3", $data6->name);
        $this->assertEquals("13/06/2017", $data6->actions);
        $this->assertEquals(1, $data6->attempts);
        $this->assertEquals("-", $data6->provgrades);
        $this->assertEquals(9.0, $data6->finalgrades);
    }

    /**
     * Tests for phpunit.
     */
    public function test_configureprofiledata () {

        require_once("/../classes/models/local_eudecustom_eudeprofile.class.php");
        global $USER;
        global $DB;
        global $CFG;

        $this->resetAfterTest();

        $manualplugin = self::enable_enrol_plugin();
        $this->assertNotEmpty($manualplugin);
        $studentrole = self::get_student_role();

        $today = time();
        $day = 86400;

        // Create user, a category and courses.
        $user1 = $this->getDataGenerator()->create_user(array('firstname' => "USUARIO 1", 'email' => 'user1@php.com'));
        $category1 = $this->getDataGenerator()->create_category();
        $course1 = $this->getDataGenerator()->create_course(array('shortname' => "CURSO", 'category' => $category1->id));
        $course2 = $this->getDataGenerator()->create_course(array('shortname' => "MI.CURSO", 'category' => $category1->id));
        $course3 = $this->getDataGenerator()->create_course(array('shortname' => "CURSONORMAL", 'category' => $category1->id));
        $course4 = $this->getDataGenerator()->create_course(array('shortname' => "CURSO2", 'category' => $category1->id));
        $course5 = $this->getDataGenerator()->create_course(array('shortname' => "MI.CURSO2", 'category' => $category1->id));
        $course6 = $this->getDataGenerator()->create_course(array('shortname' => "CURSO3", 'category' => $category1->id));
        $course7 = $this->getDataGenerator()->create_course(array('shortname' => "MI.CURSO3", 'category' => $category1->id));
        $course8 = $this->getDataGenerator()->create_course(array('shortname' => "CURSO4", 'category' => $category1->id));
        $course9 = $this->getDataGenerator()->create_course(array('shortname' => "MI.CURSO4", 'category' => $category1->id));
        $course10 = $this->getDataGenerator()->create_course(array('shortname' => "CURSO5", 'category' => $category1->id));
        $course11 = $this->getDataGenerator()->create_course(array('shortname' => "MI.CURSO5", 'category' => $category1->id));

        // Enrol courses.
        $manualinstance = self::create_manual_instance($course1->id);
        $manualplugin->enrol_user($manualinstance, $user1->id, $studentrole->id, $today - (2 * $day), $today + (5 * $day));
        $manualinstance2 = self::create_manual_instance($course3->id);
        $manualplugin->enrol_user($manualinstance2, $user1->id, $studentrole->id, $today - (2 * $day), $today + (5 * $day));
        $manualinstance3 = self::create_manual_instance($course4->id);
        $manualplugin->enrol_user($manualinstance3, $user1->id, $studentrole->id, $today + (100 * $day), $today + (107 * $day));
        $manualinstance4 = self::create_manual_instance($course5->id);
        $manualplugin->enrol_user($manualinstance4, $user1->id, $studentrole->id, $today + (100 * $day), $today + (107 * $day));
        $manualinstance5 = self::create_manual_instance($course6->id);
        $manualplugin->enrol_user($manualinstance5, $user1->id, $studentrole->id, $today - (5 * $day), $today + (2 * $day));
        $manualinstance6 = self::create_manual_instance($course7->id);
        $manualplugin->enrol_user($manualinstance6, $user1->id, $studentrole->id, $today - (5 * $day), $today + (2 * $day));

        $this->assertNotEmpty($user1);
        $this->assertNotEmpty($course1);
        $this->assertNotEmpty($course2);
        $this->assertNotEmpty($course3);
        $this->assertNotEmpty($course4);
        $this->assertNotEmpty($course5);
        $this->assertNotEmpty($course6);
        $this->assertNotEmpty($course7);
        $this->assertNotEmpty($course8);
        $this->assertNotEmpty($course9);
        $this->assertNotEmpty($course10);
        $this->assertNotEmpty($course11);

        $USER->id = $user1->id;

        $CFG->local_eudecustom_intensivemodulechecknumber = 6;
        $CFG->local_eudecustom_totalenrolsinincurse = 3;

        // Add matriculation call dates on all courses.
        $fechas2 = new stdClass();
        $fechas2->courseid = $course2->id;
        $fechas2->fecha1 = $today - (2 * $day);
        $fechas2->fecha2 = $today + (30 * $day);
        $fechas2->fecha3 = $today + (60 * $day);
        $fechas2->fecha4 = $today + (100 * $day);
        $DB->insert_record('local_eudecustom_call_date', $fechas2, false);

        $fechas5 = new stdClass();
        $fechas5->courseid = $course5->id;
        $fechas5->fecha1 = $today + (35 * $day);
        $fechas5->fecha2 = $today + (37 * $day);
        $fechas5->fecha3 = $today + (67 * $day);
        $fechas5->fecha4 = $today + (100 * $day);
        $DB->insert_record('local_eudecustom_call_date', $fechas5, false);

        $fechas7 = new stdClass();
        $fechas7->courseid = $course7->id;
        $fechas7->fecha1 = $today - (5 * $day);
        $fechas7->fecha2 = $today + (44 * $day);
        $fechas7->fecha3 = $today + (74 * $day);
        $fechas7->fecha4 = $today + (114 * $day);
        $DB->insert_record('local_eudecustom_call_date', $fechas7, false);

        $fechas9 = new stdClass();
        $fechas9->courseid = $course9->id;
        $fechas9->fecha1 = $today + (19 * $day);
        $fechas9->fecha2 = $today + (51 * $day);
        $fechas9->fecha3 = $today + (81 * $day);
        $fechas9->fecha4 = $today + (121 * $day);
        $DB->insert_record('local_eudecustom_call_date', $fechas9, false);

        $fechas11 = new stdClass();
        $fechas11->courseid = $course11->id;
        $fechas11->fecha1 = $today - (2 * $day);
        $fechas11->fecha2 = $today + (30 * $day);
        $fechas11->fecha3 = $today + (60 * $day);
        $fechas11->fecha4 = $today + (120 * $day);
        $DB->insert_record('local_eudecustom_call_date', $fechas11, false);

        // TEST 1: Without grades.
        $data = configureprofiledata($user1->id);
        $this->assertNotEmpty($data);
        $this->assertCount(3, $data);
        $this->assertEquals($data[0]->name, "CURSO3");
        $this->assertEquals($data[0]->grades, "-");
        $this->assertEquals($data[0]->gradesint, "-");
        $this->assertEquals($data[0]->action, "insideweek");
        $this->assertEquals($data[0]->actionclass, "abrirFechas");
        $this->assertEquals($data[0]->id, ' mod' . $course6->id);
        $this->assertEquals($data[0]->attempts, 0);
        $this->assertEquals($data[0]->info, "No hay notas disponibles.");
        $this->assertEquals($data[1]->name, "CURSO2");
        $this->assertEquals($data[1]->grades, "-");
        $this->assertEquals($data[1]->gradesint, "-");
        $this->assertEquals($data[1]->action, "outweek");
        $this->assertEquals($data[1]->actionclass, "abrirFechas");
        $this->assertEquals($data[1]->id, ' mod' . $course4->id);
        $this->assertEquals($data[1]->attempts, 0);
        $this->assertEquals($data[2]->name, "CURSO");
        $this->assertEquals($data[2]->grades, "-");
        $this->assertEquals($data[2]->gradesint, "-");
        $this->assertEquals($data[2]->action, "notenroled");
        $this->assertEquals($data[2]->actiontitle, "Quick matriculation");
        $this->assertEquals($data[2]->id, ' mod' . $course1->id);
        $this->assertEquals($data[2]->attempts, 0);

        // TEST 2: With grades on normal module.
        $grade1 = $this->getDataGenerator()->create_grade_item(array('itemtype' => 'course', 'courseid' => $course1->id));
        $this->assertNotEmpty($grade1);

        $grades1 = new stdClass();
        $grades1->itemid = $grade1->id;
        $grades1->finalgrade = 78;
        $grades1->userid = $user1->id;

        $DB->insert_record('grade_grades', $grades1, false);

        $data2 = configureprofiledata($user1->id);
        $this->assertNotEmpty($data2);
        $this->assertEquals($data2[2]->grades, "7.80");
        $this->assertEquals($data2[2]->gradesint, "7.80");
        $this->assertEquals($data2[2]->action, "notenroled");
        $this->assertEquals($data2[2]->actiontitle, "Increase grades");

        // TEST 3: With intensive module enrollment without grades.
        $data3 = configureprofiledata($user1->id);
        $this->assertNotEmpty($data3);

        // TEST 4: With intensive module enrollment with grades only on normal module.
        $grade4 = $this->getDataGenerator()->create_grade_item(array('itemtype' => 'course', 'courseid' => $course4->id));
        $this->assertNotEmpty($grade4);

        $grades4 = new stdClass();
        $grades4->itemid = $grade4->id;
        $grades4->finalgrade = 65;
        $grades4->userid = $user1->id;

        $DB->insert_record('grade_grades', $grades4, false);

        $data4 = configureprofiledata($user1->id);
        $this->assertNotEmpty($data4);
        $this->assertEquals($data4[1]->grades, "6.50");
        $this->assertEquals($data4[1]->gradesint, "6.50");

        // TEST 5: With intensive module enrollment with grades on normal module and intensive module.
        $grade5 = $this->getDataGenerator()->create_grade_item(array('itemtype' => 'course', 'courseid' => $course5->id));
        $this->assertNotEmpty($grade5);

        $grades5 = new stdClass();
        $grades5->itemid = $grade5->id;
        $grades5->finalgrade = 72;
        $grades5->userid = $user1->id;

        $DB->insert_record('grade_grades', $grades5, false);

        $data5 = configureprofiledata($user1->id);
        $this->assertNotEmpty($data5);
        $this->assertEquals($data5[1]->grades, "6.50");
        $this->assertEquals($data5[1]->gradesint, "7.20");

        // TEST 6: With intensive module enrollment with grades only on intensive module.
        $matint = new stdClass();
        $matint->user_email = $user1->email;
        $matint->course_shortname = $course7->shortname;
        $matint->matriculation_date = $today + (12 * $day);
        $DB->insert_record('local_eudecustom_mat_int', $matint, true);

        $grade6 = $this->getDataGenerator()->create_grade_item(array('itemtype' => 'course', 'courseid' => $course7->id));
        $this->assertNotEmpty($grade6);

        $grades6 = new stdClass();
        $grades6->itemid = $grade6->id;
        $grades6->finalgrade = 40;
        $grades6->userid = $user1->id;

        $DB->insert_record('grade_grades', $grades6, false);

        $newdata = new stdClass();
        $newdata->user_email = $user1->email;
        $newdata->course_category = $category1->id;
        $newdata->num_intensive = 1;

        $DB->insert_record('local_eudecustom_user', $newdata, false);
        $intentos = $DB->get_record('local_eudecustom_user',
                array('user_email' => $user1->email, 'course_category' => $category1->id));

        $data6 = configureprofiledata($user1->id);
        $this->assertNotEmpty($data6);
        $this->assertEquals($data6[0]->grades, "-");
        $this->assertEquals($data6[0]->gradesint, "4.00");

        // TEST 7: Enrol on normal module without enrollment on intensive module.
        $manualinstance7 = self::create_manual_instance($course8->id);
        $manualplugin->enrol_user($manualinstance7, $user1->id, $studentrole->id, $today - (10 * $day), $today - (2 * $day));

        $data7 = configureprofiledata($user1->id);
        $this->assertNotEmpty($data7);
        $this->assertEquals($data7[0]->name, "CURSO4");
        $this->assertEquals($data7[0]->grades, "-");
        $this->assertEquals($data7[0]->gradesint, "-");
        $this->assertEquals($data7[0]->action, "notenroled");
        $this->assertEquals($data7[0]->actiontitle, "Quick matriculation");
        $this->assertEquals($data7[0]->attempts, 0);

        // TEST 8: Enrol on intensive module, grade on normal module and 1 attempt.
        $manualinstance8 = self::create_manual_instance($course9->id);
        $manualplugin->enrol_user($manualinstance8, $user1->id, $studentrole->id, $today - (37 * $day), $today - (30 * $day));

        $grade8 = $this->getDataGenerator()->create_grade_item(array('itemtype' => 'course', 'courseid' => $course8->id));
        $this->assertNotEmpty($grade1);

        $grades8 = new stdClass();
        $grades8->itemid = $grade8->id;
        $grades8->finalgrade = 23;
        $grades8->userid = $user1->id;

        $DB->insert_record('grade_grades', $grades8, false);

        $newdata8 = new stdClass();
        $newdata8->id = $intentos->id;
        $newdata8->user_email = $user1->email;
        $newdata8->course_category = $category1->id;
        $newdata8->num_intensive = 2;

        $DB->update_record('local_eudecustom_user', $newdata8, false);

        $data8 = configureprofiledata($user1->id);
        $this->assertNotEmpty($data8);
        $this->assertEquals($data8[0]->name, "CURSO4");
        $this->assertEquals($data8[0]->grades, "2.30");
        $this->assertEquals($data8[0]->gradesint, "2.30");
        $this->assertEquals($data8[0]->action, "notenroled");
        $this->assertEquals($data8[0]->actiontitle, "Retry module");
        $this->assertEquals($data8[0]->actionclass, 'abrirFechas');
        $this->assertEquals($data8[0]->actionid, 'abrirFechas(' . $course8->id . ',1,1)');

        // TEST 9: Add 3 attempts and a low grade on intensive module.

        $grade9 = $this->getDataGenerator()->create_grade_item(array('itemtype' => 'course', 'courseid' => $course9->id));
        $this->assertNotEmpty($grade9);

        $matint1 = new stdClass();
        $matint1->user_email = $user1->email;
        $matint1->course_shortname = $course9->shortname;
        $matint1->matriculation_date = $today - (37 * $day);
        $DB->insert_record('local_eudecustom_mat_int', $matint1, true);

        $matint2 = new stdClass();
        $matint2->user_email = $user1->email;
        $matint2->course_shortname = $course9->shortname;
        $matint2->matriculation_date = $today - (37 * $day);
        $DB->insert_record('local_eudecustom_mat_int', $matint2, true);

        $grades9 = new stdClass();
        $grades9->itemid = $grade9->id;
        $grades9->finalgrade = 31;
        $grades9->userid = $user1->id;

        $DB->insert_record('grade_grades', $grades9, false);

        $adddata = new stdClass();
        $adddata->id = $intentos->id;
        $adddata->user_email = $user1->email;
        $adddata->course_category = $category1->id;
        $adddata->num_intensive = 9;

        $DB->update_record('local_eudecustom_user', $adddata, false);

        $prueba2 = $DB->get_record('local_eudecustom_user',
                array('user_email' => $user1->email, 'course_category' => $category1->id));

        $data9 = configureprofiledata($user1->id);
        $this->assertNotEmpty($data9);
        $this->assertEquals($data9[0]->name, "CURSO4");
        $this->assertEquals($data9[0]->grades, "2.30");
        $this->assertEquals($data9[0]->gradesint, "3.10");
        $this->assertEquals($data9[0]->action, "notenroled");
        $this->assertEquals($data9[0]->actiontitle, "Retry module");
        $this->assertEquals($data9[0]->actionid, 'abrir(' . $course8->id . ',0,1)');

        // TEST 10: Update num_intensive to 3 but add attemps to 3.
        $addnewdata = new stdClass();
        $addnewdata->id = $intentos->id;
        $addnewdata->user_email = $user1->email;
        $addnewdata->course_category = $category1->id;
        $addnewdata->num_intensive = 3;

        $DB->update_record('local_eudecustom_user', $addnewdata, false);

        $prueba3 = $DB->get_record('local_eudecustom_user',
                array('user_email' => $user1->email, 'course_category' => $category1->id));

        $data0 = configureprofiledata($user1->id);
        $this->assertNotEmpty($data0);
        $this->assertEquals($data0[0]->name, "CURSO4");
        $this->assertEquals($data0[0]->grades, "2.30");
        $this->assertEquals($data0[0]->gradesint, "3.10");
        $this->assertEquals($data0[0]->action, "notenroled");
        $this->assertEquals($data0[0]->actiontitle, "Retry module");
        $this->assertEquals($data0[0]->actionid, 'abrirFechas(' . $course8->id . ',1,1)');

        $matint3 = new stdClass();
        $matint3->user_email = $user1->email;
        $matint3->course_shortname = $course9->shortname;
        $matint3->matriculation_date = $today - (37 * $day);
        $DB->insert_record('local_eudecustom_mat_int', $matint3, true);
        $result1111 = $DB->get_records('local_eudecustom_mat_int');
        $data0b = configureprofiledata($user1->id);
        ($data0b);$this->assertNotEmpty($data0b);
        $this->assertEquals($data0b[0]->name, "CURSO4");
        $this->assertEquals($data0b[0]->grades, "2.30");
        $this->assertEquals($data0b[0]->gradesint, "3.10");
        $this->assertEquals($data0b[0]->action, "notenroled");
        $this->assertEquals($data0b[0]->actiontitle, "Retry module");
        $this->assertEquals($data0b[0]->actionid, 'abrir(' . $course8->id . ',0,1)');

        // TEST 11: Grade 10 on the new course.
        $manualinstance0 = self::create_manual_instance($course10->id);
        $manualplugin->enrol_user($manualinstance0, $user1->id, $studentrole->id, $today - (14 * $day), $today - (7 * $day));

        $manualinstanceint = self::create_manual_instance($course11->id);
        $manualplugin->enrol_user($manualinstanceint, $user1->id, $studentrole->id, $today - (14 * $day), $today - (7 * $day));
        // New items and grades.
        $grade0 = $this->getDataGenerator()->create_grade_item(array('itemtype' => 'course', 'courseid' => $course10->id));
        $this->assertNotEmpty($grade0);
        $grades0 = new stdClass();
        $grades0->itemid = $grade0->id;
        $grades0->finalgrade = 98;
        $grades0->userid = $user1->id;

        $DB->insert_record('grade_grades', $grades0, false);

        $grade0b = $this->getDataGenerator()->create_grade_item(array('itemtype' => 'course', 'courseid' => $course11->id));
        $this->assertNotEmpty($grade0b);

        $grades0b = new stdClass();
        $grades0b->itemid = $grade0b->id;
        $grades0b->finalgrade = 100;
        $grades0b->userid = $user1->id;

        $DB->insert_record('grade_grades', $grades0b, false);

        $data11 = configureprofiledata($user1->id);
        $this->assertNotEmpty($data11);
        $this->assertCount(5, $data11);
        $this->assertEquals($data11[0]->grades, "9.80");
        $this->assertEquals($data11[0]->gradesint, "10.00");
        $this->assertEquals($data11[0]->action, "insideweek");
        $this->assertEmpty($data11[0]->actiontitle);
    }

    /**
     * Tests for phpunit.
     */
    public function test_get_user_shortname_courses () {

        global $DB;

        $this->resetAfterTest(true);

        // Creating users.
        $user1 = $this->getDataGenerator()->create_user(
                array('username' => 'unit'));
        $user2 = $this->getDataGenerator()->create_user(
                array('username' => 'unit2'));
        $user3 = $this->getDataGenerator()->create_user(
                array('username' => 'unit3'));

        // Creating categories.
        $category1 = $this->getDataGenerator()->create_category(
                array('name' => 'phpunit category 1'));

        $category2 = $this->getDataGenerator()->create_category(
                array('name' => 'phpunit category 2'));

        $category3 = $this->getDataGenerator()->create_category(
                array('name' => 'phpunit category 3'));

        // Creating courses.
        $course1 = $this->getDataGenerator()->create_course(
                array('shortname' => 'phpunit cat1 course1', 'category' => $category1->id));

        $course2 = $this->getDataGenerator()->create_course(
                array('shortname' => 'phpunit cat1 course2', 'category' => $category1->id));

        $course3 = $this->getDataGenerator()->create_course(
                array('shortname' => 'phpunit cat2 course1', 'category' => $category2->id));

        $course4 = $this->getDataGenerator()->create_course(
                array('shortname' => 'phpunit cat2 course2', 'category' => $category2->id));

        $course5 = $this->getDataGenerator()->create_course(
                array('shortname' => 'phpunit cat3 course1', 'category' => $category3->id));

        $course6 = $this->getDataGenerator()->create_course(
                array('shortname' => 'phpunit cat3 course2', 'category' => $category3->id));

        // Getting the id of the roles.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

        // Enrol user1 in 2 courses of cat 1 and first of cat 2 and 3.
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course2->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course3->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course5->id, $studentrole->id, 'manual');

        // Test get user1's courses from cat 1.
        $result = get_user_shortname_courses($user1->id, $category1->id);
        $this->assertCount(2, $result);

        $c1 = new stdClass(); // First course.
        $c1->id = $course1->id;
        $c1->shortname = $course1->shortname;

        $c2 = new stdClass(); // Second course.
        $c2->id = $course2->id;
        $c2->shortname = $course2->shortname;
        $expected = array($c1->shortname => $c1, $c2->shortname => $c2);
        $this->assertEquals($expected, $result);

        // Test get user1's courses from cat 2.
        $result = get_user_shortname_courses($user1->id, $category2->id);
        $this->assertCount(1, $result);

        $c3 = new stdClass(); // Second course.
        $c3->id = $course3->id;
        $c3->shortname = $course3->shortname;
        $expected = array($c3->shortname => $c3, $c3->shortname => $c3);
        $this->assertEquals($expected, $result);

        // Test get user1's courses from cat 3.
        $result = get_user_shortname_courses($user1->id, $category3->id);
        $this->assertCount(1, $result);

        $c5 = new stdClass(); // Second course.
        $c5->id = $course5->id;
        $c5->shortname = $course5->shortname;
        $expected = array($c5->shortname => $c5, $c5->shortname => $c5);
        $this->assertEquals($expected, $result);

        // Test get user2's courses from cat 1 (should be 0).
        $result = get_user_shortname_courses($user2->id, $category1->id);
        $this->assertCount(0, $result);

        // Recovering the manager role data.
        $managerrole = $DB->get_record('role', array('shortname' => 'manager'));

        // Recovering the context of the categories.
        $contextcat1 = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $category1->id));

        // Enrol user2 as manager in cat1.
        $record = new stdClass();
        $record->roleid = $managerrole->id;
        $record->contextid = $contextcat1->id;
        $record->userid = $user3->id;
        $lastinsertid = $DB->insert_record('role_assignments', $record);

        // Test get the courses where in category1, is manager.
        $result = get_user_shortname_courses($user3->id, $category1->id);
        $this->assertCount(2, $result);

        // Test get the courses where in category2, is manager (should be 0).
        $result = get_user_shortname_courses($user3->id, $category2->id);
        $this->assertCount(0, $result);

        // Reset data.
        $this->resetAllData();
    }

    /**
     * Tests for phpunit.
     */
    public function test_get_info_grades () {

        global $DB;

        $this->resetAfterTest();

        $manualplugin = self::enable_enrol_plugin();
        $this->assertNotEmpty($manualplugin);
        $studentrole = self::get_student_role();

        $user1 = $this->getDataGenerator()->create_user(array('firstname' => "USUARIO 1"));

        $course1 = $this->getDataGenerator()->create_course(array('shortname' => "CURSO"));
        $course2 = $this->getDataGenerator()->create_course(array('shortname' => "MI.CURSO"));

        $manualinstance = self::create_manual_instance($course1->id);
        $manualplugin->enrol_user($manualinstance, $user1->id, $studentrole->id, 1493203999, 1494303999);
        $manualinstance2 = self::create_manual_instance($course2->id);
        $manualplugin->enrol_user($manualinstance2, $user1->id, $studentrole->id, 1493103999, 1494302999);

        $grade = $this->getDataGenerator()->create_grade_item(array('itemtype' => 'course', 'courseid' => $course1->id));
        $this->assertNotEmpty($grade);
        $grades = new stdClass();
        $grades->itemid = $grade->id;
        $grades->finalgrade = 88;
        $grades->feedback = 'Texto de informacion';
        $grades->userid = $user1->id;

        $DB->insert_record('grade_grades', $grades, false);

        $info = get_info_grades($course1->id, $user1->id);
        $this->assertNotEmpty($info);
        $this->assertEquals($info, 'Texto de informacion');
    }

    /**
     * Tests for phpunit.
     */
    public function test_integrate_previous_data () {

        global $DB;

        $this->resetAfterTest(true);

        // Creating a few users.
        $user1 = $this->getDataGenerator()->create_user(array('username' => 'user1', 'email' => 'user1@testmail.com'));
        $user2 = $this->getDataGenerator()->create_user(array('username' => 'user2', 'email' => 'user2@testmail.com'));
        $user3 = $this->getDataGenerator()->create_user(array('username' => 'user3', 'email' => 'user3@testmail.com'));

        // Creating several categories for future use.
        $category1 = $this->getDataGenerator()->create_category(array('name' => 'Category 1'));
        $category2 = $this->getDataGenerator()->create_category(array('name' => 'Category 2'));

        // Creating several courses and assign each to one of the categories above.
        $course1 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Normal course 1', 'category' => $category1->id));
        $course2 = $this->getDataGenerator()->create_course(
                array('shortname' => 'Normal course 2', 'category' => $category1->id));
        $course3 = $this->getDataGenerator()->create_course(
                array('shortname' => 'MI.Normal course 1', 'category' => $category2->id));
        $course4 = $this->getDataGenerator()->create_course(
                array('shortname' => 'MI.Normal course 2', 'category' => $category2->id));

        // Creating initial data ($data1 and $data6 are the only strings with correct info).
        $data1 = 'CREATE;user1@testmail.com;Normal course 1;21/04/2017;4' . PHP_EOL .
                'CREATE;user2@testmail.com;Normal course 1;22/04/2017;4';
        $data2 = 'CREATED;user3@testmail.com;Normal course 1;23/04/1970;4;CREATED' . PHP_EOL .
                'user4@testmail.com;Normal course 1;24/04/1970;1;';
        $data3 = 'CREATE;user5@testmail.com;Normal course 1;25/04/1970;4' . PHP_EOL .
                'DEL;user6@testmail.com;Curso Cron 7';
        $data4 = 'CREATE;user7@testmail.com;Normal course 1;27/04/1970;4' . PHP_EOL .
                'CREATE;user8@testmail.com;Normal course 2;28-04-1970;1';
        $data5 = 'CREATE;user9@testmail.com;Normal course 1;29/04/1970;4' . PHP_EOL .
                'CREATE;user10@testmail.com;Normal course 1;30/04/1970;5';
        $data6 = 'CREATE;user1@testmail.com;Normal course 1;21/04/2017;4' . PHP_EOL .
                'DELETE;user2@testmail.com;Normal course 1;'. PHP_EOL .
                'CREATE;user3@testmail.com;MI.Category 1.Normal course 1;22/04/2017;4';

        /* Test the function with $data1
         * (expected result: 2 entries in local_eudecustom_mat_int and local_eudecustom_user, one for each user)
         */
        $result = integrate_previous_data($data1);
        $expectedmatintrecords = $DB->get_records('local_eudecustom_mat_int');
        $expecteduserrecord1 = $DB->get_record('local_eudecustom_user', array('user_email' => $user1->email));
        $expecteduserrecord2 = $DB->get_record('local_eudecustom_user', array('user_email' => $user2->email));
        $this->assertTrue($result);
        $this->assertCount(2, $expectedmatintrecords);
        $this->assertEquals($user1->email, $expecteduserrecord1->user_email);
        $this->assertEquals($category1->id, $expecteduserrecord1->course_category);
        $this->assertEquals(1, $expecteduserrecord1->num_intensive);
        $this->assertEquals($user2->email, $expecteduserrecord2->user_email);
        $this->assertEquals($category1->id, $expecteduserrecord2->course_category);
        $this->assertEquals(1, $expecteduserrecord2->num_intensive);

        $result = integrate_previous_data($data2);
        $this->assertFalse($result);

        $result = integrate_previous_data($data3);
        $this->assertFalse($result);

        $result = integrate_previous_data($data4);
        $this->assertFalse($result);

        $result = integrate_previous_data($data5);
        $this->assertFalse($result);
        $expectedmatintrecords222 = $DB->get_records('local_eudecustom_mat_int');

        /* Test the function with $data6
         * (expected result: 2 entries in local_eudecustom_mat_int, both for user1 and
         * 1 entry in local_eudecustom_user for user1 with num_intensives = 2)
         */
        $result = integrate_previous_data($data6);
        $expectedmatintrecords = $DB->get_records('local_eudecustom_mat_int');
        $expecteduserrecord1 = $DB->get_record('local_eudecustom_user', array('user_email' => $user1->email));
        $expecteduserrecord2 = $DB->get_record('local_eudecustom_user', array('user_email' => $user2->email));
        $expecteduserrecord3 = $DB->get_record('local_eudecustom_user', array('user_email' => $user3->email));
        $this->assertTrue($result);
        $this->assertCount(3, $expectedmatintrecords);
        $this->assertEquals($user1->email, $expecteduserrecord1->user_email);
        $this->assertEquals($category1->id, $expecteduserrecord1->course_category);
        $this->assertEquals(2, $expecteduserrecord1->num_intensive);
        $this->assertFalse($expecteduserrecord2);
        $this->assertEquals($user3->email, $expecteduserrecord3->user_email);
        $this->assertEquals($category1->id, $expecteduserrecord3->course_category);
        $this->assertEquals(1, $expecteduserrecord3->num_intensive);
    }

    /**
     * Tests for phpunit.
     */
    public function test_get_usercourses_by_rol () {
        global $DB;

        $this->resetAfterTest(true);

        // Creating users.
        $user1 = $this->getDataGenerator()->create_user(
                array('username' => 'user1'));
        $user2 = $this->getDataGenerator()->create_user(
                array('username' => 'user2'));

        // Creating categories.
        $category1 = $this->getDataGenerator()->create_category(
                array('name' => 'phpunit category 1'));
        $category2 = $this->getDataGenerator()->create_category(
                array('name' => 'phpunit category 2'));

        // Creating courses.
        $course1 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT.M01', 'category' => $category1->id, 'fullname' => 'course1 fullname'));
        $course2 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT.M02', 'category' => $category1->id, 'fullname' => 'course2 fullname'));
        $course3 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT.M03', 'category' => $category2->id, 'fullname' => 'course3 fullname'));
        $course4 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT.M04', 'category' => $category2->id, 'fullname' => 'course4 fullname'));

        // Getting the id of the roles.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));

        // Enrolling student and teacher in both courses.
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course2->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user2->id, $course2->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user2->id, $course3->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user2->id, $course4->id, $teacherrole->id, 'manual');

        // Test1: Get courses from a student (should return 0).
        $result = get_usercourses_by_rol($user1->id);
        $this->assertCount(0, $result);

        // Test2: Get courses from a teacher.
        $result = get_usercourses_by_rol($user2->id);
        $this->assertCount(4, $result);

        $c1 = ['course' => $course1->id, 'category' => $category1->id];
        $c2 = ['course' => $course2->id, 'category' => $category1->id];
        $c3 = ['course' => $course3->id, 'category' => $category2->id];
        $c4 = ['course' => $course4->id, 'category' => $category2->id];

        $expected = array($c1, $c2, $c3, $c4);
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests for phpunit.
     */
    public function test_module_is_intensive () {
        global $DB;

        $this->resetAfterTest(true);

        // Creating categories.
        $category1 = $this->getDataGenerator()->create_category(
                array('name' => 'phpunit category 1'));

        // Creating courses.
        $course1 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT.M01', 'category' => $category1->id, 'fullname' => 'course1 fullname'));
        $course2 = $this->getDataGenerator()->create_course(
                array('shortname' => 'MI.CAT.M01', 'category' => $category1->id, 'fullname' => 'course2 fullname'));

        // Test1: course1 should return false.
        $result = module_is_intensive($course1->shortname);
        $this->assertEquals(false, $result);

        // Test2: course2 should return true.
        $result = module_is_intensive($course2->shortname);
        $this->assertEquals(true, $result);
    }

    /**
     * Tests for phpunit.
     */
    public function test_get_actual_module () {
        global $DB;

        $this->resetAfterTest(true);

        // Creating users.
        $user1 = $this->getDataGenerator()->create_user(
                array('username' => 'user1'));
        $user2 = $this->getDataGenerator()->create_user(
                array('username' => 'user2'));

        // Creating categories.
        $category1 = $this->getDataGenerator()->create_category(
                array('name' => 'phpunit category 1'));
        $category2 = $this->getDataGenerator()->create_category(
                array('name' => 'phpunit category 2'));
        $category3 = $this->getDataGenerator()->create_category(
                array('name' => 'phpunit category 3'));

        // Creating courses.
        $course1 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT1.M01', 'category' => $category1->id, 'fullname' => 'course1 fullname'));
        $course2 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT1.M02', 'category' => $category1->id, 'fullname' => 'course2 fullname'));
        $course3 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT2.M03', 'category' => $category2->id, 'fullname' => 'course3 fullname'));
        $course4 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT2.M04', 'category' => $category2->id, 'fullname' => 'course4 fullname'));
        $course5 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT3.M05', 'category' => $category3->id, 'fullname' => 'course4 fullname'));

        // Getting the id of the roles.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));

        // Enrolling teacher in all courses.
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course2->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course3->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course4->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course5->id, $teacherrole->id, 'manual');

        $prevstart = time() - 20000;
        $prevend = time() + 90000;
        $actualstart = time() - 20;
        $actualend = time() + 90000;
        $nextstart = time() + 60000;
        $nextend = time() + 90000;

        // Enrolling student in c1(prev), c2(actual), c3(actual), c4(next).
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id, $studentrole->id, 'manual', $prevstart, $prevend);
        $this->getDataGenerator()->enrol_user($user2->id, $course2->id, $studentrole->id, 'manual', $actualstart, $actualend);
        $this->getDataGenerator()->enrol_user($user2->id, $course3->id, $studentrole->id, 'manual', $actualstart, $actualend);
        $this->getDataGenerator()->enrol_user($user2->id, $course4->id, $studentrole->id, 'manual', $nextstart, $nextend);
        $this->getDataGenerator()->enrol_user($user2->id, $course5->id, $studentrole->id, 'manual', $nextstart, $nextend);

        // Test1: check cat1. Should return module 2.
        $result = get_actual_module($category1->id);
        $this->assertEquals('02', $result);

        // Test2: cat 2 should return module 3.
        $result = get_actual_module($category2->id);
        $this->assertEquals('03', $result);

        // Test3: Cat3 should return 0.
        $result = get_actual_module($category3->id);
        $this->assertEquals(0, $result);
    }

    /**
     * Tests for phpunit.
     */
    public function test_get_students_course_data () {
        global $DB;

        $this->resetAfterTest(true);

        // Creating users.
        $user1 = $this->getDataGenerator()->create_user(
                array('username' => 'user1'));
        $user2 = $this->getDataGenerator()->create_user(
                array('username' => 'user2'));

        // Creating categories.
        $category1 = $this->getDataGenerator()->create_category(
                array('name' => 'phpunit category 1'));

        // Creating courses.
        $course1 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT1.M01', 'category' => $category1->id, 'fullname' => 'course1 fullname'));
        $course2 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT1.M02', 'category' => $category1->id, 'fullname' => 'course2 fullname'));
        $course3 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT2.M03', 'category' => $category1->id, 'fullname' => 'course3 fullname'));
        $course4 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT2.M04', 'category' => $category1->id, 'fullname' => 'course4 fullname'));
        $course5 = $this->getDataGenerator()->create_course(
                array('shortname' => 'MI.CAT2.M05', 'category' => $category1->id, 'fullname' => 'Intensive course5 fullname'));

        // Getting the id of the roles.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));

        // Enrolling teacher in all courses.
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course2->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course3->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course4->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course5->id, $teacherrole->id, 'manual');

        $prevstart = time() - 20000;
        $prevend = time() - 10000;
        $actualstart = time() - 20;
        $actualend = time() + 20;
        $nextstart = time() + 60000;
        $nextend = time() + 90000;

        // Enrolling student in c1(prev), c2(actual), c3(next), c5(intensive, next).
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id, $studentrole->id, 'manual', $prevstart, $prevend);
        $this->getDataGenerator()->enrol_user($user2->id, $course2->id, $studentrole->id, 'manual', $actualstart, $actualend);
        $this->getDataGenerator()->enrol_user($user2->id, $course3->id, $studentrole->id, 'manual', $nextstart, $nextend);
        $this->getDataGenerator()->enrol_user($user2->id, $course5->id, $studentrole->id, 'manual', $nextstart, $nextend);

        $actualmodule = '02';

        $c1 = new stdClass();
        $c1->id = $course1->id;
        $c1->shortname = $course1->shortname;
        $c1->fullname = $course1->fullname;
        $c1->timestart = $prevstart;
        $c1->timeend = $prevend;
        $c1->userid = $user2->id;
        $c1->category = $category1->name;
        $c1->date = 'prev';

        $c3 = new stdClass();
        $c3->id = $course3->id;
        $c3->shortname = $course3->shortname;
        $c3->fullname = $course3->fullname;
        $c3->timestart = $nextstart;
        $c3->timeend = $nextend;
        $c3->userid = $user2->id;
        $c3->category = $category1->name;
        $c3->date = 'next';

        $c4 = new stdClass();
        $c4->id = $course4->id;
        $c4->shortname = $course4->shortname;
        $c4->fullname = $course4->fullname;
        $c4->timestart = 0;
        $c4->timeend = 0;
        $c4->userid = $user1->id;
        $c4->category = $category1->name;
        $c4->date = 'actual';

        $c5 = new stdClass();
        $c5->id = $course5->id;
        $c5->shortname = $course5->shortname;
        $c5->fullname = $course5->fullname;
        $c5->timestart = $nextstart;
        $c5->timeend = $nextend;
        $c5->userid = $user2->id;
        $c5->category = $category1->name;
        $c5->date = 'actual';

        // Test1: get data from course1.
        $result = get_students_course_data($course1->id, $actualmodule);
        $this->assertEquals($c1->id, $result->id);
        $this->assertEquals($c1->shortname, $result->shortname);
        $this->assertEquals($c1->fullname, $result->fullname);
        $this->assertEquals($c1->timestart, $result->timestart);
        $this->assertEquals($c1->timeend, $result->timeend);
        $this->assertEquals($c1->userid, $result->userid);
        $this->assertEquals($c1->category, $result->category);
        $this->assertEquals($c1->date, $result->date);

        // Test2: get data from course 3.
        $result = get_students_course_data($course3->id, $actualmodule);
        $this->assertEquals($c3->id, $result->id);
        $this->assertEquals($c3->shortname, $result->shortname);
        $this->assertEquals($c3->fullname, $result->fullname);
        $this->assertEquals($c3->timestart, $result->timestart);
        $this->assertEquals($c3->timeend, $result->timeend);
        $this->assertEquals($c3->userid, $result->userid);
        $this->assertEquals($c3->category, $result->category);
        $this->assertEquals($c3->date, $result->date);

        // Test3: get data from course 4. (no student enrolled, shoud get date->actual.
        $result = get_students_course_data($course4->id, $actualmodule);
        $this->assertEquals($c4->id, $result->id);
        $this->assertEquals($c4->shortname, $result->shortname);
        $this->assertEquals($c4->fullname, $result->fullname);
        $this->assertEquals($c4->timestart, $result->timestart);
        $this->assertEquals($c4->timeend, $result->timeend);
        $this->assertEquals($c4->userid, $result->userid);
        $this->assertEquals($c4->category, $result->category);
        $this->assertEquals($c4->date, $result->date);

        // Test4: get data from course 5. (Should get date->actual because is intensive).
        $result = get_students_course_data($course5->id, $actualmodule);
        $this->assertEquals($c5->id, $result->id);
        $this->assertEquals($c5->shortname, $result->shortname);
        $this->assertEquals($c5->fullname, $result->fullname);
        $this->assertEquals($c5->timestart, $result->timestart);
        $this->assertEquals($c5->timeend, $result->timeend);
        $this->assertEquals($c5->userid, $result->userid);
        $this->assertEquals($c5->category, $result->category);
        $this->assertEquals($c5->date, $result->date);
    }

    /**
     * Tests for phpunit.
     */
    public function test_add_course_activities () {
        global $DB;

        $this->resetAfterTest(true);

        // Creating users.
        $user1 = $this->getDataGenerator()->create_user(
                array('username' => 'user1'));

        // Creating categories.
        $category1 = $this->getDataGenerator()->create_category(
                array('name' => 'phpunit category 1'));

        // Creating courses.
        $course1 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT1.M01', 'category' => $category1->id, 'fullname' => 'course1 fullname'));

        // Creating announcements forum and another 2 general ones.
        $ann1 = $this->getDataGenerator()->create_module('forum', array('course' => $course1->id, 'type' => 'news'));
        $forum1 = $this->getDataGenerator()->create_module('forum', array('course' => $course1->id, 'type' => 'general'));
        $forum2 = $this->getDataGenerator()->create_module('forum', array('course' => $course1->id, 'type' => 'general'));

        // Creating 3 assignments.
        $as1 = $this->getDataGenerator()->create_module('assign', array('course' => $course1->id));
        $as2 = $this->getDataGenerator()->create_module('assign', array('course' => $course1->id));
        $as3 = $this->getDataGenerator()->create_module('assign', array('course' => $course1->id));

        // Create course object for param.
        $param = new stdClass();
        $param->id = $course1->id;
        $param->shortname = $course1->shortname;
        $param->fullname = $course1->fullname;
        $param->timestart = 0;
        $param->timeend = 0;
        $param->userid = $user1->id;
        $param->category = $category1->name;
        $param->date = 'next';

        $an1 = new stdClass();
        $an1->id = $ann1->id;
        $an1->name = $ann1->name;
        $an1->course = $ann1->course;
        $an1->type = $ann1->type;

        $f1 = new stdClass();
        $f1->id = $forum1->id;
        $f1->name = $forum1->name;
        $f1->course = $forum1->course;
        $f1->type = $forum1->type;

        $f2 = new stdClass();
        $f2->id = $forum2->id;
        $f2->name = $forum2->name;
        $f2->course = $forum2->course;
        $f2->type = $forum2->type;

        $a1 = new stdClass();
        $a1->id = $as1->id;
        $a1->name = $as1->name;
        $a1->course = $as1->course;

        $a2 = new stdClass();
        $a2->id = $as2->id;
        $a2->name = $as2->name;
        $a2->course = $as2->course;

        $a3 = new stdClass();
        $a3->id = $as3->id;
        $a3->name = $as3->name;
        $a3->course = $as3->course;

        $c1 = new stdClass();
        $c1->id = $course1->id;
        $c1->shortname = $course1->shortname;
        $c1->fullname = $course1->fullname;
        $c1->timestart = 0;
        $c1->timeend = 0;
        $c1->userid = $user1->id;
        $c1->category = $category1->name;
        $c1->date = 'next';
        $c1->notices = $an1;
        $c1->forums = [$f1, $f2];
        $c1->assigns = [$a1, $a2, $a3];

        // Test1: get object with forums and assignments.
        $result = add_course_activities($param);

        $this->assertEquals($c1->id, $result->id);
        $this->assertEquals($c1->shortname, $result->shortname);
        $this->assertEquals($c1->fullname, $result->fullname);
        $this->assertEquals($c1->timestart, $result->timestart);
        $this->assertEquals($c1->timeend, $result->timeend);
        $this->assertEquals($c1->userid, $result->userid);
        $this->assertEquals($c1->category, $result->category);
        $this->assertEquals($c1->date, $result->date);
        $this->assertEquals($c1->forums, $result->forums);
        $this->assertEquals($c1->notices, $result->notices);
        $this->assertEquals($c1->assigns, $result->assigns);
        $this->assertCount(3, $result->assigns);
        $this->assertCount(2, $result->forums);
    }

    /**
     * Tests for phpunit.
     */
    public function test_get_user_courses () {
        global $DB;

        $this->resetAfterTest(true);

        // Creating users.
        $user1 = $this->getDataGenerator()->create_user(
                array('username' => 'user1'));
        $user2 = $this->getDataGenerator()->create_user(
                array('username' => 'user2'));

        // Creating categories.
        $category1 = $this->getDataGenerator()->create_category(
                array('name' => 'phpunit category 1'));
        $category2 = $this->getDataGenerator()->create_category(
                array('name' => 'phpunit category 2'));

        // Creating courses.
        $course1 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT1.M01', 'category' => $category1->id, 'fullname' => 'course1 fullname'));
        $course2 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT1.M02', 'category' => $category1->id, 'fullname' => 'course2 fullname'));
        $course3 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT2.M03', 'category' => $category2->id, 'fullname' => 'course3 fullname'));
        $course4 = $this->getDataGenerator()->create_course(
                array('shortname' => 'CAT2.M04', 'category' => $category2->id, 'fullname' => 'course4 fullname'));
        $course5 = $this->getDataGenerator()->create_course(
                array('shortname' => 'MI.CAT2.M05', 'category' => $category2->id, 'fullname' => 'Intensive course5 fullname'));

        // Creating notices for all courses.
        $ann1 = $this->getDataGenerator()->create_module('forum', array('course' => $course1->id, 'type' => 'news'));
        $ann2 = $this->getDataGenerator()->create_module('forum', array('course' => $course2->id, 'type' => 'news'));
        $ann3 = $this->getDataGenerator()->create_module('forum', array('course' => $course3->id, 'type' => 'news'));
        $ann4 = $this->getDataGenerator()->create_module('forum', array('course' => $course4->id, 'type' => 'news'));
        $ann5 = $this->getDataGenerator()->create_module('forum', array('course' => $course5->id, 'type' => 'news'));

        // Creating forums.
        $forum1 = $this->getDataGenerator()->create_module('forum', array('course' => $course1->id, 'type' => 'general'));
        $forum2 = $this->getDataGenerator()->create_module('forum', array('course' => $course1->id, 'type' => 'general'));
        $forum3 = $this->getDataGenerator()->create_module('forum', array('course' => $course1->id, 'type' => 'general'));
        $forum4 = $this->getDataGenerator()->create_module('forum', array('course' => $course3->id, 'type' => 'general'));

        // Creating assignments.
        $as1 = $this->getDataGenerator()->create_module('assign', array('course' => $course2->id));
        $as2 = $this->getDataGenerator()->create_module('assign', array('course' => $course2->id));
        $as3 = $this->getDataGenerator()->create_module('assign', array('course' => $course5->id));

        // Getting the id of the roles.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));

        // Enrolling teacher in all courses.
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course2->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course3->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course4->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($user1->id, $course5->id, $teacherrole->id, 'manual');

        $prevstart = time() - 20000;
        $prevend = time() - 10000;
        $actualstart = time() - 20;
        $actualend = time() + 20;
        $nextstart = time() + 60000;
        $nextend = time() + 90000;

        // Enrolling student in c1(prev), c2(actual), c3(next), c5(intensive, next).
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id, $studentrole->id, 'manual', $prevstart, $prevend);
        $this->getDataGenerator()->enrol_user($user2->id, $course2->id, $studentrole->id, 'manual', $actualstart, $actualend);
        $this->getDataGenerator()->enrol_user($user2->id, $course3->id, $studentrole->id, 'manual', $actualstart, $actualend);
        $this->getDataGenerator()->enrol_user($user2->id, $course5->id, $studentrole->id, 'manual', $nextstart, $nextend);

        $actualmodule = '02';

        // Creating test objects.
        // Activities.
        $an1 = new stdClass();
        $an1->id = $ann1->id;
        $an1->name = $ann1->name;
        $an1->course = $ann1->course;
        $an1->type = $ann1->type;

        $an2 = new stdClass();
        $an2->id = $ann2->id;
        $an2->name = $ann2->name;
        $an2->course = $ann2->course;
        $an2->type = $ann2->type;

        $an3 = new stdClass();
        $an3->id = $ann3->id;
        $an3->name = $ann3->name;
        $an3->course = $ann3->course;
        $an3->type = $ann3->type;

        $an4 = new stdClass();
        $an4->id = $ann4->id;
        $an4->name = $ann4->name;
        $an4->course = $ann4->course;
        $an4->type = $ann4->type;

        $an5 = new stdClass();
        $an5->id = $ann5->id;
        $an5->name = $ann5->name;
        $an5->course = $ann5->course;
        $an5->type = $ann5->type;

        $f1 = new stdClass();
        $f1->id = $forum1->id;
        $f1->name = $forum1->name;
        $f1->course = $forum1->course;
        $f1->type = $forum1->type;

        $f2 = new stdClass();
        $f2->id = $forum2->id;
        $f2->name = $forum2->name;
        $f2->course = $forum2->course;
        $f2->type = $forum2->type;

        $f3 = new stdClass();
        $f3->id = $forum3->id;
        $f3->name = $forum3->name;
        $f3->course = $forum3->course;
        $f3->type = $forum3->type;

        $f4 = new stdClass();
        $f4->id = $forum4->id;
        $f4->name = $forum4->name;
        $f4->course = $forum4->course;
        $f4->type = $forum4->type;

        $a1 = new stdClass();
        $a1->id = $as1->id;
        $a1->name = $as1->name;
        $a1->course = $as1->course;

        $a2 = new stdClass();
        $a2->id = $as2->id;
        $a2->name = $as2->name;
        $a2->course = $as2->course;

        $a3 = new stdClass();
        $a3->id = $as3->id;
        $a3->name = $as3->name;
        $a3->course = $as3->course;

        // Courses.
        $c1 = new stdClass();
        $c1->id = $course1->id;
        $c1->shortname = $course1->shortname;
        $c1->fullname = $course1->fullname;
        $c1->timestart = "$prevstart";
        $c1->timeend = "$prevend";
        $c1->userid = $user2->id;
        $c1->category = $category1->name;
        $c1->date = 'prev';
        $c1->notices = $an1;
        $c1->forums = [$f1, $f2, $f3];
        $c1->assigns = [];

        $c2 = new stdClass();
        $c2->id = $course2->id;
        $c2->shortname = $course2->shortname;
        $c2->fullname = $course2->fullname;
        $c2->timestart = "$actualstart";
        $c2->timeend = "$actualend";
        $c2->userid = $user2->id;
        $c2->category = $category1->name;
        $c2->date = 'prev';
        $c2->notices = $an2;
        $c2->forums = [];
        $c2->assigns = [$a1, $a2];

        $c3 = new stdClass();
        $c3->id = $course3->id;
        $c3->shortname = $course3->shortname;
        $c3->fullname = $course3->fullname;
        $c3->timestart = "$actualstart";
        $c3->timeend = "$actualend";
        $c3->userid = $user2->id;
        $c3->category = $category2->name;
        $c3->date = 'actual';
        $c3->notices = $an3;
        $c3->forums = [$f4];
        $c3->assigns = [];

        $c4 = new stdClass();
        $c4->id = $course4->id;
        $c4->shortname = $course4->shortname;
        $c4->fullname = $course4->fullname;
        $c4->timestart = '0';
        $c4->timeend = '0';
        $c4->userid = $user1->id;
        $c4->category = $category2->name;
        $c4->date = 'actual';
        $c4->notices = $an4;
        $c4->forums = [];
        $c4->assigns = [];

        $c5 = new stdClass();
        $c5->id = $course5->id;
        $c5->shortname = $course5->shortname;
        $c5->fullname = $course5->fullname;
        $c5->timestart = "$nextstart";
        $c5->timeend = "$nextend";
        $c5->userid = $user2->id;
        $c5->category = $category2->name;
        $c5->date = 'actual';
        $c5->notices = $an5;
        $c5->forums = [];
        $c5->assigns = [$a3];

        // Test1: use teacher id.
        $result = get_user_courses($user1->id);
        $prevarray = [$c1];
        $actualarray = [$c2, $c3, $c4, $c5];
        $nextarray = [];

        $expected = ['actual' => $actualarray, 'prev' => $prevarray, 'next' => $nextarray];

        $this->assertCount(1, $result['prev']);
        $this->assertCount(4, $result['actual']);
        $this->assertCount(0, $result['next']);
        $this->assertEquals($expected['prev'][0]->id, $result['prev'][0]->id);
        $this->assertEquals($expected['prev'][0]->notices, $result['prev'][0]->notices);
        $this->assertCount(3, $result['prev'][0]->forums);
        $this->assertEquals($expected['prev'][0]->forums[0]->name, $result['prev'][0]->forums[0]->name);
        $this->assertEquals($expected['prev'][0]->forums[1]->name, $result['prev'][0]->forums[1]->name);
        $this->assertEquals($expected['prev'][0]->forums[2]->name, $result['prev'][0]->forums[2]->name);
        $this->assertCount(0, $result['prev'][0]->assigns);
        $this->assertEquals($expected['actual'][0]->id, $result['actual'][0]->id);
        $this->assertCount(0, $result['actual'][0]->forums);
        $this->assertCount(2, $result['actual'][0]->assigns);
        $this->assertEquals($expected['actual'][0]->assigns[0]->name, $result['actual'][0]->assigns[0]->name);
        $this->assertEquals($expected['actual'][0]->assigns[1]->name, $result['actual'][0]->assigns[1]->name);
        $this->assertEquals($expected['actual'][1]->id, $result['actual'][1]->id);
        $this->assertCount(1, $result['actual'][1]->forums);
        $this->assertEquals($expected['actual'][1]->forums[0]->name, $result['actual'][1]->forums[0]->name);
        $this->assertEquals($expected['actual'][2]->id, $result['actual'][2]->id);
        $this->assertEquals($expected['actual'][3]->id, $result['actual'][3]->id);
        $this->assertEquals($expected['actual'][3]->assigns[0]->name, $result['actual'][3]->assigns[0]->name);

        // Test2: use student id.
        $result = get_user_courses($user2->id);
        $this->assertCount(0, $result['prev']);
        $this->assertCount(0, $result['actual']);
        $this->assertCount(0, $result['actual']);
    }

}
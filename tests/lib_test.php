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
 * @package    local_comillasppi
 * @copyright  2018 Planificacion de Entornos Tecnologicos SL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once(__DIR__. '/../lib.php');
/**
 * This class is used to run the unit tests
 *
 * @package    local_comillasppi
 * @copyright  2018 Planificacion de Entornos Tecnologicos SL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_comillasppi_testcase extends advanced_testcase {

    /**
     * Tests for phpunit.
     */
    public function test_local_comillasppi_before_footer() {
        $this->resetAfterTest(true);
        // Get the function response.
        $result = local_comillasppi_before_footer();
        // Test the function response. We check the function does not return any value.
        $this->assertNull($result);
    }

    /**
     * Tests for phpunit.
     */
    public function test_local_comillasppi_before_http_headers() {
        $this->resetAfterTest(true);
        // Get the function response.
        $result = local_comillasppi_before_http_headers();
        // Test the function response. We check the function does not return any value.
        $this->assertNull($result);
    }
}

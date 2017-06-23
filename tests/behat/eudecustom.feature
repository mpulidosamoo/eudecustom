@local @local_eudecustom
Feature: Prueba
  In order to validate my credentials in the system
  As a user student
  I want to navigate into the system
  
  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | 1 | student1@example.com |
      | student2 | Student | 2 | student2@example.com |
      | student3 | Student | 3 | student3@example.com |
    And the following "categories" exist:
      | name | idnumber |
      | RRHH | 1 |
      | MBA | 2 |
    And the following "courses" exist:
      | fullname | shortname | format | category |
      | Course 0 | C0 | weeks | 1 |
      | Course 1 | C1 | weeks | 2 |
      | Course 2 | C2 | weeks | 2 |
      | MI.Course 1 | MI.C1 | weeks | 2 |
      | MI.Course 2 | MI.C2 | weeks | 2 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C0 | student |
      | student2 | C0 | student |
      | student1 | C1 | student |
      | student2 | C1 | student |
      | student3 | C1 | student |
      | student1 | C2 | student |
      | student1 | MI.C1 | student |
      | student3 | MI.C1 | student |
    And I log in as "admin"
    And I expand "Site administration" node
    And I expand "Plugins" node
    And I expand "Local plugins" node
    And I follow "Eude custom actions"
    And I select "6" from the "id_s__local_eudecustom_intensivemodulechecknumber" singleselect
    And I select "3" from the "id_s__local_eudecustom_totalenrolsinincurse" singleselect
    And I set the field "id_s__local_eudecustom_intensivemoduleprice" to "50"
    And I set the field "id_s__local_eudecustom_tpv_url_tpvv" to "https://sis.redsys.es/sis/realizarPago"
    And I press "Save changes"
    And I add intensive enrols
    And Add dates
    And I log out
    

  @javascript
  Scenario: View intensives modules like a student
    Given I log in as "student3"
    When I go to intensives
    And I select "-- Program --" from the "menucategoryname" singleselect
    And I wait "2" seconds
    And I select "MBA" from the "menucategoryname" singleselect
    And I wait "15" seconds




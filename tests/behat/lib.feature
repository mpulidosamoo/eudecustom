@local @local_comillasppi
Feature: Test the 'comillasppi' feature works.
  In order to see the pop up
  As a teacher
  I need to have logged in once before, navigate to a course where i am a teacher
  and press the Turn Editing On button

  Background: 
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | User      | 1        | user1@example.com |
    And the following "categories" exist:
      | name | idnumber |
      | Cat1 | CAT1     |
    And the following "courses" exist:
      | category | shortname | idnumber |
      | CAT1     | COU1      | C1       |
      | CAT1     | COU2      | C2       |
    And the following "course enrolments" exist:
      | user  | course    | role           |
      | user1 | COU1      | editingteacher |
      | user1 | COU2      | student        |

  # Given a teacher navigate to a course where the user has the role of teacher
  # and press turn editing on
  @javascript
  Scenario: Enter with a user with role teacher in a course and turn editing on
    Given I log in as "user1"
    And I follow "COU1"
    Given I turn editing mode on
    Then I wait "7" seconds
    And I should see "Consulta el protocolo para subir documentos ajenos a Moodlerooms"
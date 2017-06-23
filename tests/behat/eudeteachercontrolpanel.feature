@local @local_eudecustom @javascript
Feature: View my courses sorted by enrolment dates
    and get access to some activities

Background: 
    
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | user1    | User      | 1        | user1@example.com    |
      | user2    | User      | 2        | user2@example.com    |
    And the following "categories" exist:
      | name | idnumber |
      | Cat1 | CAT1     |
      | Cat2 | CAT2     |
    And the following "courses" exist:
      | category | shortname   | fullname    | idnumber |
      | CAT1     | CAT1.M01    | CAT1.M01    | C1       |
      | CAT1     | CAT1.M02    | CAT1.M02    | C2       |
      | CAT2     | MI.CAT2.M03 | MI.CAT2.M03 | C3       |
      | CAT2     | CAT2.M04    | CAT2.M04    | C4       |
    And the following "course enrolments" exist:
      | user     | course         | role           | timestart        | timeend       |
      | user1    | CAT1.M01       | editingteacher | 1389081600       | 0             |
      | user1    | CAT1.M02       | editingteacher | 1389081600       | 0             |
      | user1    | MI.CAT2.M03    | editingteacher | 1389081600       | 0             |
      | user1    | CAT2.M04       | editingteacher | 1389081600       | 0             |
      | user2    | CAT1.M01       | student        | 1496268000       | 1496440799    |
      | user2    | CAT1.M02       | student        | 1512082860       | 1543618860    |
      | user2    | MI.CAT2.M03    | student        | 1496268000       | 1496440799    |
    And the following "activities" exist:
      | activity |  idnumber    | type   | course      | 
      | forum    |     F1       | news   | CAT1.M01    |
      | forum    |     F2       | news   | CAT1.M02    |
      | forum    |     F3       | news   | MI.CAT2.M03 |
      | forum    |     F4       | news   | CAT2.M04    |
      | forum    |     F1       | general| CAT1.M01    |
      | forum    |     F2       | general| CAT1.M02    |
      | forum    |     F3       | general| MI.CAT2.M03 |
      | forum    |     F4       | general| CAT2.M04    |
    And the following "activities" exist:
      | activity    |  idnumber    | course       | timeavailable | duedate     | section |
      | assign      |     A1       |  CAT1.M01    | 86400         | 1496440000  | 1       |
      | assign      |     A2       |  CAT1.M02    | 86400         | 1543610000  | 1       |
      | assign      |     A3       |  MI.CAT2.M03 | 86400         | 1496400000  | 1       |
      

Scenario: Check course distribution
    Given I log in as "user1"
    And I go to eudeteachercontrolpanel
    # Categories should appear in "my courses section".
    And I should see "Cat1" in the "div.row.row-panel-list > li:nth-child(1) > div > a > span" "css_element"
    # Course 4 doesnt have a student enrolment, so it should appear in actual section.
    And I should see "CAT2.M04" in the "//div[@class='row eude_panel_bg']/div/div[2]/div[1]" "xpath_element"
    # Course 3 is intensive, so it should appear in actual section.
    And I should see "MI.CAT2.M03" in the "//div[@class='row eude_panel_bg']/div/div[2]/div[1]" "xpath_element"
    # Course 2 depends on today's date.
    And I should visualize "CAT1.M02" with 1512082860 and 1543618860
    And I log out

Scenario: Link to announcement forums
    Given I log in as "user1"
    And I go to eudeteachercontrolpanel
    And I set the field with xpath "//div[@class='row eude_panel_bg']/div/div[2]/div[1]/div[2]/li[1]/div[2]/select[@class='linkselect']" to "1"
    And I should see "announcements"
    And I log out

Scenario: Link to normal forums
    Given I log in as "user1"
    And I go to eudeteachercontrolpanel
    And I set the field with xpath "//div[@class='row eude_panel_bg']/div/div[2]/div[1]/div[2]/li[1]/div[2]/select[@class='linkselect']" to "2"
    And I should see "General forums"
    And I log out

Scenario: Link to assignments
    Given I log in as "user1"
    And I go to eudeteachercontrolpanel
    And I set the field with xpath "//div[@class='row eude_panel_bg']/div/div[2]/div[1]/div[2]/li[2]/div[2]/select[@class='linkselect']" to "3"
    And I should see "Assignments"
    And I log out
      
      
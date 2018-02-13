@local @local_eudecustom @javascript
Feature: Write a message
    In order to communicate with other users
    
Background: 
    
    Given the following "users" exist:
      | username    | firstname     | lastname | email                |
      | user1       | User1         | 1        | user1@example.com    |
      | teacher1    | Teacher       | Teacher  | teacher1@example.com |
    And the following "categories" exist:
      | name | idnumber |
      | Cat1 | CAT1     |
      | Cat2 | CAT2     |
    And the following "courses" exist:
      | category | shortname    | idnumber | fullname |
      | CAT1     | M01          | C1       | Course 1 |
      | CAT2     | MI.M02       | C2       | Course intensive 1 |
    And the following "course enrolments" exist:
      | user     | course        | role             | timestart  | timeend    |
      | user1    | M01           | student          | 1483255184 | 1512631184 |
      | user1    | MI.M02        | student          | 1483255184 | 1512631184 |
      | teacher1 | M01           | editingteacher   | 1483255184 | 1512631184 |
      | teacher1 | MI.M02        | editingteacher   | 1483255184 | 1512631184 |
    And the following "activities" exist:
      | activity |  idnumber   | course      |  timeopen   |    timeclose | section |
      | quiz    |     Q1       | M01         |  1488442784 |   1488529184 |     1   |
      | quiz    |     Q2       | M01         |  1493972384 |   1494058784 |     1   |
      | quiz    |     Q3       | M01         |  1504858784 |   1504945184 |     1   |
    And the following "activities" exist:
      | activity    |  idnumber    | course      |  timeavailable   |    duedate   | section |
      | assign      |     A1       | M01         |      86400       |   1483341584 |   1     |
      | assign      |     A2       | M01         |      86400       |   1491376784 |   1     | 
      | assign      |     A3       | M01         |      86400       |   1502180384 |   1     | 
   And I add events
  
Scenario: Open the eudecalendar view as user1
    Given I log in as "user1"
    And I go to eudecalendar
    And I press "openmodalwindowforprint"
    And I set the field "modulebegin_modal" to "0"
    And I set the field "testdate_modal" to "0"
    And I set the field "activityend_modal" to "0"
    And I set the field "intensivemodulebegin_modal" to "0"
    And I set the field "eudeevent_modal" to "0"
    And I set the field "questionnairedate_modal" to "0"
    And I set the field "startdatemodal" to "01/01/2017"
    And I set the field "enddatemodal" to "25/01/2018"
    And I press "generateeventlist"
    #No checks
    And I should see "There are no events related to this dates"
    #Module begin checked
    And I set the field "modulebegin" to "1"
    And I press "generateeventlist"
    And I should see "Module beginning M01"
    #Test checked
    And I set the field "testdate" to "1"
    And I press "generateeventlist"
    And I should see "Quiz 1 Course 1"
    And I should see "Quiz 2 Course 1"
    And I should see "Quiz 3 Course 1"
    #Activity submission date checked
    And I set the field "activityend" to "1"
    And I press "generateeventlist"
    And I should see "Assignment 1 Course 1"
    And I should see "Assignment 2 Course 1"
    And I should see "Assignment 3 Course 1"
    #Intensive module begin checked
    And I set the field "intensivemodulebegin" to "1"
    And I press "generateeventlist"
    And I should see "Intensive module beginning MI.M02"
    #Event site checked
    And I set the field "eudeevent" to "1"
    And I press "generateeventlist"
    And I should see "Event site 1"
    And I log out

    
    

  
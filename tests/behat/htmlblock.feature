@editor @editor_tiny @tiny @tiny_htmlblock @javascript
Feature: Tiny editor admin settings for htmlblock plugin
  To be able to actually insert html blocks in the editor, these must be defined first in the admin settings.

  Scenario: Set two html blocks in the admin settings for htmlblock and use the tiny menu to use one.
    Given I log in as "admin"
    When I navigate to "Plugins > Text editors > Predefine HTML Blocks for TinyMCE" in site administration
    And I set the field "s_tiny_htmlblock_items_name_1" to "My fancy heading"
    And I set the field "s_tiny_htmlblock_items_html_1" to "<a href=\"/\"><h4 style=\"padding: 5px; background-color: cyan;\">Go to home</h4></a>"
    And I click on "[aria-label='Add a new entry']" "css_element"
    And I set the field "s_tiny_htmlblock_items_name_2" to "Text block in frame"
    And I set the field "s_tiny_htmlblock_items_html_2" to "<span style=\"border 1px solid #aaaaaa;\">This is in a frame</span>/"
    And I click on "Save changes" "button"
    And I open my profile in edit mode
    And I wait until the page is ready
    And I click on the "Insert > HTML blocks" menu item for the "Description" TinyMCE editor
    And I click on "li[title='My fancy heading']" "css_element"
    And I click on "Insert" "button" in the "Tiny HTML Blocks" "dialogue"
    When I click on "Update profile" "button"
    Then I should see "Go to home"

  Scenario: Check the category setting of the  HTML block items.
    Given the site is running Moodle version 4.3 or lower
    And the following config values are set as admin:
      | items | [{"name":"Heading XXZ1","html":"<h4>Go home X<\/h4>","cat":""},{"name":"Block AB","html":"<span style=\"border 1px solid #aaaaaa;\">Text framed<\/span>","cat":[1]}] | tiny_htmlblock |
    And the following "categories" exist:
      | id | name | idnumber | parent |
      | 1  | Test | test     | 0      |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity | course | section | intro | idnumber |
      | label    | C1     | 1       | Label | C1LABEL1 |
    When I log in as "admin"
    And I open my profile in edit mode
    And I wait until the page is ready
    And I click on the "Insert > HTML blocks" menu item for the "Description" TinyMCE editor
    And I wait "1" seconds
    Then I should see "Go home X"
    And I should not see "Text framed"
    When I am on the "C1" "Course" page logged in as "admin"
    And I turn editing mode on
    And I edit the section "1"
    And I wait until the page is ready
    And I click on the "Insert > HTML blocks" menu item for the "Summary" TinyMCE editor
    And I wait "1" seconds
    Then I should see "Go home X"
    And I should see "Text framed"

  Scenario: Check the category setting of the  HTML block items.
    Given the site is running Moodle version 4.4 or higher
    And the following config values are set as admin:
      | items | [{"name":"Heading XXZ1","html":"<h4>Go home X<\/h4>","cat":""},{"name":"Block AB","html":"<span style=\"border 1px solid #aaaaaa;\">Text framed<\/span>","cat":[1]}] | tiny_htmlblock |
    And the following "categories" exist:
      | id | name | idnumber | parent |
      | 1  | Test | test     | 0      |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity | course | section | intro | idnumber |
      | label    | C1     | 1       | Label | C1LABEL1 |
    When I log in as "admin"
    And I open my profile in edit mode
    And I wait until the page is ready
    And I click on the "Insert > HTML blocks" menu item for the "Description" TinyMCE editor
    And I wait "1" seconds
    Then I should see "Go home X"
    And I should not see "Text framed"
    When I am on the "C1" "Course" page logged in as "admin"
    And I turn editing mode on
    And I edit the section "1"
    And I wait until the page is ready
    And I click on the "Insert > HTML blocks" menu item for the "Description" TinyMCE editor
    And I wait "1" seconds
    Then I should see "Go home X"
    And I should see "Text framed"

  Scenario: Check if the toolbar button in the tinyMCE is working.
    Given the following config values are set as admin:
      | items | [{"name":"Heading X3","html":"<h3>Go homez<\/h3>","cat":""}] | tiny_htmlblock |
    When I log in as "admin"
    And I open my profile in edit mode
    And I wait until the page is ready
    And I expand all toolbars for the "Description" TinyMCE editor
    And I click on the "HTML blocks" button for the "Description" TinyMCE editor
    And I click on "li[title='Heading X3']" "css_element"
    And I click on "Insert" "button" in the "Tiny HTML Blocks" "dialogue"
    And I click on "Update profile" "button"
    Then I should see "Go homez"

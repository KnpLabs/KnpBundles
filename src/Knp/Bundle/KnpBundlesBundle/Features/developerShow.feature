Feature: Show developer page
  As a Visitor
  I want to see detailed info about developer

  Background:
    Given the site has following users:
    | name      |
    | knplabs   |
    | fos       |
    Given the site has following bundles:
    | username  | name       | description | lastCommitAt | score | trend1 |
    | knplabs   | TestBundle | test desc   |-1 day        | 10    | 15     |
    | fos       | UserBundle | user desc   |-2 days       | 20    | 5      |

  Scenario: Show developer page
    When I go to "/developer"
    And I follow "knplabs"
    Then response is successful
    And I should see that "TestBundle" is managed by developer

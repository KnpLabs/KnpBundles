Feature: Searching bundles
  As a Visitor
  I want to search bundles

  Background:
    Given the site has following users:
    | name      |
    | knplabs   |
    | fos       |
    Given the site has following bundles:
    | username  | name        | description | lastCommitAt | score | trend1 |
    | knplabs   | TestBundle  | test desc   |-1 day        | 10    | 15     |
    | knplabs   | Test2Bundle | test desc   |-1 day        | 10    | 15     |
    | fos       | UserBundle  | user desc   |-2 days       | 20    | 5      |

  Scenario: Searching all bundles
    When I go to "/"
    And I search for "Bundle"
    Then I should see "3 Bundles"
    And I should see "TestBundle"
    And I should see "Test2Bundle"
    And I should see "UserBundle"

  Scenario: Searching some bundles
    When I go to "/"
    And I search for "Test"
    Then I should see "2 Bundles"
    And I should see "TestBundle"
    And I should see "Test2Bundle"
    And I should not see "UserBundle"
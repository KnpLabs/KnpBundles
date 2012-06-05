@solr
Feature: Searching bundles
  As a Visitor
  I want to search bundles

  Background:
    Given the site has following users:
    | name      |
    | knplabs   |
    | fos       |
    And the site has following bundles:
    | username  | name        | description | lastCommitAt | score | trend1 |
    | knplabs   | TestBundle  | test desc   |-1 day        | 10    | 15     |
    | knplabs   | Test2Bundle | test desc   |-1 day        | 10    | 15     |
    | fos       | UserBundle  | user desc   |-2 days       | 20    | 5      |
    And bundles are indexed

  Scenario: Searching some bundles
    When I go to "/"
    And I search for "Test"
    Then I should see "2 Bundles"
    And I should see "TestBundle"
    And I should see "Test2Bundle"
    And I should not see "UserBundle"

  Scenario: Search one bundle
    When I go to "/"
    And I search for "User"
    Then I should see "1 Bundle"
    And I should see "UserBundle"

  Scenario: Search one bundle with exact name
    When I go to "/"
    And I search for "UserBundle"
    Then I should be on "fos/UserBundle" bundle page

  Scenario: Searching some bundles from description
    When I go to "/"
    And I search for "desc"
    Then I should see "3 Bundles"
    And I should see "TestBundle"
    And I should see "Test2Bundle"
    And I should see "UserBundle"

  Scenario: Search nothing
    When I go to "/"
    And I search for ""
    Then I should be on "/search"
    And I should see "Please use the search input at the top right."

  Scenario: Search and not find
    When I go to "/"
    And I search for "lorem"
    Then I should be on "/search"
    And I should see "Search 'lorem'"
    And I should see "0 Bundle"

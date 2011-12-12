Feature: Showing bundles
  As a Visitor
  I want to view bundles

  Background:
    Given the site has following users:
    | name      |
    | knplabs   |
    Given the site has following bundles:
    | username  | name       | description | lastCommitAt | score | trend1 |
    | knplabs   | TestBundle | test desc   |-1 day        | 10    | 15     |

  Scenario: Show bundle
    When I go to "/"
    And I follow "TestBundle"
    Then I should be on "knplabs/TestBundle" bundle page
    And I should see "TestBundle"
    And I should see "by knplabs"
    And I should see "Score: 10"
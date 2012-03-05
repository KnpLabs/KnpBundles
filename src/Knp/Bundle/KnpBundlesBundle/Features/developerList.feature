Feature: Listing developers
  As a Visitor
  I want to browse through developers list

  Background:
    Given the site has following users:
    | name      |
    | knplabs   |
    | fos       |
    Given the site has following bundles:
    | username  | name       | description | lastCommitAt | score | trend1 |
    | knplabs   | TestBundle | test desc   |-1 day        | 10    | 15     |
    | fos       | UserBundle | user desc   |-2 days       | 20    | 5      |

  Scenario: Listing developers
    When I go to "/"
    And I follow "Developers"
    Then I should see "2 developers using Symfony2"
    And I should see "knplabs" developer
    And I should see "fos" developer

Feature: Showing bundles
  As a Visitor
  I want to view bundles

  Background:
    Given the site has following users:
    | name  |
    | l3l0  |

  Scenario: Navigate to register bundle page
    Given I am at homepage
    And I am logged in as "l3l0"
    When I follow "Register a bundle"
    Then I should be on "/add"
    And I should see "You can add a bundle manually by entering its GitHub url"

  Scenario: Register new bundle
    Given I am at homepage
    And I am logged in as "l3l0"
    And I go to "/add"
    When I fill in "bundle" with "KnpLabs/KnpBundles"
    And I press "Add Symfony2 bundle"
    Then I should be on "KnpLabs/KnpBundles" bundle page
    And I should see "KnpBundles"
    And I should see "by KnpLabs"

  Scenario: Cannot register bundle when not logged in
    When I go to "/add"
    Then I should be on "/login"

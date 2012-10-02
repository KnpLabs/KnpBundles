Feature: Showing bundles
  As a Visitor
  I want to view bundles

  Background:
    Given the site has following users:
      | name    |
      | l3l0    |
      | KnpLabs |
    Given the site has following bundles:
      | username | name       | description | lastCommitAt | score | trend1 | state |
      | KnpLabs  | TestBundle | test desc   | -1 day       | 20    | 5      | ready |

  Scenario: Cannot register bundle when not logged in
    Given I am at homepage
     When I go to "/add"
     Then I should be on "/login"

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
     When I fill in "bundle" with "KnpLabs/KnpTimeBundle"
      And I press "Add Symfony2 bundle"
     Then I should be on "KnpLabs/KnpTimeBundle" bundle page
      And I should see "KnpTimeBundle"
      And I should see "by KnpLabs"

  Scenario: Register existing bundle
    Given I am at homepage
      And I am logged in as "l3l0"
      And I go to "/add"
     When I fill in "bundle" with "KnpLabs/TestBundle"
      And I press "Add Symfony2 bundle"
     Then I should be on "KnpLabs/TestBundle" bundle page
      And I should see "TestBundle"
      And I should see "by KnpLabs"

Feature: Recommend bundles
  As a logged in user
  I want to be able to manage recommendations

  Background:
    Given the site has following users:
    | name      |
    | l3l0      |
    | KnpLabs   |
    | FoS       |
    Given the site has following bundles:
    | username  | name       | description | lastCommitAt | score | trend1 | recommendedBy |
    | KnpLabs   | TestBundle | test desc   |-1 day        | 10    | 15     | KnpLabs, FoS  |
    | FoS       | UserBundle | user desc   |-2 days       | 20    | 5      | l3l0          |

  Scenario: Show recommended bundle
    Given I am at homepage
      And I am logged in as "l3l0"
     When I go to "/FoS/UserBundle"
     Then I should not see recommend button

  Scenario: Show not recommended bundle
    Given I am at homepage
      And I am logged in as "l3l0"
     When I go to "/KnpLabs/TestBundle"
     Then I should see recommend button

  Scenario: Recommended bundle
    Given I am at homepage
      And I am logged in as "l3l0"
     When I go to "/KnpLabs/TestBundle"
      And I should see recommend button
     Then I follow "I recommend this bundle"
      And I should be on "/KnpLabs/TestBundle"
      And I should not see recommend button

  Scenario: Not recommended bundle
    Given I am at homepage
      And I am logged in as "l3l0"
     When I go to "/FoS/UserBundle"
      And I should not see recommend button
     Then I follow "I don't recommend this bundle"
      And I should be on "/FoS/UserBundle"
      And I should see recommend button

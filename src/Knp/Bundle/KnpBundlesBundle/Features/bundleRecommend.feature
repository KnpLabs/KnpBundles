#todo

Feature: Recommend bundles
  As a logged in user
  I want to be able to manage my bundles

  Background:
    Given the site has following users:
    | name      |
    | KnpLabs   |
    | FoS       |
    Given the site has following bundles:
    | username  | name       | description | lastCommitAt | score | trend1 |
    | KnpLabs   | TestBundle | test desc   |-1 day        | 10    | 15     |
    | FoS       | UserBundle | user desc   |-2 days       | 20    | 5      |


  Scenario: Show own bundle
    When I am logged in as "KnpLabs"
    And I go to "/KnpLabs/TestBundle"
    Then I should see don't recommend button
  
  Scenario: Show someone else bundle
    When I am logged in as "KnpLabs"
    And I go to "/FoD/UserBundle"
    Then I should see recommend button

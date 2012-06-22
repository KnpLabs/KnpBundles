Feature: Recommend bundles
  As a logged in user
  I want to be able to manage my bundles

  Background:
    Given the site has following users:
    | name      |
    | KnpLabs   |
    | FoS       |
    Given the site has following bundles:
    | username  | name       | description | lastCommitAt | score | trend1 | recommendedBy |
    | KnpLabs   | TestBundle | test desc   |-1 day        | 10    | 15     | KnpLabs, FoS  |
    | FoS       | UserBundle | user desc   |-2 days       | 20    | 5      | FoS           |

  Scenario: Show recommended bundle
    #When I am logged in as "KnpLabs"
    #And I go to "/KnpLabs/TestBundle"
    #Then I should see don't recommend button

  Scenario: Show not recommended bundle
    #When I am logged in as "KnpLabs"
    #And I go to "/FoS/UserBundle"
    #Then I should see recommend button

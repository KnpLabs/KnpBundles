Feature: Listing bundle feed
  As a Visitor
  I want to browse lastest bundle throught feed client

  Background:
    Given the site has following users:
    | name      |
    | KnpLabs   |
    | FoS       |
    Given the site has following bundles:
    | username  | name       | description | lastCommitAt | score | trend1 |
    | KnpLabs   | TestBundle | test desc   |-1 day        | 10    | 15     |
    | FoS       | UserBundle | user desc   |-2 days       | 20    | 5      |


  Scenario: Listing lastest bundles
    When I go to "/latest?format=atom"
    Then I should see "KnpLabs/TestBundle" atom entry
    And I should see "FoS/UserBundle" atom entry

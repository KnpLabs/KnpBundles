Feature: Listing and searching bundles by keywords
  As a Visitor
  I want to list or search bundles by keywords

  Background:
    Given the site has following users:
    | name        |
    | knplabs     |
    | fos         |
    Given the site has following bundles:
    | username    | name         | description | lastCommitAt | score | trend1 |
    | knplabs     | TestBundle   | test desc   |-1 day        | 10    | 15     |
    | knplabs     | Test2Bundle  | test desc   |-1 day        | 10    | 15     |
    | fos         | UserBundle   | user desc   |-2 days       | 20    | 5      |
    Given the bundles have following keywords:
    | bundle      | keyword      |
    | TestBundle  | test         |
    | Test2Bundle | test         |
    | Test2Bundle | unique       |
    | UserBundle  | user         |
    | UserBundle  | user login   |
    
  Scenario: List bundles by keyword
    When I go to "/keyword/test"
    Then I should see "2 bundles"
    And I should see "TestBundle"
    And I should see "Test2Bundle"
    And I should not see "UserBundle"
    
  Scenario: List one bundle by unique keyword
    When I go to "/keyword/unique"
    Then I should see "1 bundle"
    And I should see "Test2Bundle"
    And I should not see "UserBundle"
    And I should not see "TestBundle"
    
  Scenario: List bundles by complex keyword
    When I go to "/keyword/user-login"
    Then I should see "1 bundle"
    And I should see "UserBundle"
    And I should not see "TestBundle"
    And I should not see "Test2Bundle"
    
  Scenario: List bundles by invalid keyword
    When I go to "/keyword/foo"
    Then I should see "0 bundles"
    And I should not see "UserBundle"
    And I should not see "TestBundle"
    And I should not see "Test2Bundle"
    
  Scenario: Search one bundle with unique keyword
     When I go to "/"
     And I search for "unique"
     Then I should see "1 Bundle"
     And I should see "Test2Bundle" 

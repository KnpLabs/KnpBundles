Feature: Listing developers
  As a Visitor
  I want to browse through developers list

  Background:
    Given the site has following users:
    | name       |
    | cordoval1  |
    | cordoval2  |
    | cordoval3  |
    | cordoval4  |
    | cordoval5  |
    | cordoval6  |
    | cordoval7  |
    | cordoval8  |
    | cordoval9  |
    | cordoval10 |
    | cordoval11 |
    | cordoval12 |
    | cordoval13 |
    | cordoval14 |    
    Given the site has following bundles:
    | username   | name       | description | lastCommitAt | score | trend1 |
    | cordoval1  | Test1Bundle | test desc  |-1 day        | 10    | 15     |
    | cordoval2  | User1Bundle | user desc  |-2 days       | 20    | 5      |
    | cordoval3  | Test2Bundle | test desc  |-1 day        | 10    | 15     |
    | cordoval4  | User2Bundle | user desc  |-2 days       | 20    | 5      |
    | cordoval5  | Test3Bundle | test desc  |-1 day        | 10    | 15     |
    | cordoval6  | User3Bundle | user desc  |-2 days       | 20    | 5      |
    | cordoval7  | Test4Bundle | test desc  |-1 day        | 10    | 15     |
    | cordoval8  | User4Bundle | user desc  |-2 days       | 20    | 5      |
    | cordoval9  | Test5Bundle | test desc  |-1 day        | 10    | 15     |
    | cordoval10 | User5Bundle | user desc  |-2 days       | 20    | 5      |
    | cordoval11 | Test6Bundle | test desc  |-1 day        | 10    | 15     |
    | cordoval12 | User7Bundle | user desc  |-2 days       | 20    | 5      |
    | cordoval13 | User7Bundle | user desc  |-2 days       | 20    | 5      |
    | cordoval14 | User7Bundle | user desc  |-2 days       | 20    | 5      |
  Scenario: API Lists paginated list of developers
    When I send a GET request to "/developer?page=1&format=json"
    Then the json response should contain the following items:
    | count | key  | value      |
    | 1     | name | cordoval1  |
    | 1     | name | cordoval10 |
    | 1     | name | cordoval11 |
    | 1     | name | cordoval12 |
    | 1     | name | cordoval13 |
    | 1     | name | cordoval14 |
    | 1     | name | cordoval2  |
    | 1     | name | cordoval3  |
    | 1     | name | cordoval4  |
    | 1     | name | cordoval5 |
    When I send a GET request to "/developer?page=2&format=json"
    Then the json response should contain the following items:
    | count | key  | value      |
    | 1     | name | cordoval6 |
    | 1     | name | cordoval7 |
    | 1     | name | cordoval8 |
    | 1     | name | cordoval9 |
Feature:
  As a API user
  I want to get developers list
  in order to give them a hugs for great work

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
      | cordoval15 |
      | cordoval16 |
      | cordoval17 |
      | cordoval18 |
      | cordoval19 |
      | cordoval20 |
    Given the site has following bundles:
      | username   | name        | description | lastCommitAt | score | trend1 |
      | cordoval1  | Test1Bundle | test desc   |-1 day        | 10    | 15     |
      | cordoval2  | User1Bundle | user desc   |-2 days       | 20    | 5      |
      | cordoval3  | Test2Bundle | test desc   |-1 day        | 10    | 15     |
      | cordoval4  | User2Bundle | user desc   |-2 days       | 20    | 5      |
      | cordoval5  | Test3Bundle | test desc   |-1 day        | 10    | 15     |
      | cordoval6  | User3Bundle | user desc   |-2 days       | 20    | 5      |
      | cordoval7  | Test4Bundle | test desc   |-1 day        | 10    | 15     |
      | cordoval8  | User4Bundle | user desc   |-2 days       | 20    | 5      |
      | cordoval9  | Test5Bundle | test desc   |-1 day        | 10    | 15     |
      | cordoval10 | User5Bundle | user desc   |-2 days       | 20    | 5      |
      | cordoval11 | Test6Bundle | test desc   |-1 day        | 10    | 15     |
      | cordoval12 | User6Bundle | user desc   |-2 days       | 20    | 5      |
      | cordoval13 | Test7Bundle | test desc   |-2 days       | 20    | 5      |
      | cordoval14 | User7Bundle | user desc   |-2 days       | 20    | 5      |
      | cordoval15 | Test8Bundle | test desc   |-2 days       | 20    | 5      |
      | cordoval16 | User8Bundle | user desc   |-1 day        | 10    | 15     |
      | cordoval17 | Test9Bundle | test desc   |-2 days       | 20    | 5      |
      | cordoval18 | User9Bundle | user desc   |-2 days       | 20    | 5      |
      | cordoval19 | Test7Bundle | test desc   |-2 days       | 20    | 5      |
      | cordoval20 | User7Bundle | user desc   |-2 days       | 20    | 5      |

  Scenario: Show first page of developers list
    When I send a GET request to "/developer?page=1&format=json"
    Then I should get JSON with following items:
      | name       |
      | cordoval1  |
      | cordoval10 |
      | cordoval11 |
      | cordoval12 |
      | cordoval13 |
      | cordoval14 |
      | cordoval15 |
      | cordoval16 |
      | cordoval17 |
      | cordoval18 |
      | cordoval19 |
      | cordoval2  |
      | cordoval20 |
      | cordoval3  |
      | cordoval4  |
      | cordoval5  |
      | cordoval6  |
      | cordoval7  |

  Scenario: Show second page of developers list
    When I send a GET request to "/developer?page=2&format=json"
    Then I should get JSON with following items:
      | name       |
      | cordoval8  |
      | cordoval9  |

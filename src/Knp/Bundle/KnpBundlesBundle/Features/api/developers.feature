@api
Feature:
  As a API user
  I want to get developers list
  In order to give them a hugs for great work

  Background:
    Given the site has following users:
      | name       | score |
      | cordoval1  | 10    |
      | cordoval2  | 20    |
      | cordoval3  | 50    |
    Given the site has following bundles:
      | username   | name        | description | lastCommitAt | score | trend1 |
      | cordoval1  | Test1Bundle | test desc   | 2012-10-01   | 10    | 15     |
      | cordoval2  | User1Bundle | user desc   | 2012-10-05   | 20    | 5      |
      | cordoval3  | Test2Bundle | test desc   | 2012-10-10   | 50    | 15     |

  Scenario: Show first page of developers list sort by name
    When I send a GET request to "/developer/name.json?page=1&limit=2"
    Then response is successful
     And the response should contain json:
      """
      {
        "results": [
          {
            "name": "cordoval1",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "company": null,
            "location": null,
            "blog": null,
            "lastCommitAt": null,
            "score": 10,
            "url": "%base_url%/developer/cordoval1/profile"
          },
          {
            "name": "cordoval2",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "company": null,
            "location": null,
            "blog": null,
            "lastCommitAt": null,
            "score": 20,
            "url": "%base_url%/developer/cordoval2/profile"
          }
        ],
        "total": 3,
        "next": "%base_url%/developer/name.json?page=2&limit=2"
      }
      """

  Scenario: Show second page of developers list sort by name
    When I send a GET request to "/developer.json?page=2&limit=2"
    Then response is successful
     And the response should contain json:
     """
      {
        "results": [
          {
            "name": "cordoval3",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "company": null,
            "location": null,
            "blog": null,
            "lastCommitAt": null,
            "score": 50,
            "url": "%base_url%/developer/cordoval3/profile"
          }
        ],
        "total": 3,
        "prev": "%base_url%/developer/name.json?page=1&limit=2"
      }
      """

  Scenario: Show first page of developers list sort by best score
    When I send a GET request to "/developer/best.json?page=1&limit=2"
    Then response is successful
     And the response should contain json:
      """
      {
        "results": [
          {
            "name": "cordoval3",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "company": null,
            "location": null,
            "blog": null,
            "lastCommitAt": null,
            "score": 50,
            "url": "%base_url%/developer/cordoval3/profile"
          },
          {
            "name": "cordoval2",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "company": null,
            "location": null,
            "blog": null,
            "lastCommitAt": null,
            "score": 20,
            "url": "%base_url%/developer/cordoval2/profile"
          }
        ],
        "total": 3,
        "next": "%base_url%/developer/best.json?page=2&limit=2"
      }
      """

  Scenario: Show developer profile
    When I send a GET request to "/developer/cordoval1/profile.json"
    Then response is successful
     And the response should contain json:
      """
      {
        "name": "cordoval1",
        "email": null,
        "avatarUrl": null,
        "fullName": null,
        "company": null,
        "location": null,
        "blog": null,
        "bundles": [
          {
            "name": "cordoval1/Test1Bundle",
            "state": "unknown",
            "score": 10,
            "url": "%base_url%/cordoval1/Test1Bundle"
          }
        ],
        "lastCommitAt": null,
        "score": 10
      }
      """

  Scenario: Show developer bundles data
    When I send a GET request to "/developer/cordoval1/bundles.json"
    Then response is successful
     And the response should contain json:
      """
      {
        "developer": "cordoval1",
        "bundles": [
          {
            "name": "cordoval1/Test1Bundle",
            "state": "unknown",
            "score": 10,
            "url": "%base_url%/cordoval1/Test1Bundle"
          }
        ]
      }
      """

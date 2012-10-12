@api
Feature:
  As a API user
  I want to get bundle list
  In order to find interesting ones

  Background:
    Given the site has following users:
      | name      |
      | KnpLabs   |
      | FoS       |
    Given the site has following bundles:
      | username  | name        | description | createdAt  | lastCommitAt | score | trend1 | state      |
      | KnpLabs   | TestBundle  | test desc   | 2012-09-01 | 2012-10-01   | 20    | 5      | ready      |
      | KnpLabs   | TestBundle2 | test2 desc  | 2012-09-05 | 2012-10-05   | 10    | 10     | unknown    |
      | FoS       | UserBundle  | user desc   | 2012-09-10 | 2012-10-10   | 50    | 1      | deprecated |

  Scenario: Show first page of latest bundles list
    When I send a GET request to "/newest.json?page=1&limit=2"
    Then response is successful
     And the response should contain json:
      """
      {
        "results": [
          {
            "type": "Bundle",
            "name": "UserBundle",
            "ownerName": "FoS",
            "description": "user desc",
            "homepage": null,
            "score": 50,
            "nbFollowers": 0,
            "nbForks": 0,
            "createdAt": 1347235200,
            "lastCommitAt": 1349827200,
            "contributors": [],
            "url": "%base_url%/FoS/UserBundle"
          },
          {
            "type": "Bundle",
            "name": "TestBundle2",
            "ownerName": "KnpLabs",
            "description": "test2 desc",
            "homepage": null,
            "score": 10,
            "nbFollowers": 0,
            "nbForks": 0,
            "createdAt": 1346803200,
            "lastCommitAt": 1349395200,
            "contributors": [],
            "url": "%base_url%/KnpLabs/TestBundle2"
          }
        ],
        "total": 3,
        "next": "%base_url%/newest.json?page=2&limit=2"
      }
      """

  Scenario: Show second page of latest bundles list
    When I send a GET request to "/newest.json?page=2&limit=2"
    Then response is successful
     And the response should contain json:
      """
      {
        "results": [
          {
            "type": "Bundle",
            "name": "TestBundle",
            "ownerName": "KnpLabs",
            "description": "test desc",
            "homepage": null,
            "score": 20,
            "nbFollowers": 0,
            "nbForks": 0,
            "createdAt": 1346457600,
            "lastCommitAt": 1349049600,
            "contributors": [],
            "url": "%base_url%/KnpLabs/TestBundle"
          }
        ],
        "total": 3,
        "prev": "%base_url%/newest.json?page=1&limit=2"
      }
      """

  Scenario: Show bundle data
    When I send a GET request to "/KnpLabs/TestBundle.json"
    Then response is successful
     And the response should contain json:
      """
      {
        "type": "Bundle",
        "name": "TestBundle",
        "ownerName": "KnpLabs",
        "description": "test desc",
        "homepage": null,
        "score": 20,
        "nbFollowers": 0,
        "nbForks": 0,
        "createdAt": 1346457600,
        "lastCommitAt": 1349049600,
        "contributors": [],
        "readme": null
      }
      """

@api
Feature:
  As a API user
  I want to get organizations list
  In order to give them a hugs for great work

  Background:
    Given the site has following organizations:
      | name      |
      | knplabs1  |
      | knplabs2  |
      | knplabs3  |
    Given the site has following bundles:
      | username  | name        | description | lastCommitAt | score | trend1 |
      | knplabs1  | Test1Bundle | test desc   |-1 day        | 10    | 15     |
      | knplabs3  | User1Bundle | user desc   |-2 days       | 20    | 5      |
      | knplabs1  | Test2Bundle | test desc   |-1 day        | 10    | 15     |

  Scenario: Show first page of organizations list sort by name
    When I send a GET request to "/organization/name.json?page=1&limit=2"
    Then response is successful
     And the response should contain json:
      """
      {
        "results": [
          {
            "name": "knplabs1",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "%base_url%/organization/knplabs1/profile"
          },
          {
            "name": "knplabs2",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "%base_url%/organization/knplabs2/profile"
          }
        ],
        "total": 3,
        "next": "%base_url%/organization/name.json?page=2&limit=2"
      }
      """

  Scenario: Show second page of organizations list sort by name
    When I send a GET request to "/organization/name.json?page=2&limit=2"
    Then response is successful
     And the response should contain json:
      """
      {
        "results": [
          {
            "name": "knplabs3",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "%base_url%/organization/knplabs3/profile"
          }
        ],
        "total": 3,
        "prev": "%base_url%/organization/name.json?page=1&limit=2"
      }
      """

  Scenario: Show first page of organizations list sort by number of bundles
    When I send a GET request to "/organization/bundles.json?page=1&limit=2"
    Then response is successful
     And the response should contain json:
      """
      {
        "results": [
          {
            "name": "knplabs1",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "%base_url%/organization/knplabs1/profile"
          },
          {
            "name": "knplabs3",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "%base_url%/organization/knplabs3/profile"
          }
        ],
        "total": 3,
        "next": "%base_url%/organization/bundles.json?page=2&limit=2"
      }
      """

  Scenario: Show organization data
    When I send a GET request to "/organization/knplabs1/profile.json"
    Then response is successful
     And the response should contain json:
      """
      {
        "name": "knplabs1",
        "email": null,
        "avatarUrl": null,
        "fullName": null,
        "location": null,
        "blog": null,
        "score": 0
      }
      """

  Scenario: Show organization bundles data
    When I send a GET request to "/organization/knplabs1/bundles.json"
    Then response is successful
     And the response should contain json:
      """
      {
        "organization": "knplabs1",
        "bundles": [
          {
            "name": "knplabs1/Test1Bundle",
            "state": "unknown",
            "score": 10,
            "url": "%base_url%/knplabs1/Test1Bundle"
          },
          {
            "name": "knplabs1/Test2Bundle",
            "state": "unknown",
            "score": 10,
            "url": "%base_url%/knplabs1/Test2Bundle"
          }
        ]
      }
      """

  Scenario: Show organization members data
    When I send a GET request to "/organization/knplabs1/members.json"
    Then response is successful
     And the response should contain json:
      """
      {
        "organization": "knplabs1",
        "members": {}
      }
      """

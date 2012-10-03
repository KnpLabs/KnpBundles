Feature:
  As a API user
  I want to get organizations list
  in order to give them a hugs for great work

  Background:
    Given the site has following organizations:
      | name      |
      | knplabs1  |
      | knplabs2  |
      | knplabs3  |
      | knplabs4  |
      | knplabs5  |
      | knplabs6  |
      | knplabs7  |
      | knplabs8  |
      | knplabs9  |
      | knplabs10 |
      | knplabs11 |
      | knplabs12 |
      | knplabs13 |
      | knplabs14 |
      | knplabs15 |
      | knplabs16 |
      | knplabs17 |
      | knplabs18 |
      | knplabs19 |
      | knplabs20 |
    Given the site has following bundles:
      | username  | name        | description | lastCommitAt | score | trend1 |
      | knplabs1  | Test1Bundle | test desc   |-1 day        | 10    | 15     |
      | knplabs2  | User1Bundle | user desc   |-2 days       | 20    | 5      |
      | knplabs3  | Test2Bundle | test desc   |-1 day        | 10    | 15     |
      | knplabs4  | User2Bundle | user desc   |-2 days       | 20    | 5      |
      | knplabs5  | Test3Bundle | test desc   |-1 day        | 10    | 15     |
      | knplabs6  | User3Bundle | user desc   |-2 days       | 20    | 5      |
      | knplabs7  | Test4Bundle | test desc   |-1 day        | 10    | 15     |
      | knplabs8  | User4Bundle | user desc   |-2 days       | 20    | 5      |
      | knplabs9  | Test5Bundle | test desc   |-1 day        | 10    | 15     |
      | knplabs10 | User5Bundle | user desc   |-2 days       | 20    | 5      |
      | knplabs11 | Test6Bundle | test desc   |-1 day        | 10    | 15     |
      | knplabs12 | User6Bundle | user desc   |-2 days       | 20    | 5      |
      | knplabs13 | Test7Bundle | test desc   |-2 days       | 20    | 5      |
      | knplabs14 | User7Bundle | user desc   |-2 days       | 20    | 5      |
      | knplabs15 | Test8Bundle | test desc   |-2 days       | 20    | 5      |
      | knplabs16 | User8Bundle | user desc   |-1 day        | 10    | 15     |
      | knplabs17 | Test9Bundle | test desc   |-2 days       | 20    | 5      |
      | knplabs18 | User9Bundle | user desc   |-2 days       | 20    | 5      |
      | knplabs19 | Test7Bundle | test desc   |-2 days       | 20    | 5      |
      | knplabs20 | User7Bundle | user desc   |-2 days       | 20    | 5      |

  Scenario: Show first page of organizations list
    When I send a GET request to "/organization.json?page=1"
    Then the response code should be 200
     And the response should equal to JSON:
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
            "url": "http://knpbundles.local/organization/knplabs1/profile"
          },
          {
            "name": "knplabs10",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs10/profile"
          },
          {
            "name": "knplabs11",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs11/profile"
          },
          {
            "name": "knplabs12",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs12/profile"
          },
          {
            "name": "knplabs13",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs13/profile"
          },
          {
            "name": "knplabs14",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs14/profile"
          },
          {
            "name": "knplabs15",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs15/profile"
          },
          {
            "name": "knplabs16",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs16/profile"
          },
          {
            "name": "knplabs17",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs17/profile"
          },
          {
            "name": "knplabs18",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs18/profile"
          },
          {
            "name": "knplabs19",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs19/profile"
          },
          {
            "name": "knplabs2",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs2/profile"
          },
          {
            "name": "knplabs20",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs20/profile"
          },
          {
            "name": "knplabs3",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs3/profile"
          },
          {
            "name": "knplabs4",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs4/profile"
          },
          {
            "name": "knplabs5",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs5/profile"
          },
          {
            "name": "knplabs6",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs6/profile"
          },
          {
            "name": "knplabs7",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs7/profile"
          }
        ],
        "total": 20,
        "next": "http://knpbundles.local/organization/name.json?page=2"
      }
      """

  Scenario: Show second page of organizations list
    When I send a GET request to "/organization.json?page=2"
    Then the response code should be 200
     And the response should equal to JSON:
      """
      {
        "results": [
          {
            "name": "knplabs8",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs8/profile"
          },
          {
            "name": "knplabs9",
            "email": null,
            "avatarUrl": null,
            "fullName": null,
            "location": null,
            "blog": null,
            "score": 0,
            "url": "http://knpbundles.local/organization/knplabs9/profile"
          }
        ],
        "total": 20,
        "prev": "http://knpbundles.local/organization/name.json?page=1"
      }
      """

  Scenario: Show organization data
    When I send a GET request to "/organization/knplabs1/profile.json"
    Then the response code should be 200
     And the response should equal to JSON:
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
    Then the response code should be 200
     And the response should equal to JSON:
      """
      {
        "organization": "knplabs1",
        "bundles": [
          {
            "name": "knplabs1/Test1Bundle",
            "state": "unknown",
            "score": 10,
            "url": "http://knpbundles.local/knplabs1/Test1Bundle"
          }
        ]
      }
      """

  Scenario: Show organization members data
    When I send a GET request to "/organization/knplabs1/members.json"
    Then the response code should be 200
     And the response should equal to JSON:
      """
      {
        "organization": "knplabs1",
        "members": {}
      }
      """

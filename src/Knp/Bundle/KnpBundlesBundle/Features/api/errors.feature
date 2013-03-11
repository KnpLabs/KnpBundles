@api
Feature:
  As a API user
  I want to get some data, but I do it wrong
  In order broke something

  Scenario: Show some non-existsting bundle
    When I send a GET request to "/KnpLabs/UnknownBundle.json"
    Then the response status code should be 404
     And the response should contain json:
      """
      {
        "status": "error",
        "message": "Bundle not found."
      }
      """

  Scenario: Show some non-existing developer
    When I send a GET request to "/developer/Unknown/profile.json"
    Then the response status code should be 404
     And the response should contain json:
      """
      {
        "status": "error",
        "message": "Developer not found."
      }
      """

  Scenario: Show some non-existing organization
    When I send a GET request to "/organization/Unknown/profile.json"
    Then the response status code should be 404
     And the response should contain json:
      """
      {
        "status": "error",
        "message": "Organization not found."
      }
      """

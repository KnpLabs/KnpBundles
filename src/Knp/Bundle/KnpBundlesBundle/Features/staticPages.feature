Feature: Showing static page
  As a Visitor
  I want to browse informational pages

  Scenario: About scoring page
    When I go to "/about/faq-scoring"
    Then response is successful
    And I should see "Frequent answers to questions regarding bundle scoring issues"

  Scenario: Api page
    When I go to "/api"
    Then response is successful
    And I should see "Developer API"
    And I should see "Everything here is available through an HTTP API"

  Scenario: Symfony2Bundles page
    When I go to "/symfony2bundles"
    Then response is successful
    And I should see "Symfony2bundles becomes KnpBundles"

@solr
Feature: Searching bundles
  As a Visitor
  I want to search bundles

  Background:
    Given the site has following users:
    | name      |
    | knplabs   |
    | fos       |
    | tos       |
    And the site has following bundles:
    | username  | name                      | description | lastCommitAt | score | trend1 |
    | knplabs   | TestBundle                | test desc   |-1 day        | 10    | 15     |
    | knplabs   | Test2Bundle               | test desc   |-1 day        | 10    | 15     |
    | tos       | FOSUserBundle             | user desc   |-2 days       | 20    | 5      |
    | fos       | FOSTwitterBundle          | user desc   |-2 days       | 20    | 5      |
    | fos       | FOSJsRoutingBundle        | user desc   |-2 days       | 20    | 5      |
    | fos       | FOSFacebookBundle         | user desc   |-2 days       | 20    | 5      |
    | fos       | FOSAdvancedEncoderBundle  | user desc   |-2 days       | 20    | 5      |
    And bundles are indexed

  Scenario: Searching some bundles
    When I go to "/"
    And I search for "Test"
    Then I should see "Found 2 bundles"
    And I should see "TestBundle"
    And I should see "Test2Bundle"
    And I should not see "UserBundle"

  Scenario: Search one bundle
    When I go to "/"
    And I search for "User"
    Then I should see "Found 5 bundles"
    And I should see "UserBundle"

  Scenario: Search one bundle with exact name
    When I go to "/"
    And I search for "FOSUserBundle"
    Then I should be on "tos/FOSUserBundle" bundle page

  Scenario: Searching some bundles from description
    When I go to "/"
    And I search for "desc"
    Then I should see "Found 7 bundles"
    And I should see "TestBundle"
    And I should see "Test2Bundle"
    And I should see "UserBundle"

  Scenario: Search nothing
    When I go to "/"
    And I search for ""
    Then I should be on "/search"
    And I should see "Please use the search input at the top right."

  Scenario: Search and not find
    When I go to "/"
    And I search for "lorem"
    Then I should be on "/search"
    And I should see "Looking for keyword \"lorem\""
    And I should see "No bundles found"

  Scenario: Searching by partial name is rather search by author
    When I go to "/"
    And I search for "FOS"
    Then I should see "Found 5 bundles"
    And I should see "FOSTwitterBundle"
    And I should see "FOSJsRoutingBundle"
    And I should see "FOSFacebookBundle"
    And I should see "FOSAdvancedEncoderBundle"
    And I should see "FOSUserBundle"

  Scenario: Searching by partial name
    When I go to "/"
    And I search for "Twitter"
    Then I should see "Found 1 bundle"
    And I should see "FOSTwitterBundle"

  Scenario: Searching by partial name but partial name is too short
    When I go to "/"
    And I search for "f"
    Then I should be on "/search"
    And I should see "Looking for keyword \"f\""
    And I should see "No bundles found"

  Scenario: Searching by partial name but partial name is too long
    When I go to "/"
    And I search for "FOSTwitterBootstrapLongAndSuperLongNameForABundleIsJustTooMuchBundle"
    Then I should be on "/search"
    And I should see "Looking for keyword \"FOSTwitterBootstrapLongAndSuperLongNameForABundleIsJustTooMuchBundle\""
    And I should see "No bundles found"

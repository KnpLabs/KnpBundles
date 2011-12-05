Feature: Listing bundles
  As a Visitor
  I want to browse trough bundle list

  Background:
    Given the site has following users:
    | name      |
    | knplabs   |
    | fos       |
    Given the site has following bundles:
    | user_name | name       | description | lastCommitAt | score | trend1 |
    | knplabs   | TestBundle | test desc   |-1 day        | 10    | 15     |
    | fos       | UserBundle | user desc   |-2 days       | 20    | 5      |

  Scenario: Listing bundles
    When I go to "/"
    Then I should see "2 bundles"
    And I should be able to find an element ".bundle" with following texts:
      | TestBundle       |
      | test desc        |
      | by knplabs       |
      | commit 1 day ago |
      | 10               |
    And I should be able to find an element ".bundle" with following texts:
      | UserBundle        |
      | user desc         |
      | by fos            |
      | commit 2 days ago |
      | 20                |

  Scenario: Listing trending bundles
    When I go to "/"
    When I follow "Trending"
    And I should see following texts in order:
      | TestBundle |
      | UserBundle |

  Scenario: Listing best bundles
    When I go to "/"
    When I follow "Best score"
    And I should see following texts in order:
      | UserBundle |
      | TestBundle |

  Scenario: Listing updated recently bundles
    When I go to "/"
    When I follow "Updated recently"
    And I should see following texts in order:
      | TestBundle |
      | UserBundle |

Feature: Listing bundles
  As a Visitor
  I want to browse trough bundle list

  Background:
    Given the site has following users:
      | name      |
      | knplabs   |
      | fos       |
    Given the site has following bundles:
      | username  | name        | description | lastCommitAt | score | trend1 | state      | license |
      | knplabs   | TestBundle  | test desc   |-1 day        | 20    | 5      | ready      | MIT     |
      | knplabs   | TestBundle2 | test2 desc  |-3 days       | 10    | 10     | unknown    |         |
      | fos       | UserBundle  | user desc   |-2 days       | 50    | 1      | deprecated | GPL     |

  Scenario: Listing bundles
    When I go to "/"
    Then I should see "3 bundles"
     And I should be able to find a bundle row with following texts:
      | TestBundle      |
      | test desc       |
      | owner: knplabs  |
      | activity High   |
      | 20              |
      | ready           |
      | license: MIT    |
     And I should be able to find a bundle row with following texts:
      | UserBundle      |
      | user desc       |
      | owner: fos      |
      | activity High   |
      | 50              |
      | deprecated      |
      | license: GPL    |
     And I should be able to find a bundle row with following texts:
      | TestBundle2     |
      | test2 desc      |
      | owner: knplabs  |
      | activity High   |
      | 10              |

  Scenario: Listing trending bundles
    When I go to "/"
     And I follow "Trending"
    Then I should see following texts in order:
      | TestBundle2 |
      | TestBundle  |
      | UserBundle  |

  Scenario: Listing best bundles
    When I go to "/"
     And I follow "Best score"
    Then I should see following texts in order:
      | UserBundle  |
      | TestBundle  |
      | TestBundle2 |

  Scenario: Listing updated recently bundles
    When I go to "/"
     And I follow "Updated recently"
    Then I should see following texts in order:
      | TestBundle  |
      | UserBundle  |
      | TestBundle2 |

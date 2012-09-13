@organizations
Feature: Show organization page
    As a Visitor
    I want to see detailed info about organization

    Background:
        Given the site has following organizations:
            | name             |
            | KnpLabs          |
            | FriendsOfSymfony |
        Given the site has following bundles:
            | username         | name       | description | lastCommitAt | score | trend1 |
            | KnpLabs          | TestBundle | test desc   | -1 day       | 10    | 15     |
            | FriendsOfSymfony | UserBundle | user desc   | -2 days      | 20    | 5      |
        Given the site has following users:
            | name        | organization     |
            | cursedcoder | KnpLabs          |
            | stof        | FriendsOfSymfony |

    Scenario: Show organization page
        When I go to "/organization"
        And I follow "KnpLabs"
        Then response is successful
        And I should see that "TestBundle" is managed by organization

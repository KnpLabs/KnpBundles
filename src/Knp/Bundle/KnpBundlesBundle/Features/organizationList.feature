@organizations
Feature: Listing organizations
    As a Visitor
    I want to browse through organizations list

    Background:
        Given the site has following organizations:
        | name             |
        | KnpLabs          |
        | FriendsOfSymfony |

    Scenario: Listing organizations
        When I go to "/"
        And I follow "Organizations"
        Then I should see "2 organizations using Symfony2"
        And I should see "KnpLabs" organization
        And I should see "FriendsOfSymfony" organization

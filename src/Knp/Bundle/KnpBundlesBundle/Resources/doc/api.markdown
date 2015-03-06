## Overview

All API access is over HTTP. All responses are returned as `JSON` (with `Content-Type` set to `application/json`).
The API is **not** RESTful and only `GET` request are accepted.

### Bundles

#### List all bundles

To get the list of bundles sorted by a given field, use:

    # the bundles with best score first
    $ curl http://knpbundles.com/best.json

    # the newest bundles first
    $ curl http://knpbundles.com/newest.json

    # the bundles updated recently first
    $ curl http://knpbundles.com/updated.json

    # the most trending bundles first
    $ curl http://knpbundles.com/trending.json

    # the most recommended bundles first
    $ curl http://knpbundles.com/recommended.json

Return a list of bundles:

    {
        "results": [
            {
                "type": "Bundle",
                "name": "FOSJsRoutingBundle",
                "ownerName": "FriendsOfSymfony",
                "description": "A pretty nice way to expose your Symfony2 routing to client applications.",
                "homepage": null,
                "score": 309,
                "nbFollowers": 214,
                "nbForks": 41,
                "createdAt": 1305230511,
                "lastCommitAt": 1346364000,
                "contributors": [
                    "mazen"
                ],
                "url": "http://knpbundles.com/FriendsOfSymfony/FOSJsRoutingBundle"
            }
        ],
        "total": 1670,
        "next": "http://knpbundles.com/best.json?page=2"
    }

#### Show one Bundle

When requesting only one Bundle, you get more informations such as last commits, readme.

    :username/:name

    $ curl http://knpbundles.com/FriendsOfSymfony/FOSUserBundle.json

Return informations about one Bundle:

    {
        "type": "Bundle",
        "name": "FOSUserBundle",
        "ownerName": "FriendsOfSymfony",
        "description": "Provides user management for your Symfony2 Project. Compatible with Doctrine ORM & ODM, and Propel.",
        "homepage": "http://friendsofsymfony.github.com/",
        "score": 1468,
        "nbFollowers": 989,
        "nbForks": 359,
        "createdAt": 1293115855,
        "lastCommitAt": 1348783200,
        "contributors": [
            "stof"
        ],
        "lastCommits": [
            {
                "commit": {}
            }
        ],
        "readme": "# Symfony 2 Micro Kernel\r\n\r\nThis is a Ruby Sinatra inspired micro kernel for Symfony 2.[...]"
    }

### Search

    # search.json?q=:query

    $ curl http://knpbundles.com/search.json?q=oauth

Return a list of bundles:

    {
        "results": [
            {
                "name": "mazen/EtcpasswdOAuthBundle",
                "description": "OAuth Bundle...",
                "avatarUrl": "https://secure.gravatar.com/avatar/3d8483fd0e9b1fb92107dbfcd13722fb",
                "state": "unknown",
                "score": 56,
                "url": "http://knpbundles.com/mazen/EtcpasswdOAuthBundle"
            }
        ],
        "total": 15,
        "next": "http://knpbundles.com/best.json?page=2"
    }

### Developers

#### List developers

    $ curl http://knpbundles.com/developer.json

Return a list of developers:

    {
        "results": [
            {
                "name": "000fff",
                "email": null,
                "avatarUrl": "https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-user-420.png",
                "fullName": null,
                "company": null,
                "location": null,
                "blog": null,
                "lastCommitAt": null,
                "score": 0,
                "url": "http://knpbundles.com/developer/000fff/profile"
            }
        ],
        "total": 3850,
        "next": "http://knpbundles.com/developer/name.json?page=2"
    }

#### Show one developer

When requesting only one user, you get more informations.

    $ curl http://knpbundles.com/developer/stof/profile.json

Return informations about one developer.

    {
        "name": "stof",
        "email": null,
        "avatarUrl": "https://secure.gravatar.com/avatar/7894bbdf1c05b18a1444ad8c76c9d583",
        "fullName": "Christophe Coevoet",
        "company": null,
        "location": null,
        "blog": null,
        "bundles": [
            {
                "name": "stof/StofDoctrineExtensionsBundle",
                "state": "ready",
                "score": 545,
                "url": "http://knpbundles.com/stof/StofDoctrineExtensionsBundle"
            }
        ],
        "lastCommitAt": null,
        "score": 223
    }

#### List the bundles of a developer

Get a list of the bundles a given developer owns.

    $ curl http://knpbundles.com/developer/stof/bundles.json

Return a list of bundles:

    {
        "developer": "stof",
        "bundles": [
            {
                "name": "stof/StofDoctrineExtensionsBundle",
                "state": "ready",
                "score": 545,
                "url": "http://knpbundles.com/stof/StofDoctrineExtensionsBundle"
            }
        ]
    }

### Organizations

#### List organizations

    $ curl http://knpbundles.com/organization.json

Return a list of organizations:

    {
        "results": [
            {
                "name": "0bjects",
                "email": null,
                "avatarUrl": "https://secure.gravatar.com/avatar/9666a933f6e2460d7d12fe13ba5cba4f",
                "fullName": null,
                "location": "Alexandria, Egypt",
                "blog": "http://www.objects.ws",
                "score": 0,
                "url": "http://knpbundles.com/organization/0bjects/profile"
            }
        ],
        "total": 337,
        "next": "http://knpbundles.com/organization/name.json?page=2"
    }

#### Show one organization

When requesting only one organization, you get more informations.

    $ curl http://knpbundles.com/organization/KnpLabs/profile.json

Return information's about one organization.

    {
        "name": "KnpLabs",
        "email": null,
        "avatarUrl": "https://secure.gravatar.com/avatar/cdc56f94f578a933f732cd8f163c1504",
        "fullName": null,
        "location": "Where the best developers are",
        "blog": "http://KnpLabs.com",
        "score": 802
    }

#### List the bundles of a organization

Get a list of the bundles a given organization owns.

    $ curl http://knpbundles.com/developer/stof/bundles.json

Return a list of bundles:

    {
        "organization": "KnpLabs",
        "bundles": [
            {
                "name": "KnpLabs/KnpMenuBundle",
                "state": "ready",
                "score": 501,
                "url": "http://knpbundles.com/KnpLabs/KnpMenuBundle"
            }
        ]
    }

### Errors

When bundle, developer or organization will not be found, i.e.:

    $ curl http://knpbundles.com/NotExistin/Bundle.json

Return the error response:

    {
        "status": "error",
        "message": "Bundle not found."
    }

### Pagination

API calls that return a pageable list share standard paging parameters. Paging may be limited, both in the total number of pages (`page` parameter) and the number of results per page (`limit` parameter).

Example of usage:

    $ curl http://knpbundles.com/best.json?page=10&limit=50

Return a list of bundles:

    {
        "results": [
            {
                "type": "Bundle",
                "name": "HTMLPurifierBundle",
                "ownerName": "Exercise",
                "description": "HTML Purifier is a standards-compliant    HTML filter library written in    PHP.",
                "homepage": "http://htmlpurifier.org/",
                "score": 76,
                "nbFollowers": 40,
                "nbForks": 10,
                "createdAt": 1288818582,
                "lastCommitAt": 1349301600,
                "contributors": [
                    "ornicar",
                ],
                "url": "http://knpbundles.com/Exercise/HTMLPurifierBundle"
            }
        ],
        "total": 1670,
        "prev": "http://knpbundles.com/best.json?page=9",
        "next": "http://knpbundles.com/best.json?page=11"
    }

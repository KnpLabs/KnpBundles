## Formats

As for now, the only supported formats are JSON and JavaScript (JSONP)

### JSON

Add the "?format=json" parameter at the end of the url path.
The response content-type will be "application/json".

    $ curl http://knpbundles.com/best?format=json

### JavaScript (JSONP)

Use the "js" format parameter and a "callback" parameter to wrap the data.
The response content-type will be "application/javascript".

    $ curl http://knpbundles.com/best?format=js&callback=doSomething

jQuery provides an easy way to deal with jsonp, removing the need for a callback:

    $.ajax({
        url:        "http://knpbundles.com/best?format=js",
        dataType:   "jsonp",
        success:    function(data) { ... }
    });

## Bundles

### List all Bundles

To get the list of bundles sorted by a given field, use:

    # the best bundles first
    $ curl http://knpbundles.com/best?format=json

    # the newest bundles first
    $ curl http://knpbundles.com/newest?format=json

    # the bundles updated recently first
    $ curl http://knpbundles.com/updated?format=json

Return a list of Bundles:

    - type: Bundle
      name: OplBundle
      username: eXtreme
      description: Experimental Symfony2 Bundle providing support for OPL family as well with Open Power Template 2.1 templating engine.
      homepage: http://www.jacek.jedrzejewski.name/
      score: 16
      nbFollowers: 2
      nbForks: 1
      createdAt: 1278497445
      lastCommitAt: 1278513437
      tags:
        - 1.0
        - 1.1
      contributors:
        - foo
        - bar

### Show one Bundle

When requesting only one Bundle, you get more informations such as last commits, readme and documentation.

    :username/:name

    $ curl http://knpbundles.com/avalanche123/MicroKernelBundle?format=json

Return informations about one Bundle:

    type: Bundle
    name: MicroKernelBundle
    username: avalanche123
    description: A micro kernel for Symfony 2, inspired by the Ruby Sinatra Web Framework
    homepage:
    score: 21.2
    nbFollowers: 5
    nbForks: 2
    createdAt: 1273839236
    lastCommitAt: 1278106123
    tags:
      - 1.0
      - 1.1
    lastCommits:
      ~ described at http://develop.github.com/p/commits.html
    readme: # Symfony 2 Micro Kernel\r\n\r\nThis is a Ruby Sinatra inspired micro kernel for Symfony 2.[...]

## Search

    search?format=:format&q=:query

    $ curl http://knpbundles.com/search?format=json&q=mongo

Return a list of bundles:

    - type: Bundle
      name: knpbundles
      username: knplabs
      description: Comprehensive list of Symfony2 bundles ordered by relevance and integrated with GitHub.
      homepage: http://knpbundles.com/
      score: 46
      nbFollowers: 27
      nbForks: 3
      createdAt: 1278497445
      lastCommitAt: 1278513437
      tags:
        - 1.0
      contributors:
        - ornicar

## Developers

### List developers

    $ curl http://knpbundles.com/developer/name?format=json

Return a list of developers:

    - name: ornicar
      email: thibault.duplessis@gmail.com
      fullName: Thibault Duplessis
      company: knpLabs
      location: France
      blog: http://twitter.com/ornicar
      bundles:
        - GravatarBundle

### Show one developer

When requesting only one user, you get more informations such as last commits.

    $ curl http://knpbundles.com/ornicar?format=json

Return informations about one developer.

    - name: ornicar
      email: thibault.duplessis@gmail.com
      fullName: Thibault Duplessis
      company: knpLabs
      location: France
      blog: http://twitter.com/ornicar
      bundles:
        - GravatarBundle
      lastCommitAt:
      lastCommits:
        ~ described at http://develop.github.com/p/commits.html

### List the Bundles of a developer

Get a list of the Bundles a given developer owns.

    :name/bundles

    $ curl http://knpbundles.com/knplabs/bundles?format=json

Return a list of Bundles:

    - type: Bundle
      name: OplBundle
      username: eXtreme
      description: Experimental Symfony2 Bundle providing support for OPL family as well with Open Power Template 2.1 templating engine.
      homepage: http://www.jacek.jedrzejewski.name/
      score: 16
      nbFollowers: 2
      nbForks: 1
      createdAt: 1278497445
      lastCommitAt: 1278513437
      tags:
        - 1.0
        - 1.1
      contributors:
        - foo
        - bar

Return a list of Bundles.

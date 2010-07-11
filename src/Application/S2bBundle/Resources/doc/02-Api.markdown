Everything here is available through an HTTP API.

## Formats

As for now, the only supported formats are JSON and JavaScript (JSONP)

### JSON

Add the ".json" extension at the end of the url path.
The response content-type will be "application/json".

    $ curl http://symfony2bundles.org/bundle/score.json

### JavaScript (JSONP)

Add the ".js" extension at the end of the url path, and a "callback" parameter to wrap the data.
The response content-type will be "application/javascript".

    $ curl http://symfony2bundles.org/bundle/score.js?callback=doSomething

jQuery provides an easy way to deal with jsonp, removing the need for a callback:

    $.ajax({
        url:        "http://symfony2bundles.org/bundle/score.js",
        dataType:   "jsonp",
        success:    function(data) { ... }
    });

## Bundles

### List all Bundles

You must precise a sort field. Possible values are score, name, createdAt, lastCommitAt

    bundle/:sort.:format

    $ curl http://symfony2bundles.org/bundle/score.json

Return a list of Bundles:

    - type: Bundle
      name: OplBundle
      username: eXtreme
      description: Experimental Symfony2 Bundle providing support for OPL family as well with Open Power Template 2.1 templating engine.
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

    :username/:name/.:format

    $ curl http://symfony2bundles.org/avalanche123/MicroKernelBundle.json

Return informations about one Bundle:

    type: Bundle
    name: MicroKernelBundle
    username: avalanche123
    description: A micro kernel for Symfony 2, inspired by the Ruby Sinatra Web Framework
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

## Projects

### List all Projects

You must precise a sort field. Possible values are score, name, createdAt, lastCommitAt

    project/:sort.:format

    $ curl http://symfony2bundles.org/project/score.json

Return a list of Projects:

    - type: Project
      name: symfony2bundles
      username: knplabs
      description: Comprehensive list of Symfony2 bundles ordered by relevance and integrated with GitHub.
      score: 46
      nbFollowers: 27
      nbForks: 3
      createdAt: 1278497445
      lastCommitAt: 1278513437
      tags:
        - 1.0
      contributors:
        - ornicar

### Show one Project

When requesting only one Project, you get more informations such as last commits, readme and documentation.

    :username/:name/.:format

    $ curl http://symfony2bundles.org/knplabs/symfony2bundles.json

Return informations about one Project:

    type: Project
    name: symfony2bundles
    username: knplabs
    description: Comprehensive list of Symfony2 bundles ordered by relevance and integrated with GitHub.
    score: 46
    nbFollowers: 27
    nbForks: 3
    createdAt: 1278497445
    lastCommitAt: 1278513437
    tags:
      - 1.0
    contributors:
      - ornicar
    lastCommits:
      ~ described at http://develop.github.com/p/commits.html
    readme: #symfony2bundles.org\n\nOpen-source code of the [symfony2bundles.org](http:\/\/symfony2bundles.org) website[...]

## Search 

    search.:format?q=:query

    $ curl http://symfony2bundles.org/search.json?q=mongo

Return a list of bundles and projects:

    - type: Project
      name: symfony2bundles
      username: knplabs
      description: Comprehensive list of Symfony2 bundles ordered by relevance and integrated with GitHub.
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

You must precise a sort field. Possible values are name.

    developer/:sort.:format

    $ curl http://symfony2bundles.org/developer/name.json

Return a list of developers:

    - name: ornicar
      email: thibault.duplessis@gmail.com
      fullName: Thibault Duplessis
      company: knpLabs
      location: France
      blog:
      bundles:
        - GravatarBundle
      projects:
        - lichess

### Show one developer

When requesting only one user, you get more informations such as last commits.

    :name.:format

    $ curl http://symfony2bundles.org/ornicar.json

Return informations about one developer.

    - name: ornicar
      email: thibault.duplessis@gmail.com
      fullName: Thibault Duplessis
      company: knpLabs
      location: France
      blog:
      bundles:
        - GravatarBundle
      projects:
        - lichess
      lastCommitAt:
      lastCommits:
        ~ described at http://develop.github.com/p/commits.html

### List the Bundles of a developer

Get a list of the Bundles a given developer owns. 

    :name/bundles.:format

    $ curl http://symfony2bundles.org/knplabs/bundles.json

Return a list of Bundles:

    - type: Bundle
      name: OplBundle
      username: eXtreme
      description: Experimental Symfony2 Bundle providing support for OPL family as well with Open Power Template 2.1 templating engine.
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

### List the Projects of a developer

Get a list of the Projects a given developer owns. 

    :name/projects.:format

    $ curl http://symfony2bundles.org/knplabs/projects.json

Return a list of Projects.

    - type: Project
      name: symfony2bundles
      username: knplabs
      description: Comprehensive list of Symfony2 bundles ordered by relevance and integrated with GitHub.
      score: 46
      nbFollowers: 27
      nbForks: 3
      createdAt: 1278497445
      lastCommitAt: 1278513437
      tags:
        - 1.0
      contributors:
        - ornicar

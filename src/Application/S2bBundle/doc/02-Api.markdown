Everything here is available through an HTTP API.
As for now, the only supported format is JSON.

## Bundles

### List bundles

You must precise a sort field. Possible values are score, name, createdAt, lastCommitAt

    bundle/:sort.:format

    $ curl http://symfony2bundles.org/bundle/score.json

Return a list of bundles:

    - name: OplBundle
      username: eXtreme
      description: Experimental Symfony2 Bundle providing support for OPL family as well with Open Power Template 2.1 templating engine.
      score: 16
      followers: 2
      forks: 1
      createdAt: 1278497445
      lastCommitAt: 1278513437
      tags:
        - 1.0
        - 1.1

### Search bundles

    search.:format?q=:query

    $ curl http://symfony2bundles.org/search.json?q=mongo

Return a list of bundles:

    - name: OplBundle
      username: eXtreme
      description: Experimental Symfony2 Bundle providing support for OPL family as well with Open Power Template 2.1 templating engine.
      score: 16
      followers: 2
      forks: 1
      createdAt: 1278497445
      lastCommitAt: 1278513437
      tags:
        - 1.0
        - 1.1

### Show one bundle

When requesting only one bundle, you get more informations such as last commits, readme and documentation.

    username/name/.:format

    $ curl http://symfony2bundles.org/avalanche123/MicroKernelBundle.json

Return informations about one bundle:

    - name: MicroKernelBundle
      username: avalanche123
      description: A micro kernel for Symfony 2, inspired by the Ruby Sinatra Web Framework
      score: 21.2
      followers: 5
      forks: 2
      createdAt: 1273839236
      lastCommitAt: 1278106123
      tags:
        - 1.0
        - 1.1
      lastCommits:
        ~ described at http://develop.github.com/p/commits.html
      readme: # Symfony 2 Micro Kernel\r\n\r\nThis is a Ruby Sinatra inspired micro kernel for Symfony 2.[...]

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

### Show one developer

When requesting only one user, you get more informations such as last commits.

    :name.:format

    $ curl http://symfony2bundles.org/ornicar.json

Return informations about one developer.
When requesting only one developer, you get more informations such as last commits.

    - name: ornicar
      email: thibault.duplessis@gmail.com
      fullName: Thibault Duplessis
      company: knpLabs
      location: France
      blog:
      bundles:
        - GravatarBundle
      lastCommitAt: lastCommitAt
      lastCommits:
        ~ described at http://develop.github.com/p/commits.html

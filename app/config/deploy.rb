set :application,           "symfony2bundles"
set :domain,                "#{application}.org"
set :deploy_to,             "/#{application}"

set :repository,            "git://github.com/knplabs/#{application}.git"
set :scm,                   :git
# set :branch,                "prod"
set :git_enable_submodules, 1
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `subversion`, `mercurial`, `perforce`, `subversion` or `none`

role :web,                  domain                         # Your HTTP server, Apache/etc
role :app,                  domain                         # This may be the same as your `Web` server
role :db,                   domain, :primary => true       # This is where Rails migrations will run

set  :user,                 "#{application}"

set  :use_sudo,             false
set  :keep_releases,        3

set  :shared_files,         [app_path + "/config/config_prod_local.yml"]                     # The database configuration is here
set  :shared_children,      [app_path + "/logs", web_path + "/uploads", app_path + "/repos"] # The directory containing git repos

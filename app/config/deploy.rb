set :application, "KnpBundles"
set :domain,      "#{application}.com"
set :deploy_to,   "/var/www/#{domain}"
set :app_path,    "app"

set :scm,         :git
set :scm_command, "/usr/bin/git"
set :scm_verbose, true
set :repository,  "git@github.com:KnpLabs/#{application}.git"

set :branch, "feature/263"
set :user, "cordoval"
set :password, "cordoval"

set :shared_files, ["app/config/parameters.yml"]
set :shared_children, [app_path + "/logs", app_path + "/cache", "vendor"]
set :update_vendors, true
set :dump_assetic_assets, true

set :model_manager, "doctrine"
# Or: `propel`

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain                         # This may be the same as your `Web` server
role :db,         domain, :primary => true       # This is where Symfony2 migrations will run

set  :use_sudo, false
set  :keep_releases,  3
ssh_options[:forward_agent] = true

# Be more verbose by uncommenting the following line
# logger.level = Logger::MAX_LEVEL
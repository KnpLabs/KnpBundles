set :stages,                    %w(production staging)
set :default_stage,             "staging"
set :stage_dir,                 "app/config/deploy"

require 'capistrano/ext/multistage'

set :application,               "KnpBundles.com"
set :app_path,                  "app"
set :symfony_console,           "app/console"
set :group_writable,            false
set :use_sudo,                  false

set :repository,                "git://github.com/KnpLabs/KnpBundles.git"
set :scm,                       :git
set :deploy_via,                :remote_cache

set :model_manager,             "doctrine"
set :admin_runner,              nil
set :keep_releases,             2

set :writable_dirs,             ["app/cache", "app/logs"]
set :webserver_user,            "www-data"
set :permission_method,         :acl
set :use_set_permissions,       true

set :shared_files,              ["app/config/parameters.yml", "app/Resources/java", "web/.htaccess", "bin/launch-rabbit-consumers.sh"]
set :shared_children,           [app_path + "/logs"]
set :use_composer,              true
set :composer_options,          "--no-scripts --verbose --prefer-source"
set :update_vendors,            false

set :dump_assetic_assets,       true
set :interactive_mode,          false
set :use_sudo,                  false

before 'symfony:composer:install', 'symfony:copy_vendors'

namespace :symfony do
  desc "Copy vendors from previous release"
  task :copy_vendors, :except => { :no_release => true } do
    capifony_pretty_print "--> Copying vendors from previous release if exists"

    run "if [ -d #{previous_release}/vendor ]; then cp -a #{previous_release}/vendor #{latest_release}/vendor; fi"
    capifony_puts_ok
  end
end

after "deploy:update", "deploy:cleanup"
after "deploy", "deploy:set_permissions"

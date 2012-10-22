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

set :shared_files,              ["app/config/parameters.yml", "bin/launch-rabbit-consumers.sh", "app/Resources/java"]
set :shared_children,           [app_path + "/logs"]
set :use_composer,              true
set :update_vendors,            false

set :dump_assetic_assets,       true
set :interactive_mode,          false
set :use_sudo,                  false

before 'symfony:composer:install', 'symfony:copy_vendors'

namespace :symfony do
  desc "Copy vendors from previous release"
  task :copy_vendors, :except => { :no_release => true } do
    pretty_print "--> Copying vendors from previous release if exists"
    run "if [ -d #{previous_release}/vendor ]; then cp -a #{previous_release}/vendor #{latest_release}/vendor; fi"
    puts_ok
  end
end

# Be more verbose by uncommenting the following line
# logger.level = Logger::MAX_LEVEL

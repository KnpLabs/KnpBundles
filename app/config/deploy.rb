set :mainrepo,      "KnpLabs"
set :stages,        %w(prod vps100)
set :default_stage, "vps100"
set :stage_dir,     "app/config/deploy"
require 'capistrano/ext/multistage'

set :application, "KnpBundles.com"
set :app_path,    "app"
set :group_writable, false
set :use_sudo, false

ssh_options[:port] = "22123"
ssh_options[:forward_agent] = true
default_run_options[:pty] = true

set :repository,  "git@github.com:#{mainrepo}/KnpBundles.git"
set :scm,         :git
set :deploy_via, :remote_cache

set :model_manager, "doctrine"
set :admin_runner, nil

set  :keep_releases,  3
# Be more verbose by uncommenting the following line
# logger.level = Logger::MAX_LEVEL

set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,     [app_path + "/logs", web_path + "/uploads", app_path + "/sessions"]
set :use_composer, true
set :update_vendors, true
set :vendors_mode, "install"
set :dump_assetic_assets, true
set :interactive_mode, false
set :use_sudo, false

before 'symfony:composer:update', 'symfony:copy_vendors'
before 'symfony:cache:warmup', 'symfony:force_migrate'

namespace :symfony do
  desc "Copy vendors from previous release"
  task :copy_vendors, :except => { :no_release => true } do
    pretty_print "--> Copying vendors from previous release if exists"
    run "if [ -d #{previous_release}/vendor ]; then cp -a #{previous_release}/vendor #{latest_release}/vendor; fi"
    puts_ok
  end
end

logger.level = Logger::MAX_LEVEL

load 'deploy' if respond_to?(:namespace) # cap2 differentiator
Dir['vendor/**/Resources/recipes/*.rb'].each { |bundle| load(bundle) }
require 'capifony_symfony2'
load 'app/config/deploy'
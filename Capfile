load 'deploy' if respond_to?(:namespace) # cap2 differentiator
Dir['vendor/**/Resources/recipes/*.rb'].each { |bundle| load(bundle) }
load Gem.find_files('symfony2.rb').first.to_s
load 'app/config/deploy'
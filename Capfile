load 'deploy' if respond_to?(:namespace) # cap2 differentiator
Dir['vendor/bundles/*/*/recipes/*.rb'].each { |bundle| load(bundle) }
load Gem.required_location('capifony', 'symfony2.rb')
load 'app/config/deploy'
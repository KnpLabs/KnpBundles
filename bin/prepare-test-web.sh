export DISPLAY=:99
sudo apt-get install -y --force-yes apache2 libapache2-mod-php5 php5-mysql php5-sqlite > /dev/null 2> /dev/null
sed 's?%basedir%?'`pwd`'?' knpbundles.local-dist > knpbundles.local
sudo mv knpbundles.local /etc/apache2/sites-available/knpbundles.local
echo "127.0.0.1    knpbundles.local" | sudo tee -a /etc/hosts
sudo a2ensite knpbundles.local
sudo a2enmod rewrite
cp behat.yml-dist behat.yml
sudo /etc/init.d/apache2 restart
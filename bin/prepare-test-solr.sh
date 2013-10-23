echo "Delete old data"
rm -rf apache_solr
rm -f apache-solr-3.6.2.tgz

echo "Download of solr 3.6.2..."
wget --quiet http://archive.apache.org/dist/lucene/solr/3.6.2/apache-solr-3.6.2.tgz

echo "Extracting solr..."
tar -zxf apache-solr-3.6.2.tgz
mv apache-solr-3.6.2 apache-solr

echo "Starting Solr..."
php app/console --env=test kb:solr:start --solr-path="`pwd`/apache-solr/example/"
sleep 10

wget --quiet ftp://ftp.task.gda.pl/pub/www/apache/dist/lucene/solr/3.6.1/apache-solr-3.6.1.tgz
tar -zxf apache-solr-3.6.1.tgz
mv apache-solr-3.6.1 apache-solr
php app/console --env=test kb:solr:start --solr-path="`pwd`/apache-solr/example/"
sleep 10

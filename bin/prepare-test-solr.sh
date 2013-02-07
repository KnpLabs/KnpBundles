wget --quiet ftp://ftp.task.gda.pl/pub/www/apache/dist/lucene/solr/3.6.2/apache-solr-3.6.2.tgz
tar -zxf apache-solr-3.6.2.tgz
mv apache-solr-3.6.2 apache-solr
php app/console --env=test kb:solr:start --solr-path="`pwd`/apache-solr/example/"
sleep 10

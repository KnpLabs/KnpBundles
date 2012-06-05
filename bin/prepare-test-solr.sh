wget http://www.idg.pl/mirrors/apache/lucene/solr/3.6.0/apache-solr-3.6.0.tgz
tar -zxvf apache-solr-3.6.0.tgz
mv apache-solr-3.6.0 apache-solr
php app/console --env=test kb:solr:start  --solr-path="`pwd`/apache-solr/example/"

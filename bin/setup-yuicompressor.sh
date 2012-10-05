wget --quiet http://yui.zenfs.com/releases/yuicompressor/yuicompressor-2.4.7.zip
mkdir -p app/Resources/java/unzip
touch app/Resources/java/unzip
unzip -q yuicompressor-2.4.7.zip -d app/Resources/java/unzip
rm -rf yuicompressor-2.4.7.zip
mv app/Resources/java/unzip/yuicompressor-2.4.7/build/yuicompressor-2.4.7.jar app/Resources/java/
rm -rf app/Resources/java/unzip

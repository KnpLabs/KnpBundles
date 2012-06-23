# php comes with gd no need to download extension
echo "extension=gd.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`


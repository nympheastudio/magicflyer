cp -rf . ../envoimoinscher
rm -rf ../envoimoinscher/.git*
rm ../envoimoinscher/ruleset.xml
rm ../envoimoinscher/archive.sh
rm ../envoimoinscher/config.xml
cd ..
zip -r envoimoinscher.zip envoimoinscher
rm -rf envoimoinscher
cd -

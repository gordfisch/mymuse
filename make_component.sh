#/bin/bash

SUBVER='1'
JVER='J4'

echo -n "SUBVER = "
echo $SUBVER
echo -n "JVER = "
echo $JVER

version=5.0.2-$JVER-$SUBVER

cd src

zip -r  ../releases/com_mymuse-$version.zip * -x *build.xml*  plugins/storage_s3/\*

cp ../releases/com_mymuse-$version.zip ../releases/com_mymuse-latest.zip
cp ../releases/com_mymuse-$version.zip ../joomla/tests/_data/com_mymuse-latest.zip

echo -n "Look in releases "
echo -n "NEW VERSION =  "
echo $version

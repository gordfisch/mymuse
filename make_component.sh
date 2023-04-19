#/bin/bash

SUBVER='1'
JVER='J4'

SUBVER=`git rev-list HEAD | wc -l`
JVER=`git rev-parse --abbrev-ref HEAD`
if [ "$JVER" = "master" ]; then
	JVER="J4"
fi



echo -n "SUBVER = "
echo $SUBVER
echo -n "JVER = "
echo $JVER

version=5.0.9-$JVER-$SUBVER

cd src
rm ../releases/com_mymuse-$version.zip
 

zip -r  ../releases/com_mymuse-$version.zip * -x *build.xml*  *rerun* tests/\* plugins/storage_s3/\* 

cp ../releases/com_mymuse-$version.zip ../releases/com_mymuse-latest.zip
cp ../releases/com_mymuse-$version.zip ../joomla-cms/tests/Codeception/_data/com_mymuse-latest.zip

echo -n "Look in releases "
echo -n "NEW VERSION =  "
echo $version

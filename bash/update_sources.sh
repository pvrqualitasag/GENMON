#!/bin/bash
GNMSRCDIR=/home/quagadmin/source/GENMON

# change to source
cwd=$(pwd)
cd $GNMSRCDIR

# find difference
find . -type f -name '*.php' -print | while read f
do 
  echo " * Checking $f ...";
  if [ $(diff $f /var/www/html/genmon-ch/$f | wc -l) -ne 0 ]
  then
    echo " ** Copy $f to /var/www/html/genmon-ch/ ..."
    cp $f /var/www/html/genmon-ch
  fi
done

cd $cwd

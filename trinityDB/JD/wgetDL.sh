#!/bin/bash

for f in /home/nemiah/wget/*.dl;
do
	if [ -f $f ]; then
		content=`cat $f`;
		echo "Processing $f file..";
		wget -b $content;
		rm $f;
	fi
done

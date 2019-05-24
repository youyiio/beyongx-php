#!/bin/sh

basepath=$(cd `dirname $0`; pwd)

echo $basepath

#minimize permissions
chown apache:apache $basepath/data
chown -R apache:apache $basepath/data/runtime
chown -R apache:apache $basepath/data/install

chown -R apache:apache $basepath/public/upload

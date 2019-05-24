#!/bin/sh

echo "deploy timer to crontab for cms"

basepath=$(cd `dirname $0`; pwd)

if [ ! -e /var/spool/cron/ ];then
   mkdir -p /var/spool/cron/
fi

if [ `grep -v '^\s*#' /var/spool/cron/root |grep -c "$basepath"` -eq 0 ];then
  echo "*/1 * * * * cd ${basepath} && /usr/bin/php think timing" >> /var/spool/cron/root
fi
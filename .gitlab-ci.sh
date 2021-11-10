#!/bin/sh

tar -xzf bundle.tar.gz

deploy_path=$1

if [ ! -d $deploy_path ]; then
  mkdir $deploy_path
fi

# 是否首次部署
deploy_first=false
if [ ! -d "$deploy_path/config" ]; then
  deploy_first=true
fi

if [ "$deploy_first" = true ]; then
  /bin/cp -fr .env $deploy_path
  /bin/cp -fr config $deploy_path
  /bin/cp -fr data $deploy_path
fi

# 删除无需部署的文件
rm -fr data/install/database.php

# 实际部署文件
/bin/cp -fr addons $deploy_path
/bin/cp -fr application $deploy_path
/bin/cp -fr data $deploy_path
/bin/cp -fr extend $deploy_path
/bin/cp -fr public $deploy_path
/bin/cp -fr route $deploy_path
/bin/cp -fr thinkphp $deploy_path
/bin/cp -fr vendor $deploy_path

/bin/cp -fr check_env.sh $deploy_path
/bin/cp -fr start_* $deploy_path


if [ "$deploy_first" = true ]; then
  cd $deploy_path
  chmod +x  check_env.sh
  ./check_env.sh
fi

# git for me [git使用帮助]

## 配置.git

> code .git\config

添加如下：

[remote "gitlab"]
	url = http://gitlab.xxxx.com/youyiio/project_xxx.git
	fetch = +refs/heads/*:refs/remotes/origin/*

## git本地合并

> git checkout master

> git merge branch_xxx


## git提交操作

git操作
git status: see git tree status;
git add -A: 提交修改、删除或新增的文件到暂存区;
git commit -m "message": 提交到本地仓库;

## 推送至remote

注： git push remote localbranch:remotebranch
注:  git push remote branchname  (等同于：git push remote branchname:branchname)

> git push gitlab ItuizhanApi:master (ItuizhanApi=>master)

## 拉取remote

注： git pull remote remotebranch:localbranch
> git pull gitlab master:ItuizhanApi  (: 意思如 =>, 远程的master合并入本地的ItuizhanApi)


git pull = git fetch + git merge

> git pull gitlab master:master

> git pull origin master:master

冒号后面省略是时，合并到当前工作的分支

## 分支新建及删除

查看分支

> git branch

查看远程分支

> git branch -r

创建分支

> git branch newbranch   

#提交分支

> git push gitlab newbranch:newbranch

#删除分支

> git branch -d newbranch

#删除远程分支

> git push gitlab --delete newbranch

## 标签新建及同步

> git tag v1.0

> git push gitlab --tag

> git pull gitlab --tag

删除标签

> git tag -d tagname

删除远程标签与推送标签一样（本地删除后，同步）

## 分支项目或衍生项目更新

衍生项目中建立base分支[基项目]

> git branch base

配置.git\config
[remote "base"]
	url = http://gitlab.xxxx.com/youyiio/basexxx(根据实际修改).git
	fetch = +refs/heads/*:refs/remotes/origin/*

若base项目更新时，做合并更新有2种方法:

### 1) 在衍生项目中做合并

> git checkout base

> git pull base master:base

> git checkout master

> git merge base


### 从base项目中推送更新至衍生项目的base分支(当衍生项目比较多时，比较麻烦)

base项目中

> git push child_xxx master:base

衍生项目中

> git checkout base

> git pull gitlab base:base

> git checkout master

> git merge base
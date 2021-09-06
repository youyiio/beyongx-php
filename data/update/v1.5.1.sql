
update cms_config set value = 'v1.5.1' where `name` = 'beyong_cms_version';

# 更新系统设置权限
delete from `cms_auth_rule` where belongto='admin' and left(id, 1) = 5;

INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (5, 0, '系统管理', 'admin/ShowNav/System', 'fa-cog', 1, 1, 18, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (51, 5, '系统设置', 'admin/ShowNav/System/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (511, 51, '基本设置', 'admin/System/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (512, 51, '通知设置', 'admin/System/notification', '', 1, 1, 2, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (513, 51, '邮件测试', 'admin/System/testEmail', '', 1, 0, 3, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (514, 51, '短信测试', 'admin/System/testSms', '', 1, 0, 4, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (515, 51, '公众好测试', 'admin/System/testMp', '', 1, 0, 5, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (52, 5, '友情链接', 'admin/System/links', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (521, 52, '添加友链', 'admin/System/addLinks', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (522, 52, '修改友链', 'admin/System/editLinks', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (523, 52, '排序友链', 'admin/System/orderLinks', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (524, 52, '删除友链', 'admin/System/deleteLinks', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (53, 5, '清理缓存', 'admin/System/clearCache', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (54, 5, '日志审计', 'admin/System/actionLogs', '', 1, 1, 1, 1,'','admin');

update `cms_auth_group` set rules=(select GROUP_CONCAT(DISTINCT id SEPARATOR ',') from cms_auth_rule where belongto='admin') where id = 1;
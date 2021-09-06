DROP TABLE IF EXISTS api_device;
DROP TABLE IF EXISTS api_push_token;
DROP TABLE IF EXISTS cms_user_verify_code;

ALTER TABLE cms_action_log rename to sys_action_log;
ALTER TABLE cms_addons rename to sys_addons;
ALTER TABLE cms_auth_group rename to sys_auth_group;
ALTER TABLE cms_auth_group_access rename to sys_auth_group_access;
ALTER TABLE cms_auth_rule rename to sys_auth_rule;
ALTER TABLE cms_config rename to sys_config;
ALTER TABLE cms_file rename to sys_file;
ALTER TABLE cms_hooks rename to sys_hooks;
ALTER TABLE cms_image rename to sys_image;
ALTER TABLE cms_message rename to sys_message;
ALTER TABLE cms_region rename to sys_region;
ALTER TABLE cms_user rename to sys_user;
ALTER TABLE cms_user_meta rename to sys_user_meta;


ALTER TABLE `api_config_access` 
DROP COLUMN `xg_app_id`,
DROP COLUMN `xg_app_key`,
DROP COLUMN `xg_app_secret`,
DROP COLUMN `mi_app_id`,
DROP COLUMN `mi_app_key`,
DROP COLUMN `mi_app_secret`;

ALTER TABLE `sys_config` 
ADD COLUMN `id` int(0) NOT NULL AUTO_INCREMENT COMMENT '序号' FIRST,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`) USING BTREE,
ADD UNIQUE INDEX `uniq_config_name`(`name`) USING BTREE;

ALTER TABLE `sys_auth_rule` 
CHANGE COLUMN `belongto` `belongs_to` varchar(16) NULL DEFAULT NULL AFTER `condition`;

ALTER TABLE `cms_article` 
CHANGE COLUMN `ad_id` `relateds` text NULL AFTER `sort`;

ALTER TABLE `cms_link`
ADD COLUMN `start_time` datetime AFTER `status`;
ALTER TABLE `cms_link`
ADD COLUMN `end_time` datetime AFTER `start_time`; 
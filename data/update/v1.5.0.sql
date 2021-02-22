
/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     2020-09-24 15:53:00                          */
/*==============================================================*/

update cms_config set value = 'v1.1.5' where `name` = 'beyong_cms_version';

alter table `cms_links` rename to `cms_link`;

ALTER TABLE `cms_link`
ADD COLUMN `status` tinyint(4) NOT NULL DEFAULT 1 AFTER `sort`,
ADD COLUMN `create_time` datetime NULL AFTER `status`;

alter table `cms_user_token_info` rename to `api_token`;

ALTER TABLE `api_token`
ADD COLUMN `id` int(0) AUTO_INCREMENT NOT NULL FIRST,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`) USING BTREE;

alter table `cms_user_push_token` rename to `api_push_token`;

ALTER TABLE `api_push_token`
ADD COLUMN `id` int(0) NOT NULL AUTO_INCREMENT FIRST,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`) USING BTREE;


alter table `cms_config_access` rename to `api_config_access`;
alter table `cms_device` rename to `api_device`;


/*drop index idx_api_token_uid_access_device on api_token;*/

/*==============================================================*/
/* Index: idx_api_token_uid_access_device                       */
/*==============================================================*/
create index idx_api_token_uid_access_device on api_token
(
   uid,
   access_id,
   device_id,
   token
);



ALTER TABLE `cms_article`
ADD INDEX `idx_article_uid`(`uid`);
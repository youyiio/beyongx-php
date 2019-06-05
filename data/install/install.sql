SET FOREIGN_KEY_CHECKS=0;

/* ============================================建表脚本================================*/
/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     2019-04-28 14:47:38                          */
/*==============================================================*/


#drop index idx_action_log_user_id_action on cms_action_log;

#drop index idx_action_log_create_time on cms_action_log;

drop table if exists cms_action_log;

drop table if exists cms_ad;

drop table if exists cms_ad_adtype;

#drop index uniq_addons_name on cms_addons;

drop table if exists cms_addons;

drop table if exists cms_adtype;

drop table if exists cms_article;

drop table if exists cms_article_data;

#drop index idx_article_meta_meta_key on cms_article_meta;

#drop index idx_article_meta_article_id on cms_article_meta;

drop table if exists cms_article_meta;

drop table if exists cms_auth_group;

drop table if exists cms_auth_group_access;

drop table if exists cms_auth_rule;

drop table if exists cms_category;

drop table if exists cms_category_article;

drop table if exists cms_comment;

drop table if exists cms_config;

drop table if exists cms_config_access;

drop table if exists cms_crawler;

#drop index idx_crawler_meta_uid_meta_key on cms_crawler_meta;

drop table if exists cms_crawler_meta;

drop table if exists cms_device;

drop table if exists cms_feedback;

drop table if exists cms_file;

#drop index idx_geography_area_parent_code on cms_geography_area;

drop table if exists cms_geography_area;

drop table if exists cms_hooks;

drop table if exists cms_image;

drop table if exists cms_links;

drop table if exists cms_message;

#drop index uniq_user_account on cms_user;

#drop index uniq_user_email on cms_user;

#drop index uniq_user_mobile on cms_user;

drop table if exists cms_user;

#drop index idx_user_meta_uid_meta_key on cms_user_meta;

drop table if exists cms_user_meta;

drop table if exists cms_user_push_token;

drop table if exists cms_user_token_info;

#drop index idx_user_verify_code_type_target on cms_user_verify_code;

drop table if exists cms_user_verify_code;

/*==============================================================*/
/* Table: cms_action_log                                        */
/*==============================================================*/
create table cms_action_log
(
   id                   bigint not null auto_increment,
   user_id              int,
   action               varchar(64) not null,
   module               varchar(16),
   ip                   varchar(64) not null,
   remark               varchar(256),
   data                 varchar(128),
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_action_log comment '操作日志表';

/*==============================================================*/
/* Index: idx_action_log_create_time                            */
/*==============================================================*/
create index idx_action_log_create_time on cms_action_log
(
   create_time
);

/*==============================================================*/
/* Index: idx_action_log_user_id_action                         */
/*==============================================================*/
create index idx_action_log_user_id_action on cms_action_log
(
   user_id,
   action
);

/*==============================================================*/
/* Table: cms_ad                                                */
/*==============================================================*/
create table cms_ad
(
   id                   int not null auto_increment,
   title                varchar(256) not null,
   url                  varchar(256) not null,
   image_id             int,
   sort                 int not null default 0,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_ad comment '广告表';

/*==============================================================*/
/* Table: cms_ad_adtype                                         */
/*==============================================================*/
create table cms_ad_adtype
(
   ad_id                int not null,
   type                 int not null,
   primary key (ad_id, type)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_ad_adtype comment '广告类型关联表,';

/*==============================================================*/
/* Table: cms_addons                                            */
/*==============================================================*/
create table cms_addons
(
   id                   int not null auto_increment,
   name                 varchar(40) not null comment '插件名或标识',
   title                varchar(20) not null default '0' comment '中文名',
   description          text comment '描述',
   status               tinyint not null default 1 comment '状态',
   config               text,
   author               varchar(40) comment '作者',
   version              varchar(20) comment '版本号',
   create_time          datetime not null comment '安装时间',
   has_adminlist        boolean not null default 0 comment '是否有后台列表',
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_addons comment '插件表';

/*==============================================================*/
/* Index: uniq_addons_name                                      */
/*==============================================================*/
create index uniq_addons_name on cms_addons
(
   name
);

/*==============================================================*/
/* Table: cms_adtype                                            */
/*==============================================================*/
create table cms_adtype
(
   type                 int not null auto_increment,
   title_cn             varchar(32) not null,
   title_en             varchar(32) not null,
   remark               varchar(128),
   image_size           varchar(128),
   primary key (type)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_adtype comment '广告类型表';

/*==============================================================*/
/* Table: cms_article                                           */
/*==============================================================*/
create table cms_article
(
   id                   int not null auto_increment,
   title                varchar(64) not null,
   keywords             varchar(128),
   description          varchar(256),
   content              mediumtext not null,
   post_time            datetime,
   create_time          datetime not null,
   last_update_time     datetime not null,
   status               tinyint,
   is_top               boolean default 0,
   thumb_image_id       int,
   read_count           int not null default 0,
   comment_count        int not null default 0,
   author               varchar(64),
   user_id              int not null,
   sort                 int default 0,
   ad_id                int default 0,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_article comment '文章表';

/*==============================================================*/
/* Table: cms_article_data                                      */
/*==============================================================*/
create table cms_article_data
(
   id                   int not null auto_increment,
   article_a_id         int not null,
   article_b_id         int not null,
   title_similar        float not null,
   content_similar      float not null,
   last_update_time     datetime not null,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_article_data comment '文章相关表';

/*==============================================================*/
/* Table: cms_article_meta                                      */
/*==============================================================*/
create table cms_article_meta
(
   id                   int not null auto_increment,
   article_id           int not null,
   meta_key             varchar(255) not null,
   meta_value           longtext,
   last_update_time     datetime not null,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_article_meta comment '文章meta表';

/*==============================================================*/
/* Index: idx_article_meta_article_id                           */
/*==============================================================*/
create index idx_article_meta_article_id on cms_article_meta
(
   article_id
);

/*==============================================================*/
/* Index: idx_article_meta_meta_key                             */
/*==============================================================*/
create index idx_article_meta_meta_key on cms_article_meta
(
   meta_key
);

/*==============================================================*/
/* Table: cms_auth_group                                        */
/*==============================================================*/
create table cms_auth_group
(
   id                   smallint(6) not null auto_increment,
   title                varchar(32) not null,
   status               tinyint(1) not null default 1 comment '1.激活;2.冻结;3.删除',
   rules                text,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_auth_group comment '分组表';

/*==============================================================*/
/* Table: cms_auth_group_access                                 */
/*==============================================================*/
create table cms_auth_group_access
(
   uid                  mediumint(8) not null,
   group_id             mediumint(8) not null,
   primary key (uid, group_id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_auth_group_access comment '分组访问表';

/*==============================================================*/
/* Table: cms_auth_rule                                         */
/*==============================================================*/
create table cms_auth_rule
(
   id                   int not null auto_increment,
   pid                  int not null default 0,
   name                 char(80) not null,
   title                varchar(64),
   icon                 varchar(20),
   type                 tinyint(1) not null default 1,
   is_menu              tinyint(1) default 0 comment '0.否;1.是',
   sort                 int not null default 0,
   status               tinyint(1) not null default 1 comment '-1.删除;1.激活;2.暂停;',
   `condition`          char(100) not null default '',
   belongto             varchar(16),
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_auth_rule comment '规则表';

/*==============================================================*/
/* Table: cms_category                                          */
/*==============================================================*/
create table cms_category
(
   id                   int not null auto_increment,
   pid                  varchar(24) not null,
   title_cn             varchar(64) not null,
   title_en             varchar(64) not null,
   remark               varchar(128) not null,
   status               tinyint not null comment '0.下线;1.上线',
   sort                 int,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_category comment '分类表';

/*==============================================================*/
/* Table: cms_category_article                                  */
/*==============================================================*/
create table cms_category_article
(
   category_id          int not null,
   article_id           int not null,
   primary key (category_id, article_id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

/*==============================================================*/
/* Table: cms_comment                                           */
/*==============================================================*/
create table cms_comment
(
   id                   int not null auto_increment,
   pid                  int,
   content              text not null,
   status               tinyint not null comment '-1,删除;0.草稿;1.申请发布;2.拒绝;3.发布',
   author               varchar(32) not null,
   author_email         varchar(128),
   author_url           varchar(256),
   ip                   varchar(64) not null,
   uid                  int,
   article_id           int not null,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_comment comment '评论表';

/*==============================================================*/
/* Table: cms_config                                            */
/*==============================================================*/
create table cms_config
(
   name                 varchar(128) not null,
   value                text,
   remark               varchar(128),
   value_type           varchar(16) comment '值类型:bool,number,text,muti_text',
   tab                  varchar(16),
   sort                 int default 0,
   primary key (name)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_config comment '配置表';

/*==============================================================*/
/* Table: cms_config_access                                     */
/*==============================================================*/
create table cms_config_access
(
   access_id            int not null auto_increment,
   name                 varchar(64),
   access_key           varchar(32) not null,
   access_secret        varchar(32) not null,
   xg_app_id            varchar(32),
   xg_app_key           varchar(32),
   xg_app_secret        varchar(64),
   mi_app_id            varchar(32),
   mi_app_key           varchar(32),
   mi_app_secret        varchar(64),
   create_time          datetime not null,
   primary key (access_id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_config_access comment '访问配置表';

/*==============================================================*/
/* Table: cms_crawler                                           */
/*==============================================================*/
create table cms_crawler
(
   id                   int not null auto_increment,
   title                text not null,
   status               tinyint not null comment '-1,删除;0.草稿;1.采集中;2.采集成功;3.采集失败',
   url                  varchar(256) not null,
   encoding             varchar(16) not null comment 'auto:自动判断\utf-8\gbk\gb2312\iso-8859-1等',
   is_timing            tinyint not null default 0,
   is_paging            tinyint not null default 0 comment '0.否;1.是',
   start_page           int,
   end_page             int,
   paging_url           varchar(128),
   article_url          varchar(128),
   article_title        varchar(128),
   article_description  varchar(128),
   article_content      varchar(128),
   article_author       varchar(128),
   article_image        varchar(128),
   category_id          int,
   create_time          datetime not null,
   last_update_time     datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_crawler comment '采集规则表';

/*==============================================================*/
/* Table: cms_crawler_meta                                      */
/*==============================================================*/
create table cms_crawler_meta
(
   id                   int not null auto_increment,
   crawler_id           int not null,
   meta_key             varchar(32) not null,
   meta_value           text not null,
   remark               varchar(128),
   last_update_time     datetime not null,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_crawler_meta comment '采集元数据表';

/*==============================================================*/
/* Index: idx_crawler_meta_uid_meta_key                         */
/*==============================================================*/
create index idx_crawler_meta_uid_meta_key on cms_crawler_meta
(
   crawler_id,
   meta_key
);

/*==============================================================*/
/* Table: cms_device                                            */
/*==============================================================*/
create table cms_device
(
   device_id            varchar(128) not null,
   model                varchar(128) not null,
   os                   tinyint not null comment '1.Android
            2.iPhone
            3.Window Phone',
   os_version           varchar(128) not null,
   width                int not null,
   height               int not null,
   cpu                  varchar(64),
   ram                  bigint,
   rom                  bigint,
   create_time          datetime not null,
   last_update_time     datetime not null,
   primary key (device_id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_device comment '设备信息表';

/*==============================================================*/
/* Table: cms_feedback                                          */
/*==============================================================*/
create table cms_feedback
(
   id                   bigint not null auto_increment,
   content              text not null,
   status               tinyint not null,
   send_client_id       varchar(64),
   reply_client_id      varchar(64),
   reply_feedback_id    bigint,
   ip                   varchar(64),
   source               varchar(16) comment '发送可能来自网页、app或微信等',
   send_time            datetime,
   read_time            datetime,
   reply_time           datetime,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_feedback comment '意见反馈表';

/*==============================================================*/
/* Table: cms_file                                              */
/*==============================================================*/
create table cms_file
(
   file_id              int not null auto_increment,
   file_url             varchar(256) not null,
   file_path            varchar(256) not null default '0' comment 'file_ulr所在目录',
   file_name            varchar(128) not null,
   file_size            int not null,
   ext                  text,
   remark               varchar(512),
   create_time          datetime not null,
   primary key (file_id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_file comment '文件表';

/*==============================================================*/
/* Table: cms_geography_area                                    */
/*==============================================================*/
create table cms_geography_area
(
   area_code            varchar(16) not null,
   area_name            varchar(64) not null,
   parent_code          varchar(16) not null,
   capital_flag         tinyint not null default 0 comment '1.表示是captial,0表示不是',
   display_flag         tinyint not null comment '0.不显示;1.显示;2.忽略本级',
   primary key (area_code)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_geography_area comment '地理信息表';

/*==============================================================*/
/* Index: idx_geography_area_parent_code                        */
/*==============================================================*/
create index idx_geography_area_parent_code on cms_geography_area
(
   parent_code
);

/*==============================================================*/
/* Table: cms_hooks                                             */
/*==============================================================*/
create table cms_hooks
(
   id                   int not null auto_increment,
   name                 varchar(40) not null comment '钩子名称',
   description          text comment '描述',
   type                 tinyint not null default 1 comment '类型',
   status               tinyint not null default 1 comment '状态',
   addons               varchar(256) comment '钩子挂载的插件，用'',''分割',
   update_time          datetime not null comment '更新时间',
   create_time          datetime not null comment '安装时间',
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_hooks comment '钩子表';

/*==============================================================*/
/* Table: cms_image                                             */
/*==============================================================*/
create table cms_image
(
   image_id             int not null auto_increment,
   thumb_image_url      varchar(256) not null,
   image_url            varchar(256) not null default '0',
   thumb_image_size     int,
   image_size           int,
   oss_image_url        varchar(256),
   ext                  text,
   remark               varchar(512),
   create_time          datetime not null,
   primary key (image_id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_image comment '图片表';

/*==============================================================*/
/* Table: cms_links                                             */
/*==============================================================*/
create table cms_links
(
   id                   int not null auto_increment,
   title                varchar(128) not null,
   url                  varchar(256) not null,
   sort                 int not null default 0,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_links comment '链接';

/*==============================================================*/
/* Table: cms_message                                           */
/*==============================================================*/
create table cms_message
(
   id                   bigint not null auto_increment,
   type                 tinyint not null,
   title                varchar(256) not null,
   content              text not null,
   status               tinyint not null comment '-1.删除.0.草稿;1.提交;2.已发送;',
   from_uid             varchar(64) not null,
   to_uid               varchar(64) not null,
   send_time            datetime,
   is_readed            boolean default 0,
   read_time            datetime,
   ext                  text,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_message comment '消息表';

/*==============================================================*/
/* Table: cms_user                                              */
/*==============================================================*/
create table cms_user
(
   user_id              int not null auto_increment,
   mobile               varchar(24) not null,
   email                varchar(32) not null,
   account              varchar(32) not null,
   password             varchar(64) not null,
   status               tinyint not null comment '-1.删除;1.申请;2.激活;3.冻结;',
   nickname             varchar(64),
   sex                  tinyint default 1 comment '1.男;2.女;3.未知;',
   head_url             varchar(128),
   qq                   varchar(16),
   weixin               varchar(64),
   device_id            varchar(64),
   referee              int,
   register_time        datetime not null,
   register_ip          varchar(64),
   from_referee         varchar(256),
   entrance_url         varchar(256),
   ext                  text,
   last_login_time      datetime,
   last_login_ip        varchar(64),
   primary key (user_id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_user comment '用户信息表';

/*==============================================================*/
/* Index: uniq_user_mobile                                      */
/*==============================================================*/
create unique index uniq_user_mobile on cms_user
(
   mobile
);

/*==============================================================*/
/* Index: uniq_user_email                                       */
/*==============================================================*/
create unique index uniq_user_email on cms_user
(
   email
);

/*==============================================================*/
/* Index: uniq_user_account                                     */
/*==============================================================*/
create unique index uniq_user_account on cms_user
(
   account
);

/*==============================================================*/
/* Table: cms_user_meta                                         */
/*==============================================================*/
create table cms_user_meta
(
   id                   int not null auto_increment,
   user_id              int not null,
   meta_key             varchar(32) not null,
   meta_value           text not null,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_user_meta comment '用户元数据表';

/*==============================================================*/
/* Index: idx_user_meta_uid_meta_key                            */
/*==============================================================*/
create index idx_user_meta_uid_meta_key on cms_user_meta
(
   user_id,
   meta_key
);

/*==============================================================*/
/* Table: cms_user_push_token                                   */
/*==============================================================*/
create table cms_user_push_token
(
   user_id              int not null,
   access_id            int not null,
   device_id            varchar(64) not null,
   status               tinyint not null comment '1.登入;2.登出',
   os                   int not null,
   push_token           varchar(128) not null,
   create_time          datetime not null,
   last_update_time     datetime not null,
   primary key (user_id, access_id, device_id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_user_push_token comment '用户推送token';

/*==============================================================*/
/* Table: cms_user_token_info                                   */
/*==============================================================*/
create table cms_user_token_info
(
   user_id              int not null,
   access_id            int not null,
   device_id            varchar(64) not null,
   status               tinyint not null comment '1.有效;2.失效;3.过期',
   token                varchar(64) not null,
   expire_time          datetime not null,
   create_time          datetime not null,
   last_update_time     datetime not null,
   primary key (user_id, access_id, device_id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_user_token_info comment '用户token信息表';

/*==============================================================*/
/* Table: cms_user_verify_code                                  */
/*==============================================================*/
create table cms_user_verify_code
(
   id              int not null auto_increment,
   type                 varchar(32) not null comment 'register:注册验证码;reset_password:重置密码;email_active:邮件激活',
   target               varchar(32) not null comment '如手机，邮箱或uid',
   status               tinyint not null comment '1.未使用;2.已使用',
   code                 varchar(32) not null comment '验证码',
   expire_time          datetime not null,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

alter table cms_user_verify_code comment '验证码表';

/*==============================================================*/
/* Index: idx_user_verify_code_type_target                      */
/*==============================================================*/
create index idx_user_verify_code_type_target on cms_user_verify_code
(
   type,
   target
);



/* ==================================================================================================*/
/* ============================================数据初始脚本：config表================================*/
truncate table cms_config;

INSERT INTO `cms_config` VALUES ('address', '厦门市思明区软件园二期望海路000号000室', '联系地址','text', 'contact', 1);
INSERT INTO `cms_config` VALUES ('article_thumb_image', '{\"width\":280,\"height\":280,\"thumb_width\":140,\"thumb_height\":140}', '文章缩略图大小配置', 'text', 'article', 0);
INSERT INTO `cms_config` VALUES ('article_audit_switch', 'true', '文章审核', 'bool', 'article', 1);
INSERT INTO `cms_config` VALUES ('bank_card', 'xxx', '公司银行账号', 'text', NULL, 0);
INSERT INTO `cms_config` VALUES ('bank_name', '招商银行', '公司银行帐号开户行', 'text', NULL, 0);
INSERT INTO `cms_config` VALUES ('contact', 'beyongx sir', '联系人', 'text', 'contact', 5);
INSERT INTO `cms_config` VALUES ('description', 'BeyongX内容管理系统|Beyongx,ThinkPHP,CMS，可二次开发的扩展框架，包含用户管理，权限角色管理及内容管理等', '网站描述', 'muti_text', 'seo', 3);
INSERT INTO `cms_config` VALUES ('domain_name', 'www.beyongx.com', '域名', 'text', 'base', 2);
INSERT INTO `cms_config` VALUES ('email_addr', 'service@beyongx.com', '发件邮箱地址', 'text', 'email', 3);
INSERT INTO `cms_config` VALUES ('email_host', 'smtp.exmail.qq.com', '邮箱SMTP服务器地址', 'text', 'email', 1);
INSERT INTO `cms_config` VALUES ('email_name', 'service', '发件邮箱名称', 'text', 'email', 5);
INSERT INTO `cms_config` VALUES ('email_pass', 'password', '发件邮箱密码', 'text', 'email', 4);
INSERT INTO `cms_config` VALUES ('email_port', '465', 'SMTP服务器端口,一般为25', 'number', 'email', 2);
INSERT INTO `cms_config` VALUES ('email_security', 'ssl', '加密方式：null|ssl|tls, QQ邮箱必须使用ssl', 'text', 'email', 0);
INSERT INTO `cms_config` VALUES ('email_activate_user', '<style type=\"text/css\">\r\n  p{text-indent: 2em;}\r\n</style>\r\n<div><strong>尊敬的用户</strong></div>\r\n<p>您好，非常感谢您对Beyongx(<a href=\"https://www.ituizhan.com/\" target=\"_blank\" title=\"Beyongx\">Beyongx</a>)的关注和热爱</p>\r\n<p>您本次申请注册成为Beyongx会员的邮箱验证链接是: <a style=\"font-size: 30px;color: red;\" href=\"{url}\">{url}</a></p>\r\n<p>如果非您本人操作，请忽略该邮件。</p>\r\n', '新用户邮箱激活html格式', 'muti_text', 'email_template', 6);
INSERT INTO `cms_config` VALUES ('email_reset_password', '<style type=\"text/css\">\r\np{text-indent: 2em;}\r\n</style>\r\n<div><strong>尊敬的用户</strong></div>\r\n<p>您好，非常感谢您对Beyongx(<a href=\"https://www.ituizhan.com/\" target=\"_blank\" title=\"Beyongx\">Beyongx</a>)的关注和热爱</p>\r\n<p>您本次申请找回密码的邮箱验证码是: <strong style=\"font-size: 30px;color: red;\">{code}</strong></p>\r\n<p>您本次重置密码的邮箱链接是: <a style=\"font-size: 30px;color: red;\"  href=\"{url}\">{url}</strong>\r\n<p>如果非您本人操作，请忽略该邮件。</p>\r\n', '用户邮箱重置密码html格式', 'muti_text', 'email_template', 7);
INSERT INTO `cms_config` VALUES ('fax', '0592-1234567', '传真', 'text', 'contact', 3);
INSERT INTO `cms_config` VALUES ('icp', '闽ICP备xxxxxxxx号-1', '备案号', 'text', 'base', 3);
INSERT INTO `cms_config` VALUES ('keywords', 'Beyongx,ThinkPHP,CMS内容管理系统,扩展框架', '网站关键词，有英文逗号分隔', 'text', 'seo', 3);
INSERT INTO `cms_config` VALUES ('password_key', 'lGfFSc17z8Q15P5kU0guNqq906DHNbA3', '加密密钥', 'text', NULL, 0);
INSERT INTO `cms_config` VALUES ('site_name', 'BeyongX内容管理系统', '网站名称', 'text', 'base', 1);
INSERT INTO `cms_config` VALUES ('company_name', 'XXX公司', '公司名称', 'text', null, 1);
INSERT INTO `cms_config` VALUES ('stat_code', '<script>\r\nvar _hmt = _hmt || [];\r\n(function() {\r\n  var hm = document.createElement(\"script\");\r\n  hm.src = \"https://hm.baidu.com/hm.js?ce074243117e698438c49cd037b593eb\";\r\n  var s = document.getElementsByTagName(\"script\")[0]; \r\n  s.parentNode.insertBefore(hm, s);\r\n})();\r\n</script>\r\n', '统计代码', 'muti_text', 'base', 4);
INSERT INTO `cms_config` VALUES ('tel', '0592-5000000', '联系电话', 'text', 'contact', 4);
INSERT INTO `cms_config` VALUES ('title', 'Beyongx Cms平台', '网站标题', 'text', 'seo', 1);
INSERT INTO `cms_config` VALUES ('zip_code', '361008', '邮编', 'text', 'contact', 2);

INSERT INTO `cms_config` VALUES ('tab_meta', '[{\"tab\":\"base\",\"name\":\"基本设置\",\"sort\":1},{\"tab\":\"seo\",\"name\":\"SEO设置\",\"sort\":2},{\"tab\":\"contact\",\"name\":\"联系方式\",\"sort\":3},{\"tab\":\"email\",\"name\":\"邮箱设置\",\"sort\":4},{\"tab\":\"article\",\"name\":\"文章设置\",\"sort\":5},{\"tab\":\"aliyun_oss\",\"name\":\"阿里OSS存储\",\"sort\":6},{\"tab\":\"qiniuyun_oss\",\"name\":\"七牛OSS存储\",\"sort\":7},{\"tab\":\"email_template\",\"name\":\"邮件模板\",\"sort\":8},{\"tab\":\"oss\",\"name\":\"OSS存储设置\",\"sort\":9}]', 'tab标签元数据', 'text', NULL, 0);

#oss存储配置
INSERT INTO `cms_config` VALUES ('oss_switch', 'false', 'OSS存储开关', 'bool', 'oss', 1);
INSERT INTO `cms_config` VALUES ('oss_vendor', 'qiniuyun', 'OSS', 'text', 'oss', 2);

INSERT INTO `cms_config` VALUES ('ali_bucket', 'Bucket名称', '阿里oss Bucket名称', 'text', 'aliyun_oss', 1);
INSERT INTO `cms_config` VALUES ('ali_endpoint', 'xxxx.aliyuncs.com', '阿里oss 外网地址endpoint', 'text', 'aliyun_oss', 2);
INSERT INTO `cms_config` VALUES ('ali_key_id', '阿里云key id', '阿里Access Key ID', 'text', 'aliyun_oss', 3);
INSERT INTO `cms_config` VALUES ('ali_key_secret', '阿里云key secret', '阿里Access Key Secret', 'text', 'aliyun_oss', 4);
INSERT INTO `cms_config` VALUES ('ali_url', '阿里云oss域名地址', '阿里oss 访问的地址', 'text', 'aliyun_oss', 5);
INSERT INTO `cms_config` VALUES ('qiniu_bucket', 'Bucket名称', '七牛oss Bucket', 'text', 'qiniuyun_oss', 3);
INSERT INTO `cms_config` VALUES ('qiniu_key_id', '', '七牛oss Accesskey', 'text', 'qiniuyun_oss', 1);
INSERT INTO `cms_config` VALUES ('qiniu_key_secret', '', '七牛oss Secretkey', 'text', 'qiniuyun_oss', 2);
INSERT INTO `cms_config` VALUES ('qiniu_url', '七牛域名地址', '七牛oss 访问的地址', 'text', 'qiniuyun_oss', 4);


/* ================================================================================================*/
/* ============================================数据初始脚本：用户表================================*/
truncate cms_user;

#默认密码为 888888
INSERT INTO
    `cms_user`(`user_id`,`mobile`,`email`,`account`,`password`,`status`,`nickname`,`sex`,`head_url`,`device_id`,`register_time`,`last_login_time`,`last_login_ip`)
VALUES
    (1,'18888888888','admin@admin.com','admin','f6bc5c8794afdae1dd41edb7939020e2',2,'超级管理员',1,null,null,'2015-01-01 00:00:00','2017-05-12 15:55:52','110.84.32.49');

/* ================================================================================================*/
/* =========================================数据初始脚本：角色权限表===============================*/
truncate table cms_auth_rule;

#控制面板,首页等进入页面,(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto)
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (1, 0, '后台面板', 'admin/ShowNav/Index', 'fa-th-large', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (11, 1, '后台主框架', 'admin/Index/index', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (111, 11, '面板消息', 'admin/Message/index', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (12, 1, '欢迎页面', 'admin/Index/welcome', '', 1, 0, 1, 1,'','admin');

INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (13, 1, '基础面板','admin/Index/dashboard', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (131, 13, '今日数据','admin/Index/today', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (132, 13, '本月数据', 'admin/Index/month', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (133, 13, '年度数据', 'admin/Index/year', '', 1, 0, 1, 1,'','admin');
#公共功能列表,可能在其他版块也会用到
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (19, 0, '公共功能列表', 'admin/ShowNav/Common', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (191, 19, '文件上传', 'admin/File/upload', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (192, 19, '图片上传', 'admin/Image/upload', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (193, 19, '软件上传', 'admin/File/uploadSoftware', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (194, 19, '移动App上传', 'admin/File/uploadApp', '', 1, 0, 1, 1,'','admin');

#个人中心模块
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (2, 0, '个人中心', 'admin/ShowNav/Person', 'fa-user', 1, 1, 14, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (21, 2, '个人首页', 'admin/Person/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (22, 2, '修改资料', 'admin/Person/profile', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (23, 2, '修改密码', 'admin/Person/password', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (211, 21, '查看文章', 'admin/Person/viewArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (212, 21, '编辑文章', 'admin/Person/editArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (213, 21, '删除文章', 'admin/Person/deleteArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (214, 21, '发布文章', 'admin/Person/postArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (215, 21, '上头条', 'admin/Person/upTop', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (216, 21, '取消头条', 'admin/Person/deleteTop', '', 1, 0, 1, 1,'','admin');

#用户管理模块
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (3, 0, '用户管理', 'admin/ShowNav/User', 'fa-users', 1, 1, 15, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (31, 3, '用户列表', 'admin/User/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (32, 3, '新增用户', 'admin/User/addUser', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (33, 3, '用户统计', 'admin/User/userStat', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (311, 31, '修改用户', 'admin/User/editUser', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (312, 31, '查看用户', 'admin/User/viewUser', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (313, 31, '修改密码', 'admin/User/changePwd', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (314, 31, '删除用户', 'admin/User/deleteUser', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (315, 31, '发送邮件', 'admin/User/sendMail', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (316, 31, '激活用户', 'admin/User/active', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (317, 31, '冻结用户', 'admin/User/freeze', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (318, 33, '统计报表数据', 'admin/User/echartShow', '', 1, 0, 1, 1,'','admin');

#菜单管理
#INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (4, 0, '菜单管理', 'admin/Menu/index', 'fa-th-list', 1, 0, 16, 1,'','admin');
#INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (41, 4, '新增菜单', 'admin/Menu/add', '', 1, 0, 1, 1,'','admin');
#INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (42, 4, '修改菜单', 'admin/Menu/edit', '', 1, 0, 1, 1,'','admin');
#INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (43, 4, '菜单排序', 'admin/Menu/order', '', 1, 0, 1, 1,'','admin');
#INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (44, 4, '删除菜单', 'admin/Menu/delete', '', 1, 0, 1, 1,'','admin');

#权限管理模块
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (5, 0, '权限管理', 'admin/ShowNav/Rule', 'fa-key', 1, 1, 17, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (51, 5, '权限规则', 'admin/Rule/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (511, 51, '新增权限规则', 'admin/Rule/add', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (512, 51, '编辑权限规则', 'admin/Rule/edit', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (513, 51, '删除权限规则', 'admin/Rule/delete', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (514, 51, '排序权限规则', 'admin/Rule/order', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (515, 51, '设置菜单值', 'admin/Rule/setMenu', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (52, 5, '用户分组', 'admin/Rule/group', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (521, 52, '新增分组', 'admin/Rule/addGroup', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (522, 52, '编辑分组', 'admin/Rule/editGroup', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (523, 52, '删除分组', 'admin/Rule/deleteGroup', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (524, 52, '分配权限', 'admin/Rule/ruleGroup', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (525, 52, '分组成员', 'admin/Rule/checkUser', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (526, 52, '添加成员', 'admin/Rule/addUserToGroup', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (527, 52, '移除成员', 'admin/Rule/deleteUserFromGroup', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (53, 5, '管理员列表', 'admin/Rule/userList', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (531, 53, '添加管理员', 'admin/Rule/addAdmin', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (532, 53, '编辑管理员', 'admin/Rule/editAdmin', '', 1, 0, 1, 1,'','admin');

#系统管理模块
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (6, 0, '系统管理', 'admin/ShowNav/System', 'fa-cog', 1, 1, 18, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (61, 6, '系统设置', 'admin/System/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (611, 61, '基本设置', 'admin/System/index', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (612, 61, '联系信息', 'admin/System/contact', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (613, 61, '通知邮箱', 'admin/System/email', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (614, 61, 'SEO设置', 'admin/System/seo', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (62, 6, '友情链接', 'admin/System/links', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (621, 62, '添加友链', 'admin/System/addLinks', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (622, 62, '修改友链', 'admin/System/editLinks', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (623, 62, '排序友链', 'admin/System/orderLi0nks', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (624, 62, '删除友链', 'admin/System/deleteLinks', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (63, 6, '清理缓存', 'admin/System/clearCache', '', 1, 1, 1, 1,'','admin');

#文章管理
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (7, 0, '文章管理', 'admin/ShowNav/Article', 'fa-file-text', 1, 1, 11, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (71, 7, '文章管理', 'admin/Article/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (711, 71, '查看文章', 'admin/Article/viewArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (712, 71, '新增文章', 'admin/Article/addArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (713, 71, '编辑文章', 'admin/Article/editArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (714, 71, '删除文章', 'admin/Article/deleteArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (715, 71, '上头条', 'admin/Article/upTop', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (716, 71, '取消头条', 'admin/Article/deleteTop', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (717, 71, '置顶', 'admin/Article/setTop', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (718, 71, '取消置顶', 'admin/Article/unsetTop', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (719, 71, '发布文章', 'admin/Article/postArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (7191, 71, '初审', 'admin/Article/auditFirst', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (7192, 71, '终审', 'admin/Article/auditSecond', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (7193, 71, '定时发布', 'admin/Article/setTimingPost', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (7194, 71, '文章访问统计', 'admin/Article/articleStat', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (7195, 71, '文章访问量统计图', 'admin/Article/echartShow', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (72, 7, '评论管理', 'admin/Article/commentList', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (721, 72, '审核评论', 'admin/Article/auditComment', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (722, 72, '回发评论', 'admin/Article/postComment', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (723, 72, '删除评论', 'admin/Article/deleteComment', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (73, 7, '文章分类', 'admin/Article/categoryList', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (731, 73, '新增分类', 'admin/Article/addCategory', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (732, 73, '编辑分类', 'admin/Article/editCategory', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (733, 73, '排序分类', 'admin/Article/orderCategory', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (734, 73, '删除分类', 'admin/Article/deleteCategory', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (74, 7, '广告管理', 'admin/Article/adList', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (741, 74, '新增广告', 'admin/Article/addAd', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (742, 74, '编辑广告', 'admin/Article/editAd', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (743, 74, '广告排序', 'admin/Article/orderAd', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (744, 74, '删除广告', 'admin/Article/deleteAd', '', 1, 0, 1, 1,'','admin');

#客服管理
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (8, 0, '客服管理', 'admin/ShowNav/Feedback', 'fa-comment', 1, 1, 12, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (81, 8, '客服消息', 'admin/Feedback/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (811, 81, '消息列表', 'admin/Feedback/chat', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (812, 81, '消息回复', 'admin/Feedback/reply', '', 1, 0, 1, 1,'','admin');

#资源管理
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (9, 0, '资源管理', 'admin/ShowNav/Resource', 'fa-archive', 1, 1, 13, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (91, 9, '文档管理', 'admin/Resource/documents', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (911, 91, '上传文档', 'admin/Resource/uploadDocument', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (912, 91, '删除文档', 'admin/Resource/deleteDocument', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (92, 9, '图片管理', 'admin/Resource/images', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (921, 92, '上传图片', 'admin/Resource/uploadImage', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (922, 92, '删除图片', 'admin/Resource/deleteImage', '', 1, 0, 1, 1,'','admin');

#采集系统
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (10, 0, '采集系统', 'admin/ShowNav/Crawler', 'fa-bug', 1, 1, 14, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (101, 10, '规则列表', 'admin/Crawler/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (1011, 101, '编辑规则', 'admin/Crawler/edit', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (1012, 101, '采集操作', 'admin/Crawler/startCrawl', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (1013, 101, '删除规则', 'admin/Crawler/deleteCrawler', '', 1, 0, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (102, 10, '新增规则', 'admin/Crawler/create', '', 1, 1, 1, 1,'','admin');
INSERT INTO `cms_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto) VALUES (1021, 102, '采集测试', 'admin/Crawler/crawlTest', '', 1, 1, 1, 1,'','admin');


#系统定制,从200开始
#INSERT INTO `cms_auth_rule(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto)` VALUES (100, 0, '推广管理', 'admin/ShowNav/Promotion', '', 1, 0, 1, 1,'','admin');
#INSERT INTO `cms_auth_rule(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto)` VALUES (1001, 100, '询价记录', 'admin/Promotion/inquiryList', '', 1, 0, 1, 1,'','')s;
#INSERT INTO `cms_auth_rule(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto)` VALUES (10011, 1001, '新增询价记录', 'admin/Promotion/addInquiry', '', 1, 0, 1, 1,'','admin');
#INSERT INTO `cms_auth_rule(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto)` VALUES (10012, 1001, '编辑询价记录', 'admin/Promotion/editInquiry', '', 1, 0, 1, 1,'','admin');
#INSERT INTO `cms_auth_rule(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto)` VALUES (10013, 1001, '删除询价记录', 'admin/Promotion/deleteInquiry', '', 1, 0, 1, 1,'','admin');
#INSERT INTO `cms_auth_rule(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongto)` VALUES (10014, 1001, '增加询价备注', 'admin/Promotion/addRemark', '', 1, 0, 1, 1,'','admin');


truncate table cms_auth_group;

INSERT INTO `cms_auth_group` (`id`,`title`,`status`,`rules`) VALUES (1, '超级管理员', 1, (select GROUP_CONCAT(DISTINCT id SEPARATOR ',') from cms_auth_rule));
INSERT INTO `cms_auth_group` (`id`,`title`,`status`,`rules`) VALUES (2, '普通管理员', 1, '');
INSERT INTO `cms_auth_group` (`id`,`title`,`status`,`rules`) VALUES (3, '网站编辑', 1, '');
INSERT INTO `cms_auth_group` (`id`,`title`,`status`,`rules`) VALUES (4, '普通用户', 1, '');

truncate table cms_auth_group_access;

INSERT INTO `cms_auth_group_access` (`uid`,`group_id`) VALUES (1, 1);


/* ================================================================================================*/
/* =========================================数据初始脚本：插件及钩子=============================*/
truncate cms_addons;
truncate cms_hooks;

#配置插件
INSERT INTO `cms_addons`(id,name,title,description,status,config,author,version,create_time,has_adminlist) VALUES (1, 'test', 'test插件', 'test插件简介', 1, NULL, 'test', '0.1', '2018-01-01 00:00:00', 0);
INSERT INTO `cms_addons`(id,name,title,description,status,config,author,version,create_time,has_adminlist) VALUES (2, 'enhance', '系统增强插件', 'Cms系统增强插件,用于前后部分定制', 1, NULL, 'beyongx', '0.1', '2018-06-12 00:00:00', 0);

#配置插件中可使用的钩子
INSERT INTO `cms_hooks`(id,name,description,type,status,addons,update_time,create_time) VALUES (21, 'demo', 'demo钩子', 1, 1, 'test', '2018-01-01 00:00:00', '2018-01-01 00:00:00');
INSERT INTO `cms_hooks`(id,name,description,type,status,addons,update_time,create_time) VALUES (22, 'userTimeline', '用户动态列表', 1, 1, 'enhance', '2018-06-12 00:00:00', '2018-06-12 00:00:00');
INSERT INTO `cms_hooks`(id,name,description,type,status,addons,update_time,create_time) VALUES (23, 'userBalance', '用户帐户信息', 1, 1, 'enhance', '2018-06-12 00:00:00', '2018-06-12 00:00:00');


/* ================================================================================================*/
/* =========================================数据初始脚本：文章及广告表=============================*/
truncate table cms_category;

INSERT INTO `cms_category`(id,pid,title_cn,title_en,remark,status,sort,create_time) VALUES (1, 0, '公司新闻', 'company', '公司新闻文章', 1, 3, '2018-01-19 00:00:00');
INSERT INTO `cms_category`(id,pid,title_cn,title_en,remark,status,sort,create_time) VALUES (2, 0, '行业新闻', 'news', '行业新闻文章', 1, 2, '2018-01-19 00:00:00');
INSERT INTO `cms_category`(id,pid,title_cn,title_en,remark,status,sort,create_time) VALUES (3, 0, '行业动态', 'status', '行业动态文章', 1, 1, '2018-01-19 00:00:00');

truncate table cms_adtype;

INSERT INTO `cms_adtype`(type, title_cn, title_en, remark, image_size) VALUES (1, '首页头条广告', 'banner_headline', '首页头条广告左右滚动', '{"width":1000,"height":300,"thumb_width":500,"thumb_height":150}');
INSERT INTO `cms_adtype`(type, title_cn, title_en, remark, image_size) VALUES (2, '首页中间广告', 'banner_center', '首页baner广告左右滚动', null);
INSERT INTO `cms_adtype`(type, title_cn, title_en, remark, image_size) VALUES (10, '搜索框', 'link_search', '显示于搜索框下面', null);
INSERT INTO `cms_adtype`(type, title_cn, title_en, remark, image_size) VALUES (11, '资讯列表页', 'link_article_list', '显示于资讯列表页', null);
INSERT INTO `cms_adtype`(type, title_cn, title_en, remark, image_size) VALUES (12, '资讯分类列表页', 'link_article_category_list', '显示于资讯分类列表页', null);
INSERT INTO `cms_adtype`(type, title_cn, title_en, remark, image_size) VALUES (13, '资讯详情页', 'link_article_detail', '显示于资讯详情页', null);

/* ================================================================================================*/
/* =========================================数据初始脚本：设置自增起始=============================*/
alter table cms_user AUTO_INCREMENT=100000;
alter table cms_message AUTO_INCREMENT=100000;
alter table cms_file AUTO_INCREMENT=100000;
alter table cms_image AUTO_INCREMENT=100000;
alter table cms_article AUTO_INCREMENT=100000;


SET FOREIGN_KEY_CHECKS=0;

/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     2021-08-05 18:41:47                          */
/*==============================================================*/


drop table if exists api_config_access;

#drop index idx_api_token_uid_access_device on api_token;

drop table if exists api_token;

drop table if exists cms_ad;

drop table if exists cms_ad_serving;

drop table if exists cms_ad_slot;

#drop index idx_article_uid on cms_article;

#drop index idx_article_sort on cms_article;

#drop index idx_article_update_time on cms_article;

#drop index idx_article_post_time on cms_article;

#drop index idx_article_status on cms_article;

drop table if exists cms_article;

#drop index idx_article_data_title_similar on cms_article_data;

#drop index idx_article_data_b_id on cms_article_data;

#drop index idx_article_data_a_id on cms_article_data;

drop table if exists cms_article_data;

#drop index idx_article_meta_update_time on cms_article_meta;

#drop index idx_article_meta_meta_key on cms_article_meta;

#drop index idx_article_meta_article_id on cms_article_meta;

drop table if exists cms_article_meta;

drop table if exists cms_category;

#drop index idx_category_article_aid on cms_category_article;

#drop index idx_category_article_cid on cms_category_article;

drop table if exists cms_category_article;

#drop index idx_comment_article_id on cms_comment;

drop table if exists cms_comment;

drop table if exists cms_crawler;

#drop index idx_crawler_meta_target_id_meta_key on cms_crawler_meta;

drop table if exists cms_crawler_meta;

drop table if exists cms_feedback;

drop table if exists cms_link;

#drop index idx_action_log_uid_action on sys_action_log;

#drop index idx_action_log_create_time on sys_action_log;

drop table if exists sys_action_log;

#drop index uniq_addons_name on sys_addons;

drop table if exists sys_addons;

drop table if exists sys_auth_group;

drop table if exists sys_auth_group_access;

drop table if exists sys_auth_rule;

#drop index uniq_config_name on sys_config;

drop table if exists sys_config;

drop table if exists sys_file;

drop table if exists sys_hooks;

drop table if exists sys_image;

#drop index idx_message_to_uid on sys_message;

#drop index idx_message_type_status on sys_message;

drop table if exists sys_message;

drop table if exists sys_region;

#drop index uniq_user_account on sys_user;

#drop index uniq_user_email on sys_user;

#drop index uniq_user_mobile on sys_user;

drop table if exists sys_user;

#drop index idx_user_meta_target_id_meta_key on sys_user_meta;

drop table if exists sys_user_meta;

/*==============================================================*/
/* Table: api_config_access                                     */
/*==============================================================*/
create table api_config_access
(
   access_id            int not null auto_increment,
   name                 varchar(64),
   access_key           varchar(32) not null,
   access_secret        varchar(32) not null,
   create_time          datetime not null,
   primary key (access_id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table api_config_access comment '访问配置表';

/*==============================================================*/
/* Table: api_token                                             */
/*==============================================================*/
create table api_token
(
   id                   int not null auto_increment,
   uid                  int not null,
   access_id            int not null,
   device_id            varchar(64) not null,
   token                varchar(64) not null,
   status               tinyint not null comment '1.有效;2.失效;3.过期',
   expire_time          datetime not null,
   update_time          datetime not null,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table api_token comment 'token表';

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
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table cms_ad comment '广告表';

/*==============================================================*/
/* Table: cms_ad_serving                                        */
/*==============================================================*/
create table cms_ad_serving
(
   id                   int not null auto_increment,
   ad_id                int not null,
   slot_id              int not null,
   status               tinyint comment '0.下线;1.上线',
   sort                 int,
   start_time           datetime,
   end_time             datetime,
   update_time          datetime not null,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table cms_ad_serving comment '广告投放表,';

/*==============================================================*/
/* Table: cms_ad_slot                                           */
/*==============================================================*/
create table cms_ad_slot
(
   id                   int not null auto_increment,
   title_cn             varchar(32) not null,
   title_en             varchar(32) not null,
   remark               varchar(128),
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table cms_ad_slot comment '广告槽位表';

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
   update_time          datetime not null,
   status               tinyint,
   is_top               boolean default 0,
   thumb_image_id       int,
   read_count           int not null default 0,
   comment_count        int not null default 0,
   author               varchar(64),
   uid                  int not null,
   sort                 int default 0 comment '排序',
   relateds             text comment '相关文章',
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table cms_article comment '文章表';

/*==============================================================*/
/* Index: idx_article_status                                    */
/*==============================================================*/
create index idx_article_status on cms_article
(
   status
);

/*==============================================================*/
/* Index: idx_article_post_time                                 */
/*==============================================================*/
create index idx_article_post_time on cms_article
(
   post_time
);

/*==============================================================*/
/* Index: idx_article_update_time                               */
/*==============================================================*/
create index idx_article_update_time on cms_article
(
   update_time
);

/*==============================================================*/
/* Index: idx_article_sort                                      */
/*==============================================================*/
create index idx_article_sort on cms_article
(
   sort
);

/*==============================================================*/
/* Index: idx_article_uid                                       */
/*==============================================================*/
create index idx_article_uid on cms_article
(
   uid
);

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
   update_time          datetime not null,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table cms_article_data comment '文章相关表';

/*==============================================================*/
/* Index: idx_article_data_a_id                                 */
/*==============================================================*/
create index idx_article_data_a_id on cms_article_data
(
   article_a_id
);

/*==============================================================*/
/* Index: idx_article_data_b_id                                 */
/*==============================================================*/
create index idx_article_data_b_id on cms_article_data
(
   article_b_id
);

/*==============================================================*/
/* Index: idx_article_data_title_similar                        */
/*==============================================================*/
create index idx_article_data_title_similar on cms_article_data
(
   title_similar
);

/*==============================================================*/
/* Table: cms_article_meta                                      */
/*==============================================================*/
create table cms_article_meta
(
   id                   int not null auto_increment,
   article_id           int not null,
   meta_key             varchar(255) not null,
   meta_value           longtext,
   update_time          datetime not null,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

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
/* Index: idx_article_meta_update_time                          */
/*==============================================================*/
create index idx_article_meta_update_time on cms_article_meta
(
   update_time
);

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
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table cms_category comment '分类表';

/*==============================================================*/
/* Table: cms_category_article                                  */
/*==============================================================*/
create table cms_category_article
(
   id                   int not null auto_increment,
   category_id          int not null,
   article_id           int not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table cms_category_article comment '文章分类关联表';

/*==============================================================*/
/* Index: idx_category_article_cid                              */
/*==============================================================*/
create index idx_category_article_cid on cms_category_article
(
   category_id
);

/*==============================================================*/
/* Index: idx_category_article_aid                              */
/*==============================================================*/
create index idx_category_article_aid on cms_category_article
(
   article_id
);

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
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table cms_comment comment '评论表';

/*==============================================================*/
/* Index: idx_comment_article_id                                */
/*==============================================================*/
create index idx_comment_article_id on cms_comment
(
   article_id
);

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
   article_keywords     varchar(128),
   article_content      varchar(128),
   article_author       varchar(128),
   article_image        varchar(128),
   category_id          int,
   update_time          datetime not null,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table cms_crawler comment '采集规则表';

/*==============================================================*/
/* Table: cms_crawler_meta                                      */
/*==============================================================*/
create table cms_crawler_meta
(
   id                   int not null auto_increment,
   target_id            int not null,
   meta_key             varchar(32) not null,
   meta_value           text not null,
   remark               varchar(128),
   update_time          datetime not null,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table cms_crawler_meta comment '采集元数据表';

/*==============================================================*/
/* Index: idx_crawler_meta_target_id_meta_key                   */
/*==============================================================*/
create index idx_crawler_meta_target_id_meta_key on cms_crawler_meta
(
   target_id,
   meta_key
);

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
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table cms_feedback comment '意见反馈表';

/*==============================================================*/
/* Table: cms_link                                              */
/*==============================================================*/
create table cms_link
(
   id                   int not null auto_increment,
   title                varchar(128) not null,
   url                  varchar(256) not null,
   sort                 int not null default 0,
   status               tinyint not null default 1,
   start_time           datetime,
   end_time             datetime,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table cms_link comment '链接表';

/*==============================================================*/
/* Table: sys_action_log                                        */
/*==============================================================*/
create table sys_action_log
(
   id                   bigint not null auto_increment,
   uid                  int,
   action               varchar(64) not null,
   module               varchar(16),
   ip                   varchar(64) not null,
   remark               varchar(256),
   data                 varchar(128),
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_action_log comment '操作日志表';

/*==============================================================*/
/* Index: idx_action_log_create_time                            */
/*==============================================================*/
create index idx_action_log_create_time on sys_action_log
(
   create_time
);

/*==============================================================*/
/* Index: idx_action_log_uid_action                             */
/*==============================================================*/
create index idx_action_log_uid_action on sys_action_log
(
   uid,
   action
);

/*==============================================================*/
/* Table: sys_addons                                            */
/*==============================================================*/
create table sys_addons
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
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_addons comment '插件表';

/*==============================================================*/
/* Index: uniq_addons_name                                      */
/*==============================================================*/
create index uniq_addons_name on sys_addons
(
   name
);

/*==============================================================*/
/* Table: sys_auth_group                                        */
/*==============================================================*/
create table sys_auth_group
(
   id                   smallint(6) not null auto_increment,
   title                varchar(32) not null,
   status               tinyint(1) not null default 1 comment '1.激活;2.冻结;3.删除',
   rules                text,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_auth_group comment '分组表';

/*==============================================================*/
/* Table: sys_auth_group_access                                 */
/*==============================================================*/
create table sys_auth_group_access
(
   uid                  mediumint(8) not null,
   group_id             mediumint(8) not null,
   primary key (uid, group_id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_auth_group_access comment '分组访问表';

/*==============================================================*/
/* Table: sys_auth_rule                                         */
/*==============================================================*/
create table sys_auth_rule
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
   belongs_to             varchar(16),
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_auth_rule comment '规则表';

/*==============================================================*/
/* Table: sys_config                                            */
/*==============================================================*/
create table sys_config
(
   id                   int not null auto_increment,
   name                 varchar(128) not null,
   value                text,
   remark               varchar(128),
   value_type           varchar(16) comment '值类型:bool,number,text,muti_text',
   tab                  varchar(16),
   sort                 int default 0,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_config comment '配置表';

/*==============================================================*/
/* Index: uniq_config_name                                      */
/*==============================================================*/
create unique index uniq_config_name on sys_config
(
   name
);

/*==============================================================*/
/* Table: sys_file                                              */
/*==============================================================*/
create table sys_file
(
   id                   int not null auto_increment,
   file_url             varchar(256) not null,
   file_path            varchar(256) not null default '0' comment 'file_ulr所在目录',
   file_name            varchar(128) not null,
   file_size            int not null,
   oss_image_url        varchar(512),
   ext                  text,
   remark               varchar(512),
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_file comment '文件表';

/*==============================================================*/
/* Table: sys_hooks                                             */
/*==============================================================*/
create table sys_hooks
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
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_hooks comment '钩子表';

/*==============================================================*/
/* Table: sys_image                                             */
/*==============================================================*/
create table sys_image
(
   id                   int not null auto_increment,
   thumb_image_url      varchar(256) not null,
   image_url            varchar(256) not null default '0',
   thumb_image_size     int,
   image_size           int,
   oss_image_url        varchar(256),
   ext                  text,
   remark               varchar(512),
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_image comment '图片表';

/*==============================================================*/
/* Table: sys_message                                           */
/*==============================================================*/
create table sys_message
(
   id                   bigint not null auto_increment,
   type                 varchar(16) not null,
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
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_message comment '消息表';

/*==============================================================*/
/* Index: idx_message_type_status                               */
/*==============================================================*/
create index idx_message_type_status on sys_message
(
   type,
   status
);

/*==============================================================*/
/* Index: idx_message_to_uid                                    */
/*==============================================================*/
create index idx_message_to_uid on sys_message
(
   to_uid
);

/*==============================================================*/
/* Table: sys_region                                            */
/*==============================================================*/
create table sys_region
(
   id                   int not null,
   pid                  int,
   shortname            varchar(100),
   name                 varchar(100),
   merger_name          varchar(255),
   level                tinyint(4),
   pinyin               varchar(100),
   code                 varchar(100),
   zip_code             varchar(100),
   first                varchar(50),
   lng                  varchar(100),
   lat                  varchar(100),
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_region comment '地区表,';

/*==============================================================*/
/* Table: sys_user                                              */
/*==============================================================*/
create table sys_user
(
   id                   int not null auto_increment,
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
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_user comment '用户信息表';

/*==============================================================*/
/* Index: uniq_user_mobile                                      */
/*==============================================================*/
create unique index uniq_user_mobile on sys_user
(
   mobile
);

/*==============================================================*/
/* Index: uniq_user_email                                       */
/*==============================================================*/
create unique index uniq_user_email on sys_user
(
   email
);

/*==============================================================*/
/* Index: uniq_user_account                                     */
/*==============================================================*/
create unique index uniq_user_account on sys_user
(
   account
);

/*==============================================================*/
/* Table: sys_user_meta                                         */
/*==============================================================*/
create table sys_user_meta
(
   id                   int not null auto_increment,
   target_id            int not null,
   meta_key             varchar(32) not null,
   meta_value           text not null,
   update_time          datetime not null,
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_user_meta comment '用户元数据表';

/*==============================================================*/
/* Index: idx_user_meta_target_id_meta_key                      */
/*==============================================================*/
create index idx_user_meta_target_id_meta_key on sys_user_meta
(
   target_id,
   meta_key
);



/* ==================================================================================================*/
/* ============================================数据初始脚本：config表================================*/
truncate table sys_config;

INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('site_name', 'BeyongCms内容管理系统', '网站名称', 'text', 'base', 1);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('domain_name', 'www.beyongx.com', '域名', 'text', 'base', 2);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('icp', '闽ICP备xxxxxxxx号-1', '备案号', 'text', 'base', 3);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('password_key', 'lGfFSc17z8Q15P5kU0guNqq906DHNbA3', '加密密钥', 'text', 'base', 0);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('theme_package_name', 'classic', '主题名称', 'text', 'base', 2);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('stat_code', '<script>\r\nvar _hmt = _hmt || [];\r\n(function() {\r\n  var hm = document.createElement(\"script\");\r\n  hm.src = \"https://hm.baidu.com/hm.js?3d0c1af3caa383b0cd59822f1e7a751b\";\r\n  var s = document.getElementsByTagName(\"script\")[0]; \r\n  s.parentNode.insertBefore(hm, s);\r\n})();\r\n</script>\r\n<!-- 以下为自动提交代码 -->\r\n<script>\r\n(function(){\r\n    var bp = document.createElement(\"script\");\r\n    var curProtocol = window.location.protocol.split(\":\")[0];\r\n    if (curProtocol === \"https\") {\r\n        bp.src = \"https://zz.bdstatic.com/linksubmit/push.js\";\r\n    }\r\n    else {\r\n        bp.src = \"http://push.zhanzhang.baidu.com/push.js\";\r\n    }\r\n    var s = document.getElementsByTagName(\"script\")[0];\r\n    s.parentNode.insertBefore(bp, s);\r\n})();\r\n</script>\r\n', '统计代码', 'muti_text', 'base', 4);

INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('title', 'BeyongCms平台', '网站标题', 'text', 'seo', 1);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('description', 'BeyongCms内容管理系统|Beyongx,ThinkPHP,CMS，可二次开发的扩展框架，包含用户管理，权限角色管理及内容管理等', '网站描述', 'muti_text', 'seo', 3);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('keywords', 'BeyongCms,Beyongx,ThinkPHP,CMS内容管理系统,扩展框架', '网站关键词，有英文逗号分隔', 'text', 'seo', 3);

INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('company_name', 'XXX公司', '公司名称', 'text', 'company', 1);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('bank_card', 'xxx', '公司银行账号', 'text', 'company', 0);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('bank_name', '招商银行', '公司银行帐号开户行', 'text', 'company', 0);

INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('article_thumb_image', '{\"width\":280,\"height\":280,\"thumb_width\":140,\"thumb_height\":140}', '文章缩略图大小配置', 'text', 'article', 0);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('article_audit_switch', 'true', '文章审核', 'bool', 'article', 1);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('article_water', '1', '水印开关(0:无水印,1:水印文字,2:水印图片)', 'number', 'article', 2);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('article_water_text', '', '水印文本', 'text', 'article', 3);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('image_upload_quality', '80', '上传图片质量', 'text', 'article', 4);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('image_upload_max_limit', '680', '上传图片宽高最大值(单位px,0为不限制)', 'text', 'article', 5);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('address', '厦门市思明区软件园二期望海路000号000室', '联系地址','text', 'contact', 1);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('zip_code', '361008', '邮编', 'text', 'contact', 2);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('fax', '0592-1234567', '传真', 'text', 'contact', 3);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('tel', '0592-5000000', '联系电话', 'text', 'contact', 4);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('contact', 'beyongx sir', '联系人', 'text', 'contact', 5);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('email', 'xx@xxx.com', '联系邮箱', 'text', 'contact', 6);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('qq', 'qq_xxx', '联系QQ', 'text', 'contact', 7);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('weixin', 'weixin_xx', '联系微信', 'text', 'contact', 8);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('email_addr', 'service@beyongx.com', '发件邮箱地址', 'text', 'email', 3);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('email_host', 'smtp.exmail.qq.com', '邮箱SMTP服务器地址', 'text', 'email', 1);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('email_name', 'service', '发件邮箱名称', 'text', 'email', 5);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('email_pass', 'password', '发件邮箱密码', 'text', 'email', 4);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('email_port', '465', 'SMTP服务器端口,一般为25', 'number', 'email', 2);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('email_security', 'ssl', '加密方式：null|ssl|tls, QQ邮箱必须使用ssl', 'text', 'email', 0);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('email_activate_user', '<style type=\"text/css\">\r\n  p{text-indent: 2em;}\r\n</style>\r\n<div><strong>尊敬的用户</strong></div>\r\n<p>您好，非常感谢您对Beyongx(<a href=\"https://www.ituizhan.com/\" target=\"_blank\" title=\"Beyongx\">Beyongx</a>)的关注和热爱</p>\r\n<p>您本次申请注册成为Beyongx会员的邮箱验证链接是: <a style=\"font-size: 30px;color: red;\" href=\"{url}\">{url}</a></p>\r\n<p>如果非您本人操作，请忽略该邮件。</p>\r\n', '新用户邮箱激活html格式', 'muti_text', 'email_template', 6);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('email_reset_password', '<style type=\"text/css\">\r\np{text-indent: 2em;}\r\n</style>\r\n<div><strong>尊敬的用户</strong></div>\r\n<p>您好，非常感谢您对Beyongx(<a href=\"https://www.ituizhan.com/\" target=\"_blank\" title=\"Beyongx\">Beyongx</a>)的关注和热爱</p>\r\n<p>您本次申请找回密码的邮箱验证码是: <strong style=\"font-size: 30px;color: red;\">{code}</strong></p>\r\n<p>您本次重置密码的邮箱链接是: <a style=\"font-size: 30px;color: red;\"  href=\"{url}\">{url}</strong>\r\n<p>如果非您本人操作，请忽略该邮件。</p>\r\n', '用户邮箱重置密码html格式', 'muti_text', 'email_template', 7);

INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('tab_meta', '[{\"tab\":\"base\",\"name\":\"基本设置\",\"sort\":1},{\"tab\":\"seo\",\"name\":\"SEO设置\",\"sort\":2},{\"tab\":\"contact\",\"name\":\"联系方式\",\"sort\":3},{\"tab\":\"email\",\"name\":\"邮箱设置\",\"sort\":4},{\"tab\":\"article\",\"name\":\"文章设置\",\"sort\":5},{\"tab\":\"aliyun_oss\",\"name\":\"阿里OSS存储\",\"sort\":6},{\"tab\":\"qiniuyun_oss\",\"name\":\"七牛OSS存储\",\"sort\":7},{\"tab\":\"email_template\",\"name\":\"邮件模板\",\"sort\":8},{\"tab\":\"oss\",\"name\":\"OSS存储设置\",\"sort\":9}]', 'tab标签元数据', 'text', NULL, 0);

#oss存储配置
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('oss_switch', 'false', 'OSS存储开关', 'bool', 'oss', 1);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('oss_vendor', 'qiniuyun', 'OSS', 'text', 'oss', 2);

INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('ali_bucket', 'Bucket名称', '阿里oss Bucket名称', 'text', 'aliyun_oss', 1);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('ali_endpoint', 'xxxx.aliyuncs.com', '阿里oss 外网地址endpoint', 'text', 'aliyun_oss', 2);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('ali_key_id', '阿里云key id', '阿里Access Key ID', 'text', 'aliyun_oss', 3);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('ali_key_secret', '阿里云key secret', '阿里Access Key Secret', 'text', 'aliyun_oss', 4);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('ali_url', '阿里云oss域名地址', '阿里oss 访问的地址', 'text', 'aliyun_oss', 5);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('qiniu_bucket', 'Bucket名称', '七牛oss Bucket', 'text', 'qiniuyun_oss', 1);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('qiniu_key_id', '七牛oss Accesskey', '七牛oss Accesskey', 'text', 'qiniuyun_oss', 2);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('qiniu_key_secret', '七牛oss Secretkey', '七牛oss Secretkey', 'text', 'qiniuyun_oss', 3);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('qiniu_url', '七牛域名地址', '七牛oss 访问的地址', 'text', 'qiniuyun_oss', 4);

#百度站长资源push
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('zhanzhang_site', '站长域名', '', 'text', 'zhanzhang', 1);
INSERT INTO `sys_config`(name,value,remark,value_type,tab,sort) VALUES ('zhanzhang_token', '站长token', '', 'text', 'zhanzhang', 2);

/* ================================================================================================*/
/* ============================================数据初始脚本：用户表================================*/
truncate sys_user;

#默认密码为 888888
INSERT INTO
    `sys_user`(`id`,`mobile`,`email`,`account`,`password`,`status`,`nickname`,`sex`,`head_url`,`device_id`,`register_time`,`last_login_time`,`last_login_ip`)
VALUES
    (1,'18888888888','admin@admin.com','admin','f6bc5c8794afdae1dd41edb7939020e2',2,'超级管理员',1,null,null,'2015-01-01 00:00:00','2017-05-12 15:55:52','110.84.32.49');

/* ================================================================================================*/
/* =========================================数据初始脚本：角色权限表===============================*/
truncate table sys_auth_rule;

#控制面板,首页等进入页面,(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to)
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (1, 0, '综合面板', 'admin/ShowNav/Index', 'fa-th-large', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (11, 1, '后台主框架', 'admin/Index/index', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (111, 11, '面板消息', 'admin/Message/index', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (12, 1, '欢迎页面', 'admin/Index/welcome', '', 1, 0, 1, 1,'','admin');

INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (13, 1, '基础面板','admin/Index/dashboard', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (131, 13, '今日数据','admin/Index/today', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (132, 13, '本月数据', 'admin/Index/month', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (133, 13, '年度数据', 'admin/Index/year', '', 1, 0, 1, 1,'','admin');
#公共功能列表,可能在其他版块也会用到
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (19, 0, '公共功能列表', 'admin/ShowNav/Common', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (191, 19, '文件上传', 'admin/File/upload', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (192, 19, '图片上传', 'admin/Image/upload', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (193, 19, '软件上传', 'admin/File/uploadSoftware', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (194, 19, '移动App上传', 'admin/File/uploadApp', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (195, 19, '百度编辑器接口', 'admin/BaiduUeditor/index', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (196, 19, '图片上传截取', 'admin/Image/upcrop', '', 1, 0, 1, 1,'','admin');

#个人中心模块
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (2, 0, '个人中心', 'admin/ShowNav/Person', 'fa-user', 1, 1, 14, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (21, 2, '个人首页', 'admin/Person/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (22, 2, '修改资料', 'admin/Person/profile', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (23, 2, '修改密码', 'admin/Person/password', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (211, 21, '查看文章', 'admin/Person/viewArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (212, 21, '编辑文章', 'admin/Person/editArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (213, 21, '删除文章', 'admin/Person/deleteArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (214, 21, '发布文章', 'admin/Person/postArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (215, 21, '上头条', 'admin/Person/upTop', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (216, 21, '取消头条', 'admin/Person/deleteTop', '', 1, 0, 1, 1,'','admin');

#用户管理模块
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (3, 0, '用户管理', 'admin/ShowNav/User', 'fa-users', 1, 1, 16, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (31, 3, '用户列表', 'admin/User/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (32, 3, '新增用户', 'admin/User/addUser', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (33, 3, '用户统计', 'admin/User/userStat', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (311, 31, '修改用户', 'admin/User/editUser', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (312, 31, '查看用户', 'admin/User/viewUser', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (313, 31, '修改密码', 'admin/User/changePwd', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (314, 31, '删除用户', 'admin/User/deleteUser', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (315, 31, '发送邮件', 'admin/User/sendMail', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (316, 31, '激活用户', 'admin/User/active', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (317, 31, '冻结用户', 'admin/User/freeze', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (318, 33, '统计报表数据', 'admin/User/echartShow', '', 1, 0, 1, 1,'','admin');

#权限管理模块
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (4, 0, '权限管理', 'admin/ShowNav/Rule', 'fa-key', 1, 1, 17, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (41, 4, '权限规则', 'admin/Rule/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (411, 41, '新增权限规则', 'admin/Rule/add', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (412, 41, '编辑权限规则', 'admin/Rule/edit', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (413, 41, '删除权限规则', 'admin/Rule/delete', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (414, 41, '排序权限规则', 'admin/Rule/order', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (415, 41, '设置菜单值', 'admin/Rule/setMenu', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (42, 4, '用户分组', 'admin/Rule/group', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (421, 42, '新增分组', 'admin/Rule/addGroup', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (422, 42, '编辑分组', 'admin/Rule/editGroup', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (423, 42, '删除分组', 'admin/Rule/deleteGroup', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (424, 42, '分配权限', 'admin/Rule/ruleGroup', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (425, 42, '分组成员', 'admin/Rule/checkUser', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (426, 42, '添加成员', 'admin/Rule/addUserToGroup', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (427, 42, '移除成员', 'admin/Rule/deleteUserFromGroup', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (43, 4, '管理员列表', 'admin/Rule/userList', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (431, 43, '添加管理员', 'admin/Rule/addAdmin', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (432, 43, '编辑管理员', 'admin/Rule/editAdmin', '', 1, 0, 1, 1,'','admin');

#系统管理模块
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (5, 0, '系统管理', 'admin/ShowNav/System', 'fa-cog', 1, 1, 18, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (51, 5, '系统设置', 'admin/ShowNav/System/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (511, 51, '基本设置', 'admin/System/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (512, 51, '通知设置', 'admin/System/notification', '', 1, 1, 2, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (513, 51, '邮件测试', 'admin/System/testEmail', '', 1, 0, 3, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (514, 51, '短信测试', 'admin/System/testSms', '', 1, 0, 4, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (515, 51, '公众好测试', 'admin/System/testMp', '', 1, 0, 5, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (52, 5, '友情链接', 'admin/System/links', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (521, 52, '添加友链', 'admin/System/addLinks', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (522, 52, '修改友链', 'admin/System/editLinks', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (523, 52, '排序友链', 'admin/System/orderLinks', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (524, 52, '删除友链', 'admin/System/deleteLinks', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (53, 5, '清理缓存', 'admin/System/clearCache', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (54, 5, '日志审计', 'admin/System/actionLogs', '', 1, 1, 1, 1,'','admin');

#扩展功能 (主题和插件)
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (6, 0, '扩展管理', 'admin/ShowNav/Extension', 'fa-th-list', 1, 1, 15, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (61, 6, '主题管理', 'admin/Theme/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (611, 6, '查看主题', 'admin/Theme/viewTheme', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (612, 6, '主题演示', 'admin/Theme/demo', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (613, 6, '下载主题', 'admin/Theme/download', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (614, 6, '上传主题', 'admin/Theme/upload', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (615, 6, '更新主题', 'admin/Theme/update', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (616, 6, '切换主题', 'admin/Theme/setCurrentTheme', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (62, 6, '插件管理', 'admin/Addon/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (621, 6, '查看插件', 'admin/Theme/viewTheme', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (622, 6, '插件演示', 'admin/Theme/demo', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (623, 6, '下载插件', 'admin/Theme/download', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (624, 6, '上传插件', 'admin/Theme/upload', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (625, 6, '更新插件', 'admin/Theme/update', '', 1, 0, 1, 1,'','admin');

#内容管理
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (7, 0, '内容管理', 'admin/ShowNav/Cms', 'fa-file-text', 1, 1, 11, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (71, 7, '文章管理', 'admin/Article/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (711, 71, '查看文章', 'admin/Article/viewArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (712, 71, '新增文章', 'admin/Article/addArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (713, 71, '编辑文章', 'admin/Article/editArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (714, 71, '删除文章', 'admin/Article/deleteArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (715, 71, '置顶', 'admin/Article/setTop', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (716, 71, '取消置顶', 'admin/Article/unsetTop', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (717, 71, '发布文章', 'admin/Article/postArticle', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (7171, 71, '初审', 'admin/Article/auditFirst', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (7172, 71, '终审', 'admin/Article/auditSecond', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (7173, 71, '定时发布', 'admin/Article/setTimingPost', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (7174, 71, '文章访问统计', 'admin/Article/articleStat', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (7175, 71, '文章访问量统计图', 'admin/Article/echartShow', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (7176, 71, '批量修改分类', 'admin/Article/batchCategory', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (72, 7, '评论管理', 'admin/Article/commentList', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (721, 72, '审核评论', 'admin/Article/auditComment', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (722, 72, '回发评论', 'admin/Article/postComment', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (723, 72, '删除评论', 'admin/Article/deleteComment', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (724, 72, '查看评论', 'admin/Article/viewComments', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (73, 7, '文章分类', 'admin/Article/categoryList', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (731, 73, '新增分类', 'admin/Article/addCategory', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (732, 73, '编辑分类', 'admin/Article/editCategory', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (733, 73, '排序分类', 'admin/Article/orderCategory', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (734, 73, '删除分类', 'admin/Article/deleteCategory', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (74, 7, '广告管理', 'admin/Article/adList', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (741, 74, '新增广告', 'admin/Article/addAd', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (742, 74, '编辑广告', 'admin/Article/editAd', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (743, 74, '广告排序', 'admin/Article/orderAd', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (744, 74, '删除广告', 'admin/Article/deleteAd', '', 1, 0, 1, 1,'','admin');

#客服管理
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (8, 0, '客服管理', 'admin/ShowNav/Feedback', 'fa-comment', 1, 1, 12, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (81, 8, '客服消息', 'admin/Feedback/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (811, 81, '消息列表', 'admin/Feedback/chat', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (812, 81, '消息回复', 'admin/Feedback/reply', '', 1, 0, 1, 1,'','admin');

#资源管理
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (9, 0, '资源管理', 'admin/ShowNav/Resource', 'fa-archive', 1, 1, 13, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (91, 9, '文档管理', 'admin/Resource/documents', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (911, 91, '上传文档', 'admin/Resource/uploadDocument', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (912, 91, '删除文档', 'admin/Resource/deleteDocument', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (92, 9, '图片管理', 'admin/Resource/images', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (921, 92, '上传图片', 'admin/Resource/uploadImage', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (922, 92, '删除图片', 'admin/Resource/deleteImage', '', 1, 0, 1, 1,'','admin');

#采集系统
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (10, 0, '采集系统', 'admin/ShowNav/Crawler', 'fa-bug', 1, 1, 14, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (101, 10, '采集列表', 'admin/Crawler/index', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (1011, 101, '编辑规则', 'admin/Crawler/edit', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (1012, 101, '采集操作', 'admin/Crawler/startCrawl', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (1013, 101, '删除规则', 'admin/Crawler/deleteCrawler', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (1014, 101, '克隆规则', 'admin/Crawler/cloneCrawler', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (102, 10, '新增采集', 'admin/Crawler/create', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (1021, 102, '采集测试', 'admin/Crawler/crawlTest', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (103, 10, '数据预处理', 'admin/Crawler/preprocess', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (1031, 103, '数据清洗', 'admin/Crawler/cleanData', '', 1, 0, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (104, 10, '数据入库', 'admin/Crawler/warehouse', '', 1, 1, 1, 1,'','admin');
INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (105, 10, '发布计划', 'admin/Crawler/postPlan', '', 1, 1, 1, 1,'','admin');

#系统定制,从200开始
#INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (200, 0, 'CRM管理', 'admin/ShowNav/Crm', 'fa-suitcase', 1, 1, 1, 1,'','admin');
#INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (2001, 200, '产品列表', 'admin/Crm/goodsList', '', 1, 1, 1, 1,'','admin');
#INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (2002, 2001, '新增产品', 'admin/Crm/createGoods', '', 1, 0, 1, 1,'','admin');
#INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (2003, 2001, '编辑产品', 'admin/Crm/editGoods', '', 1, 0, 1, 1,'','admin');
#INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (2004, 2001, '删除产品', 'admin/Crm/deleteGoods', '', 1, 0, 1, 1,'','admin');
#INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (2005, 2001, '上架产品', 'admin/Crm/putOn', '', 1, 0, 1, 1,'','admin');
#INSERT INTO `sys_auth_rule`(id,pid,title,name,icon,type,is_menu,sort,status,`condition`,belongs_to) VALUES (2006, 2001, '下架产品', 'admin/Crm/takeOff', '', 1, 0, 1, 1,'','admin');


truncate table sys_auth_group;

INSERT INTO `sys_auth_group` (`id`,`title`,`status`,`rules`) VALUES (1, '超级管理员', 1, (select GROUP_CONCAT(DISTINCT id SEPARATOR ',') from sys_auth_rule));
INSERT INTO `sys_auth_group` (`id`,`title`,`status`,`rules`) VALUES (2, '普通管理员', 1, '');
INSERT INTO `sys_auth_group` (`id`,`title`,`status`,`rules`) VALUES (3, '网站编辑', 1, '');
INSERT INTO `sys_auth_group` (`id`,`title`,`status`,`rules`) VALUES (4, '普通用户', 1, '');
#update `sys_auth_group` set rules=(select GROUP_CONCAT(DISTINCT id SEPARATOR ',') from sys_auth_rule where belongs_to='admin') where id = 1;

truncate table sys_auth_group_access;

INSERT INTO `sys_auth_group_access` (`uid`,`group_id`) VALUES (1, 1);


/* ================================================================================================*/
/* =========================================数据初始脚本：插件及钩子=============================*/
truncate sys_addons;
truncate sys_hooks;

#配置插件
INSERT INTO `sys_addons`(id,name,title,description,status,config,author,version,create_time,has_adminlist) VALUES (1, 'test', 'test插件', 'test插件简介', 1, NULL, 'test', '0.1', '2018-01-01 00:00:00', 0);
INSERT INTO `sys_addons`(id,name,title,description,status,config,author,version,create_time,has_adminlist) VALUES (2, 'enhance', '系统增强插件', 'Cms系统增强插件,用于前后部分定制', 1, NULL, 'beyongx', '0.1', '2018-06-12 00:00:00', 0);

#配置插件中可使用的钩子
INSERT INTO `sys_hooks`(id,name,description,type,status,addons,update_time,create_time) VALUES (21, 'demo', 'demo钩子', 1, 1, 'test', '2018-01-01 00:00:00', '2018-01-01 00:00:00');
INSERT INTO `sys_hooks`(id,name,description,type,status,addons,update_time,create_time) VALUES (22, 'userTimeline', '用户动态列表', 1, 1, 'enhance', '2018-06-12 00:00:00', '2018-06-12 00:00:00');
INSERT INTO `sys_hooks`(id,name,description,type,status,addons,update_time,create_time) VALUES (23, 'userBalance', '用户帐户信息', 1, 1, 'enhance', '2018-06-12 00:00:00', '2018-06-12 00:00:00');


/* ================================================================================================*/
/* =========================================数据初始脚本：文章及广告表=============================*/
truncate table cms_category;

INSERT INTO `cms_category`(id,pid,title_cn,title_en,remark,status,sort,create_time) VALUES (1, 0, '公司新闻', 'company', '公司新闻文章', 1, 1, '2018-01-19 00:00:00');
INSERT INTO `cms_category`(id,pid,title_cn,title_en,remark,status,sort,create_time) VALUES (2, 0, '公司相册', 'album', '公司相册介绍', 1, 2, '2018-01-19 00:00:00');
INSERT INTO `cms_category`(id,pid,title_cn,title_en,remark,status,sort,create_time) VALUES (3, 0, '公司产品', 'product', '公司产品介绍', 1, 3, '2018-01-19 00:00:00');
INSERT INTO `cms_category`(id,pid,title_cn,title_en,remark,status,sort,create_time) VALUES (4, 0, '行业新闻', 'news', '行业新闻文章', 1, 4, '2018-01-19 00:00:00');
INSERT INTO `cms_category`(id,pid,title_cn,title_en,remark,status,sort,create_time) VALUES (5, 0, '行业动态', 'status', '行业动态文章', 1, 5, '2018-01-19 00:00:00');

truncate table cms_ad_slot;

INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (1, '首页头条广告', 'banner_headline', '首页头条广告左右滚动');
INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (2, '首页顶部广告', 'index_header', '首页顶部广告');
INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (3, '首页中间广告', 'index_center', '首页中间广告');
INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (4, '首页底部广告', 'index_footer', '首页底部广告');
INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (5, '侧边栏头部广告', 'sidebar_header', '侧边栏头部广告');
INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (6, '侧边栏中间广告', 'sidebar_center', '侧边栏中间广告');
INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (7, '侧边栏底部广告', 'sidebar_footer', '侧边栏底部广告');
INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (10, '搜索框', 'search', '搜索框下拉推荐广告');
INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (11, '分类列表页头部', 'category_list_header', '显示于分类列表页头部');
INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (12, '分类列表页中间', 'category_list_center', '显示于分类列表页中间');
INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (13, '分类列表页底部', 'category_list_footer', '显示于分类列表页底部');
INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (14, '文章列表页头部', 'article_list_header', '显示于文章列表页头部');
INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (15, '文章列表页中间', 'article_list_center', '显示于文章列表页中间');
INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (16, '文章列表页底部', 'article_list_footer', '显示于文章列表页底部');
INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (17, '文章详细页头部', 'article_view_header', '显示于文章详细页头部');
INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (18, '文章详细页中间', 'article_view_center', '显示于文章详细页中间');
INSERT INTO `cms_ad_slot`(id, title_cn, title_en, remark) VALUES (19, '文章详细页底部', 'article_view_footer', '显示于文章详细页底部');

/* ================================================================================================*/
/* =========================================数据初始脚本：设置自增起始=============================*/
alter table sys_user AUTO_INCREMENT=100000;
alter table sys_message AUTO_INCREMENT=100000;
alter table sys_file AUTO_INCREMENT=100000;
alter table sys_image AUTO_INCREMENT=100000;
alter table cms_article AUTO_INCREMENT=100000;
alter table cms_category AUTO_INCREMENT=100;
alter table api_config_access AUTO_INCREMENT=1001000;


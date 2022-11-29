SET FOREIGN_KEY_CHECKS=0;

/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     2021-12-20 18:23:02                          */
/*==============================================================*/


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

drop table if exists cms_feedback;

drop table if exists cms_link;

#drop index idx_action_log_action_username on sys_action_log;

#drop index idx_action_log_create_time on sys_action_log;

drop table if exists sys_action_log;

#drop index idx_sys_config_key on sys_config;

#drop index uniq_sys_config_group_key on sys_config;

drop table if exists sys_config;

drop table if exists sys_dept;

drop table if exists sys_file;

drop table if exists sys_job;

drop table if exists sys_menu;

#drop index idx_message_to_uid on sys_message;

#drop index idx_message_type_status on sys_message;

drop table if exists sys_message;

drop table if exists sys_region;

drop table if exists sys_role;

#drop index uniq_role_menu_role_id_menu_id on sys_role_menu;

drop table if exists sys_role_menu;

#drop index uniq_user_account on sys_user;

#drop index uniq_user_email on sys_user;

#drop index uniq_user_mobile on sys_user;

drop table if exists sys_user;

#drop index uniq_user_job_uid_job_id on sys_user_job;

drop table if exists sys_user_job;

#drop index idx_user_meta_target_id_meta_key on sys_user_meta;

drop table if exists sys_user_meta;

#drop index uniq_user_role_uid_role_id on sys_user_role;

drop table if exists sys_user_role;

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
   name                 varchar(32) not null comment '广告槽名称',
   title                varchar(32) not null comment '广告槽标题',
   remark               varchar(128) comment '备注',
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
   sort                 int default 0,
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
   target_id            int not null,
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
   target_id
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
   pid                  int not null,
   name                 varchar(64) not null,
   title                varchar(64) not null,
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

alter table cms_category_article comment '分类文章关联表';

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
   title                varchar(128) not null comment '标题',
   url                  varchar(256) not null comment '地址',
   sort                 int not null default 0 comment '排名',
   status               tinyint not null default 1 comment '状态',
   start_time           datetime comment '开始时间',
   end_time             datetime comment '结束时间',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table cms_link comment '友链表';

/*==============================================================*/
/* Table: sys_action_log                                        */
/*==============================================================*/
create table sys_action_log
(
   id                   bigint not null auto_increment,
   action               varchar(64) not null comment '操作类型',
   username             varchar(255) comment '用户名',
   module               varchar(255) comment '模块',
   component            varchar(255) comment '组件',
   ip                   varchar(64),
   action_time          bigint,
   params               text,
   user_agent           text comment '用户代理',
   response             text,
   response_time        bigint,
   remark               varchar(256),
   create_time          datetime not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_action_log comment '日志表';

/*==============================================================*/
/* Index: idx_action_log_create_time                            */
/*==============================================================*/
create index idx_action_log_create_time on sys_action_log
(
   create_time
);

/*==============================================================*/
/* Index: idx_action_log_action_username                        */
/*==============================================================*/
create index idx_action_log_action_username on sys_action_log
(
   action,
   username
);

/*==============================================================*/
/* Table: sys_config                                            */
/*==============================================================*/
create table sys_config
(
   id                   int(11) not null auto_increment,
   name                 varchar(255) comment '字典名称',
   `group`              varchar(255) comment '字典组',
   `key`                varchar(255) comment '字典键',
   value                text comment '字典值',
   value_type           varchar(16) comment '值类型 integer,float,string,text,boolean',
   status               tinyint comment '启用状态0.关停1.启用',
   sort                 int comment '排序',
   remark               varchar(512) comment '备注',
   create_by            varchar(255) comment '创建者',
   update_by            varchar(255) comment '更新者',
   create_time          datetime comment '创建时间',
   update_time          datetime comment '更新时间',
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_config comment '字典表';

/*==============================================================*/
/* Index: uniq_sys_config_group_key                             */
/*==============================================================*/
create unique index uniq_sys_config_group_key on sys_config
(
   `group`,
   `key`
);

/*==============================================================*/
/* Index: idx_sys_config_key                                    */
/*==============================================================*/
create index idx_sys_config_key on sys_config
(
   `key`
);

/*==============================================================*/
/* Table: sys_dept                                              */
/*==============================================================*/
create table sys_dept
(
   id                   int(11) not null auto_increment,
   pid                  int comment '上级部门',
   name                 varchar(255) comment '部门标识',
   title                varchar(255) comment '部门名称',
   status               tinyint comment '状态 -1.删除0.关停1.启用',
   sort                 int comment '排序',
   remark               varchar(512) comment '备注',
   create_by            varchar(255),
   update_by            varchar(255),
   create_time          datetime,
   update_time          datetime,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_dept comment '部门表';

/*==============================================================*/
/* Table: sys_file                                              */
/*==============================================================*/
create table sys_file
(
   id                   int not null auto_increment,
   file_url             varchar(256) not null comment '文件url',
   file_path            varchar(256) not null comment '文件路径: file_ulr所在目录',
   name                 varchar(128) not null comment '名称',
   real_name            varchar(128) comment '原始名称',
   size                 int comment '大小',
   ext                  varchar(16) comment '后缀',
   bucket               varchar(64) comment 'oss桶',
   oss_url              varchar(512) comment 'ossurl',
   thumb_image_url      varchar(256) comment '缩略图',
   remark               varchar(512) comment '备注',
   create_by            varchar(255) comment '创建者',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_file comment '文件表';

/*==============================================================*/
/* Table: sys_job                                               */
/*==============================================================*/
create table sys_job
(
   id                   int(11) not null auto_increment,
   name                 varchar(255) comment '岗位标识',
   title                varchar(255) comment '岗位名称',
   status               tinyint comment '状态 -1.删除0.关停1.启用',
   sort                 int comment '排序',
   remark               varchar(512) comment '备注',
   create_by            varchar(255),
   update_by            varchar(255),
   create_time          datetime,
   update_time          datetime,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_job comment '岗位表';

/*==============================================================*/
/* Table: sys_menu                                              */
/*==============================================================*/
create table sys_menu
(
   id                   int not null auto_increment,
   pid                  int not null default 0 comment '父节点',
   title                varchar(64) not null comment '标题',
   name                 varchar(64) comment '名称',
   component            varchar(255) comment '前端组件',
   path                 varchar(255) comment '路由地址',
   icon                 varchar(64) comment '图标',
   type                 tinyint(1) not null default 1 comment '菜单类型 0.网页页面1.菜单组件2.动作组件',
   is_menu              tinyint(1) not null comment '是否菜单',
   permission           varchar(64) comment '权限标识',
   status               tinyint(1) not null default 1 comment '状态 -1.删除;0.暂停;1.激活;',
   sort                 int not null default 0 comment '排序',
   belongs_to           varchar(16) comment '归属于',
   create_by            varchar(255) comment '创建者',
   update_by            varchar(255) comment '更新者',
   create_time          datetime comment '创建时间',
   update_time          datetime comment '更新时间',
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_menu comment '菜单表';

/*==============================================================*/
/* Table: sys_message                                           */
/*==============================================================*/
create table sys_message
(
   id                   bigint not null auto_increment comment '消息id',
   type                 varchar(16) not null comment '类型',
   title                varchar(256) not null comment '标题',
   content              text not null comment '内容',
   status               tinyint not null comment '状态 -1.删除.0.草稿;1.提交;2.已发送;',
   from_uid             varchar(64) not null comment '发送uid',
   to_uid               varchar(64) not null comment 'to用户id',
   send_time            datetime comment '发送时间',
   is_readed            boolean default 0 comment '是否已读',
   read_time            datetime comment '读取时间',
   ext                  text comment '扩展ext',
   create_time          datetime not null comment '创建时间',
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

alter table sys_region comment '地区表';

/*==============================================================*/
/* Table: sys_role                                              */
/*==============================================================*/
create table sys_role
(
   id                   smallint(6) not null auto_increment,
   name                 varchar(64) comment '角色标识',
   title                varchar(64) comment '角色名称',
   status               tinyint(1) default 1 comment '状态:1.激活;2.冻结;3.删除',
   remark               varchar(512) comment '备注',
   create_by            varchar(255) comment '创建者',
   update_by            varchar(255) comment '更新者',
   create_time          datetime comment '创建时间',
   update_time          datetime comment '更新时间',
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_role comment '角色表';

/*==============================================================*/
/* Table: sys_role_menu                                         */
/*==============================================================*/
create table sys_role_menu
(
   id                   int not null auto_increment,
   role_id              int not null,
   menu_id              int not null,
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_role_menu comment '角色菜单关联表';

/*==============================================================*/
/* Index: uniq_role_menu_role_id_menu_id                        */
/*==============================================================*/
create index uniq_role_menu_role_id_menu_id on sys_role_menu
(
   role_id,
   menu_id
);

/*==============================================================*/
/* Table: sys_template_msg                                      */
/*==============================================================*/
create table sys_template_msg
(
   id                   bigint not null auto_increment comment '序号',
   code                 varchar(32) not null comment '模板编码',
   type                 varchar(16) not null comment '类型',
   name                 varchar(256) not null,
   content              text not null comment '内容模板',
   status               tinyint not null comment '状态 -1.删除.0.失效;1.生效',
   update_time          datetime not null comment '更新时间',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_template_msg comment '模板消息表';

/*==============================================================*/
/* Table: sys_user                                              */
/*==============================================================*/
create table sys_user
(
   id                   int not null auto_increment,
   mobile               varchar(24) not null comment '手机号',
   email                varchar(32) not null comment '邮箱',
   account              varchar(32) not null comment '帐号',
   password             varchar(64) not null comment '密码',
   status               tinyint not null comment '状态:-1.删除;1.申请;2.激活;3.冻结;',
   nickname             varchar(64) comment '昵称',
   sex                  tinyint default 1 comment '性别:1.男;2.女;3.未知;',
   head_url             varchar(128) comment '头像url',
   dept_id              int comment '部门id',
   qq                   varchar(16) comment 'qq号',
   weixin               varchar(64) comment '微信号',
   referee              varchar(64) comment '介绍人',
   salt                 varchar(128) comment '盐串',
   register_time        datetime not null comment '注册时间',
   register_ip          varchar(64) comment '注册ip',
   from_referee         varchar(256) comment '来源',
   entrance_url         varchar(256) comment '首访页',
   last_login_time      datetime comment '最后登录时间',
   last_login_ip        varchar(64) comment '最后登录ip',
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
/* Table: sys_user_job                                          */
/*==============================================================*/
create table sys_user_job
(
   id                   int not null auto_increment,
   uid                  int not null comment '用户id',
   job_id               int not null comment '岗位id',
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_user_job comment '用户岗位关联表';

/*==============================================================*/
/* Index: uniq_user_job_uid_job_id                              */
/*==============================================================*/
create unique index uniq_user_job_uid_job_id on sys_user_job
(
   uid,
   job_id
);

/*==============================================================*/
/* Table: sys_user_meta                                         */
/*==============================================================*/
create table sys_user_meta
(
   id                   int not null auto_increment,
   target_id            int not null,
   meta_key             varchar(32) not null comment '元数据key',
   meta_value           text not null comment '元数据value',
   update_time          datetime not null comment '更新时间',
   create_time          datetime not null comment '创建时间',
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

/*==============================================================*/
/* Table: sys_user_role                                         */
/*==============================================================*/
create table sys_user_role
(
   id                   int not null auto_increment,
   uid                  int not null comment '用户id',
   role_id              int not null comment '角色id',
   primary key (id)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;

alter table sys_user_role comment '用户角色关联表';

/*==============================================================*/
/* Index: uniq_user_role_uid_role_id                            */
/*==============================================================*/
create unique index uniq_user_role_uid_role_id on sys_user_role
(
   uid,
   role_id
);







/* ==================================================================================================*/
/* ============================================数据初始脚本：config表================================*/
truncate table sys_config;

INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('网站名称', 'base', 'site_name', 'BeyongCms内容管理系统', '网站名称', 'string', 1, 1);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('域名', 'base', 'domain_name', 'www.beyongx.com', '域名', 'string', 1, 2);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('备案号', 'base', 'icp', '闽ICP备xxxxxxxx号-1', '备案号', 'string', 1, 3);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('加密密钥', 'base', 'password_key', 'lGfFSc17z8Q15P5kU0guNqq906DHNbA3', '加密密钥', 'string', 1, 4);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('主题名称', 'base', 'theme_package_name', 'classic', '主题名称', 'string', 1, 5);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('统计代码', 'base', 'stat_code', '<script>\r\nvar _hmt = _hmt || [];\r\n(function() {\r\n  var hm = document.createElement(\"script\");\r\n  hm.src = \"https://hm.baidu.com/hm.js?3d0c1af3caa383b0cd59822f1e7a751b\";\r\n  var s = document.getElementsByTagName(\"script\")[0]; \r\n  s.parentNode.insertBefore(hm, s);\r\n})();\r\n</script>\r\n<!-- 以下为自动提交代码 -->\r\n<script>\r\n(function(){\r\n    var bp = document.createElement(\"script\");\r\n    var curProtocol = window.location.protocol.split(\":\")[0];\r\n    if (curProtocol === \"https\") {\r\n        bp.src = \"https://zz.bdstatic.com/linksubmit/push.js\";\r\n    }\r\n    else {\r\n        bp.src = \"http://push.zhanzhang.baidu.com/push.js\";\r\n    }\r\n    var s = document.getElementsByTagName(\"script\")[0];\r\n    s.parentNode.insertBefore(bp, s);\r\n})();\r\n</script>\r\n', '统计代码', 'text', 1, 6);

INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('网站标题', 'seo', 'title', 'BeyongCms平台', '网站标题', 'string', 1, 1);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('网站描述', 'seo', 'description', 'BeyongCms内容管理系统|Beyongx,ThinkPHP,CMS，可二次开发的扩展框架，包含用户管理，权限角色管理及内容管理等', '网站描述', 'text', 1, 3);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('网站关键词，有英文逗号分隔', 'seo', 'keywords', 'BeyongCms,Beyongx,ThinkPHP,CMS内容管理系统,扩展框架', '网站关键词，有英文逗号分隔', 'string', 1, 3);

INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('公司名称', 'company', 'company_name', 'XXX公司', '公司名称', 'string', 1, 1);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('公司名称', 'company', 'bank_card', 'xxx', '公司银行账号', 'string', 1, 2);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('公司名称', 'company', 'bank_name', '招商银行', '公司银行帐号开户行', 'string', 1, 3);

INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('文章缩略图大小配置', 'article', 'article_thumb_image', '{\"width\":280,\"height\":280,\"thumb_width\":140,\"thumb_height\":140}', '文章缩略图大小配置', 'string', 1, 0);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('文章审核', 'article', 'article_audit_switch', 'true', '文章审核', 'boolean', 1, 1);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('水印开关', 'article', 'article_water', '1', '水印开关(0:无水印,1:水印文字,2:水印图片)', 'number', 1, 2);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('水印文本', 'article', 'article_water_text', '', '水印文本', 'string', 1, 3);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('上传图片质量', 'article', 'image_upload_quality', '80', '上传图片质量', 'string', 1, 4);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('上传图片宽高', 'article', 'image_upload_max_limit', '680', '上传图片宽高最大值(单位px,0为不限制)', 'string', 1, 5);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('联系地址', 'contact', 'address', '厦门市思明区软件园二期望海路000号000室', '联系地址', 'string', 1, 1);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('邮编', 'contact', 'zip_code', '361008', '邮编', 'string', 1, 2);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('传真', 'contact', 'fax', '0592-1234567', '传真', 'string', 1, 3);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('联系电话', 'contact', 'tel', '0592-5000000', '联系电话', 'string', 1, 4);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('联系人', 'contact', 'contact', 'beyongx sir', '联系人', 'string', 1, 5);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('联系邮箱', 'contact', 'email', 'xx@xxx.com', '联系邮箱', 'string', 1, 6);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('联系QQ', 'contact', 'qq', 'qq_xxx', '联系QQ', 'string', 1, 7);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('联系微信', 'contact', 'weixin', 'weixin_xx', '联系微信', 'string', 1, 8);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('邮箱服务器地址', 'email', 'email_host', 'smtp.exmail.qq.com', '邮箱SMTP服务器地址', 'string', 1, 0);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('邮箱服务器端口', 'email', 'email_port', '465', 'SMTP服务器端口,一般为25', 'number', 1, 1);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('加密方式', 'email', 'email_security', 'ssl', '加密方式：null|ssl|tls, QQ邮箱必须使用ssl', 'string', 1, 2);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('发件邮箱名称', 'email', 'email_name', 'service', '发件邮箱名称', 'string', 1, 3);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('发件邮箱地址', 'email', 'email_addr', 'service@beyongx.com', '发件邮箱地址', 'string', 1, 4);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('发件邮箱密码', 'email', 'email_pass', 'password', '发件邮箱密码', 'string', 1, 5);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('新用户邮箱激活html格式', 'email_template', 'email_activate_user', '<style type=\"text/css\">\r\n  p{text-indent: 2em;}\r\n</style>\r\n<div><strong>尊敬的用户</strong></div>\r\n<p>您好，非常感谢您对Beyongx(<a href=\"https://www.ituizhan.com/\" target=\"_blank\" title=\"Beyongx\">Beyongx</a>)的关注和热爱</p>\r\n<p>您本次申请注册成为Beyongx会员的邮箱验证链接是: <a style=\"font-size: 30px;color: red;\" href=\"{url}\">{url}</a></p>\r\n<p>如果非您本人操作，请忽略该邮件。</p>\r\n', '新用户邮箱激活html格式', 'text', 1, 6);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('用户邮箱重置密码html格式', 'email_template', 'email_reset_password', '<style type=\"text/css\">\r\np{text-indent: 2em;}\r\n</style>\r\n<div><strong>尊敬的用户</strong></div>\r\n<p>您好，非常感谢您对Beyongx(<a href=\"https://www.ituizhan.com/\" target=\"_blank\" title=\"Beyongx\">Beyongx</a>)的关注和热爱</p>\r\n<p>您本次申请找回密码的邮箱验证码是: <strong style=\"font-size: 30px;color: red;\">{code}</strong></p>\r\n<p>您本次重置密码的邮箱链接是: <a style=\"font-size: 30px;color: red;\"  href=\"{url}\">{url}</strong>\r\n<p>如果非您本人操作，请忽略该邮件。</p>\r\n', '用户邮箱重置密码html格式', 'text', 1, 7);


#oss存储配置
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('OSS存储开关', 'oss', 'oss_switch', 'false', 'OSS存储开关', 'boolean', 1, 1);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('OSS厂商', 'oss', 'oss_vendor', 'qiniuyun', 'OSS厂商', 'string', 1, 2);

INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('阿里oss Bucket名称', 'aliyun_oss', 'ali_bucket', 'Bucket名称', '阿里oss Bucket名称', 'string', 1, 1);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('阿里oss 外网地址endpoint', 'aliyun_oss', 'ali_endpoint', 'xxxx.aliyuncs.com', '阿里oss 外网地址endpoint', 'string', 1, 2);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('阿里Access Key ID', 'aliyun_oss', 'ali_key_id', '阿里云key id', '阿里Access Key ID', 'string', 1, 3);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('阿里Access Key Secret', 'aliyun_oss', 'ali_key_secret', '阿里云key secret', '阿里Access Key Secret', 'string', 1, 4);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('阿里oss 访问的地址', 'aliyun_oss', 'ali_url', '阿里云oss域名地址', '阿里oss 访问的地址', 'string', 1, 5);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('七牛oss Bucket', 'qiniuyun_oss', 'qiniu_bucket', 'Bucket名称', '七牛oss Bucket', 'string', 1, 1);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('七牛oss Accesskey', 'qiniuyun_oss', 'qiniu_key_id', '七牛oss Accesskey', '七牛oss Accesskey', 'string', 1, 2);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('七牛oss Secretkey', 'qiniuyun_oss', 'qiniu_key_secret', '七牛oss Secretkey', '七牛oss Secretkey', 'string', 1, 3);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('七牛oss 访问的地址', 'qiniuyun_oss', 'qiniu_url', '七牛域名地址', '七牛oss 访问的地址', 'string', 1, 4);

#百度站长资源push
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('站长域名', 'zhanzhang', 'zhanzhang_site', '站长域名', '', 'string', 1, 1);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('站长token', 'zhanzhang', 'zhanzhang_token', '站长token', '', 'string', 1, 2);


#状态字典
##用户状态字典
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('用户状态', 'user_status', '1', '申请', '', 'integer', 1, 1);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('用户状态', 'user_status', '2', '已激活', '', 'integer', 1, 2);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('用户状态', 'user_status', '3', '已冻结', '', 'integer', 1, 3);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('用户状态', 'user_status', '-1', '已删除', '', 'integer', 1, 4);
##消息状态字典
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('消息状态', 'message_status', '0', '草稿', '', 'integer', 1, 0);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('消息状态', 'message_status', '1', '提交', '', 'integer', 1, 1);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('消息状态', 'message_status', '2', '已发送', '', 'integer', 1, 2);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('消息状态', 'message_status', '3', '已阅读', '', 'integer', 1, 3);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('消息状态', 'message_status', '-1', '已删除', '', 'integer', 1, 4);
##文章状态字典
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('文章状态', 'article_status', '0', '草稿', '', 'integer', 1, 0);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('文章状态', 'article_status', '1', '申请发布', '', 'integer', 1, 1);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('文章状态', 'article_status', '2', '初审拒绝', '', 'integer', 1, 2);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('文章状态', 'article_status', '3', '初审通过', '', 'integer', 1, 3);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('文章状态', 'article_status', '4', '终审拒绝', '', 'integer', 1, 4);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('文章状态', 'article_status', '5', '已发布', '', 'integer', 1, 5);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('文章状态', 'article_status', '-1', '已删除', '', 'integer', 1, 6);
##评论状态字典
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('评论状态', 'comment_status', '0', '草稿', '', 'integer', 1, 0);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('评论状态', 'comment_status', '1', '申请发布', '', 'integer', 1, 1);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('评论状态', 'comment_status', '2', '拒绝', '', 'integer', 1, 2);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('评论状态', 'comment_status', '3', '已发布', '', 'integer', 1, 3);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('评论状态', 'comment_status', '-1', '已删除', '', 'integer', 1, 4);
##分类状态字典
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('分类状态', 'category_status', '0', '下线', '', 'integer', 1, 0);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('分类状态', 'category_status', '1', '上线', '', 'integer', 1, 1);
##友链状态字典
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('友链状态', 'link_status', '0', '下线', '', 'integer', 1, 0);
INSERT INTO `sys_config`(name,`group`,`key`,value,remark,value_type,status,sort) VALUES ('友链状态', 'link_status', '1', '上线', '', 'integer', 1, 1);

/* ================================================================================================*/
/* ============================================数据初始脚本：用户表================================*/
truncate `sys_user`;

#默认密码为888888
INSERT INTO 
    `sys_user` (`id`,`mobile`,`email`,`account`,`password`,`status`,`nickname`,`sex`,`head_url`,`salt`, `register_time`,`last_login_time`,`last_login_ip`) 
VALUES 
    (1,'18888888888','admin@beyongx.com','admin','93d7c7b87221947615cec93c04250959',2,'超级管理员',1,'/static/common/img/head/default.jpg', 'lGfFSc17z8Q15P5kU0guNqq906DHNbA3', '2015-01-01 00:00:00','2017-05-12 15:55:52','127.0.0.1');

truncate `sys_user_role`;

INSERT INTO `sys_user_role`(uid,role_id) VALUES(1, 1);

/* ================================================================================================*/
/* =========================================数据初始脚本：角色权限表===============================*/
# 角色初始化
truncate table `sys_role`;

INSERT INTO `sys_role` (`id`,`name`,`title`,`status`,`remark`) VALUES (1, 'admin', '超级管理员', 1, '');
INSERT INTO `sys_role` (`id`,`name`,`title`,`status`,`remark`) VALUES (2, 'manager', '普通管理员', 1, '');
INSERT INTO `sys_role` (`id`,`name`,`title`,`status`,`remark`) VALUES (3, 'editor', '网站编辑', 1, '');
INSERT INTO `sys_role` (`id`,`name`,`title`,`status`,`remark`) VALUES (4, 'member', '普通会员', 1, '');

# 菜单初始化
truncate table sys_menu;

/**************************一级菜单******************************/
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (1, 0, '面板', 'Dashboard', 'dashboard/index', 'dashboard/index','el-icon-s-home', 1, 0, null, 1, 0, 'api');
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (2, 0, '通用公共接口', 'Common', 'Layout', 'common', null, 1, 0, null, 1, 0, 'api');
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (3, 0, '用户中心', 'Ucenter', 'Layout', 'ucenter', null, 1, 0, null, 1, 0, 'api');

INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (4, 0, '内容管理', 'CmsIndex', 'Layout', 'cms', 'el-icon-news', 1, 1, null, 1, 7, 'api');
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (5, 0, '运维管理', 'OperationIndex', 'Layout', 'operation', 'el-icon-data-line', 1, 1, null, 1, 8, 'api');
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (6, 0, '系统管理', 'SystemIndex', 'Layout', 'system', 'el-icon-news', 1, 1, null, 1, 9, 'api');


/**************************二级菜单******************************/
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (100, 4, '文章管理', 'ArticleIndex', 'cms/article/index', 'article/index', null, 1, 1, 'article:list', 1, 0, 'api');
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (101, 4, '评论管理', 'CommentIndex', 'cms/comment/index', 'comment/index', null, 1, 1, 'comment:list', 1, 2, 'api');
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (102, 4, '文章分类', 'CategoryIndex', 'cms/category/index', 'category/index', null, 1, 1, 'category:list', 1, 3, 'api');
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (103, 4, '广告管理', 'AdIndex', 'cms/ad/index', 'ad/index', '', 1, 1, null, 1, 4, 'api');

INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (105, 5, '服务器监控', 'ServerIndex', 'monitor/server/index', 'server/index', '', 1, 1, null, 1, 0, 'api');
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (106, 5, '操作日志', 'LogIndex', 'monitor/log/index', 'log/index', '', 1, 1, null, 1, 1, 'api');
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (107, 5, '数据库管理', 'DatabaseIndex', 'monitor/database/index', 'database/index', '', 1, 1, null, 1, 2, 'api');

INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (108, 6, '用户管理', 'UserIndex', 'system/user/index', 'user/index', '', 1, 1, null, 1, 0, 'api');
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (109, 6, '角色管理', 'RoleIndex', 'system/role/index', 'role/index', '', 1, 1, null, 1, 1, 'api');
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (110, 6, '菜单管理', 'MenuIndex', 'system/menu/index', 'menu/index', '', 1, 1, null, 1, 2, 'api');
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (111, 6, '部门管理', 'DeptIndex', 'system/dept/index', 'dept/index', '', 1, 1, null, 1, 3, 'api');
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (112, 6, '岗位管理', 'JobIndex', 'system/job/index', 'job/index', '', 1, 1, null, 1, 4, 'api');
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (113, 6, '字典管理', 'DictIndex', 'system/dict/index', 'dict/index', '', 1, 1, null, 1, 5, 'api');
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES (114, 6, '友链管理', 'LinkIndex', 'system/link/index', 'link/index', '', 1, 0, null, 1, 6, 'api');


INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(2, '查询字典信息', '', null, '', null, 2, 0, "config:query", 1, 0, 'api'),
(2, '查询状态字典', '', null, '', null, 2, 0, "config:status", 1, 0, 'api'),
(2, '查询部门信息', '', null, '', null, 2, 0, "dept:dict", 1, 0, 'api'),
(2, '查询岗位字典', '', null, '', null, 2, 0, "job:dict", 1, 0, 'api'),
(2, '筛选用户列表', '', null, '', null, 2, 0, "user:quickSelect", 1, 0, 'api'),
(2, '图片上传', '', null, '', null, 2, 0, "image:upload", 1, 0, 'api'),
(2, '文件上传', '', null, '', null, 2, 0, "file:upload", 1, 0, 'api')
;

INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(3, '个人中心', 'UcenterIndex', 'system/user/center', 'index', '', 1, 1, null, 1, 0, 'api'),
(3, '查询用户信息', '', null, '', null, 2, 0, "ucenter:getInfo", 1, 0, 'api'),
(3, '编辑个人资料', '', null, '', null, 2, 0, "ucenter:profile", 1, 0, 'api'),
(3, '修改个人密码', '', null, '', null, 2, 0, "ucenter:modifyPassword", 1, 0, 'api'),
(3, '查询权限菜单', '', null, '', null, 2, 0, "ucenter:menus", 1, 0, 'api'),
(3, '退出登录', '', null, '', null, 2, 0, "sign:logout", 1, 0, 'api')
;

INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(100, '查询文章列表', '', null, '', null, 2, 0, "article:list", 1, 0, 'api'),
(100, '查询文章内容', 'ArticleDetail', 'cms/article/detail', 'articleDetail', null, 1, 0, 'article:query', 1, 1, 'api'),
(100, '新增文章', 'ArticleCreate', 'cms/article/operation', 'articleCreate', null, 1, 0, "article:create", 1, 0, 'api'),
(100, '编辑文章', 'ArticleUpdate', 'cms/article/operation', 'articleUpdate', null, 1, 0, "article:edit", 1, 0, 'api'),
(100, '发布文章', '', null, '', null, 2, 0, "article:publish", 1, 0, 'api'),
(100, '审核文章', '', null, '', null, 2, 0, "article:audit", 1, 0, 'api'),
(100, '删除文章', '', null, '', null, 2, 0, "article:delete", 1, 0, 'api'),
(100, '查询文章评论', '', null, '', null, 2, 0, "article:commentList", 2, 0, 'api')
;

INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(101, '查询评论列表', '', null, '', null, 2, 0, "comment:list", 1, 0, 'api'),
(101, '查询评论内容', '', null, '', null, 2, 0, "comment:query", 1, 0, 'api'),
(101, '新增评论', '', null, '', null, 2, 0, "comment:create", 1, 0, 'api'),
(101, '审核评论', '', null, '', null, 2, 0, "comment:audit", 1, 0, 'api'),
(101, '删除评论', '', null, '', null, 2, 0, "comment:delete", 1, 0, 'api')
;

INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(102, '查询分类列表', '', null, '', null, 2, 0, "category:list", 1, 0, 'api'),
(102, '新增分类', '', null, '', null, 2, 0, "category:create", 1, 0, 'api'),
(102, '编辑分类', '', null, '', null, 2, 0, "category:edit", 1, 0, 'api'),
(102, '上线/下线分类', '', null, '', null, 2, 0, "category:setStatus", 1, 0, 'api'),
(102, '删除分类', '', null, '', null, 2, 0, "category:delete", 1, 0, 'api')
;

INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(103, '查询广告列表', '', null, 'api/ad/list', null, 2, 0, "ad:list", 1, 0, 'api'),
(103, '查询广告插槽', '', null, 'api/ad/slots', null, 2, 0, "ad:slots", 1, 0, 'api'),
(103, '新增广告', '', null, 'api/ad/create', null, 2, 0, "ad:create", 1, 0, 'api'),
(103, '编辑广告', '', null, 'api/ad/edit', null, 2, 0, "ad:edit", 1, 0, 'api'),
(103, '上线/下线广告', '', null, 'api/ad/setStatus', null, 2, 0, "ad:setStatus", 1, 0, 'api'),
(103, '删除广告', '', null, 'api/ad/delete', null, 2, 0, "ad:delete", 1, 0, 'api')
;

INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(105, '查询服务器状态', '', null, '', null, 2, 0, "server:status", 1, 0, 'api')
;

INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(106, '查询操作日志', '', null, '', null, 2, 0, "log:list", 1, 0, 'api')
;

INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(107, '查询数据库表', '', null, '', null, 2, 0, "database:tables", 1, 0, 'api'),
(107, '查询数据库', '', null, '', null, 2, 0, "database:databases", 1, 0, 'api')
;

INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(108, '查询用户列表', '', null, '', null, 2, 0, "user:list", 1, 0, 'api'),
(108, '查询用户信息', '', null, '', null, 2, 0, "user:query", 1, 0, 'api'),
(108, '新增用户', '', null, '', null, 2, 0, "user:create", 1, 0, 'api'),
(108, '编辑用户', '', null, '', null, 2, 0, "user:edit", 1, 0, 'api'),
(108, '删除用户', '', null, '', null, 2, 0, "user:delete", 1, 0, 'api'),
(108, '修改密码', '', null, '', null, 2, 0, "user:modifyPassword", 1, 0, 'api'),
(108, '冻结用户', '', null, '', null, 2, 0, "user:freeze", 1, 0, 'api'),
(108, '解冻用户', '', null, '', null, 2, 0, "user:unfreeze", 1, 0, 'api'),
(108, '分配用户角色', '', null, '', null, 2, 0, "user:addRoles", 1, 0, 'api')
;

INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(109, '查询角色列表', '', null, '', null, 2, 0, "role:list", 1, 0, 'api'),
(109, '新增角色', '', null, '', null, 2, 0, "role:create", 1, 0, 'api'),
(109, '编辑角色', '', null, '', null, 2, 0, "role:edit", 1, 0, 'api'),
(109, '删除角色', '', null, '', null, 2, 0, "role:delete", 1, 0, 'api'),
(109, '查询角色权限', '', null, '', null, 2, 0, "role:menus", 1, 0, 'api'),
(109, '分配角色权限', '', null, '', null, 2, 0, "role:addMenus", 1, 0, 'api'),
(109, '查询角色用户列表', '', null, '', null, 2, 0, "role:users", 1, 0, 'api')
;

INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(110, '查询菜单列表', '', null, '', null, 2, 0, "menu:list", 1, 0, 'api'),
(110, '新增菜单', '', null, '', null, 2, 0, "menu:create", 1, 0, 'api'),
(110, '编辑菜单', '', null, '', null, 2, 0, "menu:edit", 1, 0, 'api'),
(110, '删除菜单', '', null, '', null, 2, 0, "menu:delete", 1, 0, 'api')
;

INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(111, '查询部门列表', '', null, '', null, 2, 0, "dept:list", 1, 0, 'api'),
(111, '查询部门内容', '', null, '', null, 2, 0, "dept:query", 1, 0, 'api'),
(111, '新增部门', '', null, '', null, 2, 0, "dept:create", 1, 0, 'api'),
(111, '编辑部门', '', null, '', null, 2, 0, "dept:edit", 1, 0, 'api'),
(111, '删除部门', '', null, '', null, 2, 0, "dept:delete", 1, 0, 'api')
;

INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(112, '查询岗位列表', '', null, '', null, 2, 0, "job:list", 1, 0, 'api'),
(112, '查询岗位内容', '', null, '', null, 2, 0, "job:query", 1, 0, 'api'),
(112, '新增岗位', '', null, '', null, 2, 0, "job:create", 1, 0, 'api'),
(112, '编辑岗位', '', null, '', null, 2, 0, "job:edit", 1, 0, 'api'),
(112, '删除岗位', '', null, '', null, 2, 0, "job:delete", 1, 0, 'api')
;

INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(113, '查询字典组列表', '', null, '', null, 2, 0, "config:groups", 1, 0, 'api'),
(113, '查询字典列表', '', null, '', null, 2, 0, "config:list", 1, 0, 'api'),
(113, '新增字典', '', null, '', null, 2, 0, "config:create", 1, 0, 'api'),
(113, '编辑字典', '', null, '', null, 2, 0, "config:edit", 1, 0, 'api'),
(113, '删除字典', '', null, '', null, 2, 0, "config:delete", 1, 0, 'api')
;

INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(114, '查询友链列表', '', null, '', null, 2, 0, "link:list", 1, 0, 'api'),
(114, '新增友链', '', null, '', null, 2, 0, "link:create", 1, 0, 'api'),
(114, '编辑友链', '', null, '', null, 2, 0, "link:edit", 1, 0, 'api'),
(114, '删除友链', '', null, '', null, 2, 0, "link:delete", 1, 0, 'api')
;


# 测试menu
INSERT INTO `sys_menu`(pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(0, '官网链接', '', null, 'https://www.beyongx.com', null, 0, 1, "", 1, 999, 'api')
;
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(1000, 0, '二级菜单', 'twoMenu', 'Layout', 'twoMenu', 'nested', 1, 1, "", 1, 999, 'api')
;
INSERT INTO `sys_menu`(id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to) VALUES 
(1001, 1000, '第一级菜单', 'one', 'empty/index', 'one/index', null, 1, 1, "", 1, 0, 'api'),
(1002, 1001, '第二级菜单', 'two', 'empty/index', 'two/index', null, 1, 1, "", 1, 0, 'api'),
(1003, 1000, '平级菜单', 'pingji', 'empty/index', 'pingji/index', null, 1, 1, "", 1, 1, 'api')
;


truncate `sys_role_menu`;
# delete from `sys_role_menu` where role_id = 1;

INSERT INTO `sys_role_menu`(role_id,menu_id) SELECT 1, id FROM `sys_menu`;

/* ================================================================================================*/
/* =========================================数据初始脚本：部门及岗位表=============================*/
truncate table `sys_dept`;

INSERT INTO `sys_dept`(id,pid,name,title,status,sort,remark) VALUES (1, 0, 'president', '总裁办公室', 1, 1, '');
INSERT INTO `sys_dept`(id,pid,name,title,status,sort,remark) VALUES (2, 0, 'administration', '行政部', 1, 2, '');
INSERT INTO `sys_dept`(id,pid,name,title,status,sort,remark) VALUES (3, 0, 'product', '产品部', 1, 3, '');
INSERT INTO `sys_dept`(id,pid,name,title,status,sort,remark) VALUES (4, 0, 'development', '研发部', 1, 4, '');
INSERT INTO `sys_dept`(id,pid,name,title,status,sort,remark) VALUES (5, 0, 'operation', '运营部', 1, 5, '');
INSERT INTO `sys_dept`(id,pid,name,title,status,sort,remark) VALUES (6, 0, 'market', '市场部', 1, 6, '');

truncate table `sys_job`;

INSERT INTO `sys_job`(id,name,title,status,sort,remark) VALUES (1, 'president', '总裁', 1, 1, '');
INSERT INTO `sys_job`(id,name,title,status,sort,remark) VALUES (2, 'administration-director', '行政总监', 1, 2, '');
INSERT INTO `sys_job`(id,name,title,status,sort,remark) VALUES (3, 'product-director', '产品总监', 1, 3, '');
INSERT INTO `sys_job`(id,name,title,status,sort,remark) VALUES (4, 'development-director', '研发总监', 1, 4, '');
INSERT INTO `sys_job`(id,name,title,status,sort,remark) VALUES (5, 'operation-director', '运营总监', 1, 5, '');
INSERT INTO `sys_job`(id,name,title,status,sort,remark) VALUES (6, 'market-director', '市场总监', 1, 6, '');
INSERT INTO `sys_job`(id,name,title,status,sort,remark) VALUES (7, 'product-assistant', '产品助理', 1, 6, '');	

/* ================================================================================================*/
/* =========================================数据初始脚本：文章及广告表=============================*/
truncate table cms_category;

INSERT INTO `cms_category`(id,pid,title,name,remark,status,sort,create_time) VALUES (1, 0, '公司新闻', 'company', '公司新闻文章', 1, 1, '2018-01-19 00:00:00');
INSERT INTO `cms_category`(id,pid,title,name,remark,status,sort,create_time) VALUES (2, 0, '公司相册', 'album', '公司相册介绍', 1, 2, '2018-01-19 00:00:00');
INSERT INTO `cms_category`(id,pid,title,name,remark,status,sort,create_time) VALUES (3, 0, '公司产品', 'product', '公司产品介绍', 1, 3, '2018-01-19 00:00:00');
INSERT INTO `cms_category`(id,pid,title,name,remark,status,sort,create_time) VALUES (4, 0, '行业新闻', 'news', '行业新闻文章', 1, 4, '2018-01-19 00:00:00');
INSERT INTO `cms_category`(id,pid,title,name,remark,status,sort,create_time) VALUES (5, 0, '行业动态', 'status', '行业动态文章', 1, 5, '2018-01-19 00:00:00');

truncate table cms_ad_slot;

INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (1, '首页头条广告', 'banner_headline', '首页头条广告左右滚动');
INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (2, '首页顶部广告', 'index_header', '首页顶部广告');
INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (3, '首页中间广告', 'index_center', '首页中间广告');
INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (4, '首页底部广告', 'index_footer', '首页底部广告');
INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (5, '侧边栏头部广告', 'sidebar_header', '侧边栏头部广告');
INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (6, '侧边栏中间广告', 'sidebar_center', '侧边栏中间广告');
INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (7, '侧边栏底部广告', 'sidebar_footer', '侧边栏底部广告');
INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (10, '搜索框', 'search', '搜索框下拉推荐广告');
INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (11, '分类列表页头部', 'category_list_header', '显示于分类列表页头部');
INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (12, '分类列表页中间', 'category_list_center', '显示于分类列表页中间');
INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (13, '分类列表页底部', 'category_list_footer', '显示于分类列表页底部');
INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (14, '文章列表页头部', 'article_list_header', '显示于文章列表页头部');
INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (15, '文章列表页中间', 'article_list_center', '显示于文章列表页中间');
INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (16, '文章列表页底部', 'article_list_footer', '显示于文章列表页底部');
INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (17, '文章详细页头部', 'article_view_header', '显示于文章详细页头部');
INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (18, '文章详细页中间', 'article_view_center', '显示于文章详细页中间');
INSERT INTO `cms_ad_slot`(id, title, name, remark) VALUES (19, '文章详细页底部', 'article_view_footer', '显示于文章详细页底部');

/* ================================================================================================*/
/* =========================================数据初始脚本：设置自增起始=============================*/
alter table sys_user AUTO_INCREMENT=100000;
alter table sys_message AUTO_INCREMENT=100000;
alter table sys_file AUTO_INCREMENT=100000;
alter table cms_article AUTO_INCREMENT=100000;
alter table cms_category AUTO_INCREMENT=100;


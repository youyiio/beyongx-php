/** 添加插件表数据 **/
INSERT INTO
    `addons`(`name`, `title`, `description`, `status`, `config`, `author`, `version`, `create_time`, `has_adminlist`)
VALUES
     ('enhance', '系统增强插件', 'Cms系统增强插件,用于前后部分定制', '1', NULL, 'miniappcms', '0.1', '2018-06-12 00:00:00', '0');

/** 添加勾子表数据 **/
INSERT INTO
    `hooks`(`name`, `description`, `type`,  `addons`, `status`, `update_time`, `create_time`)
VALUES
    ('usertimeline', '用户动态列表', '1', 'enhance', '1', '2018-06-12 00:00:00', '2018-06-12 00:00:00');
INSERT INTO
    `hooks`(`name`, `description`, `type`,  `addons`, `status`, `update_time`, `create_time`)
VALUES
    ('userbalance', '用户帐户信息', '1', 'enhance', '1', '2018-06-12 00:00:00', '2018-06-12 00:00:00');
INSERT INTO
    `hooks`(`name`, `description`, `type`,  `addons`, `status`, `update_time`, `create_time`)
VALUES
    ('userBusiness01', '用户业务列表01', '1', 'enhance', '1', '2018-06-12 00:00:00', '2018-06-12 00:00:00');
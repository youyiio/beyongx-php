/** 添加插件表数据 **/
INSERT INTO
    `addons`(`id`, `name`, `title`, `description`, `status`, `config`, `author`, `version`, `create_time`, `has_adminlist`)
VALUES
     ('2', 'test', 'test插件', 'test插件简介', '1', NULL, 'byron sampson', '0.1', '2018-01-01 00:00:00', '0');

/** 添加勾子表数据 **/
INSERT INTO
    `hooks`(`id`, `name`, `description`, `type`, `addons`, `status`, `update_time`, `create_time`)
VALUES
    ('21', 'demo', 'demo钩子', '1', 'test', '1', '2018-01-01 00:00:00', '2018-01-01 00:00:00');
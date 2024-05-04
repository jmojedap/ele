ALTER TABLE `post` ADD `code` VARCHAR(25) NOT NULL AFTER `tipo_id`;
ALTER TABLE `post` ADD `status` TINYINT NOT NULL DEFAULT '2' AFTER `contenido_json`;
ALTER TABLE `post` ADD `keywords` VARCHAR(1000) NOT NULL AFTER `status`;
ALTER TABLE `post` ADD `position` SMALLINT(5) NOT NULL AFTER `padre_id`;
ALTER TABLE `post` ADD `place_id` INT(10) NOT NULL DEFAULT '0' AFTER `position`;
ALTER TABLE `post` ADD `url_image` VARCHAR(280) NOT NULL AFTER `imagen_id`, ADD `url_thumbnail` VARCHAR(280) NOT NULL AFTER `url_image`;
ALTER TABLE `post` ADD `cat_1` INT(10) NOT NULL AFTER `url_thumbnail`, ADD `cat_2` INT(10) NOT NULL AFTER `cat_1`, ADD `cat_3` INT(10) NOT NULL AFTER `cat_2`, ADD INDEX (`cat_1`), ADD INDEX (`cat_2`), ADD INDEX (`cat_3`);
ALTER TABLE `post` ADD `content_embed` MEDIUMTEXT NOT NULL AFTER `contenido_json`;
ALTER TABLE `post` ADD `date_1` DATETIME NULL DEFAULT NULL AFTER `cat_3`, ADD `date_2` DATETIME NULL DEFAULT NULL AFTER `date_1`;
ALTER TABLE `post` ADD `integer_1` INT(11) NOT NULL AFTER `texto_3`, ADD `integer_2` INT(11) NOT NULL AFTER `integer_1`, ADD `integer_3` INT(11) NOT NULL AFTER `integer_2`;
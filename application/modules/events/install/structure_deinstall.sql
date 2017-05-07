DROP TABLE IF EXISTS `[prefix]events`;
DROP TABLE IF EXISTS `[prefix]events_users`;
DELETE FROM `[prefix]album_types` WHERE `gid` = 'events_type';
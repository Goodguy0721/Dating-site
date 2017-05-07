DROP TABLE IF EXISTS `[prefix]perfect_match`;

ALTER TABLE `[prefix]users` DROP `looking_user_type`;
ALTER TABLE `[prefix]users` DROP `age_min`;
ALTER TABLE `[prefix]users` DROP `age_max`;
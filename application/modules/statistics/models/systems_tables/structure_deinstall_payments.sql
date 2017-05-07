DROP TABLE IF EXISTS `[prefix]statistics_payments`;
DELETE FROM `[prefix]statistics` WHERE module = 'payments';

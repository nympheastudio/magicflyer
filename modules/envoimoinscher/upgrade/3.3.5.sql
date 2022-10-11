-- REQUEST --
UPDATE `{PREFIXE}emc_orders_post` SET `type` = 'eem' WHERE `type` IS NULL;
-- REQUEST --
ALTER TABLE `{PREFIXE}emc_orders_post` MODIFY `type` VARCHAR(20) NOT NULL DEFAULT 'eem';
-- REQUEST --
ALTER TABLE `{PREFIXE}emc_orders_post` DROP PRIMARY KEY, ADD PRIMARY KEY( `{PREFIXE}orders_id_order`, `type`);
-- REQUEST --
ALTER TABLE `{PREFIXE}emc_services`
ADD zone_es_es int(1) NOT NULL DEFAULT 0 AFTER zone_fr_es;
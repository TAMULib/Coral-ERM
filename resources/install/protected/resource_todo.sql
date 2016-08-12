ALTER TABLE `ResourceStep` ADD `reviewLoginID` VARCHAR(30) NULL DEFAULT NULL;
ALTER TABLE `ResourceStep` ADD `reviewDate` DATE NULL DEFAULT NULL;
ALTER TABLE `ResourcePayment` ADD `purchaseOrder` VARCHAR( 24 ) NULL ;
ALTER TABLE `ResourcePayment` ADD `systemID` VARCHAR( 24 ) NULL ;
ALTER TABLE `ResourcePayment` ADD `vendorCode` VARCHAR( 48 ) NULL ;
ALTER TABLE `Fund` ADD `organizationID` INT NULL , ADD INDEX ( `organizationID` ) ;
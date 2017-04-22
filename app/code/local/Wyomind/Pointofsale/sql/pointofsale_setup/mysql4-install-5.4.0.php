<?php

$installer = $this;
$installer->startSetup();
$installer->run(
    "
DROP TABLE IF EXISTS {$this->getTable('pointofsale')};
"
);
$installer->run(
    "
CREATE TABLE {$this->getTable('pointofsale')}(
  `place_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_group` varchar(300) DEFAULT NULL,
  `store_id` varchar(300) DEFAULT NULL,
  `position` int(4) NOT NULL DEFAULT '1',
  `store_code` varchar(20) NOT NULL,
  `name` varchar(60) NOT NULL,
  `address_line_1` varchar(80) NOT NULL,
  `address_line_2` varchar(80) DEFAULT NULL,
  `city` varchar(80) NOT NULL,
  `state` varchar(80) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country_code` varchar(2) NOT NULL,
  `main_phone` varchar(20) NOT NULL,
  `email` varchar(60) NOT NULL,
  `hours` text CHARACTER SET latin1,
  `description` varchar(20) DEFAULT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `visible_google_map` int(1) NOT NULL DEFAULT '1',
  `visible_product_page` int(1) NOT NULL DEFAULT '1',
  `visible_checkout` int(1) NOT NULL DEFAULT '1',
  `image` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`place_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    "
);

$table = $this->getTable('localstores_place');

if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {

    if (Mage::getSingleton('core/resource')->getConnection('core_write')->isTableExists($table)) {
        $installer->run(
            "INSERT INTO `{$this->getTable('pointofsale')}` (`place_id`,`store_code`,`name`,`main_phone`,`address_line_1`,`address_line_2`,`city`,`state`,`postal_code`,`country_code`,`hours`,`description`,`longitude`,`latitude`,`visible_google_map`,`visible_product_page`,`visible_checkout`,`image`,`customer_group`,`position`,`store_id`)
                    SELECT `place_id`,`store_code`,`name`,`main_phone`,`address_line_1`,`address_line_2`,`city`,`state`,`postal_code`,`country_code`,`hours`,`description`,`longitude`,`latitude`,`status`,`status`,`status`,`image`,`customer_group`,`position`,(SELECT GROUP_CONCAT(store_id) FROM `{$this->getTable('localstores_place_store')}` WHERE place_id={$this->getTable('localstores_place')}.place_id) FROM `{$this->getTable('localstores_place')}`;"
        );
        //$installer->run("DROP TABLE `{$this->getTable('localstores_place')}`;");
        //$installer->run("DROP TABLE `{$this->getTable('localstores_place_store')}`;");
    }
}

if (strpos($_SERVER['HTTP_HOST'], "wyomind.com")) {
    $installer->run(
        "
        insert into `pointofsale`(`place_id`,`store_code`,`name`,`main_phone`,`address_line_1`,`address_line_2`,`city`,`state`,`postal_code`,`country_code`,`hours`,`description`,`longitude`,`latitude`,`visible_google_map`,`visible_product_page`,`visible_checkout`,`image`,`customer_group`,`store_id`,`position`,`email`) values (541,'FR-75','France Paris Store','123456788','86 Avenue des Champs-Elysées',null,'Paris','01','75000','FR','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','2.303120 ','48.871203',1,1,1,'','0,1,2,3,4','3',1,'contact@website.com')
        , (542,'ES-08','Spain Barcelona Store','123456789','72 La Rambla',null,'Barcelona','','8002','ES','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','2.173886 ','41.380628',1,1,1,null,'0,1,2,3,4','3',10,'contact@website.com')
        , (543,'GB','United Kingdom London Store','123456790','9 Storey''s Gate ',null,'London','','SW1P 3AT','GB','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension',' -0.129746 ','51.500716',1,1,1,null,'0,1,2,3,4','1,3',10,'contact@website.com')
        , (544,'DE-10','Germany Berlin Store','123456791','99 Stresemannstraße',null,'Berlin','','10963','DE','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','13.380210 ','52.506098',1,1,1,null,'0,1,2,3,4','2,3',10,'contact@website.com')
        , (545,'BE-10','Belgium Brussels Store','123456792','1 rue des Chapeliers',null,'Brussells','','1000','BE','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','4.352795 ','50.846189',1,1,1,null,'0,1,2,3,4','2,3',10,'contact@website.com')
        , (546,'IT-00','Italy Rome Store','123456793','35 Viale Marco Polo',null,'Rome','','154','IT','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','12.485529 ','41.874866',1,1,1,null,'0,1,2,3,4','2',10,'contact@website.com')
        , (548,'USA-FL-33','USA Miami Store','123456795','8240 Palm Terrace',null,'Miami','FL','33181','US','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','-80.152908 ','25.891833',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (549,'USA-CA-90','USA Los Angeles Store','123456796','852 E Ocean Blvd',null,'Los Angeles','CA','90802','US','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','-118.181535 ','33.766151',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (550,'USA-CA-94','USA San Francisco Store','123456797','20499 Bicycle route',null,'San Francisco','CA','94115','US','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','-122.436126 ','37.791722',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (551,'USA-IL-60','USA Chicago Store','123456798','412 S Michigan Ave',null,'Chicago','IL','60619','US','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','-87.624296 ','41.876171',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (552,'USA-DC-20','USA Washington Store','123456799','1402 Constitution Ave',null,'Washington','DC','20560','US','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension',' -77.033057 ','38.892069',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (553,'USA-CO-80','USA Detroit Store','123456800','1601 Detroit St',null,'Denver','CO','80206','US','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','-104.954800 ','39.743335',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (555,'USA-KY-40','USA Louisville Store','123456802','1585 Cherokee rd',null,'Louisvillle','KY','40205','US','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','-85.697842 ','38.236889',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (556,'USA-TX-77','USA Houston Store','123456803','1785 Memorial Dr',null,'Houston','TX','77007','US','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','-95.385928 ','29.762094',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (557,'USA-OK-73','USA Oklahoma City Store','123456804','2220 E Overholser dr',null,'Oklahoma City','OK','73127','US','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','-97.664849 ','35.490895',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (558,'USA-TX-75','USA Dallas Store','123456805','700 Wood St',null,'Dallas','TX','75202','US','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','-96.804896 ','32.777051',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (559,'USA-UT-84','USA Salt Lakie City Store','123456806','2 Martin Luther King Jr blvd',null,'Salt Lake City','UT','84101','US','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','-111.891861 ','40.756391',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (560,'MX-06','Mexico Mexico Store','123456807','23 Lorenzo Boturini',null,'Mexico','','6800','MX','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','-99.140984 ','19.418723',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (561,'CA-BC','Canada Vancouver Store','123456808','818 W 6th ave',null,'Vancouver','BC','V5Z','CA','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','-123.122617 ','49.265940',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (562,'CA-ON','Canada Ottawa Store','123456809','149 Middle st',null,'Ottawa','ON','K1R6K4','CA','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','-75.714941 ','45.419941',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (563,'ZA-20','South Africa Johannesburg Store','123456810','125 Joubert st',null,'Johannesburg','','2000','ZA','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','28.041259 ','-26.190668',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (566,'CN','China Beijing Store','123456813','1 Weijin St',null,'Beijing','','','CN','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','116.389005 ','39.922857',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (567,'AU-NSW-20','Australia Sydney Store','123456814','5 Phillip St',null,'Sydney','NSW','2000','AU','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','151.211961 ','-33.862715',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (568,'TR-34','Turkey Istanbul Store','123456815','21 Muradiye Cd',null,'Istanbul','','34200','TK','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','28.975937 ','41.014632',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (570,'KR','South Korea Seoul Store','123456817','6-2 Namsandong 2(i)-ga',null,'Seoul','','','KR','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','126.985609','37.560344',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (571,'MY-50','Malaysia Kuala Lumpur Store','123456818','Jalan Tembusu',null,'Kuala Lumpur','','50480','MY','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','101.687393 ','3.146260',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com')
        , (573,'HU-10','Hungary Budapest Store','123456820','Hajógyári-sziget',null,'Budapest','','1033','HU','{\"Monday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Tuesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Wednesday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Thursday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Friday\":{\"from\":\"08:00\",\"to\":\"20:00\"},\"Saturday\":{\"from\":\"08:00\",\"to\":\"12:00\"}}','Magento extension','19.050812 ','47.550602',1,1,1,null,'0,1,2,3,4','1',10,'contact@website.com');
        "
    );

    $installer->getConnection()->addColumn(
        $installer->getTable('pointofsale'), 'inventory_assignation_rules', "varchar(400) DEFAULT '*'"
    );
}

$installer->endSetup();

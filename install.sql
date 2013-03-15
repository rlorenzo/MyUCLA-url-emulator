--
-- Database: `myucla_updater`
--
CREATE DATABASE IF NOT EXISTS `myucla_updater` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `myucla_updater`;

-- --------------------------------------------------------

--
-- Table structure for table `iei_urls`
--

CREATE TABLE IF NOT EXISTS `iei_urls` (
  `term` char(3) NOT NULL,
  `srs` int(9) unsigned zerofill NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`term`,`srs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

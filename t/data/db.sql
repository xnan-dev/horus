-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.6.7-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para horus_t
CREATE DATABASE IF NOT EXISTS `horus_t` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `horus_t`;

-- Volcando estructura para tabla horus_t.marketstats
CREATE TABLE IF NOT EXISTS `marketstats` (
  `marketId` varchar(50) NOT NULL,
  `marketStatsId` varchar(50) NOT NULL,
  `endBeat` bigint(20) DEFAULT NULL,
  `maxHistoryBeats` int(11) DEFAULT NULL,
  `synchedBeat` bigint(20) DEFAULT NULL,
  `beatMultiplier` int(11) DEFAULT NULL,
  PRIMARY KEY (`marketId`,`marketStatsId`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla horus_t.marketstats: ~45 rows (aproximadamente)
/*!40000 ALTER TABLE `marketstats` DISABLE KEYS */;
INSERT INTO `marketstats` (`marketId`, `marketStatsId`, `endBeat`, `maxHistoryBeats`, `synchedBeat`, `beatMultiplier`) VALUES
	('cryptosLiveArenaMarket', 'marketStatsLong', 0, 50, -1, 400),
	('cryptosLiveArenaMarket', 'marketStatsMedium', 0, 50, -1, 100),
	('cryptosLiveArenaMarket', 'marketStatsShort', 28955, 50, -1, 1),
	('cryptosTestArenaMarket', 'marketStatsLong', 0, 50, -1, 400),
	('cryptosTestArenaMarket', 'marketStatsMedium', 0, 50, -1, 100),
	('cryptosTestArenaMarket', 'marketStatsShort', 0, 50, -1, 1),
	('mathArenaMarket', 'marketStatsLong', 0, 50, -1, 400),
	('mathArenaMarket', 'marketStatsMedium', 100, 50, 100, 100),
	('mathArenaMarket', 'marketStatsShort', 117, 50, 117, 1),
	('mervalAccionesGeneralLiveArenaMarket', 'marketStatsLong', 0, 50, -1, 400),
	('mervalAccionesGeneralLiveArenaMarket', 'marketStatsMedium', 0, 50, -1, 100),
	('mervalAccionesGeneralLiveArenaMarket', 'marketStatsShort', 52, 50, 52, 1),
	('mervalAccionesGeneralTestArenaMarket', 'marketStatsLong', 0, 50, -1, 400),
	('mervalAccionesGeneralTestArenaMarket', 'marketStatsMedium', 0, 50, -1, 100),
	('mervalAccionesGeneralTestArenaMarket', 'marketStatsShort', 0, 50, -1, 1),
	('mervalAccionesLideresLiveArenaMarket', 'marketStatsLong', 0, 50, -1, 400),
	('mervalAccionesLideresLiveArenaMarket', 'marketStatsMedium', 0, 50, -1, 100),
	('mervalAccionesLideresLiveArenaMarket', 'marketStatsShort', 0, 50, -1, 1),
	('mervalAccionesLideresTestArenaMarket', 'marketStatsLong', 0, 50, -1, 400),
	('mervalAccionesLideresTestArenaMarket', 'marketStatsMedium', 0, 50, -1, 100),
	('mervalAccionesLideresTestArenaMarket', 'marketStatsShort', 0, 50, -1, 1),
	('mervalCedearsLiveArenaMarket', 'marketStatsLong', 0, 50, -1, 400),
	('mervalCedearsLiveArenaMarket', 'marketStatsMedium', 0, 50, -1, 100),
	('mervalCedearsLiveArenaMarket', 'marketStatsShort', 0, 50, -1, 1),
	('mervalCedearsTestArenaMarket', 'marketStatsLong', 0, 50, -1, 400),
	('mervalCedearsTestArenaMarket', 'marketStatsMedium', 0, 50, -1, 100),
	('mervalCedearsTestArenaMarket', 'marketStatsShort', 0, 50, -1, 1),
	('nasdaq100LiveArenaMarket', 'marketStatsLong', 0, 50, -1, 400),
	('nasdaq100LiveArenaMarket', 'marketStatsMedium', 0, 50, -1, 100),
	('nasdaq100LiveArenaMarket', 'marketStatsShort', 0, 50, -1, 1),
	('nasdaq100TestArenaMarket', 'marketStatsLong', 0, 50, -1, 400),
	('nasdaq100TestArenaMarket', 'marketStatsMedium', 0, 50, -1, 100),
	('nasdaq100TestArenaMarket', 'marketStatsShort', 0, 50, -1, 1),
	('snP100LiveArenaMarket', 'marketStatsLong', 0, 50, -1, 400),
	('snP100LiveArenaMarket', 'marketStatsMedium', 0, 50, -1, 100),
	('snP100LiveArenaMarket', 'marketStatsShort', 0, 50, -1, 1),
	('snP100TestArenaMarket', 'marketStatsLong', 0, 50, -1, 400),
	('snP100TestArenaMarket', 'marketStatsMedium', 0, 50, -1, 100),
	('snP100TestArenaMarket', 'marketStatsShort', 0, 50, -1, 1),
	('yfForexUsdLiveArenaMarket', 'marketStatsLong', 0, 50, -1, 400),
	('yfForexUsdLiveArenaMarket', 'marketStatsMedium', 0, 50, -1, 100),
	('yfForexUsdLiveArenaMarket', 'marketStatsShort', 0, 50, -1, 1),
	('yfForexUsdTestArenaMarket', 'marketStatsLong', 0, 50, -1, 400),
	('yfForexUsdTestArenaMarket', 'marketStatsMedium', 0, 50, -1, 100),
	('yfForexUsdTestArenaMarket', 'marketStatsShort', 0, 50, -1, 1);
/*!40000 ALTER TABLE `marketstats` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

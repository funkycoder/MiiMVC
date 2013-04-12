-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2013 at 08:02 AM
-- Server version: 5.5.27
-- PHP Version: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `user`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `userid` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `useremail` varchar(50) NOT NULL,
  `password` char(40) NOT NULL,
  `hash` char(32) DEFAULT NULL,
  `salt` char(32) NOT NULL,
  `username` varchar(100) NOT NULL,
  `userphone` varchar(20) NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  PRIMARY KEY (`userid`),
  UNIQUE KEY `useremail` (`useremail`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userid`, `useremail`, `password`, `hash`, `salt`, `username`, `userphone`, `timestamp`) VALUES
(6, 'quan@yahoo.com', 'c887c6e4ae6d32cce42aee7f23c64ccfdea29473', 'e265f41e0a86b05a05140453c17065c4', '1365519998', 'quannguyen', '53543543', 1365519998),
(7, 'fdasf', 'fdsa', 'fdsa', 'fdsa', 'fdsa', 'fdfds', 432432),
(8, 'thien@yahoo.com', '2ca5950b3432a2fdc974eb70949ef225df6d026f', '', '1365552860', 'q', '53543543', 1365552860),
(9, 'thien2@yahoo.com', 'c83b0c84194f7afb6bb012ac1235540764d3eff5', '', '1365552881', 'quo', '53543543', 1365552881),
(10, 'toan@gmail.com', 'f18769d58645a41ec291ca5711fdf9e7df4c53a8', '595090a8e8f1b2684b29a0cf7d23d8b7', '1365659083', 'Toan', '940394034', 1365659083);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

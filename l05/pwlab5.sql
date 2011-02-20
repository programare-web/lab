-- phpMyAdmin SQL Dump
-- version 3.1.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 19, 2010 at 02:00 PM
-- Server version: 5.1.30
-- PHP Version: 5.2.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `pwlab5`
--
CREATE DATABASE `pwlab5` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `pwlab5`;

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `article` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `article`) VALUES
(1, 'Some Article'),
(2, 'Other Article'),
(3, 'Different Article'),
(4, 'Some Other Article'),
(5, 'Some Different Other Article'),
(6, 'Some Other Different Article'),
(7, 'Some Article'),
(8, 'Some Different Article');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `rights` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `rights`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', '1,2,3,4,5,6,7,8'),
(2, 'user', '8287458823facb8ff918dbfabcd22ccb', '1,3,5,7'),
(3, 'guest', '8287458823facb8ff918dbfabcd22ccb', '1'),
(4, 'matei', '8287458823facb8ff918dbfabcd22ccb', '2,4,6,8');

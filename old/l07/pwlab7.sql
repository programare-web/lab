-- phpMyAdmin SQL Dump
-- version 3.1.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 17, 2010 at 09:44 PM
-- Server version: 5.1.30
-- PHP Version: 5.2.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `pwlab7`
--
CREATE DATABASE `pwlab7` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `pwlab7`;

-- --------------------------------------------------------

--
-- Table structure for table `criticaltable`
--

CREATE TABLE IF NOT EXISTS `criticaltable` (
  `criticalData` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `criticaltable`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`username`, `password`, `email`) VALUES
('user', 'pass', 'user@domain.com'),
('john', 'pass', 'john@domain.com'),
('us', 'a666587afda6e89aec27', 'em'),
('us', '58d4d1e7b1e97b258c9e', 'em'),
('bad_guy', 'mypass', ''),
('good_guy', '6d0f846348a856321729', 'email'),
('some_guy', '', '');

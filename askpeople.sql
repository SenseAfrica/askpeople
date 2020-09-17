-- phpMyAdmin SQL Dump
-- version 4.2.5
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 31, 2015 at 09:25 AM
-- Server version: 5.5.38
-- PHP Version: 5.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `askpeople`
--

-- --------------------------------------------------------

--
-- Table structure for table `end_users`
--

CREATE TABLE `end_users` (
`id` int(11) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(15) NOT NULL,
  `phone` bigint(20) unsigned NOT NULL,
  `forms` int(11) unsigned NOT NULL DEFAULT '0',
  `submissions` int(11) unsigned NOT NULL DEFAULT '0',
  `credits` int(11) unsigned NOT NULL DEFAULT '2000',
  `reserve` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `lost_pass`
--

CREATE TABLE `lost_pass` (
`id` int(10) unsigned NOT NULL,
  `accounts` varchar(255) NOT NULL,
  `code` int(11) NOT NULL,
  `submitted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pendingsms`
--

CREATE TABLE `pendingsms` (
`id` int(10) unsigned NOT NULL,
  `org` int(10) unsigned NOT NULL,
  `phone` bigint(20) unsigned NOT NULL,
  `msg` text NOT NULL,
  `todo` text,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
`id` int(10) unsigned NOT NULL,
  `min_age` tinyint(3) unsigned NOT NULL,
  `max_age` tinyint(3) unsigned NOT NULL,
  `gender` bit(1) DEFAULT NULL,
  `ed_0` bit(1) NOT NULL DEFAULT b'1',
  `ed_1` bit(1) NOT NULL DEFAULT b'1',
  `ed_2` bit(1) NOT NULL DEFAULT b'1',
  `job_0` bit(1) NOT NULL DEFAULT b'1',
  `job_1` bit(1) NOT NULL DEFAULT b'1',
  `job_2` bit(1) NOT NULL DEFAULT b'1',
  `kids_0` bit(1) NOT NULL DEFAULT b'1',
  `kids_1` bit(1) NOT NULL DEFAULT b'1',
  `kids_2` bit(1) NOT NULL DEFAULT b'1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `profiling`
--

CREATE TABLE `profiling` (
  `org` int(11) NOT NULL,
  `node` int(11) NOT NULL,
  `profile` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `org` int(10) unsigned NOT NULL,
  `form` int(10) unsigned NOT NULL,
  `visitor` int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `share_form`
--

CREATE TABLE `share_form` (
`id` int(11) NOT NULL,
  `org` int(11) NOT NULL,
  `tbl` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
`uid` int(11) NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `last_active` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `org` int(11) DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `realname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `node` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
`id` int(11) unsigned NOT NULL,
  `phone` bigint(20) unsigned DEFAULT NULL,
  `org` int(11) NOT NULL DEFAULT '0',
  `form` int(11) NOT NULL DEFAULT '0',
  `step` tinyint(3) unsigned NOT NULL,
  `birth` smallint(5) unsigned NOT NULL,
  `gender` bit(1) NOT NULL,
  `ed` tinyint(4) NOT NULL,
  `job` tinyint(4) NOT NULL,
  `kids` tinyint(4) NOT NULL,
  `credits` mediumint(9) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `end_users`
--
ALTER TABLE `end_users`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `lost_pass`
--
ALTER TABLE `lost_pass`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pendingsms`
--
ALTER TABLE `pendingsms`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profiling`
--
ALTER TABLE `profiling`
 ADD PRIMARY KEY (`org`,`node`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
 ADD PRIMARY KEY (`org`,`form`,`visitor`);

--
-- Indexes for table `share_form`
--
ALTER TABLE `share_form`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`uid`), ADD UNIQUE KEY `email_2` (`email`), ADD KEY `email` (`email`), ADD KEY `active` (`active`), ADD KEY `admin` (`admin`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `end_users`
--
ALTER TABLE `end_users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `lost_pass`
--
ALTER TABLE `lost_pass`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pendingsms`
--
ALTER TABLE `pendingsms`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `share_form`
--
ALTER TABLE `share_form`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

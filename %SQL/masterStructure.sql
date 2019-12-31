-- phpMyAdmin SQL Dump
-- version 4.4.9
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Jan 05, 2017 at 05:24 AM
-- Server version: 5.5.42
-- PHP Version: 5.6.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `agrofond`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_sessions`
--

DROP TABLE IF EXISTS `api_sessions`;
CREATE TABLE `api_sessions` (
  `id` varchar(36) NOT NULL,
  `api_users_id` varchar(36) NOT NULL,
  `api_token` varchar(255) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_last_use` datetime DEFAULT NULL,
  `date_expire` datetime DEFAULT NULL,
  `user_ip` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `api_users`
--

DROP TABLE IF EXISTS `api_users`;
CREATE TABLE `api_users` (
  `id` varchar(36) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` smallint(1) NOT NULL DEFAULT '1',
  `api_secret` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api_sessions`
--
ALTER TABLE `api_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `api_users`
--
ALTER TABLE `api_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

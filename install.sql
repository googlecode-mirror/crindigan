-- 
-- This file is part of Crindigan.
--
-- Crindigan is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- Crindigan is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with Crindigan. If not, see <http://www.gnu.org/licenses/>.
--
-- Copyright (c) 2009 Steven Harris
-- http://www.gnu.org/licenses/gpl.txt GPL
--

--
-- Database schema for Crindigan
--

CREATE TABLE `user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(32) NOT NULL,
  `user_password` varchar(40) NOT NULL,
  `user_salt` varchar(5) NOT NULL,
  `user_email` varchar(64) NOT NULL,
  `user_autologin` varchar(40) NOT NULL,
  `user_autologin_time` int(10) unsigned NOT NULL,
  `user_money` int(11) NOT NULL,
  `user_external_id` int(10) unsigned NOT NULL,
  `user_joindate` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `session` (
  `session_id` varchar(32) NOT NULL,
  `session_data` text NOT NULL,
  `session_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`session_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `session_memory` (
  `session_id` char(32) NOT NULL,
  `session_user_id` int(10) unsigned NOT NULL,
  `session_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `session_user_id` (`session_user_id`)
) ENGINE=MEMORY DEFAULT CHARSET=latin1;

CREATE TABLE `news` (
  `news_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `news_author` int(10) unsigned NOT NULL,
  `news_title` varchar(255) NOT NULL,
  `news_body` text NOT NULL,
  `news_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`news_id`),
  KEY `news_time` (`news_time`)
) DEFAULT CHARSET=utf8;
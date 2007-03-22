-- phpMyAdmin SQL Dump
-- version 2.8.0.3-Debian-1
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Mercredi 19 Juillet 2006 à 20:54
-- Version du serveur: 4.1.15
-- Version de PHP: 4.4.2-1build1
-- 
-- Base de données: `dc2svn`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `dc_spam_token`
-- 

CREATE TABLE `dc_spam_token` (
  `token_id` varchar(255) NOT NULL default '',
  `token_nham` int(10) unsigned NOT NULL default '0',
  `token_nspam` int(10) unsigned NOT NULL default '0',
  `token_mdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `token_p` double NOT NULL default '0',
  `token_mature` smallint(1) NOT NULL default '0',
  PRIMARY KEY  (`token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

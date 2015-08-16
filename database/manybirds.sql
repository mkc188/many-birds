SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `reteazbp_manybirds`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievement`
--

CREATE TABLE IF NOT EXISTS `achievement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `icon_path` varchar(255) NOT NULL,
  `target` smallint(5) unsigned NOT NULL,
  `value` int(10) unsigned NOT NULL,
  `end_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `achievement`
--

INSERT INTO `achievement` (`id`, `name`, `icon_path`, `target`, `value`, `end_time`) VALUES
(1, 'Starter', 'img/achievement/stone.png', 1, 1, 0),
(2, 'Bronze Medal', 'img/achievement/bronze.png', 2, 30, 0),
(3, 'Silver Medal', 'img/achievement/silver.png', 2, 50, 0),
(4, 'No Luck', 'img/achievement/devil.png', 2, 99, 0),
(5, 'Gold Medal', 'img/achievement/gold.png', 2, 100, 0),
(6, 'Smile', 'img/achievement/biggrin.png', 2, 15, 0),
(7, 'Thank for Playing', 'img/achievement/wink.png', 1, 100, 0),
(8, 'Very Bored', 'img/achievement/clown.png', 1, 500, 1);

-- --------------------------------------------------------

--
-- Table structure for table `achievement_owned`
--

CREATE TABLE IF NOT EXISTS `achievement_owned` (
  `aid` int(10) unsigned NOT NULL,
  `uid` bigint(20) unsigned NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`aid`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `achievement_owned`
--


-- --------------------------------------------------------

--
-- Table structure for table `achievement_progress`
--

CREATE TABLE IF NOT EXISTS `achievement_progress` (
  `uid` bigint(20) unsigned NOT NULL,
  `target` smallint(5) unsigned NOT NULL,
  `value` int(10) unsigned NOT NULL,
  PRIMARY KEY (`uid`,`target`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `achievement_progress`
--


-- --------------------------------------------------------

--
-- Table structure for table `battle`
--

CREATE TABLE IF NOT EXISTS `battle` (
  `hostid` bigint(20) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  `p1score` smallint(5) unsigned NOT NULL,
  `p2id` bigint(20) unsigned NOT NULL,
  `p2score` smallint(5) unsigned NOT NULL,
  `p3id` bigint(20) unsigned NOT NULL,
  `p3score` smallint(5) unsigned NOT NULL,
  `p4id` bigint(20) unsigned NOT NULL,
  `p4score` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`hostid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `battle`
--


-- --------------------------------------------------------

--
-- Table structure for table `faq`
--

CREATE TABLE IF NOT EXISTS `faq` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question` mediumtext NOT NULL,
  `answer` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `faq`
--

INSERT INTO `faq` (`id`, `question`, `answer`) VALUES
(1, 'How to create account?', 'First you need to have a facebook account. If not, please register one. Then use your facebook account to finish creating account. '),
(2, 'How to login?', 'You have to login to facebook first, then it will automactically login the game. '),
(3, 'How to play?', 'For desktop version: Left-click on the screen\r\nFor mobile version: Tap the screen of your device \r\nBounce the bird up and down to fly continuously and avoid bumping into any obstacles. One mark will be given when passing one pipe. \r\n'),
(4, 'How to use different bird/background themes?', 'First you have to play games to earn score. When you have collected enough score, you can go to item shop to buy the theme you want. Score will be deducted once you successfully purchase a theme. If you own more than one theme, you can click the enable button to choose which to use in the game. Each time you can use one theme of each type only. '),
(5, 'Can I sell the themes to earn score?', 'No. Instead, you can play more games to obtain score ☺'),
(6, 'How to play with friends?', 'You can invite friends by clicking “invite friends” or “invite me” button next to the friend you would like to invite in “Weekly Tournament”. A facebook game invitation will be sent. '),
(7, 'How to play battle mode?', 'All people have their own rooms. Press "New" to clear the room. If you would like to be the host, just press start when all your friends have come in. If you would like to join others room, click "Join". You cannot enter a game room which game has started. ');

-- --------------------------------------------------------

--
-- Table structure for table `faq_vote`
--

CREATE TABLE IF NOT EXISTS `faq_vote` (
  `uid` bigint(20) unsigned NOT NULL,
  `qid` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`uid`,`qid`),
  KEY `qid` (`qid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `faq_vote`
--

-- --------------------------------------------------------

--
-- Table structure for table `friendship`
--

CREATE TABLE IF NOT EXISTS `friendship` (
  `uid` bigint(20) unsigned NOT NULL,
  `friendid` bigint(20) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`uid`,`friendid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `friendship`
--

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE IF NOT EXISTS `item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `content_path` varchar(255) NOT NULL,
  `width` tinyint(3) unsigned NOT NULL,
  `height` tinyint(3) unsigned NOT NULL,
  `price` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`id`, `type`, `name`, `content_path`, `width`, `height`, `price`) VALUES
(1, 1, 'Default', 'img/game/bird3_1.png', 60, 42, 0),
(2, 2, 'Default', 'img/game/day_bg.png', 0, 0, 0),
(3, 2, 'Night', 'img/game/night_bg.png', 0, 0, 10),
(6, 1, 'Pink Pink', 'img/game/bird3_2.png', 60, 59, 5),
(7, 1, 'Party', 'img/game/bird3_3.png', 60, 59, 10),
(8, 1, 'Purple', 'img/game/bird3_4.png', 60, 62, 25),
(9, 1, 'Nyan', 'img/game/bird3_5.png', 60, 43, 40),
(10, 2, 'Nyan', 'img/game/nyan_bg.png', 0, 0, 20),
(11, 1, 'Awesome!!', 'img/game/bird3_6.png', 60, 73, 80),
(12, 1, 'Peter''s', 'img/game/bird3_7.png', 60, 48, 9999),
(13, 1, 'Kalysta''s', 'img/game/bird3_8.png', 60, 46, 9999),
(14, 1, 'MK''s', 'img/game/bird3_9.png', 60, 59, 9999),
(15, 1, 'Leo''s', 'img/game/bird3_10.png', 60, 84, 9999),
(16, 1, 'Trust me?', 'img/game/bird3_11.png', 18, 12, 200);

-- --------------------------------------------------------

--
-- Table structure for table `item_owned`
--

CREATE TABLE IF NOT EXISTS `item_owned` (
  `sid` int(10) unsigned NOT NULL,
  `uid` bigint(20) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `enabled` tinyint(1) unsigned NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  PRIMARY KEY (`sid`,`uid`),
  KEY `uid` (`uid`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `item_owned`
--

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(32) NOT NULL,
  `data` blob NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tournament`
--

CREATE TABLE IF NOT EXISTS `tournament` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `year` year(4) NOT NULL,
  `week` tinyint(2) unsigned NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  `map` char(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `week-in-year` (`year`,`week`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `tournament`
--

-- --------------------------------------------------------

--
-- Table structure for table `tournament_score`
--

CREATE TABLE IF NOT EXISTS `tournament_score` (
  `tid` int(10) unsigned NOT NULL,
  `uid` bigint(20) unsigned NOT NULL,
  `score` smallint(5) unsigned NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`tid`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tournament_score`
--

-- --------------------------------------------------------

--
-- Table structure for table `tournament_sessions`
--

CREATE TABLE IF NOT EXISTS `tournament_sessions` (
  `uid` bigint(20) unsigned NOT NULL,
  `hash` char(16) NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`uid`,`hash`),
  KEY `concurrent-game` (`uid`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tournament_sessions`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `uid` bigint(20) unsigned NOT NULL,
  `username` varchar(255) NOT NULL,
  `name` varchar(50) NOT NULL,
  `updated` char(25) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `friend_list_time` int(11) NOT NULL,
  `friend_list_etag` varchar(255) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

--
-- Constraints for dumped tables
--

--
-- Constraints for table `achievement_owned`
--
ALTER TABLE `achievement_owned`
  ADD CONSTRAINT `achievement_owned_ibfk_1` FOREIGN KEY (`aid`) REFERENCES `achievement` (`id`),
  ADD CONSTRAINT `achievement_owned_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`);

--
-- Constraints for table `achievement_progress`
--
ALTER TABLE `achievement_progress`
  ADD CONSTRAINT `achievement_progress_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`);

--
-- Constraints for table `battle`
--
ALTER TABLE `battle`
  ADD CONSTRAINT `battle_ibfk_1` FOREIGN KEY (`hostid`) REFERENCES `users` (`uid`);

--
-- Constraints for table `faq_vote`
--
ALTER TABLE `faq_vote`
  ADD CONSTRAINT `faq_vote_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`),
  ADD CONSTRAINT `faq_vote_ibfk_2` FOREIGN KEY (`qid`) REFERENCES `faq` (`id`);

--
-- Constraints for table `friendship`
--
ALTER TABLE `friendship`
  ADD CONSTRAINT `friendship_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`);

--
-- Constraints for table `item_owned`
--
ALTER TABLE `item_owned`
  ADD CONSTRAINT `item_owned_ibfk_1` FOREIGN KEY (`sid`) REFERENCES `item` (`id`),
  ADD CONSTRAINT `item_owned_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`);

--
-- Constraints for table `tournament_score`
--
ALTER TABLE `tournament_score`
  ADD CONSTRAINT `tournament_score_ibfk_1` FOREIGN KEY (`tid`) REFERENCES `tournament` (`id`),
  ADD CONSTRAINT `tournament_score_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`);

--
-- Constraints for table `tournament_sessions`
--
ALTER TABLE `tournament_sessions`
  ADD CONSTRAINT `tournament_sessions_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

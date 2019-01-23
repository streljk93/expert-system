--
-- Структура таблицы `answers`
--

CREATE TABLE IF NOT EXISTS `answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datacores_id` int(10) unsigned NOT NULL,
  `questions_id` int(10) unsigned NOT NULL,
  `a_yes` int(10) unsigned NOT NULL DEFAULT '0',
  `a_no` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `id` (`id`),
  KEY `id_2` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `datacores`
--

CREATE TABLE IF NOT EXISTS `datacores` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `games` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=31 ;

--
-- Дамп данных таблицы `datacores`
--

INSERT INTO `datacores` (`id`, `name`, `games`) VALUES
(1, '1н', 1),
(2, '2н', 1),
(3, '7', 1),
(4, '13', 1),
(5, '16', 1),
(6, '17', 1),
(7, '31', 1),
(8, '37', 1),
(9, '38', 1),
(10, '106', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `questions`
--

CREATE TABLE IF NOT EXISTS `questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;
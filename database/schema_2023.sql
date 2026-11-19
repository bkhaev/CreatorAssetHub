-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Июн 23 2023 г., 00:18
-- Версия сервера: 10.4.24-MariaDB
-- Версия PHP: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `bfdb`
--

DELIMITER $$
--
-- Процедуры
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `add-edit-playlist` (IN `InPlaylistId` INT, IN `InUserId` INT, IN `InTitle` VARCHAR(255), IN `InDescription` VARCHAR(1000), IN `InAccess` TINYINT(1), IN `InResourceType` VARCHAR(50))   BEGIN
DECLARE result TINYINT(1);
DECLARE userId INT DEFAULT 0;
DECLARE playlistId INT;
DECLARE _tempCount INT DEFAULT 1;
DECLARE playlistTitle VARCHAR(255);

# result = 1: пользователь не найден
# result = 2: плейлист создан
# result = 3: плейлист не найден
# result = 4: плейлист обновлён
# result = 5: плейлист принадлежит другому пользователю

SET userId = (SELECT `id` FROM `users` WHERE `id` = InUserId);

IF userId IS NULL THEN
	SET result = 1;
ELSE
	SET playlistTitle = InTitle;
	SET playlistId = (SELECT `id` FROM `user_playlists` WHERE `user_id` = InUserId AND `title` = playlistTitle LIMIT 1);
    
    IF playlistId = InPlaylistId THEN
    	SET playlistId = NULL;
    END IF;

	WHILE playlistId IS NOT NULL DO
    	SET playlistTitle = CONCAT(InTitle, ' (', _tempCount, ')');
    	SET playlistId = (SELECT `id` FROM `user_playlists` WHERE `user_id` = InUserId AND `title` = playlistTitle LIMIT 1);
        
        IF playlistId = InPlaylistId THEN
            SET playlistId = NULL;
        ELSE
        	SET _tempCount = _tempCount + 1;
        END IF;
	END WHILE;

	IF InPlaylistId IS NULL THEN
		SET playlistId = (SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'user_playlists');
		INSERT INTO `user_playlists`(`user_id`, `access`, `resource_type`, `title`, `description`) VALUES (InUserId,InAccess,InResourceType,playlistTitle,InDescription);
    	SET result = 2;
    ELSE
    	SET userId = (SELECT `user_id` FROM `user_playlists` WHERE `id` = InPlaylistId);
        IF userId IS NULL THEN
        	SET result = 3;
        ELSEIF userId = InUserId THEN
    		UPDATE `user_playlists` SET `access` = InAccess, `title` = playlistTitle, `description` = InDescription WHERE `id` = InPlaylistId;
            SET result = 4;
        ELSE
        	SET result = 5;
        END IF;
    END IF;
END IF;

SELECT result, playlistId;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `permissions-add-edit-delete` (IN `InBit` INT UNSIGNED, IN `InName` VARCHAR(50) CHARSET utf8mb4, IN `InDescription` VARCHAR(255) CHARSET utf8mb4)  COMMENT 'Добавить право' BEGIN
DECLARE result TINYINT(1);
DECLARE _bit INT DEFAULT 0;
DECLARE _bitTemp INT;

IF InBit = 0 AND InName != '' AND InDescription != '' THEN
    SET _bit = 1;
    SET _bitTemp = (SELECT `bit` FROM `user_permissions` WHERE `bit` = _bit);
    WHILE _bitTemp IS NOT NULL DO
        SET _bit = _bitTemp * 2;
        SET _bitTemp = (SELECT `bit` FROM `user_permissions` WHERE `bit` = _bit);
    END WHILE;
    INSERT INTO `user_permissions`(`bit`, `name`, `description`) VALUES (_bit,InName,InDescription);
    SET result = 1;
ELSEIF InBit != 0 AND InName != '' AND InDescription != '' THEN
	SET InBit = (SELECT `bit` FROM `user_permissions` WHERE `bit` = InBit);
    IF InBit IS NULL THEN
    	SET result = 2;
    ELSE
    	UPDATE `user_permissions` SET `name` = InName, `description` = InDescription WHERE `bit` = InBit;
        SET result = 3;
        SET _bit = InBit;
    END IF;
ELSEIF InBit != 0 AND InName = '' AND InDescription = '' THEN
	SET InBit = (SELECT `bit` FROM `user_permissions` WHERE `bit` = InBit);
    IF InBit IS NULL THEN
    	SET result = 2;
    ELSE
    	DELETE FROM `user_permissions` WHERE `bit` = InBit;
        SET result = 4;
    END IF;
ELSEIF InName = '' THEN
	SET result = 5;
ELSEIF InDescription = '' THEN
	SET result = 6;
END IF;

SELECT result, _bit AS `bit`;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `artists_of_music`
--

CREATE TABLE `artists_of_music` (
  `music_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `authors_of_phrases`
--

CREATE TABLE `authors_of_phrases` (
  `phrase_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `authors_of_phrases`
--

INSERT INTO `authors_of_phrases` (`phrase_id`, `person_id`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 3),
(5, 4),
(6, 4),
(7, 4),
(8, 3),
(9, 3),
(10, 4),
(11, 3),
(12, 6),
(13, 3),
(14, 2),
(14, 3),
(15, 4),
(16, 4),
(17, 6),
(18, 6),
(19, 4),
(20, 1),
(21, 1),
(22, 6),
(23, 3),
(24, 6),
(25, 2),
(311, 4),
(312, 163),
(312, 170);

-- --------------------------------------------------------

--
-- Структура таблицы `categories_of_images`
--

CREATE TABLE `categories_of_images` (
  `image_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `categories_of_images`
--

INSERT INTO `categories_of_images` (`image_id`, `category_id`) VALUES
(26, 15),
(27, 21),
(28, 9),
(28, 21),
(29, 21),
(30, 28),
(31, 14),
(31, 21),
(32, 21),
(33, 14),
(33, 27),
(34, 14),
(35, 2),
(35, 11),
(43, 13),
(44, 14),
(45, 8),
(45, 10),
(46, 14);

-- --------------------------------------------------------

--
-- Структура таблицы `categories_of_sounds`
--

CREATE TABLE `categories_of_sounds` (
  `sound_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `categories_of_videos`
--

CREATE TABLE `categories_of_videos` (
  `video_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `categories_of_videos`
--

INSERT INTO `categories_of_videos` (`video_id`, `category_id`) VALUES
(36, 9),
(36, 21),
(37, 28),
(38, 21),
(39, 21),
(40, 21),
(41, 16),
(42, 9),
(42, 21);

-- --------------------------------------------------------

--
-- Структура таблицы `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `downloads` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `weight` bigint(20) UNSIGNED NOT NULL COMMENT 'в байтах',
  `width` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'в пикселях',
  `height` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'в пикселях',
  `background` tinyint(1) DEFAULT NULL,
  `duration` int(10) UNSIGNED DEFAULT NULL COMMENT 'в секундах',
  `sample_rate` int(10) UNSIGNED DEFAULT NULL COMMENT 'кГц',
  `fps` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'кадров в секунду',
  `bit_rate` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'кбит в секунду',
  `vocals` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит информацию о файлах';

--
-- Дамп данных таблицы `files`
--

INSERT INTO `files` (`id`, `display_name`, `downloads`, `file_name`, `weight`, `width`, `height`, `background`, `duration`, `sample_rate`, `fps`, `bit_rate`, `vocals`) VALUES
(1, 'Стюардесса в самолёте', 0, '5.jpg', 102490, 1000, 653, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'шаблон', 0, '4.jpg', 232929, 807, 807, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'шаблон', 0, '3.jpg', 198628, 1080, 1440, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'фото', 0, '1.jpg', 370179, 1080, 538, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'шаблон', 0, '6.jpg', 15609, 600, 499, 2, NULL, NULL, NULL, NULL, NULL),
(6, 'шаблон', 0, '7.jpg', 221303, 1190, 736, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'светлый вариант', 0, '8.png', 176816, 8018, 4517, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'Школьная доска', 0, '9.jpg', 864654, 1920, 1280, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'Эйфелева башня', 1, '10.jpg', 1067474, 3840, 2160, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'Сердитый кот на вечеринке', 0, '12.mp4', 532772, 384, 480, NULL, 7, 44100, 30, NULL, NULL),
(11, 'Аутро с канала [скрыто]', 0, '11.mp4', 4254898, 1920, 1080, NULL, 12, 48000, 30, NULL, NULL),
(12, 'оригинал', 0, '13.mp4', 3764125, 720, 1276, NULL, 15, 44100, 20, NULL, NULL),
(13, 'оригинал', 0, '14.mp4', 38076960, 1152, 720, NULL, 241, 44100, 15, NULL, NULL),
(14, 'Скарлетт Йоханссон удивляется', 0, '2.jpg', 292443, 1080, 928, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'вид снизу на деревья', 1, '15.mp4', 118895712, 2560, 1440, NULL, 29, 48000, 30, NULL, NULL),
(16, 'видео', 0, '16.mp4', 55318279, 2560, 1440, NULL, 25, 48000, 30, NULL, NULL),
(17, 'видео', 0, '17.mp4', 5674625, 2560, 1440, NULL, 9, 48000, 30, NULL, NULL),
(18, 'Звёздное ночное небо', 0, '18.jpg', 489856, 2560, 1440, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'свечение (вариант 1)', 2, '19.png', 333426, 1920, 1080, 1, NULL, NULL, NULL, NULL, NULL),
(20, 'свечение (вариант 2)', 1, '20.png', 158781, 362, 436, 1, NULL, NULL, NULL, NULL, NULL),
(21, 'свечение (вариант 3)', 6, '21.png', 387116, 1600, 899, 1, NULL, NULL, NULL, NULL, NULL),
(22, 'с прозрачным фоном', 0, '22.png', 422711, 600, 510, 1, NULL, NULL, NULL, NULL, NULL),
(23, 'Витамины в бутылочке', 0, '23.png', 96108, 576, 597, 1, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `files_of_materials`
--

CREATE TABLE `files_of_materials` (
  `material_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `queue` tinyint(2) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `files_of_materials`
--

INSERT INTO `files_of_materials` (`material_id`, `file_id`, `queue`) VALUES
(26, 1, 0),
(27, 2, 1),
(28, 3, 0),
(29, 14, 0),
(30, 4, 0),
(31, 5, 0),
(32, 6, 0),
(33, 7, 0),
(34, 8, 0),
(35, 9, 1),
(36, 10, 1),
(37, 11, 0),
(38, 12, 1),
(38, 15, 2),
(39, 13, 0),
(40, 15, 0),
(41, 16, 0),
(42, 17, 0),
(43, 18, 0),
(44, 19, 1),
(44, 20, 2),
(44, 21, 3),
(45, 22, 1),
(46, 23, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `genres_of_music`
--

CREATE TABLE `genres_of_music` (
  `music_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `images`
--

CREATE TABLE `images` (
  `material_id` int(11) NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('фото','иллюстрация','векторное изображение') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `release_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит информацию о изображениях';

--
-- Дамп данных таблицы `images`
--

INSERT INTO `images` (`material_id`, `title`, `type`, `description`, `author`, `release_date`) VALUES
(26, 'Стюардесса в самолёте', 'фото', NULL, NULL, NULL),
(27, 'Энакин и Падме на пикнике', 'фото', NULL, NULL, NULL),
(28, 'День рождения кота', 'фото', NULL, NULL, NULL),
(29, 'Скарлетт Йоханссон удивляется', 'фото', NULL, NULL, NULL),
(30, 'Широкий Джонни в чёрных очках и растрёпанными волосами', 'фото', NULL, '[скрыто]', NULL),
(31, 'Демотиватор (чёрный квадрат)', 'фото', NULL, NULL, NULL),
(32, 'Веселый и грустный мужик', 'фото', NULL, NULL, NULL),
(33, 'Клетчатый светлый фон', 'векторное изображение', NULL, '[скрыто]', '2021-08-17 21:00:00'),
(34, 'Школьная доска', 'фото', NULL, NULL, NULL),
(35, 'Эйфелева башня', 'фото', NULL, NULL, NULL),
(43, 'Звёздное ночное небо', 'фото', NULL, NULL, NULL),
(44, 'Синее свечение', 'фото', NULL, NULL, NULL),
(45, 'Арбуз', 'фото', NULL, NULL, NULL),
(46, 'Витамины в бутылочке', 'иллюстрация', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `iv_categories`
--

CREATE TABLE `iv_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `picture_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит категории изображений и видео';

--
-- Дамп данных таблицы `iv_categories`
--

INSERT INTO `iv_categories` (`id`, `name`, `description`, `picture_name`) VALUES
(1, 'Другие', '', ''),
(2, 'Страны', 'Ассоциации и красоты каждой страны раскрывают удивительный и уникальный мир!', 'countries.jpg'),
(3, 'Здания и архитектура', 'Барокко, классицизм, ампир, модернизм - самые разные дизайнерские стандарты всех времён!', 'architecture.jpg'),
(4, 'Растения и цветы', 'Красные, зелёные, жёлтые, а может, дикие или домашние: каких только нет!', 'flowers.jpeg'),
(5, 'Садоводство', 'Выращивание растений и овощей и использование специальных инструментов для этого', 'garden.jpg'),
(6, 'Овощи', 'Эти витамины широко используют в кулинарии или едят сырыми', 'vegetables.jpeg'),
(7, 'Фрукты', 'Сочные витамины: абрикосы, киви, манго и бананы, а ещё сто тысяч других!', 'fruit.jpeg'),
(8, 'Ягоды', 'Маленькие, мясистые, может и съедобные плоды. А ещё яркие и сочные!', 'berries.jpg'),
(9, 'Животные', 'Летающие, бегающие и прыгающие, подводные и вездесущие - животные есть везде!', 'animals.jpg'),
(10, 'Еда', 'Сочные, аппетитные блюда и напитки для любого проекта', 'food.jpg'),
(11, 'Достопримечательности', 'Большие, красивые, старые или могучие: каждый из них чем то уникален', 'sights.jpg'),
(12, 'Транспорт', 'Вы точно найдёте тот тип, с нужной окраской и стороны', 'vehicle.jpg'),
(13, 'Природа', 'Впечатляющие снимки окружающего нашего мира в самых разных ракурсах', 'nature.jpg'),
(14, 'Графические ресурсы', 'Автономные рисунки, футажи и другие творения современных технологий', 'graphics.jpeg'),
(15, 'Люди', 'Живые, эмоциональные, задумчивые и другие портреты и не только', 'people.jpg'),
(16, 'Компьютерная игра', 'Скрины и геймплей самых разных игр, а также арты по любимым из них!', 'computer_games.jpg'),
(17, 'Игрушки', 'Детские игрушки, плюшевые или пластмасовые и не только', 'toys.jpeg'),
(18, 'Мебель', 'Передвижные или встроенные изделия для обстановки жилых и общественных помещений и различных зон пребывания человека', 'furniture.jpg'),
(19, 'Религия и культура', 'Памятники культуры, искусство, вера, ритуалы, священные вещи и места', 'culture.jpg'),
(20, 'Хобби и отдых', 'Пассивное или активное времяпровождение', 'pastime.jpg'),
(21, 'Мемы', 'Ищите шаблоны мемов для вашего нового проекта? Найдите нужный и с примерами использования!', 'memes.jpg'),
(22, 'Техника', 'Устройства, механизмы, машины, системы', 'technique.jpg'),
(23, 'Картография', 'Моделировании и отображении пространственного расположения, сочетания и взаимосвязи объектов, явлений природы и общества', 'cartography.jpg'),
(24, 'Технологии', 'Применение научного знания для решения практических задач: способы работы, её режим и последовательность действий', 'technology.jpg'),
(25, 'Экономика', 'Отношения между людьми в процессе производства, распределения, обмена и потребления благ, обозначающее масштабы хозяйственной деятельности', 'economy.jpeg'),
(26, 'Фоны', 'Простые и не только фоны разного цвета и формы', 'backgrounds.jpg'),
(27, 'Фильтры и спецэффекты', 'Закончите постпродакшн с помощью качественных фильтров и специальных эффектов', 'graphic_effects.jpg'),
(28, 'Блогеры', 'Ваши любимые стримеры, летсплейщики и влогеры', 'blogers.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `iv_categories_groups`
--

CREATE TABLE `iv_categories_groups` (
  `category_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `iv_categories_groups`
--

INSERT INTO `iv_categories_groups` (`category_id`, `parent_id`) VALUES
(4, 5),
(6, 5),
(6, 10),
(7, 5),
(7, 10),
(8, 5),
(8, 10),
(11, 3),
(26, 14),
(27, 14);

-- --------------------------------------------------------

--
-- Структура таблицы `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `access` tinyint(1) NOT NULL DEFAULT 1,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `views` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `notes` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Общая таблица материалов';

--
-- Дамп данных таблицы `materials`
--

INSERT INTO `materials` (`id`, `user_id`, `access`, `date`, `views`, `notes`) VALUES
(1, 1, 1, '2023-04-21 22:11:42', 0, NULL),
(2, 1, 1, '2023-04-27 15:20:05', 0, NULL),
(3, 1, 1, '2023-04-27 15:31:30', 0, NULL),
(4, 1, 1, '2023-04-27 15:37:53', 0, NULL),
(5, 1, 1, '2023-04-27 15:44:16', 0, NULL),
(6, 1, 1, '2023-04-27 15:57:13', 0, NULL),
(7, 1, 1, '2023-04-27 16:07:01', 0, NULL),
(8, 1, 1, '2023-04-27 16:17:48', 0, NULL),
(9, 1, 1, '2023-04-27 16:21:10', 0, NULL),
(10, 1, 1, '2023-04-27 16:25:26', 0, NULL),
(11, 1, 1, '2023-04-27 16:28:53', 0, NULL),
(12, 1, 1, '2023-04-27 16:38:28', 0, NULL),
(13, 1, 1, '2023-04-27 16:44:22', 0, NULL),
(14, 1, 1, '2023-04-27 16:51:07', 0, NULL),
(15, 1, 1, '2023-04-27 16:58:46', 0, NULL),
(16, 1, 1, '2023-04-27 19:37:55', 0, NULL),
(17, 1, 1, '2023-04-27 22:08:07', 0, NULL),
(18, 1, 1, '2023-04-27 22:11:11', 0, NULL),
(19, 1, 1, '2023-04-27 22:17:14', 0, NULL),
(20, 1, 1, '2023-04-27 22:25:04', 0, NULL),
(21, 1, 1, '2023-04-27 22:46:04', 0, NULL),
(22, 1, 1, '2023-04-27 22:57:28', 0, NULL),
(23, 1, 1, '2023-04-27 23:02:13', 0, NULL),
(24, 1, 1, '2023-04-27 23:08:14', 0, NULL),
(25, 1, 1, '2023-04-27 23:10:43', 0, NULL),
(26, 1, 1, '2023-05-18 22:36:01', 0, NULL),
(27, 1, 1, '2023-05-18 22:57:31', 0, NULL),
(28, 1, 1, '2023-05-18 23:11:00', 0, NULL),
(29, 1, 1, '2023-05-18 23:14:57', 0, NULL),
(30, 1, 1, '2023-05-18 23:32:39', 0, NULL),
(31, 1, 1, '2023-05-18 23:37:42', 0, NULL),
(32, 1, 1, '2023-05-18 23:43:46', 0, NULL),
(33, 1, 1, '2023-05-19 09:06:07', 0, NULL),
(34, 1, 1, '2023-05-19 09:15:07', 0, NULL),
(35, 1, 1, '2023-05-19 09:25:20', 0, NULL),
(36, 1, 1, '2023-05-19 09:48:17', 0, NULL),
(37, 1, 1, '2023-05-19 10:31:20', 0, NULL),
(38, 1, 1, '2023-05-19 10:41:08', 0, NULL),
(39, 1, 1, '2023-05-19 10:50:57', 0, NULL),
(40, 1, 1, '2023-05-19 11:21:02', 0, NULL),
(41, 1, 1, '2023-05-19 12:13:08', 0, NULL),
(42, 1, 1, '2023-05-19 12:57:17', 0, NULL),
(43, 1, 1, '2023-05-19 13:04:14', 0, NULL),
(44, 1, 1, '2023-05-19 15:01:12', 0, NULL),
(45, 1, 1, '2023-05-19 15:07:50', 0, NULL),
(46, 1, 1, '2023-05-19 15:14:46', 0, NULL),
(311, 41, 1, '2023-06-22 22:04:16', 0, NULL),
(312, NULL, 1, '2023-06-22 22:08:43', 0, NULL);

--
-- Триггеры `materials`
--
DELIMITER $$
CREATE TRIGGER `phrase-restrict-access` AFTER UPDATE ON `materials` FOR EACH ROW BEGIN
IF new.access IS FALSE THEN
	UPDATE `phrases` SET `access` = 0 WHERE `material_id` = new.id;
END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `user-add-point-1` AFTER INSERT ON `materials` FOR EACH ROW BEGIN
IF new.user_id IS NOT NULL THEN
	UPDATE `users` SET `points` = `points` + 5 WHERE `id` = new.user_id;
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `materials_of_playlists`
--

CREATE TABLE `materials_of_playlists` (
  `id` int(11) NOT NULL,
  `playlist_id` int(11) NOT NULL,
  `material_id` int(11) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `materials_of_playlists`
--

INSERT INTO `materials_of_playlists` (`id`, `playlist_id`, `material_id`, `date`, `notes`) VALUES
(1, 1, 19, '2023-06-02 13:34:17', 'Добавить во вторую часть проекта'),
(13, 11, 46, '2023-06-03 13:52:14', 'Для видео как [скрыто] готовил элексир'),
(16, 10, 42, '2023-06-03 13:57:14', NULL),
(40, 10, 41, '2023-06-03 14:38:54', 'Подставить типа играю я'),
(41, 1, 6, '2023-06-04 11:01:20', NULL),
(46, 11, 43, '2023-06-04 21:13:17', NULL),
(49, 1, 22, '2023-06-17 20:43:12', NULL),
(51, 1, 21, '2023-06-19 21:31:55', 'В новый проект 2'),
(52, 1, 13, '2023-06-20 21:37:46', '[скрыто] ПЫТАЕТСЯ пройти RE7'),
(59, 11, 35, '2023-06-21 16:13:47', NULL),
(60, 11, 45, '2023-06-21 16:42:27', NULL),
(61, 10, 38, '2023-06-21 17:22:27', 'Добавить в момент когда я упал в pubg с горы'),
(62, 10, 39, '2023-06-21 17:22:59', NULL),
(64, 14, 44, '2023-06-22 18:36:07', 'окей'),
(66, 15, 23, '2023-06-22 18:39:36', NULL),
(67, 15, NULL, '2023-06-22 18:51:36', NULL),
(68, 15, NULL, '2023-06-22 21:00:17', NULL),
(72, 18, 33, '2023-06-22 21:43:36', NULL),
(73, 18, 34, '2023-06-22 21:43:38', NULL),
(74, 18, 29, '2023-06-22 21:43:39', NULL),
(75, 18, 43, '2023-06-22 22:17:42', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `materials_tags`
--

CREATE TABLE `materials_tags` (
  `id` int(11) NOT NULL,
  `tag` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит теги материалов';

--
-- Дамп данных таблицы `materials_tags`
--

INSERT INTO `materials_tags` (`id`, `tag`, `user_id`, `date`) VALUES
(1, 'влог', 1, '2023-04-27 14:34:10'),
(2, 'поговорка', 1, '2023-04-27 15:02:49'),
(3, 'сарказм', 1, '2023-04-27 15:37:53'),
(4, 'засмущался', 1, '2023-04-27 16:07:01'),
(5, 'приветствие', 1, '2023-04-27 16:17:48'),
(6, 'настрой', 1, '2023-04-27 16:21:11'),
(7, 'вшоке', 1, '2023-04-27 19:37:55'),
(8, 'азиатскиедырочки', 1, '2023-04-27 22:46:04'),
(9, 'смешносказал', 1, '2023-04-27 22:57:29'),
(10, 'кривляется', 1, '2023-04-27 22:57:29'),
(11, 'полёт', 1, '2023-05-18 22:36:01'),
(12, 'пассажиры', 1, '2023-05-18 22:36:01'),
(13, 's7 airlines', 1, '2023-05-18 22:36:01'),
(14, 'экипаж самолёта', 1, '2023-05-18 22:36:01'),
(15, 'star wars', 1, '2023-05-18 22:57:31'),
(16, 'звёздные войны', 1, '2023-05-18 22:57:31'),
(17, 'энакин скайвокер', 1, '2023-05-18 22:57:31'),
(18, 'падме амидала', 1, '2023-05-18 22:57:31'),
(19, 'кошка', 1, '2023-05-18 23:11:00'),
(20, 'домашнее животное', 1, '2023-05-18 23:11:00'),
(21, 'питомец', 1, '2023-05-18 23:11:00'),
(22, 'день рождения', 1, '2023-05-18 23:11:00'),
(23, 'широкий мем', 1, '2023-05-18 23:32:39'),
(24, 'wide walking meme', 1, '2023-05-18 23:32:39'),
(25, 'банда ютуба', 1, '2023-05-18 23:32:39'),
(26, 'негодяи', 1, '2023-05-18 23:32:39'),
(27, 'не знает знает мем', 1, '2023-05-18 23:43:46'),
(28, 'лысый мужик улыбается', 1, '2023-05-18 23:43:46'),
(29, 'лысый мужик грустит', 1, '2023-05-18 23:43:46'),
(30, 'фон в клетку', 1, '2023-05-19 09:08:54'),
(31, 'белый фон', 1, '2023-05-19 09:08:54'),
(32, 'мел', 1, '2023-05-19 09:15:07'),
(33, 'школа', 1, '2023-05-19 09:15:07'),
(34, 'рамка для фотографии', 1, '2023-05-19 09:15:07'),
(35, 'франция', 1, '2023-05-19 09:25:20'),
(36, 'туса', 1, '2023-05-19 10:18:29'),
(37, 'под музыку', 1, '2023-05-19 10:18:29'),
(38, 'интро', 1, '2023-05-19 10:31:20'),
(39, 'аутро', 1, '2023-05-19 10:31:20'),
(40, 'конец ролика', 1, '2023-05-19 10:31:20'),
(41, 'конец видео', 1, '2023-05-19 10:31:20'),
(42, 'на случай важных переговоров', 1, '2023-05-19 10:41:08'),
(43, 'потеет', 1, '2023-05-19 10:50:57'),
(44, 'деревья', 1, '2023-05-19 11:21:02'),
(45, 'дерево', 1, '2023-05-19 11:21:02'),
(46, 'времена года', 1, '2023-05-19 11:21:02'),
(47, 'counter strike', 1, '2023-05-19 12:13:08'),
(48, 'шутер', 1, '2023-05-19 12:13:08'),
(49, 'стрелялки', 1, '2023-05-19 12:13:08'),
(50, 'csgo', 1, '2023-05-19 12:13:08'),
(51, 'кс го', 1, '2023-05-19 12:13:08'),
(52, 'ослы', 1, '2023-05-19 12:57:17'),
(53, 'лошадки', 1, '2023-05-19 12:57:17'),
(54, 'лошадь', 1, '2023-05-19 12:57:17'),
(55, 'осёл', 1, '2023-05-19 12:57:17'),
(56, 'звёзды', 1, '2023-05-19 13:04:14'),
(57, 'ночь', 1, '2023-05-19 13:04:14'),
(58, 'блёстки', 1, '2023-05-19 15:01:12'),
(59, 'сияние', 1, '2023-05-19 15:01:12'),
(60, 'турция', 1, '2023-05-19 15:07:50'),
(61, 'дыня', 1, '2023-05-19 15:07:50'),
(62, 'таблетки', 1, '2023-05-19 15:14:46'),
(63, 'лекарство', 1, '2023-05-19 15:14:46'),
(64, 'антибиотики', 1, '2023-05-19 15:14:46'),
(65, 'тюрьма', NULL, '2023-05-22 06:28:05'),
(66, 'ываыва', 40, '2023-06-22 20:59:48');

-- --------------------------------------------------------

--
-- Структура таблицы `moods_of_music`
--

CREATE TABLE `moods_of_music` (
  `music_id` int(11) NOT NULL,
  `mood_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `music`
--

CREATE TABLE `music` (
  `material_id` int(11) NOT NULL,
  `release_id` int(11) NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `explicit` tinyint(1) NOT NULL DEFAULT 0,
  `yt_free_use` tinyint(1) DEFAULT NULL,
  `publisher` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `producer` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `compositor` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `videoclip` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит информацию о музыкальных композициях';

-- --------------------------------------------------------

--
-- Структура таблицы `m_genres`
--

CREATE TABLE `m_genres` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `picture_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит жанры музыки';

-- --------------------------------------------------------

--
-- Структура таблицы `m_genres_groups`
--

CREATE TABLE `m_genres_groups` (
  `genre_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `m_moods`
--

CREATE TABLE `m_moods` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `picture_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит настроения музыки';

-- --------------------------------------------------------

--
-- Структура таблицы `m_pirates`
--

CREATE TABLE `m_pirates` (
  `id` int(11) NOT NULL,
  `music_id` int(11) NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `m_releases`
--

CREATE TABLE `m_releases` (
  `id` int(11) NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('album','single','cover','remixes','mixtape','soundtrack','live','bootleg','tribute','demo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `picture_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит информацию о музыкальных релизах';

-- --------------------------------------------------------

--
-- Структура таблицы `persons`
--

CREATE TABLE `persons` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `name` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `picture_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Информация о субъектах, представляющие некоторый контент';

--
-- Дамп данных таблицы `persons`
--

INSERT INTO `persons` (`id`, `user_id`, `date`, `name`, `description`, `picture_name`) VALUES
(1, 1, '2023-04-27 14:24:36', '[скрыто]', 'Блогер, летсплейщик', NULL),
(2, 1, '2023-04-27 15:22:09', '[скрыто]', 'Летсплейщик, стример', NULL),
(3, 1, '2023-04-27 15:32:10', '[скрыто]', 'Летсплейщик', NULL),
(4, 1, '2023-04-27 15:44:16', '[скрыто]', 'Летсплейщик, блогер', NULL),
(5, 1, '2023-04-27 16:07:01', '[скрыто]', 'Летсплейщик, блогер', NULL),
(6, 1, '2023-04-27 16:38:28', '[скрыто]', 'Летсплейщик, автовлогер', NULL),
(7, 1, '2023-05-12 19:19:35', 'Francis Wells', NULL, NULL),
(137, 40, '2023-06-22 19:00:17', 'фывыф', NULL, NULL),
(138, 40, '2023-06-22 19:17:15', 'dsfds', NULL, NULL),
(140, 40, '2023-06-22 20:08:29', 'ыва ыв', NULL, NULL),
(148, 40, '2023-06-22 20:30:44', 'sadfasd', NULL, NULL),
(153, 40, '2023-06-22 20:38:08', 'dsfsdf', NULL, NULL),
(162, 40, '2023-06-22 20:54:22', 'sdfsd fsdf', NULL, NULL),
(163, 40, '2023-06-22 20:54:38', 'sdfsdf', NULL, NULL),
(164, 40, '2023-06-22 20:55:30', 'asdf assa', NULL, NULL),
(165, 40, '2023-06-22 20:56:51', 'afedsgfbxfdgfxnbg', NULL, NULL),
(166, 40, '2023-06-22 20:59:48', 'ываыва', NULL, NULL),
(167, 40, '2023-06-22 21:02:16', 'вапв ава', NULL, NULL),
(168, 40, '2023-06-22 21:06:25', 'sdf', NULL, NULL),
(169, NULL, '2023-06-22 21:38:29', 'sfsdf', NULL, NULL),
(170, NULL, '2023-06-22 22:08:43', 'fdsgds', NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `persons_links`
--

CREATE TABLE `persons_links` (
  `person_id` int(11) NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `phrases`
--

CREATE TABLE `phrases` (
  `material_id` int(11) NOT NULL,
  `access` tinyint(1) NOT NULL DEFAULT 2,
  `edit_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `anonym` tinyint(1) NOT NULL DEFAULT 0,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `phrase` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tone` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'M',
  `emotion` enum('удивление','ор','насмешка','испуг','прямо','гневно','радостно','отвращение') COLLATE utf8mb4_unicode_ci NOT NULL,
  `camera` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит фразы';

--
-- Дамп данных таблицы `phrases`
--

INSERT INTO `phrases` (`material_id`, `access`, `edit_key`, `anonym`, `approved`, `phrase`, `tone`, `emotion`, `camera`) VALUES
(1, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Ну, как говорится чем бы ребёнок не тешился, главное не плакал', 'M', '', 1),
(2, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Мальчик мой, вы ютубер или кто вообще?', 'L', '', 1),
(3, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Не, ну в рубашке родился, просто чудо. Ничто иначе, как просто чудо', 'M', '', 1),
(4, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Ребята, помогите, спасите!', 'H', '', 1),
(5, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Понятно. Спасибо большое, буду знать', 'M', '', 1),
(6, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'То есть вы настолько не любите меня, да? Спасибо', 'M', '', 1),
(7, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Ой, спасибо, спасибо!', 'M', 'радостно', 1),
(8, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 1, 'Салам!', 'MH', 'радостно', 1),
(9, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Пацаны, ручки разогреваем!', 'H', 'радостно', 1),
(10, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Иди мой маленький, иди мой сладенький', 'M', 'радостно', 1),
(11, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Забираем.', 'L', 'прямо', 1),
(12, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Шутка, нет конечно.', 'M', '', 1),
(13, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 1, 'Да да да.. пацаны, да', 'MH', '', 1),
(14, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Открывай дверь! ..Закрывай дверь.', 'H', 'прямо', 1),
(15, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Здорово пацаны всем кого ещё не видел', 'M', '', 1),
(16, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Добро пожаловать в будущее ребята!', 'H', 'радостно', 1),
(17, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Я уверен что я единственный человек, который так долго проходит этот уровень', 'M', '', 1),
(18, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Я вспотел, 40 градусов.. по Форингейту', 'M', '', 1),
(19, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Ааа! Что это такое было вообще?!', 'H', 'испуг', 1),
(20, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Всё, сосредоточился, это всего лишь пиксель, который нужно преодолеть', 'L', '', 1),
(21, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Дырочка под наклоном! Под наклоном, дырочка!', 'H', 'удивление', 1),
(22, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Ааа! Не ходи туда... Там смерть!', 'L', 'прямо', 1),
(23, 2, 'bf8f6add19339002bd90b5a307949dfa', 1, 0, 'Им нужен я, они хотят заполучить меня!', 'M', '', 1),
(24, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Так, всё, солдаты Уругвая, группа на месте? На месте!', 'H', 'прямо', 1),
(25, 2, 'bf8f6add19339002bd90b5a307949dfa', 0, 0, 'Сам себя не похвалишь, никто не похвалит', 'M', '', 1),
(311, 2, '750ea0c8867e666bbd9b86fdd7e34a06', 0, 0, 'wefs dfdsf', 'MH', '', 0),
(312, 2, 'ba2b43fee981eba75cb5f39bfa3da04c', 0, 0, 'asd asdas dasd', 'L', 'гневно', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `phrases_synonyms`
--

CREATE TABLE `phrases_synonyms` (
  `id` int(11) NOT NULL,
  `phrase_id` int(11) NOT NULL,
  `synonym` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит теги и синонимы фраз';

--
-- Дамп данных таблицы `phrases_synonyms`
--

INSERT INTO `phrases_synonyms` (`id`, `phrase_id`, `synonym`) VALUES
(1, 2, 'а я тоже ютубер'),
(2, 3, 'с золотой ложечкой'),
(3, 3, 'с золотой ложкой'),
(4, 4, 'помощь'),
(5, 5, 'благодарю'),
(6, 6, 'обидно'),
(7, 6, 'расстроили'),
(8, 7, 'благодарю'),
(9, 7, 'похвалили'),
(13, 8, 'алоха'),
(11, 8, 'здорова'),
(12, 8, 'как жизнь'),
(10, 8, 'привет'),
(14, 9, 'готовимся'),
(15, 10, 'ути мой хороший'),
(16, 10, 'ути пути'),
(17, 11, 'берём'),
(18, 11, 'возьму'),
(19, 11, 'моё'),
(21, 12, 'обманул'),
(20, 12, 'пошутил'),
(26, 13, 'внатуре'),
(28, 13, 'вы правы'),
(25, 13, 'да-да'),
(29, 13, 'согласен'),
(27, 13, 'так уж и быть'),
(22, 13, 'точно'),
(23, 13, 'точняк'),
(30, 14, 'закрой за собой'),
(33, 14, 'запри'),
(31, 14, 'открой дверь'),
(32, 14, 'отпирай'),
(34, 15, 'привет'),
(35, 16, 'роботы'),
(36, 17, 'сложно'),
(37, 17, 'хардкор'),
(38, 18, 'мне жарко'),
(39, 18, 'хардкор'),
(41, 19, 'это что было'),
(40, 19, 'это что такое'),
(42, 20, 'джентельмен'),
(46, 20, 'соберись'),
(47, 20, 'собрались'),
(45, 20, 'успокоились'),
(44, 20, 'успокойся'),
(43, 20, 'это что было'),
(48, 21, 'азиатская дырочка'),
(53, 22, 'беги пока не поздно'),
(51, 22, 'ванга'),
(52, 22, 'не иди туда'),
(54, 22, 'убегай отсюда'),
(55, 22, 'уходи быстрей'),
(50, 22, 'экстрасенс'),
(49, 22, 'ясновидище'),
(59, 24, 'вперёд'),
(58, 24, 'приготовились'),
(56, 24, 'собрались'),
(57, 24, 'сосредоточились'),
(208, 311, 'xcdxvxcv'),
(209, 311, 'xvdfgds');

-- --------------------------------------------------------

--
-- Структура таблицы `related_materials`
--

CREATE TABLE `related_materials` (
  `material1_id` int(11) NOT NULL,
  `material2_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Связанные материалы';

-- --------------------------------------------------------

--
-- Структура таблицы `sounds`
--

CREATE TABLE `sounds` (
  `material_id` int(11) NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит информацию о звуковых эффектах';

-- --------------------------------------------------------

--
-- Структура таблицы `sources`
--

CREATE TABLE `sources` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `url` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time` int(10) UNSIGNED DEFAULT NULL COMMENT 'в секундах'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Дополнительные источники материалов';

--
-- Дамп данных таблицы `sources`
--

INSERT INTO `sources` (`id`, `material_id`, `url`, `title`, `time`) VALUES
(1, 1, 'https://www.youtube.com/watch?v=[скрыто]', '[скрыто] - ВЛОГ', 304),
(2, 2, 'https://www.youtube.com/watch?v=[скрыто]', 'Влог, который [скрыто]', 963),
(3, 3, 'https://www.youtube.com/watch?v=[скрыто]', 'ВРЕМЯ ИСПРАВЛЯТЬ ОШИБКИ! [скрыто]', 1256),
(4, 4, 'https://www.youtube.com/watch?v=[скрыто]', 'ВСТРЕТИЛ [скрыто]', 1),
(5, 5, 'https://www.youtube.com/watch?v=[скрыто]', 'ГОТОВИМСЯ К КРУПНОМУ [скрыто]', 1058),
(6, 6, 'https://www.youtube.com/watch?v=[скрыто]', 'ГОТОВИМСЯ К КРУПНОМУ [скрыто]', 1631),
(7, 7, 'https://www.youtube.com/watch?v=[скрыто]', 'ГОТОВИМСЯ К КРУПНОМУ [скрыто]', 1718),
(8, 8, 'https://www.youtube.com/watch?v=[скрыто]', '[скрыто] НАЧАЛО ЭПИЧНОГО ПРОХОЖДЕНИЯ!', 720),
(9, 9, 'https://www.youtube.com/watch?v=[скрыто]', 'А ЧЁ ТАК МОЖНО БЫЛО?! [скрыто]', 237),
(10, 10, 'https://www.youtube.com/watch?v=[скрыто]', 'ГОТОВИМСЯ К КРУПНОМУ [скрыто]', 3993),
(11, 11, 'https://www.youtube.com/watch?v=[скрыто]', '[скрыто] ЗА КЛЭР', 1745),
(12, 12, 'https://www.youtube.com/watch?v=[скрыто]', '[скрыто] Я В ШОКЕ ОТ ЭТОЙ ИГРЫ!', 121),
(13, 13, 'https://www.youtube.com/watch?v=[скрыто]', 'ДЕД СТАЛ ДОБРЫМ? [скрыто]', 1364),
(14, 14, 'https://www.youtube.com/watch?v=[скрыто]', 'ДЕСЕПТИКОНЫ АТАКУЮТ! [скрыто]', 1293),
(15, 15, 'https://www.youtube.com/watch?v=[скрыто]', 'ДО МУРАШЕК [скрыто]', NULL),
(16, 16, 'https://www.youtube.com/watch?v=[скрыто]', 'ДОБРО ПОЖАЛОВАТЬ В [скрыто]', 1242),
(17, 17, 'https://www.youtube.com/watch?v=[скрыто]', '[скрыто] ДО СВИДАНИЯ!', 1426),
(18, 18, 'https://www.youtube.com/watch?v=[скрыто]', '[скрыто] ДО СВИДАНИЯ!', 1175),
(19, 19, 'https://www.youtube.com/watch?v=[скрыто]', 'МЫ ТАК ЕЩЕ НИКОГДА НЕ КРИЧАЛИ! [скрыто]', 235),
(20, 20, 'https://www.youtube.com/watch?v=[скрыто]', 'НЕОЖИДАННАЯ ВСТРЕЧА! [скрыто]', 679),
(21, 21, 'https://www.youtube.com/watch?v=[скрыто]', 'НЕОЖИДАННАЯ ВСТРЕЧА! [скрыто]', 1036),
(22, 21, 'https://www.youtube.com/watch?v=[скрыто]', 'ВРАТА В АД! [скрыто]', 519),
(23, 23, 'https://www.youtube.com/watch?v=[скрыто]', 'НОВОЕ БЕЗУМИЕ! [скрыто]', 1379),
(24, 24, 'https://www.youtube.com/watch?v=[скрыто]', 'ДА НУ НАФИГ! [скрыто]', 546),
(25, 25, 'https://www.youtube.com/watch?v=[скрыто]', 'Новый режим [скрыто]', 423),
(26, 30, 'https://www.youtube.com/@[скрыто]', NULL, NULL),
(27, 33, 'https://www.youtube.com/watch?v=[скрыто]', 'Пример использования', NULL),
(28, 39, 'https://www.youtube.com/watch?v=[скрыто]', 'Пример использования', 352),
(100, 311, 'https://www.youtube.com/watch?v=[скрыто]', NULL, 751),
(101, 312, 'https://www.youtube.com/watch?v=[скрыто]', NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `s_categories`
--

CREATE TABLE `s_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `picture_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит категории звуков';

-- --------------------------------------------------------

--
-- Структура таблицы `s_categories_groups`
--

CREATE TABLE `s_categories_groups` (
  `category_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `tags_of_materials`
--

CREATE TABLE `tags_of_materials` (
  `material_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `tags_of_materials`
--

INSERT INTO `tags_of_materials` (`material_id`, `tag_id`) VALUES
(1, 1),
(1, 2),
(4, 3),
(7, 4),
(8, 5),
(9, 6),
(15, 5),
(16, 7),
(19, 7),
(20, 6),
(21, 8),
(22, 9),
(22, 10),
(23, 9),
(24, 6),
(25, 2),
(26, 11),
(26, 12),
(26, 13),
(26, 14),
(27, 15),
(27, 16),
(27, 17),
(27, 18),
(28, 19),
(28, 20),
(28, 21),
(28, 22),
(30, 23),
(30, 24),
(30, 25),
(30, 26),
(32, 27),
(32, 28),
(32, 29),
(33, 30),
(33, 31),
(34, 32),
(34, 33),
(34, 34),
(35, 35),
(36, 36),
(36, 37),
(37, 38),
(37, 39),
(37, 40),
(37, 41),
(38, 42),
(39, 43),
(40, 44),
(40, 45),
(40, 46),
(41, 47),
(41, 48),
(41, 49),
(41, 50),
(41, 51),
(42, 52),
(42, 53),
(42, 54),
(42, 55),
(43, 56),
(43, 57),
(44, 58),
(44, 59),
(45, 60),
(45, 61),
(45, 62),
(45, 63),
(45, 64);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `points` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `del_date` timestamp NULL DEFAULT NULL,
  `del_cause` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит архивные данные о пользователях системы';

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `reg_date`, `points`, `del_date`, `del_cause`) VALUES
(1, '2023-04-21 21:30:12', 0, NULL, NULL),
(2, '2023-04-21 21:57:41', 0, NULL, NULL),
(39, '2023-06-05 00:40:40', 0, NULL, NULL),
(40, '2023-06-22 18:35:24', 0, NULL, NULL),
(41, '2023-06-22 21:42:06', 5, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `users_data`
--

CREATE TABLE `users_data` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `verification` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `about` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `picture` blob DEFAULT NULL,
  `life_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `pw_change_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит основные данные о пользователях';

--
-- Дамп данных таблицы `users_data`
--

INSERT INTO `users_data` (`user_id`, `email`, `password`, `google_id`, `role`, `verification`, `name`, `about`, `picture`, `life_time`, `last_date`, `pw_change_date`) VALUES
(1, 'bulatko557@mail.ru', '$2y$12$YPChansMhDPVsITbaSyeQukhn/8CvrOy5KAFGokSxyLl0fwJ7GaIu', NULL, 19, 0, NULL, NULL, NULL, '2023-05-21 21:30:12', '2023-04-21 21:30:12', '2023-04-21 21:30:12'),
(2, 'bulathaev@yandex.ru', '$2y$12$pjqGltpS.KfhQI5uDebzGeCr3rYytx2bHbaTK3cdc7XigaMxNK8rq', NULL, 0, 0, NULL, NULL, NULL, '2023-05-21 21:57:41', '2023-04-21 21:57:41', '2023-04-21 21:57:41'),
(39, 'bulat@mail.ru', '$2y$12$pjqGltpS.KfhQI5uDebzGeCr3rYytx2bHbaTK3cdc7XigaMxNK8rq', NULL, 0, 0, 'Булат', NULL, NULL, '2023-07-05 00:40:40', '2023-06-05 00:40:40', '2023-06-05 00:40:40'),
(40, 'nik@mail.ru', '$2y$12$UYi6A9JI3NekZZgGoUsXH.OY8ig9rN86uaVwDVmbKF3AOrvxLL5tO', NULL, 0, 0, NULL, NULL, NULL, '2023-07-22 18:35:25', '2023-06-22 18:35:25', '2023-06-22 18:35:25'),
(41, 'primer1@mail.ru', '$2y$12$qcIKXDr/TzvK0n.drOq44e9lAv2JThm23u505fcfnEnv/C5u6HDZi', NULL, 0, 0, 'Булат Хаев', NULL, NULL, '2023-07-22 21:42:06', '2023-06-22 21:42:06', '2023-06-22 21:42:06');

--
-- Триггеры `users_data`
--
DELIMITER $$
CREATE TRIGGER `user-set-lifetime` BEFORE INSERT ON `users_data` FOR EACH ROW BEGIN
IF new.life_time = CURRENT_TIMESTAMP() THEN
	SET new.life_time = DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL 30 DAY);
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `users_links`
--

CREATE TABLE `users_links` (
  `user_id` int(11) NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `user_permissions`
--

CREATE TABLE `user_permissions` (
  `bit` bigint(20) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=Aria DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `user_permissions`
--

INSERT INTO `user_permissions` (`bit`, `name`, `description`) VALUES
(1, 'Users-Add', 'Добавить нового пользователя'),
(2, 'Users-Data-Edit', 'Изменить данные аккаунта пользователя'),
(4, 'Users-Edit-Delete', 'Изменить архивные данные пользователя или удалить пользователя'),
(8, 'Phrases-Files', 'Добавить, изменить или удалить файлы фразы'),
(16, 'Phrases-Edit-Delete', 'Изменить или удалить фразу; добавить, изменить или удалить файлы фраз'),
(32, 'Music-Add', 'Добавить музыкальную композицию'),
(64, 'Music-Edit-Delete', 'Изменить или удалить музыкальную композицию'),
(128, 'Sounds-Add', 'Добавить звуковой эффект'),
(256, 'Sounds-Edit-Delete', 'Изменить или удалить звуковой эффект'),
(512, 'Images-Add', 'Добавить изображение'),
(1024, 'Videos-Add', 'Добавить видео'),
(2048, 'ImagesVideos-Edit-Delete', 'Изменить или удалить видео или изображение'),
(4096, 'Categories', 'Добавить, изменить или удалить категории / жанры / настроения / эмоции;  удалить или изменить данные авторов фраз / исполнителей музыки / поставщиков звуковых эффектов'),
(8192, 'Tags-Delete', 'Управление тегами материалов');

-- --------------------------------------------------------

--
-- Структура таблицы `user_playlists`
--

CREATE TABLE `user_playlists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `access` tinyint(1) NOT NULL DEFAULT 0,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `views` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `resource_type` enum('image','video','sound','music','phrase') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит пользовательские плейлисты';

--
-- Дамп данных таблицы `user_playlists`
--

INSERT INTO `user_playlists` (`id`, `user_id`, `access`, `date`, `updated`, `views`, `resource_type`, `title`, `description`) VALUES
(1, 2, 0, '2023-06-01 17:49:44', '2023-06-16 18:21:10', 0, 'phrase', 'Понравившиеся фразы', 'В этом плейлисте собраны все ваши понравившиеся фразы'),
(2, 2, 0, '2023-06-01 17:59:21', '2023-06-16 18:21:10', 0, 'phrase', 'Понравившиеся фразы (1)', 'В этом плейлисте собраны все ваши понравившиеся фразы'),
(3, 2, 0, '2023-06-01 18:04:43', '2023-06-16 18:21:10', 0, 'phrase', 'топ фразы эндифая', 'для будущих проектов'),
(4, 2, 0, '2023-06-01 18:10:24', '2023-06-16 18:21:10', 0, 'phrase', 'Понравившиеся фразы (3)', 'для меня'),
(5, 1, 0, '2023-06-01 18:19:21', '2023-06-16 18:21:10', 0, 'phrase', 'Понравившиеся фразы', 'В том плейлисте.'),
(6, 1, 0, '2023-06-01 18:19:43', '2023-06-16 18:21:10', 0, 'phrase', 'топ фразы эндифая', 'В этом плейлисте собраны все ваши понравившиеся фразы'),
(7, 2, 0, '2023-06-01 18:24:02', '2023-06-16 18:21:10', 0, 'phrase', 'Понравившиеся фразы (2)', 'для меня и тебя (нет)'),
(8, 2, 0, '2023-06-01 18:24:46', '2023-06-16 18:21:10', 0, 'phrase', 'Понравившиеся фразы (1) (1)', 'для будущих проектов'),
(9, 2, 0, '2023-06-01 18:59:31', '2023-06-16 18:21:10', 0, 'phrase', 'Понравившиеся фразы (4)', 'В этом плейлисте собраны все ваши понравившиеся фразы'),
(10, 2, 0, '2023-06-03 13:51:47', '2023-06-16 18:21:10', 0, 'video', 'Понравившиеся видео', 'В этом плейлисте собраны все ваши понравившиеся видео'),
(11, 2, 0, '2023-06-03 13:52:14', '2023-06-16 18:21:10', 0, 'image', 'Понравившиеся изображения', 'В этом плейлисте собраны все ваши понравившиеся изображения'),
(12, 1, 0, '2023-06-04 20:18:10', '2023-06-16 18:21:10', 0, 'image', 'Понравившиеся изображения', 'В этом плейлисте собраны все ваши понравившиеся изображения'),
(13, 39, 0, '2023-06-20 21:51:10', '2023-06-20 21:51:10', 0, 'phrase', 'Понравившиеся фразы', 'В этом плейлисте собраны все ваши понравившиеся фразы'),
(14, 40, 0, '2023-06-22 18:36:04', '2023-06-22 18:36:04', 0, 'image', 'Понравившиеся изображения', 'В этом плейлисте собраны все ваши понравившиеся изображения'),
(15, 40, 0, '2023-06-22 18:39:34', '2023-06-22 18:39:34', 0, 'phrase', 'Понравившиеся фразы', 'В этом плейлисте собраны все ваши понравившиеся фразы'),
(16, 41, 0, '2023-06-22 21:42:13', '2023-06-22 21:42:13', 0, 'phrase', 'Понравившиеся фразы', 'В этом плейлисте собраны все ваши понравившиеся фразы'),
(17, 41, 0, '2023-06-22 21:43:29', '2023-06-22 21:43:29', 0, 'video', 'Понравившиеся видео', 'В этом плейлисте собраны все ваши понравившиеся видео'),
(18, 41, 0, '2023-06-22 21:43:36', '2023-06-22 21:43:36', 0, 'image', 'Понравившиеся изображения', 'В этом плейлисте собраны все ваши понравившиеся изображения');

-- --------------------------------------------------------

--
-- Структура таблицы `videos`
--

CREATE TABLE `videos` (
  `material_id` int(11) NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('репортаж','постановочная съёмка','аэросъёмка','интервью','блог') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `release_date` timestamp NULL DEFAULT NULL,
  `producer` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит информацию о видео роликах';

--
-- Дамп данных таблицы `videos`
--

INSERT INTO `videos` (`material_id`, `title`, `type`, `description`, `author`, `release_date`, `producer`) VALUES
(36, 'Сердитый кот на вечеринке', '', NULL, NULL, NULL, NULL),
(37, 'Аутро с канала [скрыто]', '', NULL, '[скрыто]', NULL, NULL),
(38, 'Сегодня умер Лёха', 'блог', NULL, NULL, NULL, NULL),
(39, 'Геймер потно играет и бомбит', 'блог', NULL, NULL, NULL, NULL),
(40, 'Осенний лес', 'постановочная съёмка', NULL, NULL, NULL, NULL),
(41, 'КС-ГО Геймплей (CS-GO)', '', NULL, NULL, NULL, NULL),
(42, 'Ослики просят еды, а оператор смеётся', 'репортаж', NULL, NULL, NULL, NULL);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `artists_of_music`
--
ALTER TABLE `artists_of_music`
  ADD PRIMARY KEY (`music_id`,`person_id`),
  ADD KEY `person_id` (`person_id`);

--
-- Индексы таблицы `authors_of_phrases`
--
ALTER TABLE `authors_of_phrases`
  ADD PRIMARY KEY (`phrase_id`,`person_id`),
  ADD KEY `person_id` (`person_id`);

--
-- Индексы таблицы `categories_of_images`
--
ALTER TABLE `categories_of_images`
  ADD PRIMARY KEY (`image_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Индексы таблицы `categories_of_sounds`
--
ALTER TABLE `categories_of_sounds`
  ADD PRIMARY KEY (`sound_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Индексы таблицы `categories_of_videos`
--
ALTER TABLE `categories_of_videos`
  ADD PRIMARY KEY (`video_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Индексы таблицы `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `file_name` (`file_name`);

--
-- Индексы таблицы `files_of_materials`
--
ALTER TABLE `files_of_materials`
  ADD PRIMARY KEY (`material_id`,`file_id`),
  ADD UNIQUE KEY `unique_queue` (`material_id`,`queue`) USING BTREE,
  ADD KEY `file_id` (`file_id`);

--
-- Индексы таблицы `genres_of_music`
--
ALTER TABLE `genres_of_music`
  ADD PRIMARY KEY (`music_id`,`genre_id`),
  ADD KEY `genre_id` (`genre_id`);

--
-- Индексы таблицы `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`material_id`);

--
-- Индексы таблицы `iv_categories`
--
ALTER TABLE `iv_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `picture_name` (`picture_name`);

--
-- Индексы таблицы `iv_categories_groups`
--
ALTER TABLE `iv_categories_groups`
  ADD PRIMARY KEY (`category_id`,`parent_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Индексы таблицы `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `materials_of_playlists`
--
ALTER TABLE `materials_of_playlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `playlist_id` (`playlist_id`,`material_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Индексы таблицы `materials_tags`
--
ALTER TABLE `materials_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tag` (`tag`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `moods_of_music`
--
ALTER TABLE `moods_of_music`
  ADD PRIMARY KEY (`music_id`,`mood_id`),
  ADD KEY `mood_id` (`mood_id`);

--
-- Индексы таблицы `music`
--
ALTER TABLE `music`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `release_id` (`release_id`);

--
-- Индексы таблицы `m_genres`
--
ALTER TABLE `m_genres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `picture_name` (`picture_name`);

--
-- Индексы таблицы `m_genres_groups`
--
ALTER TABLE `m_genres_groups`
  ADD PRIMARY KEY (`genre_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Индексы таблицы `m_moods`
--
ALTER TABLE `m_moods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `picture_name` (`picture_name`);

--
-- Индексы таблицы `m_pirates`
--
ALTER TABLE `m_pirates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `music_id` (`music_id`);

--
-- Индексы таблицы `m_releases`
--
ALTER TABLE `m_releases`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `persons`
--
ALTER TABLE `persons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `picture_name` (`picture_name`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `persons_links`
--
ALTER TABLE `persons_links`
  ADD PRIMARY KEY (`person_id`) USING BTREE;

--
-- Индексы таблицы `phrases`
--
ALTER TABLE `phrases`
  ADD PRIMARY KEY (`material_id`);

--
-- Индексы таблицы `phrases_synonyms`
--
ALTER TABLE `phrases_synonyms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phrase_id_2` (`phrase_id`,`synonym`) USING BTREE COMMENT 'индекс для уникальности',
  ADD KEY `phrase_id` (`phrase_id`) USING BTREE COMMENT 'индекс для связи';

--
-- Индексы таблицы `related_materials`
--
ALTER TABLE `related_materials`
  ADD PRIMARY KEY (`material1_id`,`material2_id`),
  ADD KEY `material2_id` (`material2_id`);

--
-- Индексы таблицы `sounds`
--
ALTER TABLE `sounds`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Индексы таблицы `sources`
--
ALTER TABLE `sources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `material_id` (`material_id`,`url`,`time`) USING HASH COMMENT 'индекс для уникальности',
  ADD KEY `material_id_2` (`material_id`) COMMENT 'индекс для связи';

--
-- Индексы таблицы `s_categories`
--
ALTER TABLE `s_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `picture_name` (`picture_name`);

--
-- Индексы таблицы `s_categories_groups`
--
ALTER TABLE `s_categories_groups`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Индексы таблицы `tags_of_materials`
--
ALTER TABLE `tags_of_materials`
  ADD PRIMARY KEY (`material_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users_data`
--
ALTER TABLE `users_data`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_id`);

--
-- Индексы таблицы `users_links`
--
ALTER TABLE `users_links`
  ADD PRIMARY KEY (`user_id`) USING BTREE;

--
-- Индексы таблицы `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`bit`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `user_playlists`
--
ALTER TABLE `user_playlists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`) USING BTREE COMMENT 'индекс для связи';

--
-- Индексы таблицы `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`material_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT для таблицы `iv_categories`
--
ALTER TABLE `iv_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT для таблицы `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=313;

--
-- AUTO_INCREMENT для таблицы `materials_of_playlists`
--
ALTER TABLE `materials_of_playlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT для таблицы `materials_tags`
--
ALTER TABLE `materials_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT для таблицы `m_genres`
--
ALTER TABLE `m_genres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `m_moods`
--
ALTER TABLE `m_moods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `m_pirates`
--
ALTER TABLE `m_pirates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `m_releases`
--
ALTER TABLE `m_releases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `persons`
--
ALTER TABLE `persons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- AUTO_INCREMENT для таблицы `phrases_synonyms`
--
ALTER TABLE `phrases_synonyms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=210;

--
-- AUTO_INCREMENT для таблицы `sources`
--
ALTER TABLE `sources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT для таблицы `s_categories`
--
ALTER TABLE `s_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT для таблицы `user_playlists`
--
ALTER TABLE `user_playlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `artists_of_music`
--
ALTER TABLE `artists_of_music`
  ADD CONSTRAINT `artists_of_music_ibfk_1` FOREIGN KEY (`music_id`) REFERENCES `music` (`material_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `artists_of_music_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `authors_of_phrases`
--
ALTER TABLE `authors_of_phrases`
  ADD CONSTRAINT `authors_of_phrases_ibfk_1` FOREIGN KEY (`phrase_id`) REFERENCES `phrases` (`material_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `authors_of_phrases_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `categories_of_images`
--
ALTER TABLE `categories_of_images`
  ADD CONSTRAINT `categories_of_images_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `iv_categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `categories_of_images_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `images` (`material_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `categories_of_sounds`
--
ALTER TABLE `categories_of_sounds`
  ADD CONSTRAINT `categories_of_sounds_ibfk_1` FOREIGN KEY (`sound_id`) REFERENCES `sounds` (`material_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `categories_of_sounds_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `s_categories` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `categories_of_videos`
--
ALTER TABLE `categories_of_videos`
  ADD CONSTRAINT `categories_of_videos_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `iv_categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `categories_of_videos_ibfk_2` FOREIGN KEY (`video_id`) REFERENCES `videos` (`material_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `files_of_materials`
--
ALTER TABLE `files_of_materials`
  ADD CONSTRAINT `files_of_materials_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `files_of_materials_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `genres_of_music`
--
ALTER TABLE `genres_of_music`
  ADD CONSTRAINT `genres_of_music_ibfk_1` FOREIGN KEY (`genre_id`) REFERENCES `m_genres` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `genres_of_music_ibfk_2` FOREIGN KEY (`music_id`) REFERENCES `music` (`material_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `images_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `iv_categories_groups`
--
ALTER TABLE `iv_categories_groups`
  ADD CONSTRAINT `iv_categories_groups_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `iv_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `iv_categories_groups_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `iv_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `materials_of_playlists`
--
ALTER TABLE `materials_of_playlists`
  ADD CONSTRAINT `materials_of_playlists_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `materials_of_playlists_ibfk_2` FOREIGN KEY (`playlist_id`) REFERENCES `user_playlists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `materials_tags`
--
ALTER TABLE `materials_tags`
  ADD CONSTRAINT `materials_tags_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `moods_of_music`
--
ALTER TABLE `moods_of_music`
  ADD CONSTRAINT `moods_of_music_ibfk_1` FOREIGN KEY (`mood_id`) REFERENCES `m_moods` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `moods_of_music_ibfk_2` FOREIGN KEY (`music_id`) REFERENCES `music` (`material_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `music`
--
ALTER TABLE `music`
  ADD CONSTRAINT `music_ibfk_1` FOREIGN KEY (`release_id`) REFERENCES `m_releases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `music_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `m_genres_groups`
--
ALTER TABLE `m_genres_groups`
  ADD CONSTRAINT `m_genres_groups_ibfk_1` FOREIGN KEY (`genre_id`) REFERENCES `m_genres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `m_genres_groups_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `m_genres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `m_pirates`
--
ALTER TABLE `m_pirates`
  ADD CONSTRAINT `m_pirates_ibfk_1` FOREIGN KEY (`music_id`) REFERENCES `music` (`material_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `persons`
--
ALTER TABLE `persons`
  ADD CONSTRAINT `persons_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `persons_links`
--
ALTER TABLE `persons_links`
  ADD CONSTRAINT `persons_links_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `phrases`
--
ALTER TABLE `phrases`
  ADD CONSTRAINT `phrases_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `phrases_synonyms`
--
ALTER TABLE `phrases_synonyms`
  ADD CONSTRAINT `phrases_synonyms_ibfk_1` FOREIGN KEY (`phrase_id`) REFERENCES `phrases` (`material_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `related_materials`
--
ALTER TABLE `related_materials`
  ADD CONSTRAINT `related_materials_ibfk_1` FOREIGN KEY (`material1_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `related_materials_ibfk_2` FOREIGN KEY (`material2_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `sounds`
--
ALTER TABLE `sounds`
  ADD CONSTRAINT `sounds_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sounds_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `sources`
--
ALTER TABLE `sources`
  ADD CONSTRAINT `sources_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `s_categories_groups`
--
ALTER TABLE `s_categories_groups`
  ADD CONSTRAINT `s_categories_groups_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `s_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `s_categories_groups_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `s_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tags_of_materials`
--
ALTER TABLE `tags_of_materials`
  ADD CONSTRAINT `tags_of_materials_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tags_of_materials_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `materials_tags` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `users_data`
--
ALTER TABLE `users_data`
  ADD CONSTRAINT `users_data_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `users_links`
--
ALTER TABLE `users_links`
  ADD CONSTRAINT `users_links_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users_data` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_playlists`
--
ALTER TABLE `user_playlists`
  ADD CONSTRAINT `user_playlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_data` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `videos_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

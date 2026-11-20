-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Фев 03 2023 г., 07:10
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
-- База данных: `sgdb`
--

-- --------------------------------------------------------

--
-- Структура таблицы `albums`
--

CREATE TABLE `albums` (
  `Id` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Type` varchar(30) DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `PictureName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `artists`
--

CREATE TABLE `artists` (
  `Id` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `About` varchar(255) DEFAULT NULL,
  `BirthDate` date DEFAULT NULL,
  `Nationality` varchar(50) DEFAULT NULL,
  `PictureName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `compositions`
--

CREATE TABLE `compositions` (
  `Id` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Date` date DEFAULT NULL,
  `Text` varchar(535) DEFAULT NULL,
  `Auditions` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `PictureName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `compositionsofalbums`
--

CREATE TABLE `compositionsofalbums` (
  `Id` int(11) NOT NULL,
  `AlbumId` int(11) NOT NULL,
  `CompositionId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `compositionsofplaylists`
--

CREATE TABLE `compositionsofplaylists` (
  `Id` int(11) NOT NULL,
  `PlaylistId` int(11) NOT NULL,
  `CompositionId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `compsofartists`
--

CREATE TABLE `compsofartists` (
  `Id` int(11) NOT NULL,
  `ArtistId` int(11) NOT NULL,
  `CompositionId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `compsofeditorschoice`
--

CREATE TABLE `compsofeditorschoice` (
  `Id` int(11) NOT NULL,
  `SetId` int(11) NOT NULL,
  `CompositionId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `compsofgenres`
--

CREATE TABLE `compsofgenres` (
  `Id` int(11) NOT NULL,
  `GenreId` int(11) NOT NULL,
  `CompositionId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `editorschoice`
--

CREATE TABLE `editorschoice` (
  `Id` int(11) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `PictureName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `genres`
--

CREATE TABLE `genres` (
  `Id` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `playlists`
--

CREATE TABLE `playlists` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `PictureName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `Id` int(11) NOT NULL,
  `Login` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`Id`);

--
-- Индексы таблицы `artists`
--
ALTER TABLE `artists`
  ADD PRIMARY KEY (`Id`);

--
-- Индексы таблицы `compositions`
--
ALTER TABLE `compositions`
  ADD PRIMARY KEY (`Id`);

--
-- Индексы таблицы `compositionsofalbums`
--
ALTER TABLE `compositionsofalbums`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `AlbumId` (`AlbumId`),
  ADD KEY `CompositionId` (`CompositionId`);

--
-- Индексы таблицы `compositionsofplaylists`
--
ALTER TABLE `compositionsofplaylists`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `PlaylistId` (`PlaylistId`),
  ADD KEY `CompositionId` (`CompositionId`);

--
-- Индексы таблицы `compsofartists`
--
ALTER TABLE `compsofartists`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `ArtistId` (`ArtistId`),
  ADD KEY `CompositionId` (`CompositionId`);

--
-- Индексы таблицы `compsofeditorschoice`
--
ALTER TABLE `compsofeditorschoice`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `SetId` (`SetId`),
  ADD KEY `CompositionId` (`CompositionId`);

--
-- Индексы таблицы `compsofgenres`
--
ALTER TABLE `compsofgenres`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `GenreId` (`GenreId`),
  ADD KEY `CompositionId` (`CompositionId`);

--
-- Индексы таблицы `editorschoice`
--
ALTER TABLE `editorschoice`
  ADD PRIMARY KEY (`Id`);

--
-- Индексы таблицы `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`Id`);

--
-- Индексы таблицы `playlists`
--
ALTER TABLE `playlists`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `UserId` (`UserId`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `albums`
--
ALTER TABLE `albums`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `artists`
--
ALTER TABLE `artists`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `compositions`
--
ALTER TABLE `compositions`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `compositionsofalbums`
--
ALTER TABLE `compositionsofalbums`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `compositionsofplaylists`
--
ALTER TABLE `compositionsofplaylists`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `compsofartists`
--
ALTER TABLE `compsofartists`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `compsofeditorschoice`
--
ALTER TABLE `compsofeditorschoice`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `compsofgenres`
--
ALTER TABLE `compsofgenres`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `editorschoice`
--
ALTER TABLE `editorschoice`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `genres`
--
ALTER TABLE `genres`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `compositionsofalbums`
--
ALTER TABLE `compositionsofalbums`
  ADD CONSTRAINT `compositionsofalbums_ibfk_1` FOREIGN KEY (`AlbumId`) REFERENCES `albums` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `compositionsofalbums_ibfk_2` FOREIGN KEY (`CompositionId`) REFERENCES `compositions` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `compositionsofplaylists`
--
ALTER TABLE `compositionsofplaylists`
  ADD CONSTRAINT `compositionsofplaylists_ibfk_1` FOREIGN KEY (`PlaylistId`) REFERENCES `playlists` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `compositionsofplaylists_ibfk_2` FOREIGN KEY (`CompositionId`) REFERENCES `compositions` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `compsofartists`
--
ALTER TABLE `compsofartists`
  ADD CONSTRAINT `compsofartists_ibfk_1` FOREIGN KEY (`ArtistId`) REFERENCES `artists` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `compsofartists_ibfk_2` FOREIGN KEY (`CompositionId`) REFERENCES `compositions` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `compsofeditorschoice`
--
ALTER TABLE `compsofeditorschoice`
  ADD CONSTRAINT `compsofeditorschoice_ibfk_1` FOREIGN KEY (`SetId`) REFERENCES `editorschoice` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `compsofeditorschoice_ibfk_2` FOREIGN KEY (`CompositionId`) REFERENCES `compositions` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `compsofgenres`
--
ALTER TABLE `compsofgenres`
  ADD CONSTRAINT `compsofgenres_ibfk_1` FOREIGN KEY (`GenreId`) REFERENCES `genres` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `compsofgenres_ibfk_2` FOREIGN KEY (`CompositionId`) REFERENCES `compositions` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `playlists`
--
ALTER TABLE `playlists`
  ADD CONSTRAINT `playlists_ibfk_1` FOREIGN KEY (`UserId`) REFERENCES `users` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

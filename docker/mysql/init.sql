CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `year_of_birth` datetime DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
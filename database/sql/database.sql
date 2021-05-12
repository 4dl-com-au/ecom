-- convert Laravel migrations to raw SQL scripts --

-- migration:2014_10_12_000000_create_users_table --
create table `users` (
  `id` bigint unsigned not null auto_increment primary key, 
  `email` varchar(191) not null, 
  `username` varchar(191) not null, 
  `role` int not null default '0', 
  `email_token` varchar(191) null, 
  `email_verified_at` timestamp null, 
  `password` varchar(191) not null, 
  `remember_token` varchar(100) null, 
  `name` longtext null, 
  `verified` varchar(191) not null default '0', 
  `media` longtext null, 
  `package` varchar(191) not null default 'free', 
  `package_due` datetime null, 
  `package_trial_done` int not null default '0', 
  `facebook_id` varchar(191) null, 
  `google_id` varchar(191) null, 
  `domain` varchar(191) not null default 'main', 
  `address` varchar(191) null, 
  `gateway` longtext null, 
  `socials` longtext null, 
  `extra` longtext null, 
  `active` int not null default '1', 
  `last_activity` varchar(191) null, 
  `last_agent` varchar(191) null, 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table 
  `users` 
add 
  unique `users_email_unique`(`email`);
alter table 
  `users` 
add 
  unique `users_username_unique`(`username`);

-- migration:2014_10_12_100000_create_password_resets_table --
create table `password_resets` (
  `email` varchar(191) not null, 
  `token` varchar(191) not null, 
  `created_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table 
  `password_resets` 
add 
  index `password_resets_email_index`(`email`);

-- migration:2019_08_19_000000_create_failed_jobs_table --
create table `failed_jobs` (
  `id` bigint unsigned not null auto_increment primary key, 
  `connection` text not null, `queue` text not null, 
  `payload` longtext not null, `exception` longtext not null, 
  `failed_at` timestamp default CURRENT_TIMESTAMP not null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

-- migration:2020_07_12_182924_tables --
create table `settings` (
  `id` bigint unsigned not null auto_increment primary key, 
  `key` varchar(191) not null, 
  `value` longtext not null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `packages` (
  `id` bigint unsigned not null auto_increment primary key, 
  `name` varchar(191) not null, 
  `slug` varchar(191) null, 
  `status` varchar(191) not null default '1', 
  `price` varchar(191) null, 
  `settings` longtext null, 
  `domains` longtext null, 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `domains` (
  `id` bigint unsigned not null auto_increment primary key, 
  `status` int not null default '0', 
  `scheme` varchar(191) null, 
  `host` varchar(191) null, 
  `index_url` varchar(191) null, 
  `settings` longtext null, 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `pages` (
  `id` bigint unsigned not null auto_increment primary key, 
  `category` varchar(191) null, 
  `url` varchar(191) not null, 
  `title` varchar(191) null, 
  `status` varchar(191) not null default '0', 
  `type` varchar(191) not null default 'internal', 
  `image` varchar(191) null, 
  `settings` longtext null, 
  `order` int not null default '0', 
  `total_views` int not null default '0', 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `pages_categories` (
  `id` bigint unsigned not null auto_increment primary key, 
  `url` varchar(191) not null, 
  `status` int not null default '1', 
  `title` varchar(191) null, 
  `description` longtext null, 
  `icon` varchar(191) null, 
  `order` int not null default '0', 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `track` (
  `id` bigint unsigned not null auto_increment primary key, 
  `user` int not null, 
  `dyid` int null, 
  `visitor_id` varchar(191) null, 
  `type` varchar(191) not null, 
  `country` varchar(191) null, 
  `ip` varchar(191) not null, 
  `os` varchar(191) not null, 
  `browser` varchar(191) not null, 
  `referer` varchar(191) not null, 
  `count` varchar(191) not null, 
  `date` datetime not null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `users_logs` (
  `id` bigint unsigned not null auto_increment primary key, 
  `user` int not null, 
  `what` varchar(191) not null, 
  `ip` varchar(191) not null, 
  `os` varchar(191) not null, 
  `browser` varchar(191) not null, 
  `date` datetime not null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `pending_payments` (
  `id` bigint unsigned not null auto_increment primary key, 
  `user` int not null, 
  `status` int not null default '0', 
  `email` varchar(191) null, 
  `name` varchar(191) null, 
  `bankName` varchar(191) null, 
  `proof` varchar(191) null, 
  `ref` varchar(191) null, 
  `package` int null, 
  `duration` varchar(191) null, 
  `type` varchar(191) not null default 'bank', 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `track_links` (
  `id` bigint unsigned not null auto_increment primary key, 
  `user` int null, 
  `type` varchar(191) not null default 'social', 
  `slug` varchar(191) null, 
  `visitor_id` varchar(191) null, 
  `country` varchar(191) null, 
  `ip` varchar(191) not null, 
  `os` varchar(191) not null, 
  `browser` varchar(191) not null, 
  `views` varchar(191) not null, 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `linker` (
  `id` bigint unsigned not null auto_increment primary key, 
  `url` varchar(191) null, 
  `slug` varchar(191) null, 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `payments` (
  `id` bigint unsigned not null auto_increment primary key, 
  `user` int not null, 
  `name` varchar(191) null, 
  `package_name` varchar(191) null, 
  `email` varchar(191) null, 
  `duration` varchar(191) null, 
  `ref` varchar(191) null, 
  `currency` varchar(191) null, 
  `price` double(8, 2) not null default '0', 
  `package` varchar(191) null, 
  `gateway` varchar(191) not null default '0', 
  `date` datetime null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `product_orders` (
  `id` bigint unsigned not null auto_increment primary key, 
  `storeuser` int not null, 
  `products` longtext null, 
  `details` longtext null, 
  `currency` varchar(191) null, 
  `ref` varchar(191) null, 
  `price` double(8, 2) not null default '0', 
  `extra` longtext null, 
  `delivered` int not null default '0', 
  `status` enum('0', '1', '2') not null default '0', 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `products` (
  `id` bigint unsigned not null auto_increment primary key, 
  `user` int not null, 
  `title` varchar(191) null, 
  `slug` varchar(191) null, 
  `price` varchar(191) null, 
  `status` int not null default '1', 
  `salePrice` varchar(191) null, 
  `stock` int null, 
  `product_type` int not null default '0', 
  `tags` varchar(191) null, 
  `featured` tinyint(1) not null default '0', 
  `product_condition` varchar(191) not null default 'new', 
  `media` longtext null, 
  `categories` longtext null, 
  `description` longtext null, 
  `variation` longtext null, 
  `extra` longtext null, 
  `position` int not null default '0', 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `product_categories` (
  `id` bigint unsigned not null auto_increment primary key, 
  `user` int not null, 
  `title` varchar(191) null, 
  `slug` varchar(191) null, 
  `status` int not null default '1', 
  `description` longtext null, 
  `media` longtext null, 
  `extra` longtext null, 
  `position` int not null default '0', 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `product_reviews` (
  `id` bigint unsigned not null auto_increment primary key, 
  `storeuser` int not null, 
  `product_id` int null, 
  `rating` varchar(191) not null default '0', 
  `review` longtext null, 
  `extra` longtext null, 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `product_wishlists` (
  `id` bigint unsigned not null auto_increment primary key, 
  `storeuser` int not null, `product` int not null, 
  `user` int not null, `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
create table `faq` (
  `id` bigint unsigned not null auto_increment primary key, 
  `status` int not null default '1', `name` longtext null, 
  `note` longtext null, `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

-- migration:2020_09_18_113034_store_orders --
create table `store_orders` (
  `id` bigint unsigned not null auto_increment primary key, 
  `slug` varchar(191) null, 
  `order_id` int null, 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

-- migration:2020_11_08_173354_updates --

create table `blog` (
  `id` bigint unsigned not null auto_increment primary key, 
  `user` int not null, 
  `name` varchar(191) null, 
  `note` longtext null, 
  `slug` varchar(191) null, 
  `image` varchar(191) null, 
  `start_date` datetime null, 
  `end_date` datetime null, 
  `extra` longtext null, 
  `order` int not null default '0', 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

create table `user_pages` (
  `id` bigint unsigned not null auto_increment primary key, 
  `user` int not null, 
  `name` varchar(191) null, 
  `show_menu` int not null default '0', 
  `is_home` int not null default '0', 
  `total_views` int not null default '0', 
  `slug` varchar(191) null, 
  `image` varchar(191) null, 
  `theme` varchar(191) null, 
  `start_date` datetime null, 
  `end_date` datetime null, 
  `extra` longtext null, 
  `order` int not null default '0', 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
 
create table `conversations` (
  `id` bigint unsigned not null auto_increment primary key, 
  `user` int not null, `customer` int not null, 
  `extra` longtext null, `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
 
create table `messages` (
  `id` bigint unsigned not null auto_increment primary key, 
  `conversation_id` int not null, 
  `from` varchar(191) not null default 'store', 
  `user_id` int not null, 
  `type` varchar(191) not null default 'text', 
  `data` longtext null, 
  `extra` longtext null, 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
 
create table `pages_sections` (
  `id` bigint unsigned not null auto_increment primary key, 
  `user` int not null, 
  `page_id` int not null, 
  `name` varchar(191) null, 
  `theme` varchar(191) null, 
  `block_slug` varchar(191) null, 
  `data` longtext null, 
  `extra` longtext null, 
  `order` int not null default '0', 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

create table `option_values` (
  `id` bigint unsigned not null auto_increment primary key, 
  `user` int not null, 
  `label` varchar(191) null, 
  `option_id` int null, 
  `price` double(8, 2) null, 
  `price_type` varchar(191) not null default 'fixed', 
  `order` int not null default '0', 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

create table `options` (
  `id` bigint unsigned not null auto_increment primary key, 
  `user` int not null, 
  `name` varchar(191) null, 
  `product` int null, 
  `type` varchar(191) null, 
  `is_required` int not null default '0', 
  `is_global` int not null default '0', 
  `order` int not null default '0', 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

create table `customers` (
  `id` bigint unsigned not null auto_increment primary key, 
  `storeuser` int not null, 
  `email` varchar(191) not null, 
  `name` varchar(191) null, 
  `status` int not null default '1', 
  `password` varchar(191) not null, 
  `details` longtext null, 
  `created_at` timestamp null, 
  `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

create table `customers_logs` (
  `id` bigint unsigned not null auto_increment primary key, 
  `user` int not null, 
  `what` varchar(191) not null, 
  `ip` varchar(191) not null, 
  `os` varchar(191) not null, 
  `browser` varchar(191) not null, 
  `date` datetime not null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

create table `product_refunds` (
  `id` bigint unsigned not null auto_increment primary key, 
  `user` int not null, `customer` int not null, 
  `order_id` int not null, `status` int not null default '0', 
  `created_at` timestamp null, `updated_at` timestamp null
) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table 
  `option_values` 
add 
  `stock` int null 
after 
  `price`;

alter table 
  `customers` 
add 
  `avatar` varchar(191) null 
after 
  `email`, 
add 
  `resetPassword` varchar(191) null 
after 
  `avatar`, 
add 
  `activateCode` varchar(191) null 
after 
  `resetPassword`, 
add 
  `active` int not null default '1' 
after 
  `activateCode`;

alter table 
  `pages_sections` 
add 
  `status` int not null default '1' 
after 
  `theme`;

alter table 
  `user_pages` 
add 
  `parent` int null 
after 
  `slug`;

alter table 
  `domains` 
add 
  `user` int null 
after 
  `id`, 
add 
  `wildcard` int not null default '0' 
after 
  `user`;


alter table 
  `products` 
add 
  `otheruser` int null 
after 
  `user`;

alter table 
  `product_orders` 
add 
  `gateway` varchar(191) null 
after 
  `currency`, 
add 
  `customer` int null 
after 
  `storeuser`, 
add 
  `send_email` int not null default '0' 
after 
  `customer`, 
add 
  `order_status` int not null default '0' 
after 
  `send_email`;


alter table 
  `users` 
add 
  `shipping` longtext null 
after 
  `media`, 
add 
  `first_welcome_screen` int not null default '0' 
after 
  `shipping`, 
add 
  `enable_welcome_screen` int not null default '0' 
after 
  `first_welcome_screen`;

alter table 
  `packages` 
add 
  `gateways` longtext null 
after 
  `domains`;

alter table 
  `products` 
add 
  `external_url_name` longtext null 
after 
  `media`, 
add 
  `external_url` longtext null 
after 
  `external_url_name`, 
add 
  `stock_management` int null 
after 
  `external_url_name`, 
add 
  `stock_status` int null 
after 
  `stock_management`, 
add 
  `sku` varchar(191) null 
after 
  `stock_status`, 
add 
  `files` longtext null 
after 
  `sku`;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`) VALUES
(1, 'ads', '{\"enabled\":\"0\",\"store_header\":null,\"store_footer\":null,\"site_header\":null,\"site_footer\":null}'),
(2, 'email', 'admin@ecom.me'),
(3, 'email_activation', '1'),
(4, 'logo', 'Fyapw0zDBAshFejiej7f0jvxFRi8WO7RPI0QxKoQ.png'),
(5, 'favicon', 'wUXDuXt9hxAtNjp3FmUPjsBHC8f6NQ4FAxDtCKOJ.png'),
(6, 'timezone', 'Pacific/Midway'),
(7, 'registration', '1'),
(8, 'custom_home', ''),
(9, 'privacy', ''),
(10, 'terms', ''),
(11, 'package_free', '{\"id\":\"free\",\"name\":\"Free\",\"slug\":\"free\",\"status\":\"1\",\"price\":{\"month\":\"FREE\",\"quarter\":\"FREE\",\"annual\":\"FREE\"},\"settings\":{\"ads\":true,\"custom_branding\":true,\"statistics\":true,\"verified\":true,\"social\":true,\"custom_background\":true,\"domains\":true,\"add_to_head\":false,\"google_analytics\":true,\"facebook_pixel\":true,\"blogs\":true,\"products_limit\":\"-1\",\"blogs_limits\":\"-1\",\"custom_domain_limit\":\"-1\"},\"domains\":\"\\\"1\\\"\",\"gateways\":\"null\"}'),
(12, 'package_trial', '{\"id\":\"trial\",\"name\":\"Trial\",\"slug\":\"trial\",\"status\":\"1\",\"price\":{\"month\":\"FREE\",\"quarter\":\"FREE\",\"annual\":\"FREE\",\"expiry\":\"7\"},\"settings\":{\"expiry\":true,\"ads\":true,\"custom_branding\":true,\"statistics\":true,\"verified\":true,\"social\":true,\"custom_background\":true,\"domains\":true,\"google_analytics\":true,\"facebook_pixel\":true,\"blogs\":true,\"blogs_limits\":true,\"products_limit\":\"-1\",\"trial\":true},\"domains\":\"\\\"1\\\"\"}'),
(13, 'business', '{\"enabled\":\"1\",\"name\":\"Jeff Jola\",\"address\":\"45 fieldmark Saraha\",\"city\":\"Lagos\",\"county\":\"Nothing\",\"zip\":\"100001\",\"country\":\"Nigeriaa\",\"email\":\"jeffjola@gmail.com\",\"phone\":\"8104199676\",\"tax_type\":null,\"tax_id\":null,\"custom_key_one\":null,\"custom_value_one\":null,\"custom_key_two\":null,\"custom_value_two\":null}'),
(14, 'captcha', ''),
(15, 'social', '{\"facebook\":\"#\",\"instagram\":null,\"youtube\":null,\"whatsapp\":null,\"twitter\":null}'),
(16, 'custom_code', '{\"enabled\":true,\"head\":\"\"}'),
(17, 'currency', 'INR'),
(18, 'email_notify', '{\"emails\":null,\"user\":false,\"payment\":false,\"bank_transfer\":false}'),
(19, 'location', '8560 Magnolia Street Laredo, TX 78043'),
(20, 'contact', '1'),
(21, 'payment_system', '1'),
(22, 'user', '{\"domains_restrict\":\"0\",\"demo_user\":\"2\",\"products_image_size\":\"1\",\"products_image_limit\":\"3\"}'),
(23, 'site', '{\"store_count\":\"1\",\"show_pages\":\"1\"}');

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `role`, `email_token`, `email_verified_at`, `password`, `remember_token`, `name`, `verified`, `media`, `shipping`, `first_welcome_screen`, `enable_welcome_screen`, `package`, `package_due`, `package_trial_done`, `facebook_id`, `google_id`, `domain`, `address`, `gateway`, `socials`, `extra`, `active`, `last_activity`, `last_agent`, `created_at`, `updated_at`) VALUES
(1, 'admin@gmail.com', 'admin', 1, '', NULL, '$2y$10$ONDIhjV4jm2MU5NOwCQPHue2tK5V25ptMfUgcXS/.QynN7vrzzHSe', '2WuHk9tXHBFxEwTqmkWLAPtX4gkbdznLKNksdsi6JsBjHNQ3GHBjuxUau48f', '{\"first_name\":\"Admin\",\"last_name\":null}', '0', '{\"avatar\":\"GfcEHr9hGmmFid8rg277uUtIRGE4qAcvabdrKwP8.png\",\"favicon\":\"b8G6FSL5ssIluQtIMxxnxJVhiDQ0qgr9LmxuLv9t.jpg\"}', '{\"Nigeria\":{\"Abuja\":{\"type\":\"free\",\"cost\":\"2000\"}}}', 1, 0, 'free', '2021-09-01 07:16:29', 0, NULL, NULL, '1', 'Alabama, USA', '{\"currency\":\"INR\",\"paypal_status\":false,\"paypal_mode\":\"sandbox\",\"paypal_client_id\":null,\"paypal_secret_key\":null,\"paystack_status\":false,\"paystack_secret_key\":null,\"bank_status\":true,\"bank_details\":\"hggggggggggg\",\"stripe_status\":false,\"stripe_client\":null,\"stripe_secret\":null,\"razor_status\":false,\"razor_key_id\":null,\"razor_secret_key\":null,\"midtrans_mode\":\"sandbox\",\"midtrans_status\":false,\"cash_status\":true,\"midtrans_client_key\":null,\"midtrans_server_key\":null,\"mercadopago_status\":false,\"mercadopago_access_token\":null,\"paytm_status\":\"1\",\"paytm_environment\":\"local\",\"paytm_merchant_id\":\"KzgLRy91373631135551\",\"paytm_merchant_key\":\"z@&6Y5tmi6HwRDoL\",\"paytm_merchant_website\":\"WEBSTAGING\",\"paytm_channel\":\"WEB\",\"paytm_industrytype\":\"Retail\"}', '{\"email\":\"#\",\"whatsapp\":\"##\",\"facebook\":\"#\",\"instagram\":\"#\",\"twitter\":\"#\",\"youtube\":\"#\"}', '{\"banner_url\":null,\"shipping_types\":\"enable\",\"invoicing\":\"1\",\"refund_request\":\"1\",\"custom_branding\":\"Custom footer branding\",\"guest_checkout\":\"1\",\"google_analytics\":\"kkl\",\"facebook_pixel\":null,\"template\":\"zoa\",\"about\":null,\"background_text_color\":\"#F1F1F1\",\"background_color\":\"#00A3FF\",\"headScript\":null}', 1, '2021-03-17 23:43:15', 'Windows', '2021-02-01 02:01:24', '2021-03-18 08:43:55');
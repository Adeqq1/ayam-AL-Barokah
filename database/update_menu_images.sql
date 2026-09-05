-- =========================================================
-- Script Update Foto Menu Terbaru
-- Ayam Penyet Al-Barokah
-- Digunakan untuk sinkronisasi database VPS & Local Development
-- =========================================================

USE `db_pemesanan`;

-- 1. Hapus data menu duplikat jika ada (id > 20)
DELETE FROM `menu` WHERE `id` > 20;
ALTER TABLE `menu` AUTO_INCREMENT = 21;

-- 2. Update nama file foto menu sesuai file fisik yang ada di assets/images/
UPDATE `menu` SET `foto` = 'menu_1788590605.jpg' WHERE `id` = 1;
UPDATE `menu` SET `foto` = 'menu_1788590594.jpg' WHERE `id` = 2;
UPDATE `menu` SET `foto` = 'default-menu.jpg'     WHERE `id` = 3;
UPDATE `menu` SET `foto` = 'menu_1788590520.jpg' WHERE `id` = 4;
UPDATE `menu` SET `foto` = 'menu_1788590474.jpg' WHERE `id` = 5;
UPDATE `menu` SET `foto` = 'menu_1788590458.jpg' WHERE `id` = 6;
UPDATE `menu` SET `foto` = 'default-menu.jpg'     WHERE `id` = 7;
UPDATE `menu` SET `foto` = 'menu_1788590363.jpg' WHERE `id` = 8;
UPDATE `menu` SET `foto` = 'default-menu.jpg'     WHERE `id` = 9;
UPDATE `menu` SET `foto` = 'default-menu.jpg'     WHERE `id` = 10;
UPDATE `menu` SET `foto` = 'default-menu.jpg'     WHERE `id` = 11;
UPDATE `menu` SET `foto` = 'menu_1788590707.jpg' WHERE `id` = 12;
UPDATE `menu` SET `foto` = 'menu_1788590694.jpg' WHERE `id` = 13;
UPDATE `menu` SET `foto` = 'menu_1788590676.jpg' WHERE `id` = 14;
UPDATE `menu` SET `foto` = 'menu_1788590652.jpg' WHERE `id` = 15;
UPDATE `menu` SET `foto` = 'menu_1788590632.jpg' WHERE `id` = 16;
UPDATE `menu` SET `foto` = 'menu_1788590618.jpg' WHERE `id` = 17;
UPDATE `menu` SET `foto` = 'menu_1788590940.jpg' WHERE `id` = 18;
UPDATE `menu` SET `foto` = 'menu_1788590844.jpg' WHERE `id` = 19;
UPDATE `menu` SET `foto` = 'menu_1788590813.jpg' WHERE `id` = 20;

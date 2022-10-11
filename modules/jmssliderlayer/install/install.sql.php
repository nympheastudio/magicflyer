<?php
/**
* 2007-2015 PrestaShop
*
* Slider Layer module for prestashop
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2015 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

$query = "CREATE TABLE IF NOT EXISTS `_DB_PREFIX_jms_sliderlayer` (
  `slide_id` int(11) NOT NULL AUTO_INCREMENT,
  `id_shop` int(11) NOT NULL,
  PRIMARY KEY (`slide_id`)
) ENGINE=_MYSQL_ENGINE_  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

INSERT INTO `_DB_PREFIX_jms_sliderlayer` (`slide_id`, `id_shop`) VALUES
(20, 1),
(23, 1),
(25, 1);

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_jms_sliderlayer_layers` (
  `layer_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slide_id` int(11) NOT NULL,
  `layer_class` varchar(100) NOT NULL,
  `parallax_class` varchar(30) NOT NULL,
  `data_x` varchar(20) NOT NULL,
  `data_y` varchar(20) NOT NULL,
  `speed` int(11) NOT NULL,
  `start` int(11) NOT NULL,
  `easing` varchar(100) NOT NULL,
  `endspeed` int(11) NOT NULL,
  `end` int(11) NOT NULL,
  `endeasing` varchar(100) NOT NULL,
  `incoming_class` varchar(20) NOT NULL,
  `outgoing_class` varchar(20) NOT NULL,
  `special_class` varchar(100) NOT NULL,
  `customin` varchar(300) NOT NULL,
  `customout` varchar(300) NOT NULL,
  `splitin` varchar(20) NOT NULL,
  `splitout` varchar(20) NOT NULL,
  `elementdelay` float NOT NULL,
  `endelementdelay` float NOT NULL,
  `linktoslide` varchar(20) NOT NULL,
  `data_type` varchar(30) NOT NULL,
  `layer_img` varchar(100) NOT NULL,
  `img_ww` int(11) NOT NULL,
  `img_hh` int(11) NOT NULL,
  `layer_video` varchar(50) NOT NULL,
  `video_width` int(11) NOT NULL,
  `video_height` int(11) NOT NULL,
  `video_autoplay` tinyint(1) NOT NULL,
  `video_fullscreen` tinyint(1) NOT NULL,
  `layer_text` mediumtext NOT NULL,
  `ordering` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`layer_id`)
) ENGINE=_MYSQL_ENGINE_  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

INSERT INTO `_DB_PREFIX_jms_sliderlayer_layers` (`layer_id`, `title`, `slide_id`, `layer_class`, `parallax_class`, `data_x`, `data_y`, `speed`, `start`, `easing`, `endspeed`, `end`, `endeasing`, `incoming_class`, `outgoing_class`, `special_class`, `customin`, `customout`, `splitin`, `splitout`, `elementdelay`, `endelementdelay`, `linktoslide`, `data_type`, `layer_img`, `img_ww`, `img_hh`, `layer_video`, `video_width`, `video_height`, `video_autoplay`, `video_fullscreen`, `layer_text`, `ordering`, `active`) VALUES
(1, 'HIGH-HEELS', 23, 'roboto_72', '', 'center', '303', 1000, 3000, 'easeOutBack', 0, 7000, '', 'sft', 'str', '', '', '', '', '', 0, 0, '', 'text', '', 0, 0, '', 0, 0, 0, 0, 'HIGH-HEELS', 0, 1),
(2, 'Starting', 23, 'roboto_42', '', 'center', '432', 1000, 3200, 'easeOutBack', 0, 6000, '', 'sfb', 'str', '', '', '', '', '', 0, 0, '', 'text', '', 0, 0, '', 0, 0, 0, 0, 'Up To <span style=\"color:#f01342;\">35%</span> Off', 0, 1),
(3, 'Big button', 23, 'big_button', '', 'center', '550', 1000, 3500, 'easeOutBack', 0, 5500, '', 'sfb', 'str', '', '', '', '', '', 0, 0, '', 'text', '', 0, 0, '', 0, 0, 0, 0, '<a href=\"#\" title=\"Shop now\">Shop now</a>', 0, 1),
(4, 'CREATIVE COLLECTION', 25, 'roboto_24', '', '100', '312', 1000, 3000, 'easeOutBack', 0, 7000, '', 'fade', 'fadeout', '', '', '', '', '', 0, 0, '', 'text', '', 0, 0, '', 0, 0, 0, 0, 'CREATIVE COLLECTION', 0, 1),
(5, 'CLASSIC STYLE', 25, 'roboto_72_n', '', '41', '378', 1000, 2500, 'easeOutBack', 0, 7500, '', 'fade', 'fadeout', '', '', '', '', '', 0, 0, '', 'text', '', 0, 0, '', 0, 0, 0, 0, 'CLASSIC STYLE', 0, 1),
(6, 'Shop now', 25, 'big_button', '', '223', '532', 1000, 3000, 'easeOutBack', 0, 7000, '', 'fade', 'fadeout', '', '', '', '', '', 0, 0, '', 'text', '', 0, 0, '', 0, 0, 0, 0, '<a href=\"#\" title=\"Shop now\">Shop now</a>', 0, 1),
(7, 'MEN’S SHOES', 20, 'roboto_72', '', '1111', '292', 1000, 3000, 'easeOutBack', 0, 7500, '', 'sfr', 'str', '', '', '', '', '', 0, 0, '', 'text', '', 0, 0, '', 0, 0, 0, 0, 'NEW STYLE', 0, 1),
(8, 'New Layer', 20, 'roboto_36', '', '1267', '423', 1000, 3500, 'easeOutBack', 0, 7000, '', 'sfr', 'str', '', '', '', '', '', 0, 0, '', 'text', '', 0, 0, '', 0, 0, 0, 0, '<p>Starting <span style=\"color:#FF0000;\">$69.00</span></p>', 0, 1),
(9, 'New Layer', 20, 'big_button', '', '1364', '539', 1000, 3700, 'easeOutBack', 0, 6700, '', 'sfr', 'str', '', '', '', '', '', 0, 0, '', 'text', '', 0, 0, '', 0, 0, 0, 0, '<a href=\"#\" title=\"Shop now\">Shop now</a>', 0, 1);

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_jms_sliderlayer_slides` (
  `slide_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `transition` varchar(30) NOT NULL,
  `slotamount` int(11) NOT NULL,
  `masterspeed` int(11) NOT NULL,
  `delay` int(11) NOT NULL,
  `link` varchar(255) NOT NULL,
  `target` varchar(10) NOT NULL,
  `bg_type` tinyint(1) NOT NULL,
  `main_img` varchar(255) NOT NULL,
  `bg_color` varchar(7) NOT NULL,
  `thumb_img` varchar(100) NOT NULL,
  `kenburns` tinyint(1) NOT NULL,
  `duration` int(11) NOT NULL,
  `ease` varchar(100) NOT NULL,
  `bgrepeat` varchar(20) NOT NULL,
  `bgfit` varchar(20) NOT NULL,
  `bgfitend` varchar(20) NOT NULL,
  `bgposition` varchar(30) NOT NULL,
  `bgpositionend` varchar(30) NOT NULL,
  `ordering` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`slide_id`)
) ENGINE=_MYSQL_ENGINE_  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

INSERT INTO `_DB_PREFIX_jms_sliderlayer_slides` (`slide_id`, `title`, `transition`, `slotamount`, `masterspeed`, `delay`, `link`, `target`, `bg_type`, `main_img`, `bg_color`, `thumb_img`, `kenburns`, `duration`, `ease`, `bgrepeat`, `bgfit`, `bgfitend`, `bgposition`, `bgpositionend`, `ordering`, `active`) VALUES
(20, 'Slide 0', 'boxslide', 7, 500, 0, '#', '_blank', 1, 'd713914fc0a82096e547e35372ee7700.jpg', '#FFFFFF', '', 0, 0, '', '0', 'cover', '1000', 'right top', 'center bottom', 0, 1),
(23, 'Slide 1', '3dcurtain-horizontal', 7, 300, 0, '', '_blank', 1, 'c5ac30096da8bf23cd1771767508ca9d.jpg', '#FFFFFF', '', 0, 0, '', '0', 'cover', '1000', 'right top', '', 0, 1),
(25, 'Slide 2', 'boxslide', 7, 300, 0, '', '_blank', 1, '20b31de385451d07d5fd128ee53370aa.jpg', '#FFFFFF', '', 0, 0, '', '0', 'cover', '1000', 'right top', '', 0, 1);

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_jms_sliderlayer_slides_lang` (
  `slide_id` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=latin1;

INSERT INTO `_DB_PREFIX_jms_sliderlayer_slides_lang` (`slide_id`, `id_lang`) VALUES
(20, 0),
(23, 0),
(25, 0);
";
?>
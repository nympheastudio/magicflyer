<?php
/** Chronossimo - Gestion automatique de l'affranchissement et du suivi des colis
 *
 * NOTICE OF LICENCE
 *
 * This source file is subject to a commercial license from SARL VANVAN
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL VANVAN is strictly forbidden.
 * In order to obtain a license, please contact us: contact@chronossimo.fr
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concédée par la société VANVAN
 * Toute utilisation, reproduction, modification ou distribution du présent
 * fichier source sans contrat de licence écrit de la part de la SARL VANVAN est
 * expressément interdite.
 * Pour obtenir une licence, veuillez contacter la SARL VANVAN a l'adresse:
 *                  contact@chronossimo.fr
 * ...........................................................................
 * @package    Chronossimo
 * @version    1.0
 * @copyright  Copyright(c) 2012-2014 VANVAN SARL
 * @author     Wandrille R. <contact@chronossimo.fr>
 * @license    Commercial license
 * @link http://www.chronossimo.fr
 */
$a462bc26fdceaa86fd5e245510e5edbe = dirname(__FILE__)."\x2f\x2e\x2e\x2f\x2e\x2e\x2f\x63\x6f\x6e\x66\x69\x67\x2f\x63\x6f\x6e\x66\x69\x67\x2e\x69\x6e\x63\x2e\x70\x68\x70"; $a15a85814db8f037c48134084e9c5ab4 = strstr($_SERVER["\x53\x43\x52\x49\x50\x54\x5f\x46\x49\x4c\x45\x4e\x41\x4d\x45"], "\x6d\x6f\x64\x75\x6c\x65\x73\x2f", true)."\x63\x6f\x6e\x66\x69\x67\x2f\x63\x6f\x6e\x66\x69\x67\x2e\x69\x6e\x63\x2e\x70\x68\x70"; if (is_file($a462bc26fdceaa86fd5e245510e5edbe)) include($a462bc26fdceaa86fd5e245510e5edbe); elseif (is_file($a15a85814db8f037c48134084e9c5ab4)) include($a15a85814db8f037c48134084e9c5ab4); $a8a325d861393cf389f485fd2769d394 = dirname(__FILE__)."\x2f\x2e\x2e\x2f\x2e\x2e\x2f\x69\x6e\x69\x74\x2e\x70\x68\x70"; $a0b1e3a7ff4a9764e95b9b03fba09dee8 = strstr($_SERVER["\x53\x43\x52\x49\x50\x54\x5f\x46\x49\x4c\x45\x4e\x41\x4d\x45"], "\x6d\x6f\x64\x75\x6c\x65\x73\x2f", true)."\x69\x6e\x69\x74\x2e\x70\x68\x70"; if (is_file($a8a325d861393cf389f485fd2769d394)) include($a8a325d861393cf389f485fd2769d394); elseif (is_file($a0b1e3a7ff4a9764e95b9b03fba09dee8)) include($a0b1e3a7ff4a9764e95b9b03fba09dee8); if ((!isset($_REQUEST["\x73\x65\x63\x75\x72\x69\x74\x79\x5f\x6b\x65\x79"])) || ($_REQUEST["\x73\x65\x63\x75\x72\x69\x74\x79\x5f\x6b\x65\x79"] != (version_compare(_PS_VERSION_, "\x31\x2e\x35", "\x3e\x3d")?Configuration::get("\x43\x46\x5f\x53\x45\x43\x55\x52\x49\x54\x59\x5f\x4b\x45\x59", null, 0, 0):Configuration::get("\x43\x46\x5f\x53\x45\x43\x55\x52\x49\x54\x59\x5f\x4b\x45\x59")) )) { header("\x48\x54\x54\x50\x2f\x31\x2e\x30\x20\x34\x30\x31\x20\x41\x75\x74\x68\x6f\x72\x69\x7a\x61\x74\x69\x6f\x6e\x20\x52\x65\x71\x75\x69\x72\x65\x64"); die("\x3c\x68\x31\x3e\x43\x6c\xc3\xa9\x20\x64\x65\x20\x73\xc3\xa9\x63\x75\x72\x69\x74\xc3\xa9\x20\x6e\x6f\x6e\x20\x76\x61\x6c\x69\x64\x65\x3c\x2f\x68\x31\x3e"); } if (isset($_REQUEST["\x69\x64\x5f\x6f\x72\x64\x65\x72"]) && $_REQUEST["\x69\x64\x5f\x6f\x72\x64\x65\x72"]) if (isset($_REQUEST["\x74\x72\x61\x63\x6b\x69\x6e\x67"]) && $_REQUEST["\x74\x72\x61\x63\x6b\x69\x6e\x67"]) setTracking($_REQUEST["\x69\x64\x5f\x6f\x72\x64\x65\x72"], $_REQUEST["\x74\x72\x61\x63\x6b\x69\x6e\x67"]); else getOrderInfos($_REQUEST["\x69\x64\x5f\x6f\x72\x64\x65\x72"]); die("\x65\x72\x72\x6f\x72"); function setTracking($f553cdfefece024181ffb789f3f266a50, $h738c05a704ec66dc5945da6205ab6384) { addTrackingToHistory($f553cdfefece024181ffb789f3f266a50, $h738c05a704ec66dc5945da6205ab6384); $c596d825bf660ec5e619cc607ce3d9a1 = updateTrackingNumber($f553cdfefece024181ffb789f3f266a50, $h738c05a704ec66dc5945da6205ab6384); die(json_encode(array("\x73\x75\x63\x63\x65\x73\x73"=>$c596d825bf660ec5e619cc607ce3d9a1))); } function getOrderInfos($f553cdfefece024181ffb789f3f266a50) { if ($f553cdfefece024181ffb789f3f266a50) { $d3c9cc32d19470dd4142392652ecfbbe2 = new Order((int)$f553cdfefece024181ffb789f3f266a50); if ($d3c9cc32d19470dd4142392652ecfbbe2 instanceof Order) { if (empty($d3c9cc32d19470dd4142392652ecfbbe2->conversion_rate)) $d3c9cc32d19470dd4142392652ecfbbe2->conversion_rate = 1; $g6277608020d2d4852c4f9d4718dc339f = $d3c9cc32d19470dd4142392652ecfbbe2->getFields(); if (isset($g6277608020d2d4852c4f9d4718dc339f["\x69\x64\x5f\x61\x64\x64\x72\x65\x73\x73\x5f\x64\x65\x6c\x69\x76\x65\x72\x79"]) && $g6277608020d2d4852c4f9d4718dc339f["\x69\x64\x5f\x61\x64\x64\x72\x65\x73\x73\x5f\x64\x65\x6c\x69\x76\x65\x72\x79"]) { $c7de88f16e3c73d11841f5083f774ff0 = new Address($g6277608020d2d4852c4f9d4718dc339f["\x69\x64\x5f\x61\x64\x64\x72\x65\x73\x73\x5f\x64\x65\x6c\x69\x76\x65\x72\x79"]); if (empty($c7de88f16e3c73d11841f5083f774ff0->alias)) { $c7de88f16e3c73d11841f5083f774ff0->alias = "\x44\x65\x66\x61\x75\x6c\x74"; if (method_exists($c7de88f16e3c73d11841f5083f774ff0, "\x75\x70\x64\x61\x74\x65")) $c7de88f16e3c73d11841f5083f774ff0->update(); } $a05da5bb04383a0ea0fa3b67c10654675 = $c7de88f16e3c73d11841f5083f774ff0->getFields(); } if ($g6277608020d2d4852c4f9d4718dc339f) { $f596e8e622035aabd22c6addff3d93357 = array( "\x72\x65\x66\x65\x72\x65\x6e\x63\x65"=> isset($g6277608020d2d4852c4f9d4718dc339f["\x72\x65\x66\x65\x72\x65\x6e\x63\x65"]) ? $g6277608020d2d4852c4f9d4718dc339f["\x72\x65\x66\x65\x72\x65\x6e\x63\x65"] : null, "\x63\x75\x73\x74\x6f\x6d\x65\x72" => (isset($a05da5bb04383a0ea0fa3b67c10654675["\x66\x69\x72\x73\x74\x6e\x61\x6d\x65"]) ? $a05da5bb04383a0ea0fa3b67c10654675["\x66\x69\x72\x73\x74\x6e\x61\x6d\x65"] : "")."\x20".(isset($a05da5bb04383a0ea0fa3b67c10654675["\x6c\x61\x73\x74\x6e\x61\x6d\x65"]) ? $a05da5bb04383a0ea0fa3b67c10654675["\x6c\x61\x73\x74\x6e\x61\x6d\x65"] : ""), "\x70\x72\x69\x63\x65" => isset($g6277608020d2d4852c4f9d4718dc339f["\x74\x6f\x74\x61\x6c\x5f\x70\x61\x69\x64"]) ? $g6277608020d2d4852c4f9d4718dc339f["\x74\x6f\x74\x61\x6c\x5f\x70\x61\x69\x64"] : null, "\x64\x61\x74\x65" => isset($g6277608020d2d4852c4f9d4718dc339f["\x64\x61\x74\x65\x5f\x61\x64\x64"]) ? $g6277608020d2d4852c4f9d4718dc339f["\x64\x61\x74\x65\x5f\x61\x64\x64"] : null ); die(json_encode($f596e8e622035aabd22c6addff3d93357)); } } } } function isMoreRecent($i8c8b55bc021f725acc7fbc23372cd7ba) { return version_compare(_PS_VERSION_, $i8c8b55bc021f725acc7fbc23372cd7ba, "\x3e\x3d"); } function addTrackingToHistory($a0961a113eff914e87ee08b1933ea6bdd, $h7475002673af242521aaaba7353b50fe) { if ($a0961a113eff914e87ee08b1933ea6bdd && $h7475002673af242521aaaba7353b50fe) return Db::getInstance()->Execute("\x55\x50\x44\x41\x54\x45\x20\x60"._DB_PREFIX_."\x63\x68\x72\x6f\x6e\x6f\x73\x73\x69\x6d\x6f\x5f\x68\x69\x73\x74\x6f\x72\x79\x60\x20\x53\x45\x54\x20\x60\x73\x75\x69\x76\x69\x60\x20\x3d\x20\x27".pSQL($h7475002673af242521aaaba7353b50fe)."\x27\x20\x57\x48\x45\x52\x45\x20\x60"._DB_PREFIX_."\x63\x68\x72\x6f\x6e\x6f\x73\x73\x69\x6d\x6f\x5f\x68\x69\x73\x74\x6f\x72\x79\x60\x2e\x60\x6f\x72\x64\x65\x72\x5f\x69\x64\x60\x20\x3d\x20\x27".pSQL($a0961a113eff914e87ee08b1933ea6bdd)."\x27\x20\x41\x4e\x44\x20\x60"._DB_PREFIX_."\x63\x68\x72\x6f\x6e\x6f\x73\x73\x69\x6d\x6f\x5f\x68\x69\x73\x74\x6f\x72\x79\x60\x2e\x60\x6c\x69\x76\x72\x65\x60\x20\x3d\x20\x27\x30\x27\x20\x4f\x52\x44\x45\x52\x20\x42\x59\x20\x63\x68\x72\x6f\x6e\x6f\x73\x73\x69\x6d\x6f\x5f\x69\x64\x20\x44\x45\x53\x43\x20\x4c\x49\x4d\x49\x54\x20\x31\x3b\x29\x3b"); } function updateTrackingNumber($a0961a113eff914e87ee08b1933ea6bdd, $h7475002673af242521aaaba7353b50fe) { if ( Validate::isLoadedObject($d3c9cc32d19470dd4142392652ecfbbe2 = new Order($a0961a113eff914e87ee08b1933ea6bdd))) { $d3c9cc32d19470dd4142392652ecfbbe2->shipping_number = $h7475002673af242521aaaba7353b50fe; $d3c9cc32d19470dd4142392652ecfbbe2->update(); if (isMoreRecent("\x31\x2e\x35")) { $ba5a0e0d3856877039dc7d93a5bfe2af = Db::getInstance()->getValue("\xa\x9\x9\x9\x9\x9\x9\x9\x53\x45\x4c\x45\x43\x54\x20\x60\x69\x64\x5f\x6f\x72\x64\x65\x72\x5f\x63\x61\x72\x72\x69\x65\x72\x60\xa\x9\x9\x9\x9\x9\x9\x9\x46\x52\x4f\x4d\x20\x60"._DB_PREFIX_."\x6f\x72\x64\x65\x72\x5f\x63\x61\x72\x72\x69\x65\x72\x60\xa\x9\x9\x9\x9\x9\x9\x9\x57\x48\x45\x52\x45\x20\x60\x69\x64\x5f\x6f\x72\x64\x65\x72\x60\x20\x3d\x20".(int)$d3c9cc32d19470dd4142392652ecfbbe2->id); $h793868c9b4a942065dabac1db2d766f6 = new OrderCarrier($ba5a0e0d3856877039dc7d93a5bfe2af); if (!Validate::isLoadedObject($h793868c9b4a942065dabac1db2d766f6)) return false; $h793868c9b4a942065dabac1db2d766f6->tracking_number = pSQL($h7475002673af242521aaaba7353b50fe); $h793868c9b4a942065dabac1db2d766f6->update(); } if ($h7475002673af242521aaaba7353b50fe) { global $_LANGMAIL; $a7d164b9e966ecd58aab3af853c7f177 = new Customer((int)($d3c9cc32d19470dd4142392652ecfbbe2->id_customer)); $d3ba7057b16066746960ccf9f8ec7ccc9 = new Carrier((int)($d3c9cc32d19470dd4142392652ecfbbe2->id_carrier)); if (!Validate::isLoadedObject($a7d164b9e966ecd58aab3af853c7f177) OR !Validate::isLoadedObject($d3ba7057b16066746960ccf9f8ec7ccc9)) { print("\x45\x72\x72\x65\x75\x72\x20\x6c\x6f\x72\x73\x20\x64\x65\x20\x6c\x61\x20\x6d\x69\x73\x65\x20\xc3\xa0\x20\x6a\x6f\x75\x72\x20\x64\x75\x20\x6e\x75\x6d\xc3\xa9\x72\x6f\x20\x64\x65\x20\x73\x75\x69\x76\x69\x20\x3a".Tools::displayError()); return null; } $c0663dae937cc7db6da9e8873ad615a4 = array( "\x7b\x66\x6f\x6c\x6c\x6f\x77\x75\x70\x7d" => str_replace("\x40", $d3c9cc32d19470dd4142392652ecfbbe2->shipping_number, $d3ba7057b16066746960ccf9f8ec7ccc9->url), "\x7b\x66\x69\x72\x73\x74\x6e\x61\x6d\x65\x7d" => $a7d164b9e966ecd58aab3af853c7f177->firstname, "\x7b\x6c\x61\x73\x74\x6e\x61\x6d\x65\x7d" => $a7d164b9e966ecd58aab3af853c7f177->lastname, "\x7b\x69\x64\x5f\x6f\x72\x64\x65\x72\x7d" => (int)($d3c9cc32d19470dd4142392652ecfbbe2->id) ); if (method_exists($d3c9cc32d19470dd4142392652ecfbbe2, "\x67\x65\x74\x55\x6e\x69\x71\x52\x65\x66\x65\x72\x65\x6e\x63\x65")) $c0663dae937cc7db6da9e8873ad615a4["\x7b\x6f\x72\x64\x65\x72\x5f\x6e\x61\x6d\x65\x7d"] = $d3c9cc32d19470dd4142392652ecfbbe2->getUniqReference(); if (function_exists("\x70\x72\x6f\x70\x65\x72\x74\x79\x5f\x65\x78\x69\x73\x74\x73") && property_exists($d3c9cc32d19470dd4142392652ecfbbe2, "\x73\x68\x69\x70\x70\x69\x6e\x67\x5f\x6e\x75\x6d\x62\x65\x72")) $c0663dae937cc7db6da9e8873ad615a4["\x7b\x73\x68\x69\x70\x70\x69\x6e\x67\x5f\x6e\x75\x6d\x62\x65\x72\x7d"] = $d3c9cc32d19470dd4142392652ecfbbe2->shipping_number; @Mail::Send((int)($d3c9cc32d19470dd4142392652ecfbbe2->id_lang), "\x69\x6e\x5f\x74\x72\x61\x6e\x73\x69\x74", Mail::l("\x50\x61\x63\x6b\x61\x67\x65\x20\x69\x6e\x20\x74\x72\x61\x6e\x73\x69\x74"), $c0663dae937cc7db6da9e8873ad615a4, $a7d164b9e966ecd58aab3af853c7f177->email, $a7d164b9e966ecd58aab3af853c7f177->firstname."\x20".$a7d164b9e966ecd58aab3af853c7f177->lastname, NULL, NULL, NULL, NULL, _PS_MAIL_DIR_, true); if(method_exists("\x48\x6f\x6f\x6b", "\x65\x78\x65\x63")) Hook::exec("\x61\x63\x74\x69\x6f\x6e\x41\x64\x6d\x69\x6e\x4f\x72\x64\x65\x72\x73\x54\x72\x61\x63\x6b\x69\x6e\x67\x4e\x75\x6d\x62\x65\x72\x55\x70\x64\x61\x74\x65", array("\x6f\x72\x64\x65\x72" => $d3c9cc32d19470dd4142392652ecfbbe2, "\x63\x75\x73\x74\x6f\x6d\x65\x72" => $a7d164b9e966ecd58aab3af853c7f177, "\x63\x61\x72\x72\x69\x65\x72" => $d3ba7057b16066746960ccf9f8ec7ccc9)); return true; } } return false; }
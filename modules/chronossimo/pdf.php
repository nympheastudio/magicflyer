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
$a462bc26fdceaa86fd5e245510e5edbe = dirname(__FILE__)."\x2f\x2e\x2e\x2f\x2e\x2e\x2f\x63\x6f\x6e\x66\x69\x67\x2f\x63\x6f\x6e\x66\x69\x67\x2e\x69\x6e\x63\x2e\x70\x68\x70"; $a15a85814db8f037c48134084e9c5ab4 = strstr($_SERVER["\x53\x43\x52\x49\x50\x54\x5f\x46\x49\x4c\x45\x4e\x41\x4d\x45"], "\x6d\x6f\x64\x75\x6c\x65\x73\x2f", true)."\x63\x6f\x6e\x66\x69\x67\x2f\x63\x6f\x6e\x66\x69\x67\x2e\x69\x6e\x63\x2e\x70\x68\x70"; if (is_file($a462bc26fdceaa86fd5e245510e5edbe)) include($a462bc26fdceaa86fd5e245510e5edbe); elseif (is_file($a15a85814db8f037c48134084e9c5ab4)) include($a15a85814db8f037c48134084e9c5ab4); $a8a325d861393cf389f485fd2769d394 = dirname(__FILE__)."\x2f\x2e\x2e\x2f\x2e\x2e\x2f\x69\x6e\x69\x74\x2e\x70\x68\x70"; $a0b1e3a7ff4a9764e95b9b03fba09dee8 = strstr($_SERVER["\x53\x43\x52\x49\x50\x54\x5f\x46\x49\x4c\x45\x4e\x41\x4d\x45"], "\x6d\x6f\x64\x75\x6c\x65\x73\x2f", true)."\x69\x6e\x69\x74\x2e\x70\x68\x70"; if (is_file($a8a325d861393cf389f485fd2769d394)) include($a8a325d861393cf389f485fd2769d394); elseif (is_file($a0b1e3a7ff4a9764e95b9b03fba09dee8)) include($a0b1e3a7ff4a9764e95b9b03fba09dee8); if ((!isset($_REQUEST["\x73\x65\x63\x75\x72\x69\x74\x79\x5f\x6b\x65\x79"])) || ($_REQUEST["\x73\x65\x63\x75\x72\x69\x74\x79\x5f\x6b\x65\x79"] != (version_compare(_PS_VERSION_, "\x31\x2e\x35", "\x3e\x3d")?Configuration::get("\x43\x46\x5f\x53\x45\x43\x55\x52\x49\x54\x59\x5f\x4b\x45\x59", null, 0, 0):Configuration::get("\x43\x46\x5f\x53\x45\x43\x55\x52\x49\x54\x59\x5f\x4b\x45\x59")) )) { header("\x48\x54\x54\x50\x2f\x31\x2e\x30\x20\x34\x30\x31\x20\x41\x75\x74\x68\x6f\x72\x69\x7a\x61\x74\x69\x6f\x6e\x20\x52\x65\x71\x75\x69\x72\x65\x64"); die("\x3c\x68\x31\x3e\x43\x6c\xc3\xa9\x20\x64\x65\x20\x73\xc3\xa9\x63\x75\x72\x69\x74\xc3\xa9\x20\x6e\x6f\x6e\x20\x76\x61\x6c\x69\x64\x65\x3c\x2f\x68\x31\x3e"); } $h7735be54e0d52b91c33537858164bd82 = explode("\x2d",$_REQUEST["\x6f\x72\x64\x65\x72\x73"]); if (is_array($h7735be54e0d52b91c33537858164bd82) && count($h7735be54e0d52b91c33537858164bd82)>0) { if (empty($h7735be54e0d52b91c33537858164bd82[count($h7735be54e0d52b91c33537858164bd82)-1])) unset($h7735be54e0d52b91c33537858164bd82[count($h7735be54e0d52b91c33537858164bd82)-1]); if (isMoreRecent("\x31\x2e\x35")) { $c4a2f0ac0c49b4dab56ba4f68a789b0e = ""; foreach($h7735be54e0d52b91c33537858164bd82 as $f553cdfefece024181ffb789f3f266a50) { $d3c9cc32d19470dd4142392652ecfbbe2 = new Order((int)$f553cdfefece024181ffb789f3f266a50); if (!Validate::isLoadedObject($d3c9cc32d19470dd4142392652ecfbbe2)) die(Tools::displayError("\x43\x61\x6e\x6e\x6f\x74\x20\x66\x69\x6e\x64\x20\x6f\x72\x64\x65\x72\x20\x69\x6e\x20\x64\x61\x74\x61\x62\x61\x73\x65")); $c4a2f0ac0c49b4dab56ba4f68a789b0e.= "\x69\x64\x5f\x6f\x72\x64\x65\x72\x3d".(int)$f553cdfefece024181ffb789f3f266a50."\x20\x4f\x52\x20"; } $c4a2f0ac0c49b4dab56ba4f68a789b0e = substr($c4a2f0ac0c49b4dab56ba4f68a789b0e, 0, strlen($c4a2f0ac0c49b4dab56ba4f68a789b0e)-3); $dbb084ead0ba0862e07c72be85c1c1f9 = new Collection("\x4f\x72\x64\x65\x72\x49\x6e\x76\x6f\x69\x63\x65"); $dbb084ead0ba0862e07c72be85c1c1f9->sqlWhere($c4a2f0ac0c49b4dab56ba4f68a789b0e); generatePDF($dbb084ead0ba0862e07c72be85c1c1f9, PDF::TEMPLATE_INVOICE); } else PDF::multipleInvoices($h7735be54e0d52b91c33537858164bd82); } function isMoreRecent($i8c8b55bc021f725acc7fbc23372cd7ba) { return version_compare(_PS_VERSION_, $i8c8b55bc021f725acc7fbc23372cd7ba, "\x3e\x3d"); } function generatePDF($ef6ded4c764c989add49757cd623ab93, $bc278908fb72608fc7b9d4b1fc4e34d6) { $j9d39b10435cfc01801220a6176cc70c8 = new PDF($ef6ded4c764c989add49757cd623ab93, $bc278908fb72608fc7b9d4b1fc4e34d6, Context::getContext()->smarty); $j9d39b10435cfc01801220a6176cc70c8->render(); }
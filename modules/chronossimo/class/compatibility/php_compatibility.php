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
if (!function_exists("\x6a\x73\x6f\x6e\x5f\x64\x65\x63\x6f\x64\x65")) { function json_decode($b198e852f03260676c3015c89686729d6, $c2393d20ced8b34f63189e5972ad1e1ea=false) { if (!class_exists("\x53\x65\x72\x76\x69\x63\x65\x73\x5f\x4a\x53\x4f\x4e")) require_once("\x4a\x53\x4f\x4e\x2e\x70\x68\x70"); if ($c2393d20ced8b34f63189e5972ad1e1ea) { $j9e7bffa10d5c846dea1962dfecb95c5c = new Services_JSON(SERVICES_JSON_LOOSE_TYPE); } else { $j9e7bffa10d5c846dea1962dfecb95c5c = new Services_JSON; } return $j9e7bffa10d5c846dea1962dfecb95c5c->decode($b198e852f03260676c3015c89686729d6); } } if (!function_exists("\x6a\x73\x6f\x6e\x5f\x65\x6e\x63\x6f\x64\x65")) { function json_encode($b198e852f03260676c3015c89686729d6) { if (!class_exists("\x53\x65\x72\x76\x69\x63\x65\x73\x5f\x4a\x53\x4f\x4e")) require_once("\x4a\x53\x4f\x4e\x2e\x70\x68\x70"); $j9e7bffa10d5c846dea1962dfecb95c5c = new Services_JSON; return $j9e7bffa10d5c846dea1962dfecb95c5c->encode($b198e852f03260676c3015c89686729d6); } } ?>
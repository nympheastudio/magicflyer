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
if (!defined("\x5f\x50\x53\x5f\x56\x45\x52\x53\x49\x4f\x4e\x5f")) exit; class CChronossimo { const h7607b0d1ef694e22dc545c55c8382df1 = false; const d355035c61c08231837016af78c33e57 = "\x72\x65\x71\x75\x65\x73\x74\x2e\x74\x78\x74"; const e47f61d68bb44c88e2cea0c1022eb3d52 = "\x2e\x2e\x2f\x6d\x6f\x64\x75\x6c\x65\x73\x2f\x63\x68\x72\x6f\x6e\x6f\x73\x73\x69\x6d\x6f\x2f\x63\x65\x72\x74\x69\x66\x69\x63\x61\x74\x65\x2f\x63\x68\x72\x6f\x6e\x6f\x73\x73\x69\x6d\x6f\x2e\x63\x72\x74"; const d43087963b36f2eb13b85b415c1c2fb5 = "\x68\x74\x74\x70\x3a\x2f\x2f\x61\x75\x74\x6f\x63\x6f\x6d\x2e\x63\x68\x72\x6f\x6e\x6f\x73\x73\x69\x6d\x6f\x2e\x66\x72"; const d36da9bbc842138230abab76347503c7e = "\x68\x74\x74\x70\x73\x3a\x2f\x2f\x61\x75\x74\x6f\x63\x6f\x6d\x2e\x63\x68\x72\x6f\x6e\x6f\x73\x73\x69\x6d\x6f\x2e\x66\x72"; const b9e52a1467ba36bd6bfb6804638fb406 = "\x2f\x61\x75\x74\x6f\x63\x6f\x6d\x2f\x73\x65\x6e\x64\x49\x6e\x66\x6f"; const i89a8769074a6356c19c589b65affcee6 = "\x2f\x61\x75\x74\x6f\x63\x6f\x6d\x2f\x61\x66\x66\x72\x61\x6e\x63\x68\x69\x73\x73\x65\x6d\x65\x6e\x74"; const j9765901a296528bbb20f867f4ebcfb47 = "\x2f\x61\x75\x74\x6f\x63\x6f\x6d\x2f\x76\x61\x6c\x69\x64\x61\x74\x65\x46\x6f\x72\x6d"; const a0b494ae5b26d9b604b3075768623f357 = "\x2f\x61\x75\x74\x6f\x63\x6f\x6d\x2f\x65\x78\x65\x63\x75\x74\x65\x2f\x69\x6e\x73\x74\x61\x6c\x6c"; const h77fc3b81cb5906b1f3d59588d0d7a38b = "\x2f\x61\x75\x74\x6f\x63\x6f\x6d\x2f\x65\x78\x65\x63\x75\x74\x65\x2f\x75\x70\x67\x72\x61\x64\x65"; const e4e2473e25a72943eccca1bc8df94ba3e = "\x2f\x61\x75\x74\x6f\x63\x6f\x6d\x2f\x65\x78\x65\x63\x75\x74\x65\x2f\x69\x6e\x73\x74\x61\x6e\x74"; const a73afed19dd6ffc8da549add57961b6f = "\x2f\x61\x75\x74\x6f\x63\x6f\x6d\x2f\x63\x68\x65\x63\x6b\x75\x70\x67\x72\x61\x64\x65"; const g6fde70e12fcf9841e2cbff8bb1ce50d6 = "\x2f\x61\x75\x74\x6f\x63\x6f\x6d\x2f\x63\x68\x65\x63\x6b\x53\x75\x69\x76\x69"; const c24a010a43492fde417c7a5f2512c26fc = "\x2f\x61\x75\x74\x6f\x63\x6f\x6d\x2f\x63\x68\x65\x63\x6b\x48\x74\x74\x70\x53\x75\x69\x76\x69"; const d3be76917ee9569d0b72c677e6121a82d = "\x68\x74\x74\x70\x73\x3a\x2f\x2f\x61\x70\x69\x2e\x6c\x61\x70\x6f\x73\x74\x65\x2e\x66\x72\x2f\x73\x75\x69\x76\x69\x2f\x76\x31\x2f"; const f5dcaca53e9eb5b32371136a921843fa7 = "\x68\x74\x74\x70\x3a\x2f\x2f\x77\x77\x77\x2e\x63\x6f\x6c\x69\x73\x73\x69\x6d\x6f\x2e\x66\x72\x2f\x70\x6f\x72\x74\x61\x69\x6c\x5f\x63\x6f\x6c\x69\x73\x73\x69\x6d\x6f\x2f\x73\x75\x69\x76\x72\x65\x2e\x64\x6f\x3f\x63\x6f\x6c\x69\x73\x70\x61\x72\x74\x3d"; const f80f85a25af4c048da19ec3cbfeb1603 = "\x68\x74\x74\x70\x3a\x2f\x2f\x77\x77\x77\x2e\x63\x73\x75\x69\x76\x69\x2e\x63\x6f\x75\x72\x72\x69\x65\x72\x2e\x6c\x61\x70\x6f\x73\x74\x65\x2e\x66\x72\x2f\x73\x75\x69\x76\x69\x2f\x69\x6e\x64\x65\x78\x3f\x69\x64\x3d"; const a01dddc3d898adb2edb6a164f502a37cb = "\x57\x39\x35\x76\x73\x65\x62\x4e\x4a\x59\x39\x64\x4c\x79\x35\x44\x43\x33\x54\x43\x56\x39\x6d\x4c\x73\x47\x48\x4e\x72\x57\x52\x34\x56\x52\x6c\x62\x66\x2f\x51\x77\x49\x77\x51\x4d\x42\x6a\x6c\x46\x4e\x6c\x7a\x6b\x69\x76\x6c\x70\x49\x67\x32\x44\x4c\x46\x52\x6c"; const f5615c6e40e906d826f0d4d2cb4299b70 = "\x73\x65\x73\x73\x69\x6f\x6e\x49\x64"; const aa301952e32c7e02b8fedb9d715cd2b8 = 600; const d985f3afd84f1b02088e7b363a64624e = 300; const j9214b3ae2a4070c21af441c011a77d47 = "\x56\x61\x6e\x56\x61\x6e\x20\x43\x6f\x72\x70\x2f\x31\x2e\x30\x20\x28\x4c\x69\x6e\x75\x78\x29"; protected $h7205ea56d5dc17c92adbb925699c0349 = self::d43087963b36f2eb13b85b415c1c2fb5; protected $b9dc70e1bdc4668107f7fd2b5278c23b = false; protected $cookie = null; protected $g6cbad28e95f03369e61692326c80a107 = null; protected $i83ada26c99a628c1354f09eebbbfaa60 = null; protected $b18d8876a2c4379fd0854985e55823210 = null; protected $ddc76f9850c9374cf153fc4a46e85ee1 = null; protected $h753a567d0c7afb4e81f8bef0fdcb4ca5 = null; protected $i840588dc219c4b7dbba5b1948a5143ea = 0; protected $i8c8b55bc021f725acc7fbc23372cd7ba = null; protected $c162a60b810922f00c51f54b66c86115 = null; protected $d67cfda8e3014577593a398bcbdaecef = null; protected $d387629bba1dd9874c84ef83c30e49db3 = false; protected $j991c92545c65966c4ec5608655b6a383 = array(); protected $d0599b268a5c7f2b3e45cbbb5b50257f = array(); protected $i85f08e7f5ada20308b5475176ee52066 = null; protected $b8903db432b6dfe4e584866be1b3f4dc = array(); protected $c2a36b311258617b43fadd025e9ea43a6 = array(); protected $cae07e3c38ef3923150b2fd68f928547 = null; protected $b1dfae95f1953fd05003d5c75c0c6bf44 = null; protected $abf7394ee41034194c67529cbed7375a = null; protected $f596e8e622035aabd22c6addff3d93357 = array(); function __construct($i83ada26c99a628c1354f09eebbbfaa60, $b18d8876a2c4379fd0854985e55823210, $i8c8b55bc021f725acc7fbc23372cd7ba = null, $c162a60b810922f00c51f54b66c86115 = null, $d67cfda8e3014577593a398bcbdaecef = null, $d329f9bac4566517feb2ab272a1f538f5 = false) { if (empty($i83ada26c99a628c1354f09eebbbfaa60)) return $this->eb196103b0b617aea56dfe69e1882d86("\x45\x6d\x61\x69\x6c\x20\x69\x6e\x76\x61\x6c\x69\x64\x65\x2e\x20\x52\x65\x6e\x64\x65\x7a\x2d\x76\x6f\x75\x73\x20\x64\x61\x6e\x73\x20\x6c\x61\x20\x63\x6f\x6e\x66\x69\x67\x75\x72\x61\x74\x69\x6f\x6e\x20\x64\x75\x20\x6d\x6f\x64\x75\x6c\x65\x2e"); ini_set("\x75\x73\x65\x72\x5f\x61\x67\x65\x6e\x74", self::j9214b3ae2a4070c21af441c011a77d47); $this->i83ada26c99a628c1354f09eebbbfaa60 = $i83ada26c99a628c1354f09eebbbfaa60; $this->b18d8876a2c4379fd0854985e55823210 = $b18d8876a2c4379fd0854985e55823210; $this->i8c8b55bc021f725acc7fbc23372cd7ba = $i8c8b55bc021f725acc7fbc23372cd7ba; $this->c162a60b810922f00c51f54b66c86115 = $c162a60b810922f00c51f54b66c86115; $this->d67cfda8e3014577593a398bcbdaecef = $d67cfda8e3014577593a398bcbdaecef; $this->h7205ea56d5dc17c92adbb925699c0349 = $d329f9bac4566517feb2ab272a1f538f5?self::d36da9bbc842138230abab76347503c7e:self::d43087963b36f2eb13b85b415c1c2fb5; $this->b9dc70e1bdc4668107f7fd2b5278c23b = $d329f9bac4566517feb2ab272a1f538f5; } function __destruct() { } public function sendInstall(){ return $this->j91898de452ff0b74f4871f6d20e0fd07($this->h7205ea56d5dc17c92adbb925699c0349.self::a0b494ae5b26d9b604b3075768623f357."\x3f".$this->g6a77380a98320a1b698fecee637c0279()); } public function sendUpgrade() { return $this->j91898de452ff0b74f4871f6d20e0fd07($this->h7205ea56d5dc17c92adbb925699c0349.self::h77fc3b81cb5906b1f3d59588d0d7a38b."\x3f".$this->g6a77380a98320a1b698fecee637c0279()); } public function executeInstant($h78f933d2b337e7834fa379e1f7c0849f) { return $this->a08a7454609f6f196a20e6ad99283b0e5($this->h7205ea56d5dc17c92adbb925699c0349.self::e4e2473e25a72943eccca1bc8df94ba3e, $h78f933d2b337e7834fa379e1f7c0849f); } public function checkUpgrade() { $g66956672cba754c98850129a6ea71cf6 = $this->j91898de452ff0b74f4871f6d20e0fd07($this->h7205ea56d5dc17c92adbb925699c0349.self::a73afed19dd6ffc8da549add57961b6f."\x3f".$this->g6a77380a98320a1b698fecee637c0279()); return json_decode($g66956672cba754c98850129a6ea71cf6,true); } public function checkSuivi($a03908463a42bada2d3dfe7cc389e32e9) { $f766b0e47c7ada2e3812c9d3790c5121 = array(); $fcf05e95c60fb7549b1c694048425c0d = 0; foreach ($a03908463a42bada2d3dfe7cc389e32e9 as $d3c9cc32d19470dd4142392652ecfbbe2) { $d396abc6be52bb2c31b996292f916371 = trim($d3c9cc32d19470dd4142392652ecfbbe2["\x73\x75\x69\x76\x69"]); if (!empty($d396abc6be52bb2c31b996292f916371)) { if ($d3c9cc32d19470dd4142392652ecfbbe2["\x63\x61\x72\x72\x69\x65\x72"] == "\x4c\x45\x54\x54\x52\x45\x53\x55\x49\x56\x49\x45") $f766b0e47c7ada2e3812c9d3790c5121[$fcf05e95c60fb7549b1c694048425c0d] = $this->checkSuiviJson($d396abc6be52bb2c31b996292f916371); else $f766b0e47c7ada2e3812c9d3790c5121[$fcf05e95c60fb7549b1c694048425c0d] = $this->checkHTTPSuivi($d396abc6be52bb2c31b996292f916371); } $f766b0e47c7ada2e3812c9d3790c5121[$fcf05e95c60fb7549b1c694048425c0d]["\x73\x75\x69\x76\x69"] = $d396abc6be52bb2c31b996292f916371; $fcf05e95c60fb7549b1c694048425c0d++; } $g66956672cba754c98850129a6ea71cf6 = $this->b9055b168231ef0f808497d55f73c18c($this->h7205ea56d5dc17c92adbb925699c0349.self::c24a010a43492fde417c7a5f2512c26fc."\x3f".$this->g6a77380a98320a1b698fecee637c0279(), json_encode($f766b0e47c7ada2e3812c9d3790c5121)); if ($g66956672cba754c98850129a6ea71cf6) return json_decode($g66956672cba754c98850129a6ea71cf6, true); else return false; } public function checkSuiviJson($d396abc6be52bb2c31b996292f916371) { $h7456761430eb090e26f9fc6f3e7dc396 = array(); $d396abc6be52bb2c31b996292f916371 = trim($d396abc6be52bb2c31b996292f916371); if (!empty($d396abc6be52bb2c31b996292f916371)) { $b198e852f03260676c3015c89686729d6 = $this->j91898de452ff0b74f4871f6d20e0fd07(self::d3be76917ee9569d0b72c677e6121a82d.trim($d396abc6be52bb2c31b996292f916371), array("\x43\x6f\x6e\x74\x65\x6e\x74\x2d\x54\x79\x70\x65\x3a\x20\x61\x70\x70\x6c\x69\x63\x61\x74\x69\x6f\x6e\x2f\x6a\x73\x6f\x6e","\x58\x2d\x4f\x6b\x61\x70\x69\x2d\x4b\x65\x79\x3a\x20".self::a01dddc3d898adb2edb6a164f502a37cb), false, false, "\x68\x74\x74\x70\x3a\x2f\x2f\x61\x70\x69\x2e\x6c\x61\x70\x6f\x73\x74\x65\x2e\x66\x72"); $j99971673516346a2a7f51580ce4b472b = @json_decode($this->a073eb08eb5a1bf3edb3c3ffd884e1bc3($b198e852f03260676c3015c89686729d6), true); if ($j99971673516346a2a7f51580ce4b472b && isset($j99971673516346a2a7f51580ce4b472b["\x6d\x65\x73\x73\x61\x67\x65"])) $h7456761430eb090e26f9fc6f3e7dc396["\x74\x65\x78\x74"] = $j99971673516346a2a7f51580ce4b472b["\x6d\x65\x73\x73\x61\x67\x65"]; else $h7456761430eb090e26f9fc6f3e7dc396["\x74\x65\x78\x74"] = $this->a073eb08eb5a1bf3edb3c3ffd884e1bc3($b198e852f03260676c3015c89686729d6); } $h7456761430eb090e26f9fc6f3e7dc396["\x73\x75\x69\x76\x69"] = $d396abc6be52bb2c31b996292f916371; return $h7456761430eb090e26f9fc6f3e7dc396; } protected function a073eb08eb5a1bf3edb3c3ffd884e1bc3($c2b61d3c1044fa825416014245bb7af54) { if (preg_match("\x23\x5e\x48\x54\x54\x50\x2f\x5b\x30\x2d\x39\x5d\x7b\x31\x7d\x5c\x2e\x5b\x30\x2d\x39\x5d\x7b\x31\x7d\x20\x5b\x30\x2d\x39\x5d\x2b\x20\x5b\x41\x2d\x5a\x5d\x2b\x23\x69", $c2b61d3c1044fa825416014245bb7af54, $d3c75ccc1aba17760ca63b6575852f3d6)) { if (($h70d3e795b3022bcb3ba185322028ef2e = strpos($c2b61d3c1044fa825416014245bb7af54, "\x0a\x0a") ) !== false) return substr($c2b61d3c1044fa825416014245bb7af54, $h70d3e795b3022bcb3ba185322028ef2e+2); if (($h70d3e795b3022bcb3ba185322028ef2e = strpos($c2b61d3c1044fa825416014245bb7af54, "\x0a\x0d") ) !== false) return substr($c2b61d3c1044fa825416014245bb7af54, $h70d3e795b3022bcb3ba185322028ef2e+2); } return $c2b61d3c1044fa825416014245bb7af54; } public function checkHTTPSuivi($d396abc6be52bb2c31b996292f916371) { $h7456761430eb090e26f9fc6f3e7dc396 = array(); $d3dff8300f4e6264f0cb5798b34ca7330= null; $cookie = false; $d396abc6be52bb2c31b996292f916371 = trim($d396abc6be52bb2c31b996292f916371); if (!empty($d396abc6be52bb2c31b996292f916371)) { $b198e852f03260676c3015c89686729d6 = $this->j91898de452ff0b74f4871f6d20e0fd07(self::f5dcaca53e9eb5b32371136a921843fa7.urlencode($d396abc6be52bb2c31b996292f916371), true, false, $cookie); if (preg_match_all("\x2f\x53\x65\x74\x2d\x43\x6f\x6f\x6b\x69\x65\x3a\x20\x3f\x28\x5b\x5e\x20\x5d\x2a\x3f\x3b\x29\x2f\x69", $b198e852f03260676c3015c89686729d6, $d3c75ccc1aba17760ca63b6575852f3d6)) { $cookie = ""; foreach ($d3c75ccc1aba17760ca63b6575852f3d6[1] as $c79b2246d14634e2494a192cfcca3da8) $cookie .= $c79b2246d14634e2494a192cfcca3da8."\x20"; $cookie = substr($cookie, 0, strlen($cookie) -2); } if (preg_match("\x23\x3c\x69\x6d\x67\x5b\x5e\x3e\x5d\x2b\x27\x28\x69\x6d\x61\x67\x65\x69\x6f\x5b\x5e\x27\x5d\x2b\x3f\x6c\x69\x62\x65\x5b\x5e\x27\x5d\x2b\x3f\x29\x27\x23", $b198e852f03260676c3015c89686729d6, $d3c75ccc1aba17760ca63b6575852f3d6) && $cookie) { $d3dff8300f4e6264f0cb5798b34ca7330 = $this->j91898de452ff0b74f4871f6d20e0fd07(substr(self::f5dcaca53e9eb5b32371136a921843fa7,0, strripos(self::f5dcaca53e9eb5b32371136a921843fa7, "\x2f")+1).$d3c75ccc1aba17760ca63b6575852f3d6[1], false, false, $cookie, self::f5dcaca53e9eb5b32371136a921843fa7.urlencode($d396abc6be52bb2c31b996292f916371)); $j9b28d0207c7efc61b2b4965cc8486eb3 = md5($d3dff8300f4e6264f0cb5798b34ca7330); if (!in_array($j9b28d0207c7efc61b2b4965cc8486eb3, $this->j991c92545c65966c4ec5608655b6a383)) $h7456761430eb090e26f9fc6f3e7dc396["\x69\x6d\x67"] = base64_encode($d3dff8300f4e6264f0cb5798b34ca7330); $h7456761430eb090e26f9fc6f3e7dc396["\x6d\x64\x35"] = $j9b28d0207c7efc61b2b4965cc8486eb3; $this->j991c92545c65966c4ec5608655b6a383[] = $j9b28d0207c7efc61b2b4965cc8486eb3; } if (preg_match("\x23\x3c\x74\x61\x62\x6c\x65\x2e\x2a\x3f\x3e\x2e\x2a\x3f\x3c\x74\x64\x2e\x2a\x3f\x3e\x2e\x2a\x3f\x28\x5c\x64\x2b\x2f\x5c\x64\x2b\x2f\x5c\x64\x2b\x29\x2e\x2a\x3f\x3c\x2f\x74\x64\x3e\x2e\x2a\x3f\x3c\x74\x64\x2e\x2a\x3f\x3e\x28\x2e\x2a\x3f\x29\x3c\x2f\x74\x64\x3e\x2e\x2a\x3f\x3c\x2f\x74\x61\x62\x6c\x65\x3e\x23\x73\x69", $b198e852f03260676c3015c89686729d6, $d3c75ccc1aba17760ca63b6575852f3d6)) $h7456761430eb090e26f9fc6f3e7dc396["\x74\x65\x78\x74"] = $d3c75ccc1aba17760ca63b6575852f3d6[1]."\x20".strip_tags($d3c75ccc1aba17760ca63b6575852f3d6[2]); } return $h7456761430eb090e26f9fc6f3e7dc396; } public function checkHTTPSuiviLettre($d396abc6be52bb2c31b996292f916371) { $h7456761430eb090e26f9fc6f3e7dc396 = array(); $d396abc6be52bb2c31b996292f916371 = trim($d396abc6be52bb2c31b996292f916371); if (!empty($d396abc6be52bb2c31b996292f916371)) { $b198e852f03260676c3015c89686729d6 = $this->j91898de452ff0b74f4871f6d20e0fd07(self::f80f85a25af4c048da19ec3cbfeb1603.urlencode($d396abc6be52bb2c31b996292f916371), true, false); if (preg_match("\x23\x3c\x73\x70\x61\x6e\x5b\x5e\x3e\x5d\x2b\x63\x6c\x61\x73\x73\x3d\x22\x61\x63\x68\x5f\x63\x6f\x75\x72\x72\x69\x65\x72\x5f\x62\x6f\x78\x5f\x73\x74\x61\x74\x75\x74\x22\x5b\x5e\x3e\x5d\x2a\x3e\x28\x5b\x5e\x3c\x5d\x2b\x29\x3c\x2f\x73\x70\x61\x6e\x3e\x23\x69", $b198e852f03260676c3015c89686729d6, $d3c75ccc1aba17760ca63b6575852f3d6) && preg_match("\x23".$d396abc6be52bb2c31b996292f916371."\x23", $b198e852f03260676c3015c89686729d6)) { $h7456761430eb090e26f9fc6f3e7dc396["\x74\x65\x78\x74"] = $d3c75ccc1aba17760ca63b6575852f3d6[1]; } } return $h7456761430eb090e26f9fc6f3e7dc396; } public function setBilling(Adresse $i85f08e7f5ada20308b5475176ee52066) { $this->i85f08e7f5ada20308b5475176ee52066 = $i85f08e7f5ada20308b5475176ee52066->toArray(true); } public function setCarrier($d47e8a6f1ae320049528eb8dd77a87b4, $i83ada26c99a628c1354f09eebbbfaa60, $b18d8876a2c4379fd0854985e55823210) { $this->c2a36b311258617b43fadd025e9ea43a6 = array( "\x69\x64" => $d47e8a6f1ae320049528eb8dd77a87b4, "\x6c\x6f\x67\x69\x6e" => $i83ada26c99a628c1354f09eebbbfaa60, "\x70\x61\x73\x73\x77\x6f\x72\x64" => $b18d8876a2c4379fd0854985e55823210 ); } public function addColis(Colis $i818d0a07ff3e8f18f9439c08ea8431b6) { $this->d0599b268a5c7f2b3e45cbbb5b50257f[] = $i818d0a07ff3e8f18f9439c08ea8431b6->toArray(); } public function setManualPayment($e4e90a13339d20edaf8fd0a18a9d8c316 = true) { $this->d387629bba1dd9874c84ef83c30e49db3 = $e4e90a13339d20edaf8fd0a18a9d8c316; } public function setCB($baedab81bf3710a73b3d682470c29278 = null, $e468464ff0cc309f42c5e0c9f74df81c6 = null, $d3398c5427983c97e6543231ec2acb99b = null, $d3dd4306fd7b3f27f3d709e0815ccc111 = null, $h70acf741a4eda66f4721d49b5ad17b63 = null) { $this->b8903db432b6dfe4e584866be1b3f4dc = array( "\x6e\x6f\x6d"=>$baedab81bf3710a73b3d682470c29278, "\x6e\x75\x6d"=>$e468464ff0cc309f42c5e0c9f74df81c6, "\x76\x65\x72\x69\x66"=>$d3398c5427983c97e6543231ec2acb99b, "\x6d\x6f\x69\x73"=>$d3dd4306fd7b3f27f3d709e0815ccc111, "\x61\x6e\x6e\x65\x65"=>$h70acf741a4eda66f4721d49b5ad17b63 ); } public function setReturnURL($f543874942f3806d9951a26753e076876) { $this->cae07e3c38ef3923150b2fd68f928547 = $f543874942f3806d9951a26753e076876; } public function getDetails() { $j9e7bffa10d5c846dea1962dfecb95c5c = $this->b9055b168231ef0f808497d55f73c18c($this->h7205ea56d5dc17c92adbb925699c0349.self::b9e52a1467ba36bd6bfb6804638fb406."\x3f".self::f5615c6e40e906d826f0d4d2cb4299b70."\x3d".$this->getSessionID(),array_merge($this->e458ae2847e4d2ebaf354f65597283394(), array("\x6a\x73\x6f\x6e"=>$this->e471ab81ad11c1fcbfe645c678e2fa257(false)))); $bd0386da5c37a51c3f35ff8e45df7eae = json_decode($j9e7bffa10d5c846dea1962dfecb95c5c, true); return $bd0386da5c37a51c3f35ff8e45df7eae===null?$j9e7bffa10d5c846dea1962dfecb95c5c:$bd0386da5c37a51c3f35ff8e45df7eae; } public function validate() { $j9e7bffa10d5c846dea1962dfecb95c5c = $this->b9055b168231ef0f808497d55f73c18c($this->h7205ea56d5dc17c92adbb925699c0349.self::i89a8769074a6356c19c589b65affcee6."\x3f".self::f5615c6e40e906d826f0d4d2cb4299b70."\x3d".$this->getSessionID(),array_merge($this->e458ae2847e4d2ebaf354f65597283394(), array("\x6a\x73\x6f\x6e"=>$this->e471ab81ad11c1fcbfe645c678e2fa257(true)))); $bd0386da5c37a51c3f35ff8e45df7eae = json_decode($j9e7bffa10d5c846dea1962dfecb95c5c, true); return $bd0386da5c37a51c3f35ff8e45df7eae===null?$j9e7bffa10d5c846dea1962dfecb95c5c:$bd0386da5c37a51c3f35ff8e45df7eae; } public function validateForm($b7266b674edddf499b869ce0af854737, $bd0386da5c37a51c3f35ff8e45df7eae) { $j9e7bffa10d5c846dea1962dfecb95c5c = json_encode(array("\x50\x4f\x53\x54"=>$b7266b674edddf499b869ce0af854737, "\x64\x65\x74\x61\x69\x6c\x73"=>$bd0386da5c37a51c3f35ff8e45df7eae)); $j9e7bffa10d5c846dea1962dfecb95c5c = $this->b9055b168231ef0f808497d55f73c18c($this->h7205ea56d5dc17c92adbb925699c0349.self::j9765901a296528bbb20f867f4ebcfb47."\x3f".self::f5615c6e40e906d826f0d4d2cb4299b70."\x3d".$this->getSessionID(),array_merge($this->e458ae2847e4d2ebaf354f65597283394(), array("\x6a\x73\x6f\x6e"=>$j9e7bffa10d5c846dea1962dfecb95c5c))); $bd0386da5c37a51c3f35ff8e45df7eae = json_decode($j9e7bffa10d5c846dea1962dfecb95c5c, true); return $bd0386da5c37a51c3f35ff8e45df7eae===null?$j9e7bffa10d5c846dea1962dfecb95c5c:$bd0386da5c37a51c3f35ff8e45df7eae; } protected function e471ab81ad11c1fcbfe645c678e2fa257($d37a3d02dd05e294729d3ce744b7e7ad6 = false) { return json_encode(array( "\x61\x66\x66\x72\x61\x6e\x63\x68\x69\x73\x73\x65\x6d\x65\x6e\x74\x5f\x69\x64" =>$this->abf7394ee41034194c67529cbed7375a, "\x74\x72\x61\x6e\x73\x70\x6f\x72\x74\x65\x75\x72" =>$this->c2a36b311258617b43fadd025e9ea43a6, "\x66\x61\x63\x74\x75\x72\x61\x74\x69\x6f\x6e" =>$this->i85f08e7f5ada20308b5475176ee52066, "\x63\x6f\x6c\x69\x73" =>$this->d0599b268a5c7f2b3e45cbbb5b50257f, "\x70\x61\x69\x65\x6d\x65\x6e\x74" =>($d37a3d02dd05e294729d3ce744b7e7ad6?$this->b8903db432b6dfe4e584866be1b3f4dc:null), "\x72\x65\x64\x69\x72\x65\x63\x74\x50\x61\x69\x65\x6d\x65\x6e\x74" =>($d37a3d02dd05e294729d3ce744b7e7ad6?$this->d387629bba1dd9874c84ef83c30e49db3:false), "\x72\x65\x74\x75\x72\x6e" =>$this->cae07e3c38ef3923150b2fd68f928547, "\x69\x6e\x66\x6f\x73" =>$this->f596e8e622035aabd22c6addff3d93357 )); } private function eb196103b0b617aea56dfe69e1882d86($j9320ee5a57e311e3c4cc9c6e3cc5eaee) { throw new Exception($j9320ee5a57e311e3c4cc9c6e3cc5eaee); return false; } private function a08a7454609f6f196a20e6ad99283b0e5($f543874942f3806d9951a26753e076876, $d3274a34000f688a26f50179a855060ac) { } private function g6a77380a98320a1b698fecee637c0279() { return $this->a07b11fcb6cf2ddb99b3b7140ba075c97($this->e458ae2847e4d2ebaf354f65597283394()); } private function e458ae2847e4d2ebaf354f65597283394() { $h78f933d2b337e7834fa379e1f7c0849f = array("\x76\x65\x72\x73\x69\x6f\x6e"=>$this->i8c8b55bc021f725acc7fbc23372cd7ba, "\x6c\x6f\x67\x69\x6e"=>$this->i83ada26c99a628c1354f09eebbbfaa60, "\x70\x61\x73\x73\x77\x6f\x72\x64"=>$this->b18d8876a2c4379fd0854985e55823210, "\x6e\x6f\x6d\x4c\x6f\x67\x69\x63\x69\x65\x6c"=> $this->c162a60b810922f00c51f54b66c86115, "\x76\x65\x72\x73\x69\x6f\x6e\x4c\x6f\x67\x69\x63\x69\x65\x6c"=>$this->d67cfda8e3014577593a398bcbdaecef, "\x76\x65\x72\x73\x69\x6f\x6e\x50\x48\x50"=>phpversion()); if ($this->b1dfae95f1953fd05003d5c75c0c6bf44) $h78f933d2b337e7834fa379e1f7c0849f = array_merge($h78f933d2b337e7834fa379e1f7c0849f, array(self::f5615c6e40e906d826f0d4d2cb4299b70=>$this->b1dfae95f1953fd05003d5c75c0c6bf44)); return $h78f933d2b337e7834fa379e1f7c0849f; } private function i886a462d3f79a2940cde309c89fda047() { return (in_array("\x63\x75\x72\x6c", get_loaded_extensions()))?true:false; } private function j91898de452ff0b74f4871f6d20e0fd07($f543874942f3806d9951a26753e076876, $eac73d19593a6f900904182c6e78a958 = false, $fa0af89b8ca5e4bcf1ea00285c7f678a = true, $g6ff46fef756ed0d28b57ef77c244fa7c = false, $b12a91f13f9e4d0a90b9acc8f5675f8f4 = null) { if ($this->i886a462d3f79a2940cde309c89fda047()) return $this->a5bd88ab9fb25746d718cdc1ae8d677b($f543874942f3806d9951a26753e076876, $eac73d19593a6f900904182c6e78a958, $fa0af89b8ca5e4bcf1ea00285c7f678a, $g6ff46fef756ed0d28b57ef77c244fa7c, $b12a91f13f9e4d0a90b9acc8f5675f8f4); else return $this->h7607d9ed6a05ea5f8ddee0c2fc04f753($f543874942f3806d9951a26753e076876, $eac73d19593a6f900904182c6e78a958, $g6ff46fef756ed0d28b57ef77c244fa7c, $b12a91f13f9e4d0a90b9acc8f5675f8f4); } private function b9055b168231ef0f808497d55f73c18c($f543874942f3806d9951a26753e076876, $f9e4de909aa463d00c8a1780640bc00c = null, $eac73d19593a6f900904182c6e78a958 = false, $fa0af89b8ca5e4bcf1ea00285c7f678a = true, $g6ff46fef756ed0d28b57ef77c244fa7c = false, $b12a91f13f9e4d0a90b9acc8f5675f8f4 = null) { if ($this->i886a462d3f79a2940cde309c89fda047()) return $this->a729b71a63ed3ecea7601eff1f91d455($f543874942f3806d9951a26753e076876, $f9e4de909aa463d00c8a1780640bc00c, $eac73d19593a6f900904182c6e78a958, $fa0af89b8ca5e4bcf1ea00285c7f678a, $g6ff46fef756ed0d28b57ef77c244fa7c, $b12a91f13f9e4d0a90b9acc8f5675f8f4); else return $this->j9d4d7e170fd97d2e8a1208f72d3b4971($f543874942f3806d9951a26753e076876, $f9e4de909aa463d00c8a1780640bc00c, $eac73d19593a6f900904182c6e78a958, $g6ff46fef756ed0d28b57ef77c244fa7c, $b12a91f13f9e4d0a90b9acc8f5675f8f4); } private function a5bd88ab9fb25746d718cdc1ae8d677b($f543874942f3806d9951a26753e076876, $i87136d1a5b99300574cfe39bb38796da = false, $fa0af89b8ca5e4bcf1ea00285c7f678a = true, $g6ff46fef756ed0d28b57ef77c244fa7c = false, $b12a91f13f9e4d0a90b9acc8f5675f8f4 = null) { $c25bc2b34b6ca7d0d7a36f5a71d3a66ff = curl_init(); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_URL, $f543874942f3806d9951a26753e076876); if ($g6ff46fef756ed0d28b57ef77c244fa7c !== null) curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_COOKIE, $g6ff46fef756ed0d28b57ef77c244fa7c!==false?$g6ff46fef756ed0d28b57ef77c244fa7c:$this->cookie); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_FOLLOWLOCATION, ini_get("\x73\x61\x66\x65\x5f\x6d\x6f\x64\x65") || ini_get("\x6f\x70\x65\x6e\x5f\x62\x61\x73\x65\x64\x69\x72")?FALSE:TRUE); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_COOKIESESSION, TRUE); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_RETURNTRANSFER, TRUE); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_SSL_VERIFYPEER, $fa0af89b8ca5e4bcf1ea00285c7f678a); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_SSL_VERIFYHOST, 2); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_USERAGENT, self::j9214b3ae2a4070c21af441c011a77d47); if (is_array($i87136d1a5b99300574cfe39bb38796da)) curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_HTTPHEADER, $i87136d1a5b99300574cfe39bb38796da); else curl_setopt ($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_HEADER, $i87136d1a5b99300574cfe39bb38796da?1:0); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_CAINFO, self::e47f61d68bb44c88e2cea0c1022eb3d52); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_REFERER, $b12a91f13f9e4d0a90b9acc8f5675f8f4?$b12a91f13f9e4d0a90b9acc8f5675f8f4:($this->h7205ea56d5dc17c92adbb925699c0349)); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_TIMEOUT_MS, self::d985f3afd84f1b02088e7b363a64624e * 1000); if (self::h7607b0d1ef694e22dc545c55c8382df1) { $d743b5283734c039d980ea2641dce9b8 = fopen(self::d355035c61c08231837016af78c33e57, "\x61\x2b"); curl_setopt ($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_STDERR, $d743b5283734c039d980ea2641dce9b8); curl_setopt ($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_VERBOSE, 1); } $d38e5cdbd31ed11a4b0c1e6d34897dec0 = curl_exec($c25bc2b34b6ca7d0d7a36f5a71d3a66ff); if ($d38e5cdbd31ed11a4b0c1e6d34897dec0 === FALSE) { $this->eb196103b0b617aea56dfe69e1882d86("\x63\x55\x52\x4c\x20\x65\x72\x72\x6f\x72\x3a".curl_error($c25bc2b34b6ca7d0d7a36f5a71d3a66ff)); } curl_close($c25bc2b34b6ca7d0d7a36f5a71d3a66ff); return $d38e5cdbd31ed11a4b0c1e6d34897dec0; } private function a729b71a63ed3ecea7601eff1f91d455($f543874942f3806d9951a26753e076876, $f9e4de909aa463d00c8a1780640bc00c = null, $i87136d1a5b99300574cfe39bb38796da = false, $fa0af89b8ca5e4bcf1ea00285c7f678a = true, $g6ff46fef756ed0d28b57ef77c244fa7c = false, $b12a91f13f9e4d0a90b9acc8f5675f8f4 = null) { $c25bc2b34b6ca7d0d7a36f5a71d3a66ff = curl_init(); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_URL, $f543874942f3806d9951a26753e076876); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_POST, TRUE); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_POSTFIELDS, $this->a07b11fcb6cf2ddb99b3b7140ba075c97($f9e4de909aa463d00c8a1780640bc00c)); if ($g6ff46fef756ed0d28b57ef77c244fa7c !== null) curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_COOKIE, $g6ff46fef756ed0d28b57ef77c244fa7c!==false?$g6ff46fef756ed0d28b57ef77c244fa7c:$this->cookie); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_FOLLOWLOCATION, ini_get("\x73\x61\x66\x65\x5f\x6d\x6f\x64\x65") || ini_get("\x6f\x70\x65\x6e\x5f\x62\x61\x73\x65\x64\x69\x72")?FALSE:TRUE); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_COOKIESESSION, TRUE); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_RETURNTRANSFER, TRUE); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_SSL_VERIFYPEER, $fa0af89b8ca5e4bcf1ea00285c7f678a); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_SSL_VERIFYHOST, 2); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_USERAGENT, self::j9214b3ae2a4070c21af441c011a77d47); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_CAINFO, self::e47f61d68bb44c88e2cea0c1022eb3d52); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_REFERER, $b12a91f13f9e4d0a90b9acc8f5675f8f4?$b12a91f13f9e4d0a90b9acc8f5675f8f4:($this->h7205ea56d5dc17c92adbb925699c0349)); curl_setopt($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_TIMEOUT_MS, self::d985f3afd84f1b02088e7b363a64624e * 1000); curl_setopt ($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_HEADER, $i87136d1a5b99300574cfe39bb38796da?1:0); if (self::h7607b0d1ef694e22dc545c55c8382df1) { $d743b5283734c039d980ea2641dce9b8 = fopen(self::d355035c61c08231837016af78c33e57, "\x61\x2b"); curl_setopt ($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_STDERR, $d743b5283734c039d980ea2641dce9b8); curl_setopt ($c25bc2b34b6ca7d0d7a36f5a71d3a66ff, CURLOPT_VERBOSE, 1); } $d38e5cdbd31ed11a4b0c1e6d34897dec0 = curl_exec($c25bc2b34b6ca7d0d7a36f5a71d3a66ff); if ($d38e5cdbd31ed11a4b0c1e6d34897dec0 === true) $d38e5cdbd31ed11a4b0c1e6d34897dec0 = null; if ($d38e5cdbd31ed11a4b0c1e6d34897dec0 === FALSE) { $this->eb196103b0b617aea56dfe69e1882d86("\x63\x55\x52\x4c\x20\x65\x72\x72\x6f\x72\x3a".curl_error($c25bc2b34b6ca7d0d7a36f5a71d3a66ff)); } curl_close($c25bc2b34b6ca7d0d7a36f5a71d3a66ff); return $d38e5cdbd31ed11a4b0c1e6d34897dec0; } private function h7607d9ed6a05ea5f8ddee0c2fc04f753($f543874942f3806d9951a26753e076876, $eac73d19593a6f900904182c6e78a958 = false, $g6ff46fef756ed0d28b57ef77c244fa7c = false, $b12a91f13f9e4d0a90b9acc8f5675f8f4 = null) { $h71018586fa81026f9ffb3d2bb3ccd9c8 = array( "\x68\x74\x74\x70"=>array( "\x74\x69\x6d\x65\x6f\x75\x74"=> self::d985f3afd84f1b02088e7b363a64624e, "\x6d\x65\x74\x68\x6f\x64"=>"\x47\x45\x54", "\x68\x65\x61\x64\x65\x72"=>"\x41\x63\x63\x65\x70\x74\x2d\x6c\x61\x6e\x67\x75\x61\x67\x65\x3a\x20\x66\x72\x0d\x0a" . "\x43\x6f\x6f\x6b\x69\x65\x3a\x20$g6ff46fef756ed0d28b57ef77c244fa7c\x0d\x0a" ), "\x73\x73\x6c" => array( "\x76\x65\x72\x69\x66\x79\x5f\x70\x65\x65\x72" => TRUE, "\x63\x61\x66\x69\x6c\x65" => self::e47f61d68bb44c88e2cea0c1022eb3d52, "\x76\x65\x72\x69\x66\x79\x5f\x64\x65\x70\x74\x68" => 5), ); $a0a3dc5c64b3f827efe7d527db1a7df6e = stream_context_create($h71018586fa81026f9ffb3d2bb3ccd9c8); $h78f933d2b337e7834fa379e1f7c0849f = @file_get_contents($f543874942f3806d9951a26753e076876, false, $a0a3dc5c64b3f827efe7d527db1a7df6e); if ($h78f933d2b337e7834fa379e1f7c0849f === FALSE) throw new Exception("\x49\x6d\x70\x6f\x73\x73\x69\x62\x6c\x65\x20\x64\x27\xc3\xa9\x74\x61\x62\x6c\x69\x72\x20\x6c\x61\x20\x63\x6f\x6e\x6e\x65\x78\x69\x6f\x6e".($this->b9dc70e1bdc4668107f7fd2b5278c23b?"\x20\x73\x65\x63\x75\x72\x69\x73\x65":"")."\x20\x64\x65\x20\x74\x72\x61\x6e\x73\x66\x65\x72\x74\x20\x64\x65\x20\x64\x6f\x6e\x6e\xc3\xa9\x65\x73"); if ($eac73d19593a6f900904182c6e78a958) return implode("\x0a", $c653dcc773dc87565452174fa864371a).$h78f933d2b337e7834fa379e1f7c0849f; else return $h78f933d2b337e7834fa379e1f7c0849f; } private function j9d4d7e170fd97d2e8a1208f72d3b4971($f543874942f3806d9951a26753e076876, $f9e4de909aa463d00c8a1780640bc00c = null, $eac73d19593a6f900904182c6e78a958 = false, $g6ff46fef756ed0d28b57ef77c244fa7c = false, $b12a91f13f9e4d0a90b9acc8f5675f8f4 = null) { $h71018586fa81026f9ffb3d2bb3ccd9c8 = array("\x68\x74\x74\x70" => array( "\x74\x69\x6d\x65\x6f\x75\x74"=> self::d985f3afd84f1b02088e7b363a64624e, "\x6d\x65\x74\x68\x6f\x64" => "\x50\x4f\x53\x54", "\x68\x65\x61\x64\x65\x72" => "\x43\x6f\x6e\x74\x65\x6e\x74\x2d\x74\x79\x70\x65\x3a\x20\x61\x70\x70\x6c\x69\x63\x61\x74\x69\x6f\x6e\x2f\x78\x2d\x77\x77\x77\x2d\x66\x6f\x72\x6d\x2d\x75\x72\x6c\x65\x6e\x63\x6f\x64\x65\x64\x0d\x0a". "\x43\x6f\x6f\x6b\x69\x65\x3a\x20$g6ff46fef756ed0d28b57ef77c244fa7c\x0d\x0a", "\x63\x6f\x6e\x74\x65\x6e\x74" => $this->a07b11fcb6cf2ddb99b3b7140ba075c97($f9e4de909aa463d00c8a1780640bc00c) ), "\x73\x73\x6c" => array( "\x76\x65\x72\x69\x66\x79\x5f\x70\x65\x65\x72" => TRUE, "\x63\x61\x66\x69\x6c\x65" => self::e47f61d68bb44c88e2cea0c1022eb3d52, "\x76\x65\x72\x69\x66\x79\x5f\x64\x65\x70\x74\x68" => 5) ); $a0a3dc5c64b3f827efe7d527db1a7df6e = stream_context_create($h71018586fa81026f9ffb3d2bb3ccd9c8); $h78f933d2b337e7834fa379e1f7c0849f = @file_get_contents($f543874942f3806d9951a26753e076876, false, $a0a3dc5c64b3f827efe7d527db1a7df6e); if ($h78f933d2b337e7834fa379e1f7c0849f === FALSE) throw new Exception("\x49\x6d\x70\x6f\x73\x73\x69\x62\x6c\x65\x20\x64\x27\xc3\xa9\x74\x61\x62\x6c\x69\x72\x20\x6c\x61\x20\x63\x6f\x6e\x6e\x65\x78\x69\x6f\x6e".($this->b9dc70e1bdc4668107f7fd2b5278c23b?"\x20\x73\x65\x63\x75\x72\x69\x73\x65":"")."\x20\x64\x65\x20\x74\x72\x61\x6e\x73\x66\x65\x72\x74\x20\x64\x65\x20\x64\x6f\x6e\x6e\xc3\xa9\x65\x73"); if ($eac73d19593a6f900904182c6e78a958) return implode("\x0a", $c653dcc773dc87565452174fa864371a).$h78f933d2b337e7834fa379e1f7c0849f; else return $h78f933d2b337e7834fa379e1f7c0849f; } private function a07b11fcb6cf2ddb99b3b7140ba075c97($a03908463a42bada2d3dfe7cc389e32e9) { if (is_array($a03908463a42bada2d3dfe7cc389e32e9)) { $h7d774b8643f95a1af755057c7b9be8aa = array(); foreach ($a03908463a42bada2d3dfe7cc389e32e9 as $d3b91605096cea244a9e2d3e118bbba5a => $eb2c85bf2973802a858eba7d06ee0ad4) { if (is_array($eb2c85bf2973802a858eba7d06ee0ad4)) foreach ($eb2c85bf2973802a858eba7d06ee0ad4 as $a016044c9f59e72ed2dad726d44ad7e89) $h7d774b8643f95a1af755057c7b9be8aa[] = $d3b91605096cea244a9e2d3e118bbba5a . "\x5b\x5d\x3d" . urlencode($a016044c9f59e72ed2dad726d44ad7e89); else $h7d774b8643f95a1af755057c7b9be8aa[] = $d3b91605096cea244a9e2d3e118bbba5a . "\x3d" . urlencode($eb2c85bf2973802a858eba7d06ee0ad4); } return implode("\x26", $h7d774b8643f95a1af755057c7b9be8aa); } else return $a03908463a42bada2d3dfe7cc389e32e9; } private function b1e00349d0a84e95ed8e72254a276f92a() { $this->b1dfae95f1953fd05003d5c75c0c6bf44 = md5(time().microtime().rand().$this->i83ada26c99a628c1354f09eebbbfaa60.$this->b18d8876a2c4379fd0854985e55823210.$this->i8c8b55bc021f725acc7fbc23372cd7ba); return $this->b1dfae95f1953fd05003d5c75c0c6bf44; } public function getSessionID() { if ($this->b1dfae95f1953fd05003d5c75c0c6bf44) return $this->b1dfae95f1953fd05003d5c75c0c6bf44; else return $this->b1e00349d0a84e95ed8e72254a276f92a(); } public function setSessionID($b1dfae95f1953fd05003d5c75c0c6bf44) { if (preg_match("\x23\x5b\x61\x2d\x7a\x41\x2d\x5a\x30\x2d\x39\x5d\x23", $b1dfae95f1953fd05003d5c75c0c6bf44)) { $this->b1dfae95f1953fd05003d5c75c0c6bf44 = $b1dfae95f1953fd05003d5c75c0c6bf44; return true; } else return false; } public function getAffranchissementID() { if ($this->abf7394ee41034194c67529cbed7375a) return $this->abf7394ee41034194c67529cbed7375a; } public function setAffranchissementID($abf7394ee41034194c67529cbed7375a) { $this->abf7394ee41034194c67529cbed7375a = $abf7394ee41034194c67529cbed7375a; } public function addInfos($d3b91605096cea244a9e2d3e118bbba5a, $eb2c85bf2973802a858eba7d06ee0ad4) { $this->f596e8e622035aabd22c6addff3d93357[$d3b91605096cea244a9e2d3e118bbba5a] = $eb2c85bf2973802a858eba7d06ee0ad4; } public function getInfos() { return $this->f596e8e622035aabd22c6addff3d93357; } public function resetInfos() { $this->f596e8e622035aabd22c6addff3d93357 = array(); } public function setNoBL($b19a26ef35a4635d53fe58545a0011f5e = true) { $this->addInfos("\x6e\x6f\x5f\x62\x6c", $b19a26ef35a4635d53fe58545a0011f5e?true:false); } public function setInsuranceSalesPrice($b19a26ef35a4635d53fe58545a0011f5e = true) { $this->addInfos("\x61\x73\x73\x75\x72\x61\x6e\x63\x65\x5f\x70\x72\x69\x78\x5f\x76\x65\x6e\x74\x65", $b19a26ef35a4635d53fe58545a0011f5e?true:false); } } ?>
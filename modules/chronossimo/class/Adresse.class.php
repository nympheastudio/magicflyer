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
class Adresse { const b1bb1a887c2efff67858499b261df4e3b = 1; protected $c23f1d3313f5d4785c82b8ddc276b0985 = "\x6d\x72"; protected $baedab81bf3710a73b3d682470c29278; protected $b1e6415413d59b68cbdf2656be51b58fa; protected $a0ff211e991fb8a5d17e328b6b897591b; protected $cd48a83430b91b7beb2f129216ae966f; protected $efa033de75643b4575c3182a32bda9c9; protected $b15b43229a15b324991dcfbbcea927b1e; protected $b13714d92fc3457c3fc5788f643ce8603; protected $c2e244cef7046465cfbbadfe7bbd68dad; protected $b1d9c2f708e66129871428fb859d7bc44; protected $g68471dcf21986715cb7b22488b2cecd7; protected $c2d8386e995e05103aeb266fdb537fa0d; protected $c1c3b3f186a5ab7f622da70515185f24; protected $c2c887eeec6ba14815e8faefead759dc1; protected $daee4ee043dd2dd07d7502dca4446ca4; protected $ecb6bd151ecfc5fb8b492e2a68b1a43b; protected $f9f87fbae28e831d1c08358ccf8ee7b0; protected $h7488afb0a75d601155b58ef4a2e57cb0 = true; protected $j952909c96093cfc83755c73a741a8d4e = false; function __construct($a03908463a42bada2d3dfe7cc389e32e9 = null, $h7488afb0a75d601155b58ef4a2e57cb0 = false, $j952909c96093cfc83755c73a741a8d4e = false) { if (is_array($a03908463a42bada2d3dfe7cc389e32e9)) $this->array_import($a03908463a42bada2d3dfe7cc389e32e9); $this->h7488afb0a75d601155b58ef4a2e57cb0 = $h7488afb0a75d601155b58ef4a2e57cb0; $this->j952909c96093cfc83755c73a741a8d4e = $j952909c96093cfc83755c73a741a8d4e; } public function setCivilite($c23f1d3313f5d4785c82b8ddc276b0985) { $c23f1d3313f5d4785c82b8ddc276b0985 = strtolower($c23f1d3313f5d4785c82b8ddc276b0985); if (in_array($c23f1d3313f5d4785c82b8ddc276b0985, array("\x6d\x72", "\x6d", "\x68", "\x68\x6f\x6d\x6d\x65", "\x6d\x6f\x6e\x73\x69\x65\x75\x72", "\x73\x69\x72", 1))) $this->c23f1d3313f5d4785c82b8ddc276b0985 = "\x6d\x72"; if (in_array($c23f1d3313f5d4785c82b8ddc276b0985, array("\x6d\x6d\x65", "\x6d\x6d", "\x66", "\x66\x65\x6d\x6d\x65", "\x6d\x61\x64\x61\x6d\x65", 2))) $this->c23f1d3313f5d4785c82b8ddc276b0985 = "\x6d\x6d\x65"; if (in_array($c23f1d3313f5d4785c82b8ddc276b0985, array("\x6d\x6c\x65", "\x6d\x6c\x6c\x65", "\x6d\x61\x64\x65\x6d\x6f\x69\x73\x65\x6c\x6c\x65", 3))) $this->c23f1d3313f5d4785c82b8ddc276b0985 = "\x6d\x6c\x65"; } public function setNom($baedab81bf3710a73b3d682470c29278) { $this->baedab81bf3710a73b3d682470c29278 = $this->ee4e4ea9dad3aad86d203d6d15617e8e($baedab81bf3710a73b3d682470c29278, 20); } public function setPrenom($b1e6415413d59b68cbdf2656be51b58fa) { $this->b1e6415413d59b68cbdf2656be51b58fa = $this->ee4e4ea9dad3aad86d203d6d15617e8e($b1e6415413d59b68cbdf2656be51b58fa, 15); } public function setRaisonsoc($a0ff211e991fb8a5d17e328b6b897591b) { $this->a0ff211e991fb8a5d17e328b6b897591b = $this->ee4e4ea9dad3aad86d203d6d15617e8e($a0ff211e991fb8a5d17e328b6b897591b, 35); } public function setSiret($cd48a83430b91b7beb2f129216ae966f) { if ($this->d8bfefc6ce101661664c400b4e998b81($cd48a83430b91b7beb2f129216ae966f) == 0) $this->cd48a83430b91b7beb2f129216ae966f = preg_replace("\x23\x5b\x5e\x30\x2d\x39\x5d\x2b\x23", "", $cd48a83430b91b7beb2f129216ae966f); else $this->eb196103b0b617aea56dfe69e1882d86("\x4e\x75\x6d\xc3\xa9\x72\x6f\x20\x64\x65\x20\x73\x69\x72\x65\x74\x20\x69\x6e\x63\x6f\x6d\x70\x6c\x65\x74\x20\x6f\x75\x20\x69\x6e\x76\x61\x6c\x69\x64\x65"); } public function setAdresse1($efa033de75643b4575c3182a32bda9c9) { $this->efa033de75643b4575c3182a32bda9c9 = $this->ee4e4ea9dad3aad86d203d6d15617e8e($efa033de75643b4575c3182a32bda9c9); } public function setAdresse2($b15b43229a15b324991dcfbbcea927b1e) { $this->b15b43229a15b324991dcfbbcea927b1e = $this->ee4e4ea9dad3aad86d203d6d15617e8e($b15b43229a15b324991dcfbbcea927b1e); } public function setAdresse3($b13714d92fc3457c3fc5788f643ce8603) { $this->b13714d92fc3457c3fc5788f643ce8603 = $this->ee4e4ea9dad3aad86d203d6d15617e8e($b13714d92fc3457c3fc5788f643ce8603); } public function setAdresse4($c2e244cef7046465cfbbadfe7bbd68dad) { $this->c2e244cef7046465cfbbadfe7bbd68dad = $this->ee4e4ea9dad3aad86d203d6d15617e8e($c2e244cef7046465cfbbadfe7bbd68dad); } public function setComplements($b1d9c2f708e66129871428fb859d7bc44) { $this->b1d9c2f708e66129871428fb859d7bc44 = $this->ee4e4ea9dad3aad86d203d6d15617e8e($b1d9c2f708e66129871428fb859d7bc44); } public function setCp($g68471dcf21986715cb7b22488b2cecd7) { $this->g68471dcf21986715cb7b22488b2cecd7 = trim(str_replace("\x20", "", $this->ee4e4ea9dad3aad86d203d6d15617e8e($g68471dcf21986715cb7b22488b2cecd7))); } public function setVille($c2d8386e995e05103aeb266fdb537fa0d) { $this->c2d8386e995e05103aeb266fdb537fa0d = $this->ee4e4ea9dad3aad86d203d6d15617e8e($c2d8386e995e05103aeb266fdb537fa0d); } public function setPays($c1c3b3f186a5ab7f622da70515185f24) { $this->c1c3b3f186a5ab7f622da70515185f24 = $this->ee4e4ea9dad3aad86d203d6d15617e8e($c1c3b3f186a5ab7f622da70515185f24); } public function setFixe($c2c887eeec6ba14815e8faefead759dc1) { $this->c2c887eeec6ba14815e8faefead759dc1 = $this->ee4e4ea9dad3aad86d203d6d15617e8e($c2c887eeec6ba14815e8faefead759dc1, 15); } public function setPortable($daee4ee043dd2dd07d7502dca4446ca4) { $this->daee4ee043dd2dd07d7502dca4446ca4 = $this->ee4e4ea9dad3aad86d203d6d15617e8e($daee4ee043dd2dd07d7502dca4446ca4, 15); } public function setEmail($ecb6bd151ecfc5fb8b492e2a68b1a43b) { if ($this->j92833bbaafd830fdf27fc082577b06af($ecb6bd151ecfc5fb8b492e2a68b1a43b)) { $this->ecb6bd151ecfc5fb8b492e2a68b1a43b = trim($ecb6bd151ecfc5fb8b492e2a68b1a43b); $this->f9f87fbae28e831d1c08358ccf8ee7b0 = trim($ecb6bd151ecfc5fb8b492e2a68b1a43b); } else $this->eb196103b0b617aea56dfe69e1882d86("\x41\x64\x72\x65\x73\x73\x65\x20\x65\x6d\x61\x69\x6c\x20\x69\x6e\x63\x6f\x6d\x70\x6c\xc3\xa8\x74\x65\x20\x6f\x75\x20\x69\x6e\x76\x61\x6c\x69\x64\x65"); } public function getNom() { return $this->baedab81bf3710a73b3d682470c29278; } public function getPrenom() { return $this->b1e6415413d59b68cbdf2656be51b58fa; } public function getRaisonsoc() { return $this->a0ff211e991fb8a5d17e328b6b897591b; } public function getSiret() { return $this->cd48a83430b91b7beb2f129216ae966f; } public function getAdresse1() { return $this->efa033de75643b4575c3182a32bda9c9; } public function getAdresse2() { return $this->b15b43229a15b324991dcfbbcea927b1e; } public function getAdresse3() { return $this->b13714d92fc3457c3fc5788f643ce8603; } public function getAdresse4() { return $this->c2e244cef7046465cfbbadfe7bbd68dad; } public function getComplements() { return $this->b1d9c2f708e66129871428fb859d7bc44; } public function getCp() { return $this->g68471dcf21986715cb7b22488b2cecd7; } public function getVille() { return $this->c2d8386e995e05103aeb266fdb537fa0d; } public function getPays() { return $this->c1c3b3f186a5ab7f622da70515185f24; } public function getFixe() { return $this->c2c887eeec6ba14815e8faefead759dc1; } public function getPortable() { return $this->daee4ee043dd2dd07d7502dca4446ca4; } public function getEmail() { return $this->ecb6bd151ecfc5fb8b492e2a68b1a43b; } public function __toString() { return $this->c23f1d3313f5d4785c82b8ddc276b0985."\x0a". $this->b1e6415413d59b68cbdf2656be51b58fa."\x20".$this->baedab81bf3710a73b3d682470c29278."\x0a". $this->a0ff211e991fb8a5d17e328b6b897591b."\x0a". $this->cd48a83430b91b7beb2f129216ae966f."\x0a". $this->efa033de75643b4575c3182a32bda9c9."\x0a". $this->b15b43229a15b324991dcfbbcea927b1e."\x0a". $this->b13714d92fc3457c3fc5788f643ce8603."\x0a". $this->c2e244cef7046465cfbbadfe7bbd68dad."\x0a". $this->g68471dcf21986715cb7b22488b2cecd7."\x0a". $this->c2d8386e995e05103aeb266fdb537fa0d."\x0a". $this->c1c3b3f186a5ab7f622da70515185f24."\x0a". $this->daee4ee043dd2dd07d7502dca4446ca4."\x0a". $this->ecb6bd151ecfc5fb8b492e2a68b1a43b; } public function toArray($a04ded5bdd5a4665c7683a43f2ed8a9c9 = false) { $this->f5dbf1e2177d40cb54d99f3651b7b558e(); if (!$a04ded5bdd5a4665c7683a43f2ed8a9c9 && !$this->checkAdresse()) return $this->eb196103b0b617aea56dfe69e1882d86("\x41\x64\x72\x65\x73\x73\x65\x20\x69\x6e\x76\x61\x6c\x69\x64\x65"); return array( "\x63\x69\x76\x69\x6c\x69\x74\x65" => $this->c23f1d3313f5d4785c82b8ddc276b0985, "\x70\x72\x65\x6e\x6f\x6d" => $this->b1e6415413d59b68cbdf2656be51b58fa, "\x6e\x6f\x6d" => $this->baedab81bf3710a73b3d682470c29278, "\x72\x61\x69\x73\x6f\x6e\x73\x6f\x63" => $this->a0ff211e991fb8a5d17e328b6b897591b, "\x73\x69\x72\x65\x74" => $this->cd48a83430b91b7beb2f129216ae966f, "\x61\x64\x72\x65\x73\x73\x65\x31" => $this->efa033de75643b4575c3182a32bda9c9, "\x61\x64\x72\x65\x73\x73\x65\x32" => $this->b15b43229a15b324991dcfbbcea927b1e, "\x61\x64\x72\x65\x73\x73\x65\x33" => $this->b13714d92fc3457c3fc5788f643ce8603, "\x61\x64\x72\x65\x73\x73\x65\x34" => $this->c2e244cef7046465cfbbadfe7bbd68dad, "\x63\x6f\x6d\x70\x6c\x65\x6d\x65\x6e\x74\x73" => $this->b1d9c2f708e66129871428fb859d7bc44, "\x63\x70" => $this->g68471dcf21986715cb7b22488b2cecd7, "\x76\x69\x6c\x6c\x65\x63\x69\x74\x79\x63\x6f\x6d\x62\x6f" => $this->c2d8386e995e05103aeb266fdb537fa0d, "\x70\x61\x79\x73" => $this->c1c3b3f186a5ab7f622da70515185f24, "\x66\x69\x78\x65" => $this->c2c887eeec6ba14815e8faefead759dc1, "\x70\x6f\x72\x74\x61\x62\x6c\x65" => $this->daee4ee043dd2dd07d7502dca4446ca4, "\x65\x6d\x61\x69\x6c" => $this->ecb6bd151ecfc5fb8b492e2a68b1a43b, ); } public function array_import($h78f933d2b337e7834fa379e1f7c0849f) { if (is_array($h78f933d2b337e7834fa379e1f7c0849f)) { $this->setCivilite($h78f933d2b337e7834fa379e1f7c0849f["\x63\x69\x76\x69\x6c\x69\x74\x65"]); $this->setPrenom($h78f933d2b337e7834fa379e1f7c0849f["\x70\x72\x65\x6e\x6f\x6d"]); $this->setNom($h78f933d2b337e7834fa379e1f7c0849f["\x6e\x6f\x6d"]); $this->setRaisonsoc($h78f933d2b337e7834fa379e1f7c0849f["\x72\x61\x69\x73\x6f\x6e\x73\x6f\x63"]); if ($h78f933d2b337e7834fa379e1f7c0849f["\x73\x69\x72\x65\x74"]) $this->setSiret($h78f933d2b337e7834fa379e1f7c0849f["\x73\x69\x72\x65\x74"]); $this->setAdresse1($h78f933d2b337e7834fa379e1f7c0849f["\x61\x64\x72\x65\x73\x73\x65\x31"]); $this->setAdresse2($h78f933d2b337e7834fa379e1f7c0849f["\x61\x64\x72\x65\x73\x73\x65\x32"]); $this->setAdresse3($h78f933d2b337e7834fa379e1f7c0849f["\x61\x64\x72\x65\x73\x73\x65\x33"]); $this->setAdresse4($h78f933d2b337e7834fa379e1f7c0849f["\x61\x64\x72\x65\x73\x73\x65\x34"]); $this->setComplements($h78f933d2b337e7834fa379e1f7c0849f["\x63\x6f\x6d\x70\x6c\x65\x6d\x65\x6e\x74\x73"]); $this->setCp($h78f933d2b337e7834fa379e1f7c0849f["\x63\x70"]); $this->setVille($h78f933d2b337e7834fa379e1f7c0849f["\x76\x69\x6c\x6c\x65\x63\x69\x74\x79\x63\x6f\x6d\x62\x6f"]); $this->setPays($h78f933d2b337e7834fa379e1f7c0849f["\x70\x61\x79\x73"]); $this->setPortable($h78f933d2b337e7834fa379e1f7c0849f["\x70\x6f\x72\x74\x61\x62\x6c\x65"]); $this->setFixe($h78f933d2b337e7834fa379e1f7c0849f["\x66\x69\x78\x65"]); if ($h78f933d2b337e7834fa379e1f7c0849f["\x65\x6d\x61\x69\x6c"]) $this->setEmail($h78f933d2b337e7834fa379e1f7c0849f["\x65\x6d\x61\x69\x6c"]); $this->f5dbf1e2177d40cb54d99f3651b7b558e(); } } public function getNumeroAdresse() { foreach (array($this->efa033de75643b4575c3182a32bda9c9, $this->b15b43229a15b324991dcfbbcea927b1e, $this->b13714d92fc3457c3fc5788f643ce8603, $this->c2e244cef7046465cfbbadfe7bbd68dad) as $c7de88f16e3c73d11841f5083f774ff0) { if (preg_match("\x23\x5e\x28\x5b\x30\x2d\x39\x5d\x2b\x29\x23", trim($c7de88f16e3c73d11841f5083f774ff0), $d3c75ccc1aba17760ca63b6575852f3d6)) { if ($d3c75ccc1aba17760ca63b6575852f3d6[1] != $this->g68471dcf21986715cb7b22488b2cecd7) return $d3c75ccc1aba17760ca63b6575852f3d6[1]; } elseif (preg_match("\x23\x28\x5b\x30\x2d\x39\x5d\x2b\x29\x24\x23", trim($c7de88f16e3c73d11841f5083f774ff0), $d3c75ccc1aba17760ca63b6575852f3d6)) { if ($d3c75ccc1aba17760ca63b6575852f3d6[1] != $this->g68471dcf21986715cb7b22488b2cecd7) return $d3c75ccc1aba17760ca63b6575852f3d6[1]; } } return null; } public function getRueAdresse() { foreach (array($this->efa033de75643b4575c3182a32bda9c9, $this->b15b43229a15b324991dcfbbcea927b1e, $this->b13714d92fc3457c3fc5788f643ce8603, $this->c2e244cef7046465cfbbadfe7bbd68dad) as $c7de88f16e3c73d11841f5083f774ff0) { if (preg_match("\x23\x5e\x5b\x30\x2d\x39\x5d\x2b\x28\x2e\x2b\x29\x23", trim($c7de88f16e3c73d11841f5083f774ff0), $d3c75ccc1aba17760ca63b6575852f3d6)) { if (trim(strtolower($d3c75ccc1aba17760ca63b6575852f3d6[1])) != trim(strtolower($this->c2d8386e995e05103aeb266fdb537fa0d))) return trim($d3c75ccc1aba17760ca63b6575852f3d6[1]); } elseif(preg_match("\x23\x28\x2e\x2b\x3f\x29\x5b\x30\x2d\x39\x5d\x2b\x24\x23", trim($c7de88f16e3c73d11841f5083f774ff0), $d3c75ccc1aba17760ca63b6575852f3d6)) { if (trim(strtolower($d3c75ccc1aba17760ca63b6575852f3d6[1])) != trim(strtolower($this->c2d8386e995e05103aeb266fdb537fa0d))) return trim($d3c75ccc1aba17760ca63b6575852f3d6[1]); } } return $this->efa033de75643b4575c3182a32bda9c9; } public function getComplementsAdresseArray() { $a03908463a42bada2d3dfe7cc389e32e9 = array(); foreach (array($this->efa033de75643b4575c3182a32bda9c9, $this->b15b43229a15b324991dcfbbcea927b1e, $this->b13714d92fc3457c3fc5788f643ce8603, $this->c2e244cef7046465cfbbadfe7bbd68dad) as $c7de88f16e3c73d11841f5083f774ff0) { if (!empty($c7de88f16e3c73d11841f5083f774ff0) && strrpos($c7de88f16e3c73d11841f5083f774ff0, $this->getNumeroAdresse()) === false && strrpos($c7de88f16e3c73d11841f5083f774ff0, $this->getRueAdresse()) === false) $a03908463a42bada2d3dfe7cc389e32e9[] = $c7de88f16e3c73d11841f5083f774ff0; } return $a03908463a42bada2d3dfe7cc389e32e9; } public function checkAdresse() { if (!$this->a22596b26feb1f2a8fc315c0db6f70e9($this->baedab81bf3710a73b3d682470c29278, 20)) return false; if (!$this->a22596b26feb1f2a8fc315c0db6f70e9($this->b1e6415413d59b68cbdf2656be51b58fa, 15)) return false; if (!$this->a22596b26feb1f2a8fc315c0db6f70e9($this->b1e6415413d59b68cbdf2656be51b58fa, 15)) return false; if (!$this->a22596b26feb1f2a8fc315c0db6f70e9($this->a0ff211e991fb8a5d17e328b6b897591b, 35, 0)) return false; if (!$this->a22596b26feb1f2a8fc315c0db6f70e9($this->daee4ee043dd2dd07d7502dca4446ca4, 15, 0)) return false; if (!$this->g68471dcf21986715cb7b22488b2cecd7) return false; if (!$this->a22596b26feb1f2a8fc315c0db6f70e9($this->c2d8386e995e05103aeb266fdb537fa0d)) return false; return true; } private function f5dbf1e2177d40cb54d99f3651b7b558e() { if ($this->h7488afb0a75d601155b58ef4a2e57cb0) { if (empty($this->baedab81bf3710a73b3d682470c29278) xor empty($this->b1e6415413d59b68cbdf2656be51b58fa)) { $baedab81bf3710a73b3d682470c29278 = empty($this->baedab81bf3710a73b3d682470c29278)?$this->b1e6415413d59b68cbdf2656be51b58fa:$this->baedab81bf3710a73b3d682470c29278; list($this->b1e6415413d59b68cbdf2656be51b58fa, $this->baedab81bf3710a73b3d682470c29278) = explode("\x20", $baedab81bf3710a73b3d682470c29278, 2); } $cf4d0c398c1dcb38811fbe4a1d65c128 = array(&$this->efa033de75643b4575c3182a32bda9c9, &$this->b15b43229a15b324991dcfbbcea927b1e, &$this->b13714d92fc3457c3fc5788f643ce8603, &$this->c2e244cef7046465cfbbadfe7bbd68dad); for($b116036a7de5ed04cba6d634f46430842=0; $b116036a7de5ed04cba6d634f46430842<3;$b116036a7de5ed04cba6d634f46430842++) { if (strlen(trim($cf4d0c398c1dcb38811fbe4a1d65c128[$b116036a7de5ed04cba6d634f46430842])) == 0) { for($e48914b025ad41aa03e39c2327fbd18fa=$b116036a7de5ed04cba6d634f46430842; $e48914b025ad41aa03e39c2327fbd18fa<4;$e48914b025ad41aa03e39c2327fbd18fa++) { if (strlen(trim($cf4d0c398c1dcb38811fbe4a1d65c128[$e48914b025ad41aa03e39c2327fbd18fa])) != 0) $cf4d0c398c1dcb38811fbe4a1d65c128[$b116036a7de5ed04cba6d634f46430842] = $cf4d0c398c1dcb38811fbe4a1d65c128[$e48914b025ad41aa03e39c2327fbd18fa]; $cf4d0c398c1dcb38811fbe4a1d65c128[$e48914b025ad41aa03e39c2327fbd18fa] = null; } } } } } private function a22596b26feb1f2a8fc315c0db6f70e9($h7c6597dd0cd2a028371d5f707ce9079a, $f858c2cce382e6647fb97bf020a8f5ec = 255, $f12ad7604d1ab1b7fb8113d2f1168c32 = self::b1bb1a887c2efff67858499b261df4e3b) { if (!$this->j952909c96093cfc83755c73a741a8d4e) return (strlen($h7c6597dd0cd2a028371d5f707ce9079a) >= $f12ad7604d1ab1b7fb8113d2f1168c32); if (!mb_check_encoding($h7c6597dd0cd2a028371d5f707ce9079a, "\x55\x54\x46\x2d\x38")) $h7c6597dd0cd2a028371d5f707ce9079a = utf8_encode($h7c6597dd0cd2a028371d5f707ce9079a); return preg_match("\x23\x5e\x5b\x61\x2d\x7a\x41\x2d\x5a\x30\x2d\x39\x20\x5c\x27\x5c\x2d\x22\x2b\xc2\xb0\x2c\x2e\x5f\x2f\x3a\x40\xc3\x80\xc3\xa0\xc3\xa2\xc3\x82\xc3\xa7\xc3\x88\xc3\xa8\xc3\x89\xc3\xa9\xc3\x8a\xc3\xaa\xc3\x8e\xc3\xae\xc3\x94\xc3\xb4\xc3\xb9\xc3\xbb\x5d\x7b".$f12ad7604d1ab1b7fb8113d2f1168c32."\x2c".$f858c2cce382e6647fb97bf020a8f5ec."\x7d\x24\x23\x69\x75", $h7c6597dd0cd2a028371d5f707ce9079a); } private function ee4e4ea9dad3aad86d203d6d15617e8e($h7c6597dd0cd2a028371d5f707ce9079a, $f858c2cce382e6647fb97bf020a8f5ec = null) { if ($this->h7488afb0a75d601155b58ef4a2e57cb0) { if (!mb_check_encoding($h7c6597dd0cd2a028371d5f707ce9079a, "\x55\x54\x46\x2d\x38")) $h7c6597dd0cd2a028371d5f707ce9079a = utf8_encode($h7c6597dd0cd2a028371d5f707ce9079a); $h7c6597dd0cd2a028371d5f707ce9079a = preg_replace("\x23\x5b\x5e\x61\x2d\x7a\x41\x2d\x5a\x30\x2d\x39\x20\x5c\x27\x5c\x2d\x22\x2b\xc2\xb0\x2c\x2e\x5f\x2f\x3a\x40\xc3\x80\xc3\xa0\xc3\xa2\xc3\x82\xc3\xa7\xc3\x88\xc3\xa8\xc3\x89\xc3\xa9\xc3\x8a\xc3\xaa\xc3\x8e\xc3\xae\xc3\x94\xc3\xb4\xc3\xb9\xc3\xbb\x5d\x23\x75", "", $h7c6597dd0cd2a028371d5f707ce9079a); } if ($f858c2cce382e6647fb97bf020a8f5ec === null || !$this->h7488afb0a75d601155b58ef4a2e57cb0) return $h7c6597dd0cd2a028371d5f707ce9079a; else return substr($h7c6597dd0cd2a028371d5f707ce9079a, 0, $f858c2cce382e6647fb97bf020a8f5ec); } private function j92833bbaafd830fdf27fc082577b06af($ecb6bd151ecfc5fb8b492e2a68b1a43b) { $ecb6bd151ecfc5fb8b492e2a68b1a43b = trim($ecb6bd151ecfc5fb8b492e2a68b1a43b); if (!preg_match("\x23\x5e\x5b\x5e\x40\x5d\x7b\x31\x2c\x36\x34\x7d\x40\x5b\x5e\x40\x5d\x7b\x31\x2c\x32\x35\x35\x7d\x24\x23", $ecb6bd151ecfc5fb8b492e2a68b1a43b)) { return false; } $g6d50680557949d676d4480197d15fa0f = explode("\x40", $ecb6bd151ecfc5fb8b492e2a68b1a43b); $c2a786b940407afb391b6a500121d8e2a = explode("\x2e", $g6d50680557949d676d4480197d15fa0f[0]); for ($b116036a7de5ed04cba6d634f46430842 = 0; $b116036a7de5ed04cba6d634f46430842 < sizeof($c2a786b940407afb391b6a500121d8e2a); $b116036a7de5ed04cba6d634f46430842++) { if (!preg_match("\x40\x5e\x28\x28\x5b\x41\x2d\x5a\x61\x2d\x7a\x30\x2d\x39\x21\x23\x24\x25\x26\x27\x2a\x2b\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d\x7e\x2d\x5d\x5b\x41\x2d\x5a\x61\x2d\x7a\x30\x2d\x39\x21\x23\x24\x25\x26\xa\x9\xe2\x86\xaa\x27\x2a\x2b\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d\x7e\x5c\x2e\x2d\x5d\x7b\x30\x2c\x36\x33\x7d\x29\x7c\x28\x22\x5b\x5e\x28\x5c\x5c\x7c\x22\x29\x5d\x7b\x30\x2c\x36\x32\x7d\x22\x29\x29\x24\x40", $c2a786b940407afb391b6a500121d8e2a[$b116036a7de5ed04cba6d634f46430842])) { return false; } } if (!preg_match("\x23\x5e\x5c\x5b\x3f\x5b\x30\x2d\x39\x5c\x2e\x5d\x2b\x5c\x5d\x3f\x24\x23", $g6d50680557949d676d4480197d15fa0f[1])) { $d735761818463645a93b1faa6552a73a = explode("\x2e", $g6d50680557949d676d4480197d15fa0f[1]); if (sizeof($d735761818463645a93b1faa6552a73a) < 2) { return false; } for ($b116036a7de5ed04cba6d634f46430842 = 0; $b116036a7de5ed04cba6d634f46430842 < sizeof($d735761818463645a93b1faa6552a73a); $b116036a7de5ed04cba6d634f46430842++) { if (!preg_match("\x23\x5e\x28\x28\x5b\x41\x2d\x5a\x61\x2d\x7a\x30\x2d\x39\x5d\x5b\x41\x2d\x5a\x61\x2d\x7a\x30\x2d\x39\x2d\x5d\x7b\x30\x2c\x36\x31\x7d\x5b\x41\x2d\x5a\x61\x2d\x7a\x30\x2d\x39\x5d\x29\x7c\xa\x9\xe2\x86\xaa\x28\x5b\x41\x2d\x5a\x61\x2d\x7a\x30\x2d\x39\x5d\x2b\x29\x29\x24\x23", $d735761818463645a93b1faa6552a73a[$b116036a7de5ed04cba6d634f46430842])) { return false; } } } return true; } private function j9aca1d000d2761770f143bff9e085e7e($abc43f7098ec46986c08190fe50e028c) { $abc43f7098ec46986c08190fe50e028c = preg_replace("\x23\x5b\x5e\x30\x2d\x39\x5d\x2b\x23", "", $abc43f7098ec46986c08190fe50e028c); if (strlen($abc43f7098ec46986c08190fe50e028c) != 9) return 1; if (!is_numeric($abc43f7098ec46986c08190fe50e028c)) return 2; $cde64027dcb77268b49d9ddcefd3f476 = 0; for ($f566f3efd47ffeea0f1870a7b87c09b19 = 0; $f566f3efd47ffeea0f1870a7b87c09b19 < 9; $f566f3efd47ffeea0f1870a7b87c09b19 ++) { $b1cb99bfb6e6005e6f2151ffafb7537ab = (int) $abc43f7098ec46986c08190fe50e028c[$f566f3efd47ffeea0f1870a7b87c09b19]; if (($f566f3efd47ffeea0f1870a7b87c09b19 % 2) != 0) { if (($b1cb99bfb6e6005e6f2151ffafb7537ab *= 2) > 9) $b1cb99bfb6e6005e6f2151ffafb7537ab -= 9; } $cde64027dcb77268b49d9ddcefd3f476 += $b1cb99bfb6e6005e6f2151ffafb7537ab; } if (($cde64027dcb77268b49d9ddcefd3f476 % 10) != 0) return 3; else return 0; } private function d8bfefc6ce101661664c400b4e998b81($cd48a83430b91b7beb2f129216ae966f) { $cd48a83430b91b7beb2f129216ae966f = preg_replace("\x23\x5b\x5e\x30\x2d\x39\x5d\x2b\x23", "", $cd48a83430b91b7beb2f129216ae966f); if (strlen($cd48a83430b91b7beb2f129216ae966f) != 14) return 1; if (!is_numeric($cd48a83430b91b7beb2f129216ae966f)) return 2; $cde64027dcb77268b49d9ddcefd3f476 = 0; for ($f566f3efd47ffeea0f1870a7b87c09b19 = 0; $f566f3efd47ffeea0f1870a7b87c09b19 < 14; $f566f3efd47ffeea0f1870a7b87c09b19 ++) { $b1cb99bfb6e6005e6f2151ffafb7537ab = (int) $cd48a83430b91b7beb2f129216ae966f[$f566f3efd47ffeea0f1870a7b87c09b19]; if (($f566f3efd47ffeea0f1870a7b87c09b19 % 2) == 0) { if (($b1cb99bfb6e6005e6f2151ffafb7537ab *= 2) > 9) $b1cb99bfb6e6005e6f2151ffafb7537ab -= 9; } $cde64027dcb77268b49d9ddcefd3f476 += $b1cb99bfb6e6005e6f2151ffafb7537ab; } if (($cde64027dcb77268b49d9ddcefd3f476 % 10) != 0) return 3; else return 0; } private function eb196103b0b617aea56dfe69e1882d86($j9320ee5a57e311e3c4cc9c6e3cc5eaee) { throw new Exception($j9320ee5a57e311e3c4cc9c6e3cc5eaee); return false; } } ?>
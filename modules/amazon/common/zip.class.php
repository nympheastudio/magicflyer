<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Common-Services Co., Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Common-Services Co., Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2018 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @license   Commercial license
 * @package   Common-Classes
 * Support by mail:  support@common-services.com
 */

abstract class CommonZip
{
    public static $debug_mode = false;
    /**
     * @param $zipfile
     * @param $from
     *
     * @return bool
     */
    public function createZip($zipfile, $from)
    {
        $pclzip = _PS_ROOT_DIR_.'/tools/pclzip/pclzip.lib.php';

        if (method_exists('ZipArchive', 'addFile')) {
            return (self::createZipWithZipArchive($zipfile, $from));
        } elseif (file_exists($pclzip)) {
            require_once(_PS_ROOT_DIR_.'/tools/pclzip/pclzip.lib.php');
            return (self::createZipWithPclZip($zipfile, $from));
        } else {
            Tools::displayError(sprintf('%s(%s): Not any existing method to zip the file', basename(__FILE__), __LINE__));
            return (false);
        }
    }

    /**
     * @param $zipfile
     * @param $from
     *
     * @return bool
     */
    private function createZipWithPclZip($zipfile, $from)
    {
        if (self::$debug_mode) {
            printf('Creating Zip File with PclZip: %s', $zipfile);
        }

        if (file_exists($filename = $zipfile)) {
            if (!unlink($filename)) {
                if (self::$debug_mode) {
                    Tools::displayError(sprintf('%s(%s): '.$this->l('Unable to remove: %s'), basename(__FILE__), __LINE__, $zipfile));
                }

                return (false);
            }
        }
        if (self::$debug_mode) {
            CommonTools::p(getcwd());
            CommonTools::p($from);
        }

        $zip = new PclZip($zipfile);
        $result = $zip->add($from);
        $pass = true;

        if (is_array($result) && count($result) == count($from)) {
            foreach ($result as $diag) {
                if (is_array($diag) && array_key_exists('status', $diag) && $diag['status'] == 'ok') {
                    $pass &=  true;
                } else {
                    $pass &= false;
                }
                if (self::$debug_mode) {
                    CommonTools::p($diag);
                }
            }
        } else {
            if (self::$debug_mode) {
                CommonTools::p($result);
            }
        }

        return($pass);
    }

    /**
     * @param $zipfile
     * @param $from
     *
     * @return bool
     */
    private function createZipWithZipArchive($zipfile, $from)
    {
        if (self::$debug_mode) {
            printf('Creating Zip File: %s', $zipfile);
        }

        if (file_exists($filename = $zipfile)) {
            if (!@unlink($filename)) {
                if (self::$debug_mode) {
                    Tools::displayError(sprintf('%s(%s): '.$this->l('Unable to remove: %s'), basename(__FILE__), __LINE__, $zipfile));
                }

                return (false);
            }
        }
        $zip = new ZipArchive();

        if (!$zip->open($filename, ZIPARCHIVE::CREATE)) {
            if (self::$debug_mode) {
                Tools::displayError(sprintf('%s(%s): '.$this->l('Unable to open zip for writing: %s'), basename(__FILE__), __LINE__, $zipfile));
            }

            return (false);
        }

        foreach ($from as $key => $origin) {
            if (self::$debug_mode) {
                printf('Trying to add: %s to %s'."\n<br>", $origin, $filename);
            }

            if (!$zip->addFile($origin)) {
                if (self::$debug_mode) {
                    Tools::displayError(sprintf('%s(%s): '.$this->l('Unable to add to zip: %s'), basename(__FILE__), __LINE__, $origin));
                }

                return (false);
            } else {
                if (self::$debug_mode) {
                    printf('Added: %s'."\n<br>", $origin);
                }
            }
        }
        $zip->close();

        return (true);
    }
}

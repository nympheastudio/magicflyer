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

if (!class_exists('CommonCertificates')) {
    abstract class CommonCertificates
    {
        const SITE_PEM = 'site.crt';

        /**
         * Official URL for curl certs
         */
        const URL = 'https://curl.haxx.se/ca/cacert.pem';

        /**
         * Has to be in the file
         */
        const PURPOSE = 'Bundle of CA Root Certificates';
        /**
         * File must have a SHA key, unfortunately, we can't parse it
         */
        const HEADER_REGEX = '## SHA[0-9]{1,3}: ([0-9a-f]{40})';
        /**
         * End of file
         */
        const TRAILER_REGEX = '-----END CERTIFICATE-----';

        /**
         * Expiration
         */
        const EXPIRES = 2592000; //1 month

        /**
         * Directory
         */
        const DIR_CERT = 'cert';
        /**
         * Default cert file which has not to be removed
         */
        const FILE_DEFAULT = 'cacert.pem';

        public static $debug_mode = false;

        /**
         * Returns null in case of huge trouble
         * @return null|false|string
         */
        public static function getCertificate()
        {
            $fileid = floor((time() % (86400 * 365)) / self::EXPIRES); // file is valid till self::EXPIRES

            $cert_dir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.self::DIR_CERT;
            $cert_file = $cert_dir.DIRECTORY_SEPARATOR.'cacert.'.$fileid.'.pem';

            if (self::$debug_mode) {
                CommonTools::p(sprintf('%s - %s::%s()/#%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                CommonTools::p(sprintf('cert_file: %s', print_r($cert_file, true)));
            }

            if (!is_dir($cert_dir)) {
                mkdir($cert_dir);
                if (!is_dir($cert_dir)) {
                    if (self::$debug_mode) {
                        CommonTools::p(sprintf('%s - %s::%s()/#%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                        CommonTools::p(sprintf('unable to create cert directory: %s', print_r($cert_dir, true)));
                    }
                    return(false);
                }
            }
            if (!is_readable($cert_dir) || (file_exists($cert_file) && !is_readable($cert_file))) {
                if (self::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s()/#%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p(sprintf('unable to read cert file: %s', print_r($cert_file, true)));
                }
                return(false);
            }
            if (!is_writeable($cert_dir)) {
                if (self::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s()/#%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p(sprintf('unable to create cert directory: %s', print_r($cert_dir, true)));
                }
                if (file_exists($cert_file)) {
                    return ($cert_file);
                } else {
                    $default_certificate = self::getDefaultCertificatePath();

                    if (file_exists($default_certificate) && is_readable($default_certificate)) {
                        return($default_certificate);
                    } else {
                        return(false);
                    }
                }
            }

            self::cleanup();

            if (file_exists($cert_file)) {
                if (self::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s()/#%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p(sprintf('return cert file: %s', print_r($cert_file, true)));
                }

                return($cert_file);
            } else {
                $cert_content = CommonTools::fileGetContents(self::URL, false, null, 30, self::getDefaultCertificatePath());
                if (self::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s()/#%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p(sprintf('download cert file: %s', print_r($cert_file, true)));
                }

                $purpose = preg_match('/'.self::PURPOSE.'/i', $cert_content);
                $sha_check = preg_match('/'.self::HEADER_REGEX.'/i', $cert_content);
                $eof_check = preg_match('/'.self::TRAILER_REGEX.'/i', $cert_content);

                if ($purpose && $sha_check && $eof_check) {
                    if (self::$debug_mode) {
                        CommonTools::p(sprintf('%s - %s::%s()/#%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                        CommonTools::p(sprintf('download ok: %s', print_r($cert_file, true)));
                    }

                    $alt_pem = $cert_dir.DIRECTORY_SEPARATOR.self::SITE_PEM;
                    $alt_pem_content = null;

                    if (is_readable($cert_dir) && file_exists($alt_pem) && is_readable($alt_pem)) {
                        $alt_pem_content = Tools::file_get_contents($alt_pem);
                    } elseif (self::$debug_mode) {
                        CommonTools::p(sprintf('%s - %s::%s()/#%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                        CommonTools::p(sprintf('unable to read cert file: %s', print_r($alt_pem, true)));
                    }
                    if (Tools::strlen($alt_pem_content)) {
                        $cert_content .= "\n".$alt_pem_content;
                    }
                    
                    if (file_put_contents($cert_file, $cert_content) !== false) {
                        return($cert_file);
                    }
                } else {
                    if (self::$debug_mode) {
                        CommonTools::p(sprintf('%s - %s::%s()/#%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                        CommonTools::p(sprintf('download failed: %s/%s/%s', $purpose, $sha_check, $eof_check));
                        CommonTools::p(sprintf('content: %s', print_r($cert_content, true)));
                    }
                }
            }
            return(self::getDefaultCertificatePath());
        }

        /**
         * returns null if the user has deleted the file
         * @return null|string
         */
        public static function getDefaultCertificatePath()
        {
            $default_cert_file = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.self::DIR_CERT.DIRECTORY_SEPARATOR.self::FILE_DEFAULT;

            if (file_exists($default_cert_file) && filesize($default_cert_file) && is_readable($default_cert_file)) {
                return($default_cert_file);
            } else {
                return(null);
            }
        }

        /**
         * delete old certificates
         * @return null
         */
        private static function cleanup()
        {
            $now = time();
            $default_certificate_file = self::getDefaultCertificatePath();

            $cert_dir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.self::DIR_CERT;

            if (!is_dir($cert_dir)) {
                return null;
            }

            $files = glob($cert_dir.'*.pem');

            if (!is_array($files) || !count($files)) {
                return null;
            }

            foreach ($files as $file) {
                if (basename($file) == $default_certificate_file) {
                    continue;
                }
                if (filemtime($file) < $now - self::EXPIRES) {
                    unlink($file);
                }
            }
        }
    }
}

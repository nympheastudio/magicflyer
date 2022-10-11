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

if (!class_exists('CommonTools')) {
    abstract class CommonTools extends Tools
    {
        public static $debug_mode = false;

        protected static $_ps_version;

        /**
         * Source : http://www.edmondscommerce.co.uk/php/ean13-barcode-check-digit-with-php/
         * Many thanks ;)
         * @param $code
         *
         * @return bool
         */
        public static function eanUpcCheck($code)
        {
            if (!is_numeric($code) || Tools::strlen($code) < 12) {
                return (false);
            }
            //first change digits to a string so that we can access individual numbers
            $digits = sprintf('%012s', Tools::substr(sprintf('%013s', $code), 0, 12));
            // 1. Add the values of the digits in the even-numbered positions: 2, 4, 6, etc.
            $even_sum = $digits{1}
            + $digits{3}
            + $digits{5}
            + $digits{7}
            + $digits{9}
            + $digits{11};
            // 2. Multiply this result by 3.
            $even_sum_three = $even_sum * 3;
            // 3. Add the values of the digits in the odd-numbered positions: 1, 3, 5, etc.
            $odd_sum = $digits{0}
            + $digits{2}
            + $digits{4}
            + $digits{6}
            + $digits{8}
            + $digits{10};
            // 4. Sum the results of steps 2 and 3.
            $total_sum = $even_sum_three + $odd_sum;
            // 5. The check character is the smallest number which, when added to the result in step 4,  produces a multiple of 10.
            $next_ten = (ceil($total_sum / 10)) * 10;
            $check_digit = (int)$next_ten - $total_sum;
            $last_digit = (int)Tools::substr($code, Tools::strlen($code) - 1, 1);

            return ((int)$last_digit == (int)$check_digit);
        }

        /**
         * @param $code
         *
         * @return bool
         */
        public static function eanUpcisPrivate($code)
        {
            return (in_array(Tools::substr(sprintf('%013s', $code), 0, 1), array('2')));
        }

        /**
         * @param string $date
         * @param null $id_lang
         * @param bool $full
         * @param string $separator
         *
         * @return string
         * @throws PrestaShopException
         */
        public static function displayDate($date, $id_lang = null, $full = false, $separator = '-')
        {
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                $id_lang = null;

                return (Tools::displayDate($date, $id_lang, $full));
            }

            return (Tools::displayDate($date, $id_lang, $full, $separator));
        }


        /**
         * @param bool $http
         * @param bool $entities
         * @param bool $ignore_port
         *
         * @return string
         */
        public static function getHttpHost($http = false, $entities = false, $ignore_port = false)
        {
            if (method_exists('Tools', 'getHttpHost')) {
                return (Tools::getHttpHost($http, $entities, $ignore_port));
            } else {
                $host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']);
                if ($entities) {
                    $host = htmlspecialchars($host, ENT_COMPAT, 'UTF-8');
                }

                if ($http) {
                    $host = self::getProtocol().$host;
                }

                return $host;
            }
        }

        /**
         * @param $str
         *
         * @return string
         */
        public static function ucfirst($str)
        {
            if (method_exists('Tools', 'ucfirst')) {
                return Tools::ucfirst($str);
            }

            return Tools::strtoupper(Tools::substr($str, 0, 1)).Tools::substr($str, 1);
        }

        /**
         * @param $str
         *
         * @return string
         */
        public static function ucwords($str)
        {
            if (method_exists('Tools', 'ucwords')) {
                return Tools::ucwords($str);
            }

            return ucwords(Tools::strtolower($str));
        }

        /**
         * @param $table
         * @param bool $use_cache
         * @return mixed|null
         * @throws PrestaShopDatabaseException
         */
        public static function tableExists($table, $use_cache = true)
        {
            static $table_exists = array();
            static $show_tables_content = null;

            if (isset($table_exists[$table]) && $use_cache) {
                return $table_exists[$table];
            }

            // Check if exists
            //
            if ($show_tables_content === null || !$use_cache) {
                $tables = array();

                $query_result = Db::getInstance()->executeS('SHOW TABLES FROM `'.pSQL(_DB_NAME_).'`', true, false);

                if (!is_array($query_result) || !count($query_result)) {
                    return (null);
                }

                $show_tables_content = $query_result;
            }

            foreach ($show_tables_content as $rows) {
                foreach ($rows as $table_check) {
                    $tables[$table_check] = 1;
                }
            }

            if (isset($tables[$table])) {
                $table_exists[$table] = true;
            } else {
                $table_exists[$table] = false;
            }

            return $table_exists[$table];
        }

        /**
         * @param $table
         * @param $field
         *
         * @return mixed|null
         */
        public static function fieldExists($table, $field)
        {
            static $field_exists = array();
            $fields = array();

            if (isset($field_exists[$table.$field])) {
                return $field_exists[$table.$field];
            }

            // Check if exists
            //
            $query = Db::getInstance()->ExecuteS('SHOW COLUMNS FROM `'.pSQL($table).'`');

            if (!is_array($query) || !count($query)) {
                return (null);
            }

            foreach ($query as $row) {
                $fields[$row['Field']] = 1;
            }

            if (isset($fields[$field])) {
                $field_exists[$table.$field] = true;
            } else {
                $field_exists[$table.$field] = false;
            }

            return $field_exists[$table.$field];
        }


        /**
         * @param $path
         *
         * @return bool
         */
        public static function isDirWriteable($path)
        {
            $path = rtrim($path, '/\\');

            $testfile = sprintf('%s%stestfile_%s.chk', $path, DIRECTORY_SEPARATOR, uniqid());
            $timestamp = time();

            if (@file_put_contents($testfile, $timestamp)) {
                $result = trim(CommonTools::fileGetContents($testfile));
                @unlink($testfile);

                if ((int)$result == (int)$timestamp) {
                    return (true);
                }
            }

            return (false);
        }

        /**
         * http://php.net/manual/fr/function.glob.php#106595
         * @param $pattern
         * @param int $flags
         *
         * @return array
         */
        public static function globRecursive($pattern, $flags = 0)
        {
            $files = glob($pattern, $flags);

            if (is_array($files) && count($files)) {
                $dirs = glob(dirname($pattern).'/*', GLOB_ONLYDIR | GLOB_NOSORT);

                if (is_array($dirs) && count($dirs)) {
                    foreach ($dirs as $dir) {
                        $other_files = self::globRecursive($dir.'/'.basename($pattern), $flags);

                        if (is_array($other_files) && count($other_files)) {
                            $files = array_merge($files, $other_files);
                        }
                    }
                }
            }

            return $files;
        }


        /**
         * @param $url
         * @param bool $use_include_path
         * @param null $stream_context
         * @param int $curl_timeout
         * @param null $certificate
         * @param null $disable_ssl_check
         *
         * @return bool|mixed|string
         */
        public static function fileGetContents($url, $use_include_path = false, $stream_context = null, $curl_timeout = 30, $certificate = null, $disable_ssl_check = null)
        {
            if (function_exists('curl_init') && preg_match('/^https?:\/\//', $url)) {
                $curl = curl_init();
                $cert = Tools::strlen($certificate) ? $certificate : CommonCertificates::getCertificate();

                if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
                    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                }

                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
                curl_setopt($curl, CURLOPT_TIMEOUT, $curl_timeout);

                if ($disable_ssl_check) {
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                } else {
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
                    curl_setopt($curl, CURLOPT_CAINFO, $cert);
                }

                if ($stream_context != null) {
                    $opts = stream_context_get_options($stream_context);
                    if (isset($opts['http']['method']) && Tools::strtolower($opts['http']['method']) == 'post') {
                        curl_setopt($curl, CURLOPT_POST, true);
                        if (isset($opts['http']['content'])) {
                            parse_str($opts['http']['content'], $post_data);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
                        }
                    }
                }
                if (self::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p(sprintf('timeout: %s'."\n", print_r($curl_timeout, true)));
                    CommonTools::p(sprintf('cert file: %s'."\n", print_r($cert, true)));
                    CommonTools::p(sprintf('curl error: %s (%d)'."\n", curl_error($curl), curl_errno($curl)));
                    CommonTools::p(sprintf('curl info: %s'."\n", print_r(curl_getinfo($curl), true)));
                }

                $content = curl_exec($curl);
                curl_close($curl);

                return $content;
            } elseif (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) || !preg_match('/^https?:\/\//', $url)) {
                if ($stream_context == null && preg_match('/^https?:\/\//', $url)) {
                    if (preg_match('/^https:\/\//', $url) && !$disable_ssl_check) {
                        $contextOptions = array(
                            'ssl' => array(
                                'verify_peer'   => true,
                                'cafile'        => Tools::strlen($certificate) ? $certificate : CommonCertificates::getCertificate()
                            )
                        );
                        $stream_context = @stream_context_create(array('http' => array('timeout' => $curl_timeout)), $contextOptions);
                    } else {
                        $contextOptions = array();
                        $stream_context = null;
                    }
                }

                if (self::$debug_mode) {
                    return file_get_contents($url, $use_include_path, is_resource($stream_context) ? $stream_context : null);//TODO Validation: http://forge.prestashop.com/browse/PSCSX-7758
                } else {
                    return @file_get_contents($url, $use_include_path, is_resource($stream_context) ? $stream_context : null);//TODO Validation: http://forge.prestashop.com/browse/PSCSX-7758
                }
            } else {
                return false;
            }
        }

        /**
         * Get current version of PrestaShop
         * @return string
         */
        public static function getPsVersion()
        {
            if (! self::$_ps_version) {
                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                    self::$_ps_version = '1.4';
                } elseif (version_compare(_PS_VERSION_, '1.6', '<')) {
                    self::$_ps_version = '1.5';
                } elseif (version_compare(_PS_VERSION_, '1.7', '<')) {
                    self::$_ps_version = '1.6';
                } else {
                    self::$_ps_version = '1.7';
                }
            }

            return self::$_ps_version;
        }

        /**
         * Implementation of array_column function for PHP 5 < 5.5.0.
         *
         * @param array $input
         * @param mixed $column_key
         * @param mixed $index_key
         * @return array
         * @see http://php.net/manual/en/function.array-column.php
         * @author debuss-a
         */
        public static function arrayColumn($input, $column_key, $index_key = null)
        {
            if (function_exists('array_column')) {
                return array_column($input, $column_key, $index_key);
            }

            if (func_num_args() < 2) {
                trigger_error(
                    'array_column() expects at least 2 parameters, '.func_num_args().' given',
                    E_USER_WARNING
                );

                return null;
            } elseif (!is_array($input)) {
                trigger_error(
                    'array_column() expects parameter 1 to be array, '.gettype($input).' given',
                    E_USER_WARNING
                );

                return null;
            }

            if ($index_key) {
                return array_combine(
                    self::arrayColumn($input, $index_key),
                    self::arrayColumn($input, $column_key)
                );
            }

            return array_map(
                array('CommonTools', 'arrayColumnCallback'),
                $input,
                array_fill(0, count($input), $column_key)
            );
        }

        /**
         * @ignore
         * @param array $columns
         * @param int|string $column_key
         * @return mixed
         * @see CommonTools::arrayColumn()
         * @author debuss-a
         */
        private static function arrayColumnCallback($columns, $column_key)
        {
            return $columns[$column_key];
        }

        /**
         * Wrap debug code with html pre tag
         * Grab html_entity_decode to one place for readability
         * @param array $content Content to be printed
         * @param bool  $return  Return string instead of print out
         *
         * @return string
         */
        public static function pre($content, $return = false)
        {
            $result = '';
            $result .= html_entity_decode("&lt;pre&gt;");
            foreach ($content as $string) {
                $result .= print_r($string, true);
            }
            $result .= html_entity_decode('&lt;/pre&gt;');

            if (! $return) {
                print_r($result);
            }
            return $result;
        }

        /**
         * Print object
         * @param $object
         * @param bool $kill
         *
         * @return mixed
         */
        public static function p($object, $kill = false)
        {
            if (method_exists('Tools', 'p')) {
                return Tools::p($object, $kill);
            } else {
                return Tools::dieObject($object, $kill);
            }
        }

        /**
         * alias of static::dieObject
         * @param $object
         * @param bool $kill
         *
         * @return mixed
         */
        public static function d($object, $kill = true)
        {
            if (method_exists('Tools', 'd')) {
                return Tools::d($object, $kill);
            } else {
                return Tools::dieObject($object, $kill);
            }
        }
    }
}

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
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
*/

require_once(dirname(__FILE__).'/amazon.csv.base.class.php');


class AmazonCSVReference extends AmazonCSVBase
{
    public $id_product           = null;
    public $id_product_attribute = null;

    public $reference = null;

    public $upc   = null;
    public $ean13 = null;

    public $comment      = null;
    public $manufacturer = null;
}

class AmazonCSVReferences
{
    const FM_ARRAY = '1';
    const FM_OBJECT = '2';

    const FIELD_ID = 0;
    const FIELD_REFERENCE = 1;
    const FIELD_UPC = 2;
    const FIELD_EAN = 3;
    const FIELD_MANUFACTURER = 5;

    const SEP_COMMA = ',';
    const SEP_SEMI = ';';
    const SEP_TAB = "\t";

    #Data
    public $csvfile  = null;
    public $loadflag = false;
    public $datalist = null;

    public function __construct($filepath)
    {
        $this->csvfile = $filepath;
    }

    public function getData()
    {
        if (!$this->csvfile) {
            return array();
        }

        if ($this->loadflag) {
            return $this->datalist;
        }

        if ($this->loadData()) {
            return $this->datalist;
        }

        return array();
    }

    public function loadData()
    {
        if (!file_exists($this->csvfile)) {
            throw new Exception('Invalid file');
        }

        $this->datalist = array();
        $fileSize = 10000;

        $fp = fopen($this->csvfile, 'r');

        // Identifying delimiter
        $best_delimiter = null;
        $previous = 0;
        foreach (array(self::SEP_SEMI, self::SEP_COMMA, self::SEP_TAB) as $delimiter) {
            $csv = fgetcsv($fp, 10000, $delimiter);
            $c = is_array($csv) ? count($csv) : 0;

            if ($c > $previous) {
                $best_delimiter = $delimiter;
            }

            $previous = $c;
            fseek($fp, 0);
        }

        $delimiter = $best_delimiter;
        $headerok = false;

        while ($data = fgetcsv($fp, $fileSize, $delimiter)) {
            #  ignore header data
            if (!$headerok) {
                if (!isset($data[AmazonCSVReferences::FIELD_EAN]) || !isset($data[AmazonCSVReferences::FIELD_EAN])) {
                    continue;
                }

                if (Tools::strtoupper($data[AmazonCSVReferences::FIELD_EAN]) == 'EAN' && Tools::strtoupper($data[AmazonCSVReferences::FIELD_UPC]) == 'UPC') {
                    $headerok = true;
                }

                continue;
            }

            if (!is_array($data)) {
                continue;
            }
            $szTemp = implode($data, '');
            if (!trim($szTemp)) {
                continue;
            }

            $item = $this->parse($data);

            if ($item) {
                $this->datalist[] = $item;
            }
        }
        fclose($fp);

        if ($headerok) {
            $this->loadflag = true;
        }

        return true;
    }

    public function parse($data)
    {
        if (!is_array($data)) {
            return null;
        }

        $data = array_map('trim', $data);

        $obj = new AmazonCSVReference();

        if (isset($data[AmazonCSVReferences::FIELD_ID])) {
            $item = $data[AmazonCSVReferences::FIELD_ID];

            if (stristr($item, '_') !== false) {
                $split_combination = explode('_', $item);

                if (!(int)$split_combination[0]) {
                    return (false);
                }

                $obj->set_id_product((int)$split_combination[0]);
                $obj->set_id_product_attribute((int)$split_combination[1]);
            } else {
                if (!(int)$item) {
                    return (false);
                }

                $obj->set_id_product((int)$item);
                $obj->set_id_product_attribute(false);
            }
        } else {
            return (false);
        }

        if (isset($data[AmazonCSVReferences::FIELD_REFERENCE])) {
            $item = ltrim($data[AmazonCSVReferences::FIELD_REFERENCE], "'");

            if (AmazonTools::validateSKU($item)) {
                $obj->set_reference((string)$item);
            }
        } else {
            return (false);
        }

        if (isset($data[AmazonCSVReferences::FIELD_EAN])) {
            $item = ltrim($data[AmazonCSVReferences::FIELD_EAN], "'");

            if (AmazonTools::eanUpcCheck($item) && !AmazonTools::eanUpcisPrivate($item)) {
                $obj->set_ean13((string)$item);
            }
        } else {
            return (false);
        }

        if (isset($data[AmazonCSVReferences::FIELD_UPC])) {
            $item = ltrim($data[AmazonCSVReferences::FIELD_UPC], "'");

            if (AmazonTools::eanUpcCheck($item) && !AmazonTools::eanUpcisPrivate($item)) {
                $obj->set_upc((string)$item);
            }
        } else {
            return (false);
        }

        if (isset($data[AmazonCSVReferences::FIELD_MANUFACTURER])) {
            $item = trim($data[AmazonCSVReferences::FIELD_MANUFACTURER]);

            if (Tools::strlen($item) && Validate::isCatalogName($item)) {
                $obj->set_manufacturer((string)$item);
            }
        } else {
            return (false);
        }

        return $obj;
    }
}

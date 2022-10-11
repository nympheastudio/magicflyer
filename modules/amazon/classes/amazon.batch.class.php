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

class AmazonBatch
{
    public $id = null;

    public $timestart = 0;
    public $timestop  = 0;

    public $type   = null;
    public $region = null;

    public $created = 0;
    public $updated = 0;
    public $deleted = 0;

    public function __construct($timestamp = null)
    {
        if ($timestamp == null) {
            $this->timestart = time();
        } else {
            $this->timestart = (int)$timestamp;
        }
    }

    /**
     * Format Batch for output (display)
     * @return array
     */
    public function format()
    {
        $result = array();

        $result['id'] = $this->id ? $this->id : '-';

        $result['hasid'] = $this->id ? true : false;

        $result['timestart'] = $this->timestart ? AmazonTools::displayDate(date('Y-m-d H:i:s', $this->timestart), null, true) : '-';
        $result['timestop'] = $this->timestop ? AmazonTools::displayDate(date('Y-m-d H:i:s', $this->timestop), null, true) : '-';
        $result['duration'] = $this->timestop - $this->timestart;

        if ($result['duration'] < 0) {
            $result['duration'] = 0;
        }

        $result['type'] = AmazonTools::ucfirst($this->type);
        $result['region'] = $this->region;

        $result['created'] = $this->created;
        $result['updated'] = $this->updated;
        $result['deleted'] = $this->deleted;
        $result['records'] = $this->created + $this->updated + $this->deleted;

        return ($result);
    }
}

class AmazonBatches extends Amazon
{
    const MAX_BATCHES = 100;

    const ORDER_REPORT  = 'batch_order_report';

    public $key     = null;
    /**
     * @var int
     */
    public $current = 0;
    public $batches = array();

    public function __construct($key = null)
    {
        $this->key = $key;
        $this->load();
    }


    /**
     * Compare dates, callback function
     * @param $a
     * @param $b
     * @return null
     */
    private function getLastForRegionCompare($a, $b)
    {
        return $a->timestart && $b->timestart ? $b->timestart - $a->timestart : null;
    }


    /**
     * Get latest batch for the region
     * @param $region
     * @return bool|null|string
     */
    public function getLastForRegion($region)
    {
        $batches = unserialize(AmazonConfiguration::get($this->key));

        if (Tools::strlen($region) && is_array($batches) && count($batches)) {
            usort($batches, array('self', 'getLastForRegionCompare'));

            foreach ($batches as $batch) {
                if ($batch instanceof AmazonBatch && $batch->id && $batch->timestart && $region == $batch->region) {
                    $this->current = sprintf('%s.%s', $batch->timestop, $batch->id);
                    return date('Y-m-d H:i:s', $batch->timestart);
                }
            }
        }

        return null;
    }

    /**
     * Return current batch
     * @return mixed|null
     */
    public function getCurrent()
    {
        if ($this->current) {
            return($this->batches[$this->current]);
        } else {
            return(null);
        }
    }

    /**
     * Delete configuration keu
     */
    public function deleteKey()
    {
        AmazonConfiguration::deleteKey($this->key);
    }


    /**
     * Load Batches
     * @return array|mixed
     */
    public function load()
    {
        $batches = unserialize(AmazonConfiguration::get($this->key));

        if (Amazon::$debug_mode) {
            CommonTools::p('Batches, Load');
            CommonTools::p(Tools::substr(print_r($batches, true), 0, 256).'...');
        }

        if (is_array($batches) && count($batches)) {
            return ($this->batches = $batches);
        } else {
            return ($this->batches = array());
        }
    }

    /**
     * Add a new batch
     * @param AmazonBatch $batch
     * @return bool
     */
    public function add(AmazonBatch $batch)
    {
        if (!$batch instanceof AmazonBatch) {
            return (false);
        }

        if (!(is_array($this->batches) && count($this->batches) && reset($this->batches) instanceof AmazonBatch)) {
            $this->batches = array();
        }

        $this->current = $index = sprintf('%s.%s', $batch->timestop, $batch->id);

        $this->batches[$index] = $batch;

        krsort($this->batches);

        if (Amazon::$debug_mode) {
            CommonTools::p("Batches, Add");
            CommonTools::p($batch);
            CommonTools::p(Tools::substr(print_r($this->batches, true), 0, Tools::strlen(print_r($batch, true))));
        }

        $this->batches = array_slice($this->batches, 0, self::MAX_BATCHES, true);

        return (true);
    }

    /**
     * Provides current batch
     * @return null or batch
     */
    public function current()
    {
        if (!(is_array($this->batches) && isset($this->batches[$this->current]) && $this->batches[$this->current] instanceof AmazonBatch)) {
            return (null);
        }

        return ($this->batches[$this->current]);
    }

    /**
     * Save current batch
     * @return bool
     */
    public function save()
    {
        $serialized = serialize($this->batches);

        if (Amazon::$debug_mode) {
            CommonTools::p("Batches, Serialized");
            CommonTools::p(Tools::substr(print_r($serialized, true), 0, 128).'...');
        }

        return (AmazonConfiguration::updateValue($this->key, $serialized));
    }
}

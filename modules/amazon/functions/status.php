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

require_once(dirname(__FILE__).'/env.php');
require_once(dirname(__FILE__).'/../amazon.php');

require_once(dirname(__FILE__).'/../classes/amazon.order_info.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.carrier.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.batch.class.php');
require_once(dirname(__FILE__).'/../common/order.class.php');

class AmazonBulkMode extends Amazon
{
    const DEFAULT_PERIOD_IN_DAYS = 15;

    public function __construct()
    {
        parent::__construct();

        AmazonContext::restore($this->context);

        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
    }

    public function bulkUpdate()
    {
        $timestart = time();
        $cr = Amazon::LF; // carriage return

        $ps_order_list = array();
        $shipping_order_list = array();

        $tokens = Tools::getValue('cron_token');
        $amazon_lang = Tools::getValue('lang');
        $europe = (int)Tools::getValue('europe');
        $force = (int)Tools::getValue('force');
        $period_override = (int)Tools::getValue('period', 1);

        $period = max(self::DEFAULT_PERIOD_IN_DAYS, $period_override);

        $id_lang = Language::getIdByIso($amazon_lang);

        if (!AmazonTools::checkToken($tokens)) {
            die('Wrong Token');
        }

        if (!$amazon_lang) {
            echo $this->l('No selected language, nothing to do...');
            die;
        }

        // Orders States
        //
        $sent_state = AmazonConfiguration::get('SENT_STATE');
        $actives = AmazonConfiguration::get('ACTIVE');

        // Regions
        //
        $marketPlaceRegion = AmazonConfiguration::get('REGION');
        $marketLang2Region = array_flip($marketPlaceRegion);

        $id_lang_list = '';

        // Making id_lang_list to select orders
        //
        if ($europe) {
            foreach (AmazonTools::languages() as $language) {
                if (!isset($actives[$language['id_lang']]) || !$actives[$language['id_lang']]) {
                    continue;
                }

                if (!AmazonTools::isUnifiedAccount($marketPlaceRegion[$language['id_lang']])) {
                    continue;
                }

                $id_lang_list .= $language['id_lang'].',';
            }
            $id_lang_list = rtrim($id_lang_list, ',');
        } else {
            if (!isset($marketLang2Region[$amazon_lang])) {
                die('No selected language, nothing to do...');
            }

            $id_lang_list = $marketLang2Region[$amazon_lang];
        }


        if (!$id_lang_list) {
            die('No selected language, nothing to do...');
        }

        $order_state = new OrderState($sent_state, $this->id_lang);

        if (!Validate::isLoadedObject($order_state)) {
            die(sprintf('%s(%d): Wrong id order state', basename(__FILE__), __LINE__));
        }

        echo "Updating order statuses for:\n";

        foreach (explode(',', $id_lang_list) as $id_lang) {
            printf("- Amazon %s\n", Tools::strtoupper($marketPlaceRegion[$id_lang]));
        }
        printf("- Period: %s days\n", $period);
        printf("- State: %s (%d)\n", $order_state->name, $sent_state);
        printf("- Language: %s\n", $amazon_lang);
        echo str_repeat('-', 160)."\n";

        $id_lang = Language::getIdByIso($amazon_lang);

        if (Amazon::$debug_mode) {
            printf('Parameters: %s %s'.$cr, $sent_state, nl2br(print_r($id_lang_list, true)));
        }

        // Fetch Orders
        if (!($orders = AmazonOrder::getMarketplaceOrdersStatesByIdLang($id_lang_list, $sent_state, $period, $force, Amazon::$debug_mode))) {
            printf($this->l('No Orders - exiting normally').$cr);
            echo str_repeat('-', 160)."\n";
            exit;
        }

        echo "Order List:\n";

        foreach ($orders as $order) {
            printf('id_order: %d amazon order: %s id_lang: %d id_carrier: %d shipping_number: %s date: %s'."\n", $order['id_order'], $order['mp_order_id'], $order['id_lang'], $order['id_carrier'], $order['shipping_number'], $order['date_add']);
        }
        echo str_repeat('-', 160)."\n";


        $unknown_carriers = array();

        echo "Preparing shipping list:\n";


        foreach ($orders as $order) {
            $id_lang = $order['id_lang'];
            $id_order = $order['id_order'];

            $amazonCarrier = AmazonCarrier::getAmazonCarrierById($order['id_carrier'], $id_lang, Amazon::$debug_mode);

            if (!Tools::strlen($amazonCarrier)) {
                if (!isset($unknown_carriers[$id_lang])) {
                    $unknown_carriers[$id_lang] = array();
                }
                $unknown_carriers[$id_lang][$order['id_carrier']] = $order['id_carrier'];
                continue;
            }

            if (!$amazonCarrier) {
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s:%d %s %d (%d)'.$cr, basename(__FILE__), __LINE__, $this->l('Skipping order - Empty carrier for order #'), $order['id_order'], $id_lang));
                }
                continue;
            }

            if (empty($order['shipping_number'])) {
                $ps_order = new Order($id_order);

                if (!Validate::isLoadedObject($ps_order)) {
                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s:%d %s %d (%d)'.$cr, basename(__FILE__), __LINE__, 'Unable to load order', $order['id_order'], $id_lang));
                    }
                    continue;
                }
                $order['shipping_number'] = AmazonOrder::getShippingNumber($ps_order);
            }

            // Building array for Amazon API
            //
            $ps_order_list[] = $order['id_order'];
            $shipping_order_list[$order['mp_order_id']] = array(
                'order_id' => $order['mp_order_id'],
                'merchant_order_id' => $id_order,
                'shipping_number' => $order['shipping_number'],
                'carrier' => null,
                'carrier_name' => null,
                'timestamp' => strtotime($order['date_add'])
            );

            // If it is a listed carrier, send carrier code, else, send carrier name
            //
            if (AmazonCarrier::isCarrierCode($amazonCarrier)) {
                $shipping_order_list[$order['mp_order_id']]['carrier'] = $amazonCarrier;
            } else {
                $shipping_order_list[$order['mp_order_id']]['carrier_name'] = $amazonCarrier;
            }

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('Order: %s Carrier: %s Shipping Number: %s'.$cr, $order['mp_order_id'], $amazonCarrier, $order['shipping_number']));
            }
            $shipping_order_item = $shipping_order_list[$order['mp_order_id']];

            printf('amazon order: %s merchant order id: %d shipping_number: %s carrier: %s carrier alt./name: %s shipping date/time: %s'."\n", $shipping_order_item['order_id'], $shipping_order_item['merchant_order_id'], $shipping_order_item['shipping_number'], $shipping_order_item['carrier'] ? $shipping_order_item['carrier'] : 'none', $shipping_order_item['carrier_name'] ? $shipping_order_item['carrier_name'] : 'none', ($shipping_order_item['timestamp'] ? date('Y-m-d H:i:s', $shipping_order_item['timestamp']) : 'n/a'));
        }
        echo str_repeat('-', 160)."\n";

        if (count($unknown_carriers)) {
            echo "Unknown carriers:\n";

            foreach ($unknown_carriers as $id_lang => $unknown_carrier) {
                foreach ($unknown_carrier as $key => $id_carrier) {
                    $carrier = new Carrier($id_carrier);
                    $missingCarrier = isset($carrier->name) ? $carrier->name : $id_carrier;
                    printf('%s:%d %s %s (%s) - %d'.$cr, basename(__FILE__), __LINE__, $this->l('Carrier not found, please configure your carriers associations for:'), $missingCarrier, Language::getIsoById($id_lang), $id_carrier);
                }
            }
            echo str_repeat('-', 160)."\n";
        }


        if (!count($shipping_order_list)) {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf($this->l('No Orders - exiting normally')));
            }
            exit;
        } else {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf($this->l('%s Orders'), count($shipping_order_list)));
            }
        }

        // Init Amazon
        //
        $platform = AmazonTools::selectPlatform($id_lang, Amazon::$debug_mode);

        if (Amazon::$debug_mode) {
            CommonTools::p(print_r($platform['auth'], true).print_r($platform['params'], true).print_r($platform['platforms'], true));
        }

        $pass = true;

        if (!($amazonApi = new AmazonWebService($platform['auth'], $platform['params'], null, Amazon::$debug_mode))) {
            echo $this->l('Unable to login').$cr;
            $pass = false;
        }

        if ($pass) {
            echo "Sending Feed to Amazon:\n";

            // Submitting Orders
            //
            if (!($result = $amazonApi->confirmMultipleOrders($shipping_order_list))) {
                printf($this->l('Unable to send data to Amazon').$cr);
            }

            if (isset($result->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId)) {
                foreach ($ps_order_list as $id_order) {
                    AmazonOrder::updateMarketplaceStatus($id_order, AmazonOrder::CHECKED);
                }
                printf('%s %s', $this->l('Data sucessfully submitted, FeedSubmissionId: '), $result->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId);

                // Save Session
                $batches = new AmazonBatches('session_status');
                $batch = new AmazonBatch($timestart);
                $batch->id = uniqid();
                $batch->timestop = time();
                $batch->type = $this->l('Cron');
                $batch->region = $marketPlaceRegion[$id_lang];
                $batch->created = 0;
                $batch->updated = count($shipping_order_list);
                $batch->deleted = 0;
                $batches->add($batch);
                $batches->save();

                $batches = new AmazonBatches('batch_status');
                $batch = new AmazonBatch($timestart);
                $batch->id = (string)$result->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
                $batch->timestop = time();
                $batch->type = 'Status';
                $batch->region = $marketPlaceRegion[$id_lang];
                $batch->created = 0;
                $batch->updated = count($shipping_order_list);
                $batch->deleted = 0;
                $batches->add($batch);
                $batches->save();
            } else {
                printf('Amazon Returned: %s'.$cr, print_r($result, true));
            }

            echo str_repeat('-', 160)."\n";
        }
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $id_lang));
    }
}

$amazonBulkMode = new AmazonBulkMode;
$amazonBulkMode->bulkUpdate();

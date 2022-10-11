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

require_once(dirname(__FILE__).'/../classes/libs/sqs.php');

class AmazonRepricing extends Amazon
{
    const REPRICING_WHOLESALE_PRICE = 1;
    const REPRICING_REGULAR_PRICE = 2;

    const INPUT_QUEUE = 1;
    const OUTPUT_QUEUE = 2;

    const MAX_RETRIEVE_MESSAGES = 60;
    const MAX_MESSAGES_AT_ONCE = 10;
    const MAX_EMPTY_LOOPS = 5;

    const MESSAGE_VISIBILITY_TIMEOUT = 120;

    public static $queue_prefix      = null;
    public static $input_queue_name  = null;
    public static $output_queue_name = null;

    public static function setQueueName()
    {
        static $queue_prefix = null;

        if ($queue_prefix === null) {
            $queue_prefix = Tools::ucfirst(Tools::substr(AmazonTools::toKey(Configuration::get('PS_SHOP_NAME')), 0, 16));

            if (!Tools::strlen($queue_prefix)) {
                $queue_prefix = 'AmazonPrestashop';
            }
        }

        if (isset($_SERVER['DropBox']) && $_SERVER['DropBox']) {
            $queue_prefix = 'Common-Services';
        }

        self::$queue_prefix = $queue_prefix;
        self::$input_queue_name = sprintf('%s-In', $queue_prefix);
        self::$output_queue_name = sprintf('%s-Out', $queue_prefix);
    }

    public static function getQueueName($region, $type)
    {
        self::setQueueName();

        switch ($type) {
            case self::INPUT_QUEUE:
                return (self::$input_queue_name.'-'.$region);
            case self::OUTPUT_QUEUE:
                return (self::$output_queue_name.'-'.$region);
        }

        return (null);
    }


    public static function countMessages($sqs, $queue)
    {
        $queue_info = $sqs->getQueueAttributes($queue, 'ApproximateNumberOfMessages');

        if (!(is_array($queue_info) && isset($queue_info['RequestId']) && isset($queue_info['Attributes']) && preg_match('/([a-z0-9]*-){4,}/', $queue_info['RequestId']))) {
            if (Amazon::$debug_mode) {
                printf('%s(%d): Error'."\n", basename(__FILE__), __LINE__);
                var_dump($queue_info);
            }
            die('ERROR, unable to getQueueAttributes for queue '.$queue);
        }

        return ((int)$queue_info['Attributes']['ApproximateNumberOfMessages']);
    }

    public static function retrieveMessages($awsKeyId, $awsSecretKey, $queue, $script_start_time, $max_execution_time, $verbose = false)
    {
        $messages_set = array();

        $sqs = new AmazonSQS($awsKeyId, $awsSecretKey);

        if ($verbose) {
            CommonTools::p("Ready to receive messages from queue:".$queue);
        }

        $nMessages = self::countMessages($sqs, $queue);
        $nMessagesToRetrieve = min($nMessages, self::MAX_RETRIEVE_MESSAGES);

        if (!$nMessagesToRetrieve) {
            if ($verbose) {
                CommonTools::p("No pending messages in the queue, exiting");
            }

            return (false);
        }

        $loop_start_time = microtime(true);

        $supposedLoops = ceil($nMessagesToRetrieve / self::MAX_MESSAGES_AT_ONCE);
        $emptyLoops = 0;
        $effectiveLoops = 0;
        $messagesCount = 0;
        $fetch = true;
        $i = 0;

        while ($fetch) {
            $effectiveLoops++;

            if ($verbose) {
                CommonTools::p(sprintf('Loop: %d on %d supposed loops with %d empty loops, messages: %d on %d expected'."\n", $effectiveLoops, $supposedLoops, $emptyLoops, $messagesCount, $nMessagesToRetrieve));
            }

            $messages = $sqs->receiveMessage($queue, self::MAX_MESSAGES_AT_ONCE, self::MESSAGE_VISIBILITY_TIMEOUT);

            $loop_average = (microtime(true) - $loop_start_time) / ($i + 1);
            $total_elapsed = microtime(true) - $script_start_time;
            $max_estimated = (($loop_start_time - $script_start_time) + $loop_average * $i * 1.4);

            if ($verbose) {
                CommonTools::p(sprintf('Loop average: %.02f, Max estimated: %.02f, Total Elapsed: %.02f'."\n", $loop_average, $max_estimated, $total_elapsed));
            }

            if ($max_execution_time && ($max_estimated >= $max_execution_time || $total_elapsed >= $max_execution_time)) {
                if ($verbose) {
                    CommonTools::p(sprintf('%s(%d): %s (%d/%d/%d)', basename(__FILE__), __LINE__, 'Warning: time allowed is about to be reached, loop aborted', $max_execution_time, $max_estimated, $total_elapsed));

                    return ($messages_set);
                }
            }

            if (is_array($messages) && isset($messages['Messages']) && is_array($messages['Messages']) && count($messages['Messages'])) {
                $messagesCount += $messageCount = is_array($messages['Messages']) ? count($messages['Messages']) : 0;

                if (!$messageCount) {
                    $emptyLoops++;
                }

                $messages_set[] = $messages['Messages'];

                if ($verbose) {
                    CommonTools::p('Messages received:'.$messageCount.' on '.$messagesCount.'/'.$nMessagesToRetrieve);
                }
            } elseif ($verbose) {
                CommonTools::p("ERROR: Failed to retrieve message");

                var_dump($messages);
            }

            if ($messagesCount >= $nMessagesToRetrieve) {
                break;
            }

            if ($messagesCount < $nMessagesToRetrieve && $emptyLoops > self::MAX_EMPTY_LOOPS) {
                if ($verbose) {
                    CommonTools::p("WARNING: Not all message have been retrieved, MAX_EMPTY_LOOPS reached");
                }
                $fetch = false;
            }
        }

        return ($messages_set);
    }

    public static function checkService($awsKeyId, $awsSecretKey)
    {
        self::setQueueName();

        $sqs = new AmazonSQS($awsKeyId, $awsSecretKey);
        $result = $sqs->listQueues(self::$queue_prefix);

        if (!(is_array($result) && isset($result['RequestId']) && preg_match('/([a-z0-9]*-){4,}/', $result['RequestId']))) {
            CommonTools::p($result);

            return (false);
        }
        if (!isset($result['Queues'])) {
            return (false);
        }

        return ($result['RequestId']);
    }


    public static function listQueues($awsKeyId, $awsSecretKey)
    {
        self::setQueueName();

        $sqs = new AmazonSQS($awsKeyId, $awsSecretKey);
        $result = $sqs->listQueues(self::$queue_prefix);

        if (!(is_array($result) && isset($result['RequestId']) && preg_match('/([a-z0-9]*-){4,}/', $result['RequestId']))) {
            if (Amazon::$debug_mode) {
                printf('%s(%d): Error'."\n", basename(__FILE__), __LINE__);
                var_dump($result);
            }

            return (false);
        }
        if (!isset($result['Queues'])) {
            return (false);
        }

        return ($result['Queues']);
    }

    public static function createQueues($awsKeyId, $awsSecretKey, $region, $existingQueues = array())
    {
        $toCreate = array(
            self::INPUT_QUEUE => self::getQueueName($region, self::INPUT_QUEUE),
            self::OUTPUT_QUEUE => self::getQueueName($region, self::OUTPUT_QUEUE)
        );
        $queueList = array();

        if (is_array($existingQueues) && count($existingQueues)) {
            $queues = $existingQueues;
        } else {
            if (($queues = self::listQueues($awsKeyId, $awsSecretKey)) === false) {
                return (false);
            }
        }

        // Exclude Existing Queues
        foreach ($queues as $queue_url) {
            $url = trim(dirname(dirname($queue_url)));
            $queue = trim(basename($queue_url));

            if (strpos(AmazonSQS::ENDPOINT_US_EAST, $url) === false) {
                continue;
            }

            if ($queue == self::getQueueName($region, self::INPUT_QUEUE)) {
                unset($toCreate[self::INPUT_QUEUE]);
                $queueList[self::INPUT_QUEUE] = $queue_url;
            } elseif ($queue == self::getQueueName($region, self::OUTPUT_QUEUE)) {
                unset($toCreate[self::OUTPUT_QUEUE]);
                $queueList[self::OUTPUT_QUEUE] = $queue_url;
            }
        }

        if (is_array($toCreate) && count($toCreate)) {
            $sqs = new AmazonSQS($awsKeyId, $awsSecretKey);

            foreach ($toCreate as $queue_id => $queue) {
                $result = $sqs->createQueue($queue);

                if (!(is_array($result) && isset($result['RequestId']) && preg_match('/([a-z0-9]*-){4,}/', $result['RequestId']))) {
                    if (Amazon::$debug_mode) {
                        printf('%s(%d): Error'."\n", basename(__FILE__), __LINE__);
                        var_dump($result);
                    }

                    return (false);
                }

                $queueList[$queue_id] = $result['QueueUrl'];

                $attributes = array();

                switch ($queue_id) {
                    case self::INPUT_QUEUE:
                        //$attributes['MaximumMessageSize'] = 256*1024;
                        $attributes['ReceiveMessageWaitTimeSeconds'] = 4;//long polling
                        $attributes['MessageRetentionPeriod'] = 3600;
                        break;
                    case self::OUTPUT_QUEUE:
                        //$attributes['MaximumMessageSize'] = 256*1024;
                        $attributes['MessageRetentionPeriod'] = 14400;
                        break;
                }

                if (is_array($attributes) && count($attributes)) {
                    $result = $sqs->setQueueAttributes($result['QueueUrl'], $attributes);

                    if (!(is_array($result) && isset($result['RequestId']) && preg_match('/([a-z0-9]*-){4,}/', $result['RequestId']))) {
                        if (Amazon::$debug_mode) {
                            printf('%s(%d): Error'."\n", basename(__FILE__), __LINE__);
                            var_dump($result);
                        }
                    }
                }
            }
        }

        return ($queueList);
    }

    public static function setQueuePermission($awsKeyId, $awsSecretKey, $queue, $awsAccountId, $permission)
    {
        $permissions = array();
        $permissions[$awsAccountId] = $permission;

        $sqs = new AmazonSQS($awsKeyId, $awsSecretKey);
        $permission_result = $sqs->addPermission($queue, 'Prestashop Repricing Queue - '.$permission, $permissions);

        if (!(is_array($permission_result) && isset($permission_result['RequestId']) && preg_match('/([a-z0-9]*-){4,}/', $permission_result['RequestId']))) {
            if (Amazon::$debug_mode) {
                printf('%s(%d): Error'."\n", basename(__FILE__), __LINE__);
                var_dump($permission_result);
            }

            return (false);
        }

        return (true);
    }

    public static function listRegisteredDestinations($amazonApi)
    {
        $registered_destinations = $amazonApi->listRegisteredDestinations();
        $registered = array();

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): AmazonRepricing::ListRegisteredDestinations: %s', basename(__FILE__), __LINE__, nl2br(print_r($registered_destinations))));
        }

        if ($registered_destinations instanceof SimpleXMLElement) {
            $registered_destinations->registerXPathNamespace('xmlns', 'http://mws.amazonservices.com/schema/Subscriptions/2013-07-01');

            $xpath_result = $registered_destinations->xpath('//xmlns:DestinationList/xmlns:member/xmlns:AttributeList/xmlns:member');

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s(#%d): AmazonRepricing::ListRegisteredDestinations - Queues Found: %s', basename(__FILE__), __LINE__, nl2br(print_r($xpath_result, true))));
            }

            if (is_array($xpath_result) && count($xpath_result)) {
                foreach ($xpath_result as $queue) {
                    if (!$queue instanceof SimpleXMLElement) {
                        continue;
                    }
                    if (!property_exists($queue, 'Value')) {
                        continue;
                    }

                    // Value = https://sqs.us-east-1.amazonaws.com/828718987559/Test
                    $queue_name = trim(basename((string)$queue->Value));

                    $registered[$queue_name] = (string)$queue->Value;
                }
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): Amazon - Queue Registered Found: %s', basename(__FILE__), __LINE__, nl2br(print_r($registered, true))));
                }
            }
        }

        return ($registered);
    }

    public static function registerDestination($amazonApi, $target_queue)
    {
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): AmazonRepricing::RegisterDestination - Target Queue: %s', basename(__FILE__), __LINE__, $target_queue));
        }
        $register_result = $amazonApi->RegisterDestination($target_queue);

        if (Amazon::$debug_mode) {
            CommonTools::p($register_result->asXML());
        }

        if (isset($register_result->ResponseMetadata->RequestId) && isset($register_result->RegisterDestinationResult)) {
            return (true);
        }

        return (false);
    }


    public static function deregisterDestination($amazonApi, $target_queue)
    {
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): AmazonRepricing::DeregisterDestination - Target Queue: %s', basename(__FILE__), __LINE__, $target_queue));
        }
        $unregister_result = $amazonApi->DeregisterDestination($target_queue);

        if (Amazon::$debug_mode) {
            CommonTools::p($unregister_result->asXML());
        }

        if (isset($unregister_result->ResponseMetadata->RequestId) && isset($unregister_result->DeregisterDestinationResult)) {
            return (true);
        }

        return (false);
    }


    public static function testQueue($amazonApi, $target_queue)
    {
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): AmazonRepricing::testQueue - Target Queue: %s', basename(__FILE__), __LINE__, $target_queue));
        }
        $notification_result = $amazonApi->sendTestNotificationToDestination($target_queue);

        if (Amazon::$debug_mode) {
            CommonTools::p($notification_result->asXML());
        }

        return ($notification_result);
    }

    public static function checkSubscription($amazonApi, $target_queue)
    {
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): AmazonRepricing::checkSubscription - Target Queue: %s', basename(__FILE__), __LINE__, $target_queue));
        }
        $subscription_result = $amazonApi->getSubscription($target_queue);

        if (Amazon::$debug_mode) {
            CommonTools::p($subscription_result->asXML());
        }

        $subscription_result->registerXPathNamespace('xmlns', 'http://mws.amazonservices.com/schema/Subscriptions/2013-07-01');

        $xpath_result = $subscription_result->xpath('//xmlns:IsEnabled/text()');

        if (is_array($xpath_result)) {
            $isEnabled = reset($xpath_result);

            if ((string)$isEnabled == 'true') {
                return (true);
            }
        }

        return (false);
    }

    public static function createSubscription($amazonApi, $target_queue)
    {
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): AmazonRepricing::createSubscription - Target Queue: %s', basename(__FILE__), __LINE__, $target_queue));
        }
        $subscription_result = $amazonApi->createSubscription($target_queue);

        if (Amazon::$debug_mode) {
            CommonTools::p($subscription_result->asXML());
        }
        $subscription_result->registerXPathNamespace('xmlns', 'http://mws.amazonservices.com/schema/Subscriptions/2013-07-01');

        $xpath_result = $subscription_result->xpath('//xmlns:ResponseMetadata/xmlns:RequestId');

        if (is_array($xpath_result) && count($xpath_result)) {
            return (true);
        }

        return (false);
    }

    public static function deleteSubscription($amazonApi, $target_queue)
    {
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): AmazonRepricing::deleteSubscription - Target Queue: %s', basename(__FILE__), __LINE__, $target_queue));
        }
        $subscription_result = $amazonApi->deleteSubscription($target_queue);

        if (Amazon::$debug_mode) {
            CommonTools::p($subscription_result->asXML());
        }
        $subscription_result->registerXPathNamespace('xmlns', 'http://mws.amazonservices.com/schema/Subscriptions/2013-07-01');

        $xpath_result = $subscription_result->xpath('//xmlns:ResponseMetadata/xmlns:RequestId');

        if (is_array($xpath_result) && count($xpath_result)) {
            return (true);
        }

        return (false);
    }


    public static function countrySelector()
    {
        return (AmazonSpecificField::countrySelector());
    }

    public static function getIdAdressForTaxes()
    {
        $country_iso_code = Tools::strtolower(Configuration::get('PS_LOCALE_COUNTRY'));
        $id_country = (int)Country::getByIso($country_iso_code);

        $any_customer_address = Db::getInstance()->getRow('SELECT id_address FROM `'._DB_PREFIX_.'address` WHERE id_country='.(int)$id_country.' AND id_customer > 0');
        $id_address = isset($any_customer_address['id_address']) ? (int)$any_customer_address['id_address'] : null;

        return ($id_address);
    }

    public static function generateFakeNotification($params)
    {
        $firstItem = reset($params['items']);
        $merchantId = $params['merchantId'];
        $marketplaceId = $params['marketplaceId'];

        $notification = new SimpleXMLElement(html_entity_decode('&lt;Notification /&gt;'));
        $notification->addChild('NotificationMetaData');
        $notification->NotificationMetaData->addChild('PayloadVersion', '1.0');
        $notification->NotificationMetaData->addChild('UniqueId', uniqid());
        $notification->NotificationMetaData->addChild('PublishTime', date('c'));
        $notification->NotificationMetaData->addChild('SellerId', $merchantId);
        $notification->NotificationMetaData->addChild('MarketplaceId', $marketplaceId);

        $notification->addChild('NotificationPayload');
        $notification->NotificationPayload->addChild('AnyOfferChangedNotification');
        $notification->NotificationPayload->AnyOfferChangedNotification->addChild('OfferChangeTrigger');
        $notification->NotificationPayload->AnyOfferChangedNotification->OfferChangeTrigger->addChild('MarketplaceId', $marketplaceId);
        $notification->NotificationPayload->AnyOfferChangedNotification->OfferChangeTrigger->addChild('ASIN', $firstItem['ASIN']);
        $notification->NotificationPayload->AnyOfferChangedNotification->OfferChangeTrigger->addChild('ItemCondition', $firstItem['condition']);
        $notification->NotificationPayload->AnyOfferChangedNotification->OfferChangeTrigger->addChild('TimeOfOfferChange', date('c'));

        $notification->NotificationPayload->AnyOfferChangedNotification->addChild('Summary');
        $notification->NotificationPayload->AnyOfferChangedNotification->Summary->addChild('NumberOfOffers');
        $notification->NotificationPayload->AnyOfferChangedNotification->Summary->NumberOfOffers->addChild('OfferCount');

        $notification->NotificationPayload->AnyOfferChangedNotification->Summary->addChild('LowestPrices');
        $notification->NotificationPayload->AnyOfferChangedNotification->Summary->addChild('SalesRankings');

        $notification->NotificationPayload->AnyOfferChangedNotification->addChild('Offers');

        foreach (array('AAAAAAAAAAAAAZ', $merchantId) as $key => $merchantID) {
            foreach ($params['items'] as $item) {
                $lowestPrice = $notification->NotificationPayload->AnyOfferChangedNotification->Summary->LowestPrices->addChild('LowestPrice');

                $lowestPrice->addAttribute('condition', $item['condition']);
                $lowestPrice->addAttribute('fulfillmentChannel', 'Amazon');

                $lowestPrice->addChild('LandedPrice');
                $lowestPrice->addChild('ListingPrice');
                $lowestPrice->addChild('Shipping');

                $lowestPrice->ListingPrice->addChild('Amount', $item['Price']['Amount']);
                $lowestPrice->ListingPrice->addChild('CurrencyCode', $item['Price']['CurrencyCode']);

                $lowestPrice->Shipping->addChild('Amount', $item['Shipping']['Amount']);
                $lowestPrice->Shipping->addChild('CurrencyCode', $item['Shipping']['CurrencyCode']);


                $newOffer = $notification->NotificationPayload->AnyOfferChangedNotification->Offers->addChild('Offer');
                
                $newOffer->addChild('SellerId', $merchantID);
                $newOffer->addChild('SubCondition', $item['condition']);
                $newOffer->addChild('SellerFeedbackRating');
                $newOffer->SellerFeedbackRating->addChild('SellerPositiveFeedbackRating', $key ? 100 : 0);
                $newOffer->SellerFeedbackRating->addChild('FeedbackCount', $key ? 100 : 0);

                $newOffer->addChild('ShippingTime');
                $newOffer->ShippingTime->addAttribute('minimumHours', '0');
                $newOffer->ShippingTime->addAttribute('maximumHours', '0');
                $newOffer->ShippingTime->addAttribute('availabilityType', 'NOW');

                $newOffer->addChild('ListingPrice');
                $newOffer->ListingPrice->addChild('Amount', $item['Price']['Amount']);
                $newOffer->ListingPrice->addChild('CurrencyCode', $item['Price']['CurrencyCode']);

                $newOffer->addChild('Shipping');
                $newOffer->Shipping->addChild('Amount', ($key ? $item['Shipping']['Amount'] : $item['Shipping']['Amount'] - 0.01));
                $newOffer->Shipping->addChild('CurrencyCode', $item['Shipping']['CurrencyCode']);

                $newOffer->addChild('ShipsFrom');
                $newOffer->ShipsFrom->addChild('Country', Tools::strtoupper($params['shipsFrom']));
                $newOffer->ShipsFrom->addChild('State');

                $newOffer->addChild('IsFulfilledByAmazon', $key ? 'false' : 'true');
                $newOffer->addChild('IsBuyBoxWinner', $key ? 'false' : 'true');
                $newOffer->addChild('IsFeaturedMerchant', $key ? 'false' : 'true');
                $newOffer->addChild('ShipsDomestically', $key ? 'false' : 'true');
            }
        }

        if (Amazon::$debug_mode) {
            $XML = new DOMDocument();
            $XML->loadXML($notification->asXML());
            $XML->formatOutput = true;

            CommonTools::p(htmlentities($XML->saveXML()));
        }

        $notification_xml = $notification->asXML();

        return($notification_xml);
    }
}

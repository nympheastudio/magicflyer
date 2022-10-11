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
require_once(dirname(__FILE__).'/../classes/amazon.strategies.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.repricing.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.batch.class.php');
require_once(dirname(__FILE__).'/../classes/libs/sqs.php');

if (!isset($start_time)) {
    //TODO: Yes, it is defined, we need it to calculate allowed execution_time
    $start_time = microtime(true);
}//TODO: VALIDATION: start_time from init

/**
 * Class AmazonRepricingAutomaton
 */
class AmazonRepricingAutomaton extends Amazon
{
    /**
     *
     */
    const SUBSCRIBE = 1;
    /**
     *
     */
    const CANCEL = 2;
    /**
     *
     */
    const CHECK = 3;
    /**
     *
     */
    const PURGE = 4;

    /**
     *
     */
    const MAX_MAX_EXECUTION_TIME = 120;

    /**
     * @var bool
     */
    protected $verbose = true;

    /**
     * @var null
     */
    protected $script_start_time  = null;
    /**
     * @var null
     */
    protected $max_execution_time = null;

    /**
     * @var
     */
    protected $amazon_id_lang;
    /**
     * @var
     */
    protected $specials;
    /**
     * @var
     */
    protected $useTax;

    /**
     * @var null
     */
    protected $merchantId = null;
    /**
     * @var null
     */
    protected $marketplaceId = null;
    /**
     * @var null
     */
    protected $awsKeyId     = null;
    /**
     * @var null
     */
    protected $awsSecretKey = null;

    /**
     * @var null
     */
    protected $UrlQueueIn  = null;
    /**
     * @var null
     */
    protected $UrlQueueOut = null;

    /**
     * @var null
     */
    protected $region    = null;
    /**
     * @var AmazonWebService
     */
    protected $amazonApi = null;

    /**
     * @var null
     */
    protected $fbaFormula             = null;
    /**
     * @var null
     */
    protected $hasPerProductRepricing = null;

    /**
     * @var null
     */
    protected static $logfilename = null;

    /**
     *
     */
    const MAX_PUSH = 40;

    /**
     * AmazonRepricingAutomaton constructor.
     */
    public function __construct()
    {
        parent::__construct();

        AmazonContext::restore($this->context);

        self::$debug_mode = (bool)Configuration::get('AMAZON_DEBUG_MODE') || (bool)Tools::getValue('debug');

        $this->amazon_features = $this->getAmazonFeatures();
    }

    /**
     * @param $start_time
     */
    public function dispatch($start_time)
    {
        if (($test_action = Tools::getValue('action')) == 'reprice' || $test_action == 'export') {
            echo "Start: ".date('Y/m/d H:i:s')."\n";
        }

        $this->script_start_time = $start_time;

        $server_max_execution_time = (int)@ini_get('max_execution_time');

        // Max execution time is set and is reasonable, otherwise we determine it
        if (is_numeric($server_max_execution_time) && $server_max_execution_time < 5 * 60) {
            $this->max_execution_time = (int)$server_max_execution_time;
        } else {
            $this->max_execution_time = (int)self::MAX_MAX_EXECUTION_TIME;
        }

        // Regions
        //
        $marketPlaceRegion = AmazonConfiguration::get('REGION');
        $marketLang2Region = is_array($marketPlaceRegion) ? array_flip($marketPlaceRegion) : null;

        $lang = $this->amazon_lang = Tools::getValue('lang');

        if (Amazon::$debug_mode) {
            self::p("lang: $lang");
            self::p(print_r($marketLang2Region, true));
        }

        if ($lang && is_array($marketLang2Region) && isset($marketLang2Region[$lang])) {
            $this->amazon_lang = $lang;
            $this->amazon_id_lang = $marketLang2Region[$lang];
        } else {
            echo $this->l('No selected language, nothing to do...');
            die;
        }

        if (!isset($marketLang2Region[$this->amazon_lang]) || empty($marketLang2Region[$this->amazon_lang])) {
            die(Tools::displayError('Wrong parameter lang'));
        }

        if (!is_array($marketPlaceRegion) || !isset($marketPlaceRegion[$this->amazon_id_lang]) || !$marketPlaceRegion[$this->amazon_id_lang]) {
            die(sprintf('%s (%s)', $this->l('Marketplace is not yet configured for this language'), $this->amazon_lang));
        }

        if (Amazon::$debug_mode) {
            self::p("Features: " . print_r($this->amazon_features, true));
        }

        if (!$this->amazon_features['repricing']) {
            die(Tools::displayError($this->l('Repricing feature is not active !')));
        }

        $tokens = Tools::getValue('cron_token');
        $token = Tools::getValue('instant_token');

        if ($tokens) {
            if (!AmazonTools::checkToken($tokens)) {
                die('Wrong Token');
            }
        } else {
            if (!$token || $token != Configuration::get('AMAZON_INSTANT_TOKEN', null, 0, 0)) {
                die('Wrong Token');
            }
        }

        $this->region = $marketPlaceRegion[$this->amazon_id_lang];

        if (empty($this->region)) {
            die(sprintf('%s (%s)', $this->l('Marketplace is not yet configured for this language'), $this->amazon_lang));
        }

        switch (Tools::getValue('action')) {
            case 'check-queue':
                $this->service(self::CHECK);
                break;
            case 'purge-queue':
                $this->service(self::PURGE);
                break;
            case 'check-service':
                $this->service(self::SUBSCRIBE);
                break;
            case 'cancel-service':
                $this->service(self::CANCEL);
                break;
            case 'check':
                $this->checkAWS();
                break;
            case 'reprice':
                $this->reprice();
                break;
            case 'export':
                $this->export();
                break;
            case 'push':
                $this->pushPendingProducts();
                break;
            default:
                die('wrong action');
        }
        echo "Stop: ".date('Y/m/d H:i:s')."\n";
    }

    /**
     * @param string $string
     * @param bool $specific
     * @param null $id_lang
     *
     * @return string
     */
    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $id_lang));
    }

    /**
     *
     */
    public function initAmazonMarketplaceAPI()
    {
        // Init Amazon
        //
        $platform = AmazonTools::selectPlatform($this->amazon_id_lang, Amazon::$debug_mode);

        if (Amazon::$debug_mode) {
            self::p(print_r($platform['auth'], true).print_r($platform['params'], true).print_r($platform['platforms'], true));
        }

        if (is_array($platform) && isset($platform['auth']) && is_array($platform['auth']) && isset($platform['auth']['MerchantID']) && Tools::strlen($platform['auth']['MerchantID'])) {
            $this->merchantId = $platform['auth']['MerchantID'];
        } else {
            die($this->l('Unable to login'));
        }

        if (is_array($platform) && isset($platform['auth']) && is_array($platform['auth']) && isset($platform['auth']['MarketplaceID']) && Tools::strlen($platform['auth']['MarketplaceID'])) {
            $this->marketplaceId = $platform['auth']['MarketplaceID'];
        } else {
            die($this->l('Unable to login'));
        }

        if (!($this->amazonApi = new AmazonWebService($platform['auth'], $platform['params'], null, Amazon::$debug_mode))) {
            die($this->l('Unable to login'));
        }

        $this->currency_code = $platform['params']['Currency'];
    }

    /**
     * @param $action
     */
    /** @noinspection PhpInconsistentReturnPointsInspection */
    public function service($action)
    {
        $callback = Tools::getValue('callback');

        if ($callback == '?') {
            $callback = 'jsonp_'.time();
        }

        $params = Tools::getValue('repricing');

        $awsKeyId = is_array($params) && isset($params['awsKeyId']) ? trim($params['awsKeyId']) : null;
        $awsSecretKey = is_array($params) && isset($params['awsSecretKey']) ? trim($params['awsSecretKey']) : null;

        if (!Tools::strlen($awsKeyId) || !Tools::strlen($awsSecretKey)) {
            die($this->l('Failure: Both of AWS Key Id and AWS Secret Key must be filled'));
        }

        $this->awsSecretKey = $awsSecretKey;
        $this->awsKeyId = $awsKeyId;

        $this->initAmazonMarketplaceAPI();

        switch ($action) {
            case self::CHECK:
                $this->checkQueues($callback);
                break;
            case self::PURGE:
                $this->purgeQueues($callback);
                break;
            case self::CANCEL:
                $this->cancelService($callback);
                break;
            case self::SUBSCRIBE:
                $this->checkService($callback);
                break;
        }
    }


    /**
     * @param $callback
     */
    public function purgeQueues($callback)
    {
        $pass = true;

        $this->setAwsSettings();

        $sqs = new AmazonSQS($this->awsKeyId, $this->awsSecretKey);

        $queues = Tools::getValue('purge_queue');

        if (!is_array($queues) || !count($queues)) {
            die('Please select at least one queue');
        }

        $queues_purged = array();

        foreach ($queues as $queue_name => $url) {
            $result = $sqs->purgeQueue($url);

            if (!(is_array($result) && isset($result['RequestId']) && preg_match('/([a-z0-9]*-){4,}/', $result['RequestId']))) {
                if (Amazon::$debug_mode) {
                    printf('%s(%d): Error'."\n", basename(__FILE__), __LINE__);
                    var_dump($result);
                }
                die('ERROR: Failed to delete queue:'.$queue_name);
            }
            $queues_purged[$queue_name] = $queue_name;
        }

        if (is_array($queues_purged) && count($queues_purged)) {
            //
            die((string)$callback.'('.Tools::jsonEncode(array(
                    'result' => $this->l('Queues successfully purged'),
                    'queues' => $queues_purged
                )).')');
        } else {
            die('ERROR: An unexpected error occured during purge:');
        }
    }

    /**
     * @param $callback
     *
     * @throws Exception
     * @throws SmartyException
     */
    public function checkQueues($callback)
    {
        $pass = true;

        $this->setAwsSettings();

        // Retrieve or Create our queues
        if (!($queueUrls = AmazonRepricing::listQueues($this->awsKeyId, $this->awsSecretKey))) {
            die('Error: Unable to list or create queues - '.$this->region.' - Queues: '.nl2br(print_r($queueUrls, true)));
        }

        if ($queueUrls && Amazon::$debug_mode) {
            self::p(sprintf('%s(#%d): Amazon - Available Queues: %s', basename(__FILE__), __LINE__, print_r($queueUrls, true)));
        }

        $target_in_queue_url = null;
        $target_in_queue_name = null;
        $target_out_queue_url = null;
        $target_out_queue_name = null;

        // Filter Target Queues
        foreach ($queueUrls as $queue_url) {
            $url = trim(dirname(dirname($queue_url)));
            $queue = trim(basename($queue_url));

            if (strpos(AmazonSQS::ENDPOINT_US_EAST, $url) === false) {
                continue;
            }

            if ($queue == AmazonRepricing::getQueueName($this->region, AmazonRepricing::INPUT_QUEUE)) {
                $target_in_queue_url = $queue_url;
                $target_in_queue_name = $queue;
            } elseif ($queue == AmazonRepricing::getQueueName($this->region, AmazonRepricing::OUTPUT_QUEUE)) {
                $target_out_queue_url = $queue_url;
                $target_out_queue_name = $queue;
            }
        }

        $sqs = new AmazonSQS($this->awsKeyId, $this->awsSecretKey);

        if ($target_in_queue_url) {
            $nMessagesIn = AmazonRepricing::countMessages($sqs, $target_in_queue_url);
        } else {
            $nMessagesIn = 0;
        }

        if ($target_out_queue_url) {
            $nMessagesOut = AmazonRepricing::countMessages($sqs, $target_out_queue_url);
        } else {
            $nMessagesOut = 0;
        }

        $view_params = array();
        $view_params[AmazonRepricing::OUTPUT_QUEUE] = array();
        $view_params[AmazonRepricing::OUTPUT_QUEUE]['name'] = $target_out_queue_name;
        $view_params[AmazonRepricing::OUTPUT_QUEUE]['url'] = $target_out_queue_url;
        $view_params[AmazonRepricing::OUTPUT_QUEUE]['count'] = $nMessagesOut;

        $view_params[AmazonRepricing::INPUT_QUEUE] = array();
        $view_params[AmazonRepricing::INPUT_QUEUE]['name'] = $target_in_queue_name;
        $view_params[AmazonRepricing::INPUT_QUEUE]['url'] = $target_in_queue_url;
        $view_params[AmazonRepricing::INPUT_QUEUE]['count'] = $nMessagesIn;

        if ($target_in_queue_url) {
            $context = Context::getContext();
            $context->smarty->assign('data', $view_params);

            $html = $context->smarty->fetch(_PS_MODULE_DIR_.'/amazon/views/templates/admin/configure/helpers/repricing_queues.tpl');

            die((string)$callback.'('.Tools::jsonEncode(array('result' => $html)).')');
        } else {
            die('Unable to list queues for: '.$this->region);
        }
    }

    /**
     * @param $callback
     */
    public function cancelService($callback)
    {
        $pass = true;

        // Retrieve or Create our queues
        if (!($queueUrls = AmazonRepricing::listQueues($this->awsKeyId, $this->awsSecretKey))) {
            die('Error: Unable to list or create queues - '.$this->region.' - Queues: '.nl2br(print_r($queueUrls, true)));
        }

        if ($queueUrls && Amazon::$debug_mode) {
            self::p(sprintf('%s(#%d): Amazon - Available Queues: %s', basename(__FILE__), __LINE__, print_r($queueUrls, true)));
        }

        $target_queue_url = null;
        $target_queue_name = null;

        // Filter Target Queues
        foreach ($queueUrls as $queue_url) {
            $url = trim(dirname(dirname($queue_url)));
            $queue = trim(basename($queue_url));

            if (strpos(AmazonSQS::ENDPOINT_US_EAST, $url) === false) {
                continue;
            }

            if ($queue == AmazonRepricing::getQueueName($this->region, AmazonRepricing::INPUT_QUEUE)) {
                $target_queue_url = $queue_url;
                $target_queue_name = $queue;
                break;
            }
        }

        $registered_destinations = AmazonRepricing::listRegisteredDestinations($this->amazonApi);
        $registered_queue = false;

        // Queue is already registered, checking if this is our own.
        if (is_array($registered_destinations) && count($registered_destinations)) {
            foreach ($registered_destinations as $queue_name => $queue_url) {
                if (strpos($queue_url, AmazonSQS::ENDPOINT_US_EAST) === false) {
                    continue;
                }

                if ($target_queue_name == $queue_name) {
                    $registered_queue = true;
                }
            }
            if ($registered_queue && Amazon::$debug_mode) {
                self::p(printf('%s(#%d): Amazon - Queue well registered: %s', basename(__FILE__), __LINE__, $target_queue_name));
            }
        }

        // Check if our Subscribption exists
        if ($target_queue_url && AmazonRepricing::checkSubscription($this->amazonApi, $target_queue_url)) {
            // Create subscribption to that queue
            if (!AmazonRepricing::deleteSubscription($this->amazonApi, $target_queue_url)) {
                $pass = false;
            }
        }

        // Deregister Destinations
        if ($registered_queue) {
            if (!AmazonRepricing::deregisterDestination($this->amazonApi, $target_queue_url)) {
                $pass = false;
            }
        }

        if (!$pass) {
            die($this->l('Failed to unsubscribe the service'));
        } elseif ($registered_queue && $target_queue_url) {
            $message = $this->l('Repricing service unsubscribed with success');
            die((string)$callback.'('.Tools::jsonEncode(array('result' => $message)).')');
        } else {
            $message = 'Repricing service was already unsubscribed';
            die((string)$callback.'('.Tools::jsonEncode(array('result' => $message)).')');
        }
    }

    /**
     * @param $callback
     */
    public function checkService($callback)
    {
        // Retrieve or Create our queues
        if (!($queueUrls = AmazonRepricing::createQueues($this->awsKeyId, $this->awsSecretKey, $this->region))) {
            die('Error: Unable to list or create queues - '.$this->region.' - Queues: '.nl2br(print_r($queueUrls, true)));
        }

        if ($queueUrls && Amazon::$debug_mode) {
            self::p(sprintf('%s(#%d): Amazon - Available Queues: %s', basename(__FILE__), __LINE__, print_r($queueUrls, true)));
        }

        if (!is_array($queueUrls) || !isset($queueUrls[AmazonRepricing::INPUT_QUEUE]) || !isset($queueUrls[AmazonRepricing::OUTPUT_QUEUE])) {
            die('Error: Missing expected queues - '.nl2br(print_r($queueUrls, true)));
        }

        $target_inqueue_url = $queueUrls[AmazonRepricing::INPUT_QUEUE];
        $target_inqueue_name = AmazonRepricing::getQueueName($this->region, AmazonRepricing::INPUT_QUEUE);

        $target_outqueue_url = $queueUrls[AmazonRepricing::OUTPUT_QUEUE];

        $registered_destinations = AmazonRepricing::listRegisteredDestinations($this->amazonApi);
        $registered_queue = false;

        // Queue is already registered, checking if this is our own.
        if (is_array($registered_destinations) && count($registered_destinations)) {
            foreach ($registered_destinations as $queue_name => $queue_url) {
                if (strpos($queue_url, AmazonSQS::ENDPOINT_US_EAST) === false) {
                    continue;
                }

                if ($target_inqueue_name == $queue_name) {
                    $registered_queue = true;
                }
            }
            if ($registered_queue && Amazon::$debug_mode) {
                self::p(sprintf('%s(#%d): Amazon - Queue already registered: %s', basename(__FILE__), __LINE__, $target_inqueue_name));
            }
        }

        // Our Queue is not yet registered, register
        if (!$registered_queue) {
            $registration_result = AmazonRepricing::registerDestination($this->amazonApi, $target_inqueue_url);

            if ($registration_result == false) {
                die('Error: Failed to register: INPUT_QUEUE'.' - '.$target_inqueue_url);
            }
        }

        // Queue is existing or Created, testing queue
        $testQueueResult = AmazonRepricing::testQueue($this->amazonApi, $target_inqueue_url);

        $awsAccountId = null;

        if (isset($testQueueResult->Error) && isset($testQueueResult->Error->Code)) {
            $errorMsg = (string)$testQueueResult->Error->Message;
            $errorCode = (string)$testQueueResult->Error->Code;
            $pass = false;

            // Catch the case: User has no permission to write to the queue:
            // SQS queue 'https://sqs.us-east-1.amazonaws.com/xx/AmazonPrestashopIQ-fr' does not exist or AWS Account '456465' is not authorized to it
            if ($errorCode == 'DependencyFatalException') {
                if (preg_match("/AWS\sAccount\s'([0-9]*)'\sis\snot\sauthorized\sto\sit/", $errorMsg, $result) == 1) {
                    $awsAccountId = (string)$result[1];
                    $pass = true;

                    if (Amazon::$debug_mode) {
                        self::p(sprintf('%s(#%d): Amazon - AWS Account ID caught: %s', basename(__FILE__), __LINE__, $awsAccountId));
                    }
                }
            }
            if (!$pass) {
                die('Error: Failed to test queue, Amazon returned: '.$errorMsg);
            }
        }

        // Granting Permissions:
        // We got an account ID, we need to give the permission from AWS to MWS to write into the Queue
        if ($awsAccountId) {
            if (!AmazonRepricing::setQueuePermission($this->awsKeyId, $this->awsSecretKey, $target_inqueue_url, $awsAccountId, 'SendMessage')) {
                die('Error: Failed to grant permission to queue: '.$target_inqueue_url);
            }

            if (!AmazonRepricing::setQueuePermission($this->awsKeyId, $this->awsSecretKey, $target_outqueue_url, $awsAccountId, 'SendMessage')) {
                die('Error: Failed to grant permission to queue: '.$target_outqueue_url);
            }

            if (!AmazonRepricing::setQueuePermission($this->awsKeyId, $this->awsSecretKey, $target_outqueue_url, $awsAccountId, 'ReceiveMessage')) {
                die('Error: Failed to grant permission to queue: '.$target_outqueue_url);
            }
        }

        // Check if our Subscribption exists
        if (!AmazonRepricing::checkSubscription($this->amazonApi, $target_inqueue_url)) {
            // Create subscribption to that queue
            if (AmazonRepricing::createSubscription($this->amazonApi, $target_inqueue_url)) {
                $message = $this->l('Repricing service has been configured and activated with success');
                die((string)$callback.'('.Tools::jsonEncode(array('result' => $message)).')');
            } else {
                die('Error: Failed create subscription: '.$target_inqueue_url);
            }
        } else {
            $message = $this->l('Repricing service is already configured and well activated');
            die((string)$callback.'('.Tools::jsonEncode(array('result' => $message)).')');
        }
    }

    /**
     *
     */
    public function checkAWS()
    {
        $callback = Tools::getValue('callback');

        if ($callback == '?') {
            $callback = 'jsonp_'.time();
        }

        $params = Tools::getValue('repricing');

        $awsKeyId = is_array($params) && isset($params['awsKeyId']) ? trim($params['awsKeyId']) : null;
        $awsSecretKey = is_array($params) && isset($params['awsSecretKey']) ? trim($params['awsSecretKey']) : null;

        if (!Tools::strlen($awsKeyId) || !Tools::strlen($awsSecretKey)) {
            die($this->l('Failure: Both of AWS Key Id and AWS Secret Key must be filled'));
        }

        if ($result = AmazonRepricing::checkService($awsKeyId, $awsSecretKey)) {
            $message = $this->l('Connection to Amazon : Ok').'('.$result.')';
            die((string)$callback.'('.Tools::jsonEncode(array('result' => $message)).')');
        } else {
            die($this->l('Warning: Connection to Amazon Failed !'));
        }
    }

    /**
     *
     */
    public function setAwsSettings()
    {
        $repricingSettings = AmazonConfiguration::get('REPRICING');

        if (!isset($repricingSettings['awsKeyId']) || !isset($repricingSettings['awsSecretKey'])) {
            die('Repricing tool is not yet configured');
        }

        $awsKeyId = trim($repricingSettings['awsKeyId']);
        $awsSecretKey = trim($repricingSettings['awsSecretKey']);

        if (empty($awsKeyId) || !preg_match('/([0-9A-Z]{12,})/', $awsKeyId)) {
            die('Wrong AWS Key ID');
        }

        if (empty($awsSecretKey) || Tools::strlen($awsSecretKey) < 10) {
            die('Wrong AWS Secret Key');
        }

        $this->awsSecretKey = $awsSecretKey;
        $this->awsKeyId = $awsKeyId;

        if (!$this->amazon_features['demo_mode']) {
            $queues = AmazonRepricing::listQueues($this->awsKeyId, $this->awsSecretKey);

            if (!is_array($queues) || !count($queues)) {
                die('Unable to retrieve queues from Amazon AWS, please verify your AWS configuration');
            }

            $input_queue = null;
            $output_queue = null;

            foreach ($queues as $queue_url) {
                $url = trim(dirname(dirname($queue_url)));
                $queue = trim(basename($queue_url));

                if (strpos(AmazonSQS::ENDPOINT_US_EAST, $url) === false) {
                    continue;
                }

                if ($queue == AmazonRepricing::getQueueName($this->region, AmazonRepricing::INPUT_QUEUE)) {
                    $input_queue = $queue_url;
                } elseif ($queue == AmazonRepricing::getQueueName($this->region, AmazonRepricing::OUTPUT_QUEUE)) {
                    $output_queue = $queue_url;
                }
            }

            if (!Tools::strlen($input_queue) || !Tools::strlen($output_queue)) {
                die('Unable to find queues for '.$this->region);
            }

            $this->UrlQueueIn = $input_queue;
            $this->UrlQueueOut = $output_queue;
        }
    }


    /**
     *
     */
    public function export()
    {
        $productsUpdate = array();
        $timestart = time();
        $submissionFeedId = null;
        $moduleDataMessages = array();
        $skuObjects = array();

        $this->initAmazonMarketplaceAPI();

        if (Tools::getValue('cron')) {
            $this->logOutputStart();
        }

        $this->setAwsSettings();

        $sqs = new AmazonSQS($this->awsKeyId, $this->awsSecretKey);

        $message_set = AmazonRepricing::retrieveMessages($this->awsKeyId, $this->awsSecretKey, $this->UrlQueueOut, $this->script_start_time, $this->max_execution_time, $this->verbose);

        if (is_array($message_set) && count($message_set)) {
            if ($this->verbose) {
                self::p("Messages Sets:".count($message_set));
            }

            foreach ($message_set as $messages) {
                // First Loop - Checking Notifications
                foreach ($messages as $message) {
                    if (!isset($message['ReceiptHandle']) || !isset($message['MessageId']) || !isset($message['Body']) || !isset($message['MD5OfBody'])) {
                        if (Amazon::$debug_mode) {
                            printf('%s(#%d): One of ReceiptHandle, MessageId, Body, MD5OfBody missing', basename(__FILE__), __LINE__);
                        }
                        continue;
                    }

                    $Body = &$message['Body'];
                    $md5 = $message['MD5OfBody'];

                    if (md5($message['Body']) != $md5) {
                        if (Amazon::$debug_mode) {
                            printf('%s(#%d): md5 mismatch: %s/%s', basename(__FILE__), __LINE__, md5($message['Body']), $md5);
                        }
                        continue;
                    }

                    if (Amazon::$debug_mode) {
                        var_dump($message);
                    }

                    $ReceiptHandle = (string)$message['ReceiptHandle'];
                    $MessageId = (string)$message['MessageId'];

                    $message_content = &$message['Body'];

                    if (strpos($message_content, '{') !== 0) {
                        if (Amazon::$debug_mode) {
                            printf('%s(#%d): wrong content', basename(__FILE__), __LINE__);
                        }
                        continue;
                    }

                    $moduleData = Tools::jsonDecode($message_content);

                    if (!$moduleData instanceof stdClass) {
                        if (Amazon::$debug_mode) {
                            printf('%s(#%d): unable to decode json data', basename(__FILE__), __LINE__);
                        }

                        continue;
                    }

                    if (!isset($moduleData->Data) && !isset($moduleData['Data'])) {
                        if (Amazon::$debug_mode) {
                            printf('%s(#%d): message doesnt contain data', basename(__FILE__), __LINE__);
                            var_dump($moduleData);
                        }

                        continue;
                    }

                    $moduleData->ReceiptHandle = $ReceiptHandle;
                    $moduleData->MessageId = $MessageId;

                    $moduleDataMessages[] = $moduleData;
                }
            }
        } elseif ($this->verbose) {
            self::p("No Messages pending...");
        }

        if (Amazon::$debug_mode) {
            printf('%s(#%d): messages:', basename(__FILE__), __LINE__);
            self::p(($moduleDataMessages));
        }

        if (is_array($moduleDataMessages) && count($moduleDataMessages)) {
            foreach ($moduleDataMessages as $moduleDataMessage) {
                if (is_array($moduleDataMessage)) {
                    $moduleDataMessage = (object)$moduleDataMessage;
                }// on certain environment we get an array instead of an object !

                $date = $moduleDataMessage->Date;
                $timestamp = strtotime($moduleDataMessage->Date);

                // Group by SKU, Date
                foreach ($moduleDataMessage->Data as $skuItem) {
                    $skuItem->date = $date;
                    $skuItem->timestamp = $timestamp;
                    $skuObjects[$skuItem->SKU][] = $skuItem; // preserve the items as unique (as the index is the SKU)
                }
            }

            if (is_array($skuObjects) && count($skuObjects)) {
                if ($this->verbose) {
                    self::p("Product Feed:");
                }

                foreach ($skuObjects as $skuObjectArray) {
                    $currentSkuObject = reset($skuObjectArray);

                    if (is_array($skuObjectArray) && count($skuObjectArray) > 1) {
                        $timestamp = 0;

                        foreach ($skuObjectArray as $skuObject) {
                            if ($skuObject->timestamp > $timestamp) {
                                // take the older

                                $currentSkuObject = $skuObject;
                            }
                        }
                    }

                    if ($this->verbose) {
                        printf('%s: SKU: %s Price: %.02f'."\n", $currentSkuObject->date, $currentSkuObject->SKU, $currentSkuObject->Price);
                    }

                    unset($currentSkuObject->date);
                    unset($currentSkuObject->timestamp);

                    $productsUpdate[] = (array)$currentSkuObject; // Revert To Arrray, Convert to an indexed array to be compatible with the AmazonWebService class format
                }
            }

            if (is_array($productsUpdate) && count($productsUpdate)) {
                if ($this->verbose) {
                    self::p("Preparing Feed Submission for".count($productsUpdate)." offers");
                }


                // Submit Product Feed to Amazon
                $submissionFeedId = $this->amazonApi->updatePricesFeed($productsUpdate);
            }

            if ($submissionFeedId || !count($productsUpdate)) {
                // if feed has been submitted we delete the previous queue

                foreach ($moduleDataMessages as $moduleDataMessage) {
                    $delete_result = $sqs->deleteMessage($this->UrlQueueOut, (string)$moduleDataMessage->ReceiptHandle);

                    if (!(is_array($delete_result) && isset($delete_result['RequestId']) && preg_match('/([a-z0-9]*-){4,}/', $delete_result['RequestId']))) {
                        if (Amazon::$debug_mode) {
                            printf('%s(%d): Error'."\n", basename(__FILE__), __LINE__);
                            var_dump($delete_result);
                        }
                        self::p('ERROR: Failed to delete message Id:'.$moduleDataMessage->MessageId);
                    }
                }
            }
        }

        if ($submissionFeedId) {
            // Save Session
            $batches = new AmazonBatches('session_repricing');
            $batch = new AmazonBatch($timestart);
            $batch->id = uniqid();
            $batch->timestop = time();
            $batch->type = $this->l('Cron');
            $batch->region = $this->region;
            $batch->created = 0;
            $batch->updated = count($productsUpdate);
            $batch->deleted = 0;
            $batches->add($batch);
            $batches->save();

            $batches = new AmazonBatches('batch_repricing');
            $batch = new AmazonBatch($timestart);
            $batch->id = $submissionFeedId;
            $batch->timestop = time();
            $batch->type = 'Repricing';
            $batch->region = $this->region;
            $batch->created = 0;
            $batch->updated = count($productsUpdate);
            $batch->deleted = 0;
            $batches->add($batch);
            $batches->save();

            if ($this->verbose) {
                self::p("Feed update completed, Submission Feed Id:".$submissionFeedId);
            }
        } elseif (is_array($productsUpdate) && count($productsUpdate) && !$submissionFeedId) {
            if ($this->verbose) {
                self::p("Failed to submit Price Feed to Amazon !");
            }
        }

        return;
    }

    /**
     *
     */
    public function pushPendingProducts()
    {
        $skus_to_acknowledge = array();
        $competitions = array();
        $my_prices = array();

        if (Tools::getValue('cron')) {
            $this->logOutputStart();
        }

        $this->initReprice();

        $p = AmazonProduct::marketplaceActionList($this->amazon_id_lang, Amazon::REPRICE);

        $i=0;

        if (is_array($p) && count($p)) {
            if (Amazon::$debug_mode) {
                self::p("Pending Products");
                self::p(print_r($p, true));
            }
            $skus = array();

            foreach ($p as $item) {
                if (!isset($item['sku']) || !AmazonTools::validateSKU($item['sku'])) {
                    continue;
                }
                if ($i++ >= self::MAX_PUSH) {
                    continue;
                }
                $skus[] = $item['sku'];
            }
            $skus_to_acknowledge = $skus ;

            if (is_array($skus) && count($skus)) {
                $competitions = $this->getCompetitivePricingForSKU($skus);
            }
        }
        if (is_array($competitions) && count($competitions)) {
            $skus = array_keys($competitions);

            $my_prices = $this->getMyPriceForSKU($skus);
        }

        if (is_array($my_prices) && count($my_prices)) {
            $params = array();
            $params['merchantId'] = $this->merchantId;
            $params['marketplaceId'] = $this->marketplaceId;
            $params['shipsFrom'] = $this->region;
            $params['items'] = array();

            foreach ($my_prices as $sku => $my_price) {
                $product = new AmazonProduct($sku);

                // Update ASIN
                if (Validate::isLoadedObject($product)) {
                    $result = AmazonProduct::updateProductOptions($product->id, $this->amazon_id_lang, 'asin1', $my_price['ASIN'], $product->id_product_attribute);

                    if (!$result && $this->verbose) {
                        self::p(sprintf('%s(%d): %s "%s"', basename(__FILE__), __LINE__, 'Unable to save product options', $sku));
                    }
                }

                $params['items'][$sku] = array();
                $params['items'][$sku]['ASIN'] = $my_price['ASIN'];
                $params['items'][$sku]['condition'] = 'new';

                $params['items'][$sku]['Price'] = array();
                $params['items'][$sku]['Price']['Amount'] = Tools::ps_round($competitions[$sku]['price'], 2);
                $params['items'][$sku]['Price']['CurrencyCode'] = 'EUR';

                $params['items'][$sku]['Shipping'] = array();
                $params['items'][$sku]['Shipping']['Amount'] = Tools::ps_round($competitions[$sku]['shipping'], 2);
                $params['items'][$sku]['Shipping']['CurrencyCode'] = 'EUR';
            }

            if (is_array($params['items']) && count($params['items'])) {
                $fake_notification_xml = AmazonRepricing::generateFakeNotification($params);

                $sqs = new AmazonSQS($this->awsKeyId, $this->awsSecretKey);

                if (!$this->UrlQueueOut) {
                    die('Missing expected queue');
                }

                $result = $sqs->sendMessage($this->UrlQueueIn, $fake_notification_xml);

                if (Amazon::$debug_mode) {
                    self::p($result);
                }
            }

            AmazonProduct::marketplaceActionAcknowledgde(Amazon::REPRICE, $this->amazon_id_lang, $skus_to_acknowledge, date('Y-m-d H:i:s'));
        }
    }

    /**
     * @param $skus
     *
     * @return array
     */
    public function getMyPriceForSKU($skus)
    {
        $probeCount = 0;
        $loop_start_time = microtime(true);
        $remainingSKUs = $skus;
        $my_prices = array();

        while (is_array($remainingSKUs) && count($remainingSKUs)) {
            $i = 0;
            $slice = array_slice($remainingSKUs, 0, 20);
            $remainingSKUs = array_slice($remainingSKUs, 20);

            if (count($slice)) {
                $i++;
                $loop_average = (microtime(true) - $loop_start_time) / ($i + 1);
                $total_elapsed = microtime(true) - $this->script_start_time;
                $max_estimated = (($loop_start_time - $this->script_start_time) + $loop_average * $i * 1.4);

                if ($this->verbose) {
                    self::p(sprintf('Loop average: %.02f, Max estimated: %.02f, Total Elapsed: %.02f', $loop_average, $max_estimated, $total_elapsed));
                }

                if ($this->max_execution_time && ($max_estimated >= $this->max_execution_time || $total_elapsed >= $this->max_execution_time)) {
                    if ($this->verbose) {
                        self::p(sprintf('%s(%d): %s (%d/%d/%d)', basename(__FILE__), __LINE__, 'Warning: time allowed is about to be reached, loop aborted', $this->max_execution_time, $max_estimated, $total_elapsed));
                        break;
                    }
                }
                $result = $this->amazonApi->getMyPriceForSKU($slice);

                if ($result instanceof SimpleXMLElement) {
                    if (Amazon::$debug_mode) {
                        $XML = new DOMDocument();
                        $XML->loadXML($result->asXML());
                        $XML->formatOutput = true;

                        self::p(sprintf('%s(%d)', basename(__FILE__), __LINE__));
                        self::p(htmlentities($XML->saveXML()));
                    }

                    foreach ($result as $getMyPriceItem) {
                        $probeCount++;
                        $getMyPriceItem->registerXPathNamespace('xmlns', 'http://mws.amazonservices.com/schema/Products/2011-10-01');
                        $xpath_identifier = $getMyPriceItem->xpath('xmlns:Product/xmlns:Identifiers/xmlns:MarketplaceASIN/xmlns:ASIN/text()');
                        $xpath_sku = $getMyPriceItem->xpath('xmlns:Product/xmlns:Identifiers/xmlns:SKUIdentifier/xmlns:SellerSKU/text()');
                        $xpath_offer = $getMyPriceItem->xpath('xmlns:Product/xmlns:Offers');

                        $first_offer = reset($xpath_offer);

                        if ($first_offer instanceof SimpleXMLElement) {
                            $ASIN = (string)reset($xpath_identifier);
                            $SKU = (string)reset($xpath_sku);
                            $price = (float)$first_offer->Offer->BuyingPrice->ListingPrice->Amount;
                            $shipping = (float)$first_offer->Offer->BuyingPrice->ListingPrice->Amount;

                            $my_price = array('ASIN' => $ASIN, 'SKU' => $SKU, 'price' => $price, 'shipping' => $shipping);

                            if (Amazon::$debug_mode) {
                                self::p(sprintf('%s(%d)', basename(__FILE__), __LINE__));
                                self::p('My Price:'.print_r($my_price, true));
                            }
                            $my_prices[$SKU] = $my_price;
                        }
                    }
                }
            }
        }
        return($my_prices);
    }

    /**
     * @param $skus
     *
     * @return array
     */
    public function getCompetitivePricingForSKU($skus)
    {
        $probeCount = 0;
        $loop_start_time = microtime(true);
        $remainingSKUs = $skus;
        $competitions = array();

        while (is_array($remainingSKUs) && count($remainingSKUs)) {
            $i = 0;
            $slice = array_slice($remainingSKUs, 0, 20);
            $remainingSKUs = array_slice($remainingSKUs, 20);

            if (count($slice)) {
                $i++;
                $loop_average = (microtime(true) - $loop_start_time) / ($i + 1);
                $total_elapsed = microtime(true) - $this->script_start_time;
                $max_estimated = (($loop_start_time - $this->script_start_time) + $loop_average * $i * 1.4);

                if ($this->verbose) {
                    self::p(sprintf('Loop average: %.02f, Max estimated: %.02f, Total Elapsed: %.02f', $loop_average, $max_estimated, $total_elapsed));
                }

                if ($this->max_execution_time && ($max_estimated >= $this->max_execution_time || $total_elapsed >= $this->max_execution_time)) {
                    if ($this->verbose) {
                        self::p(sprintf('%s(%d): %s (%d/%d/%d)', basename(__FILE__), __LINE__, 'Warning: time allowed is about to be reached, loop aborted', $this->max_execution_time, $max_estimated, $total_elapsed));
                        break;
                    }
                }
                $result = $this->amazonApi->getCompetitivePricingForSKU($slice);

                if ($result instanceof SimpleXMLElement) {
                    if (Amazon::$debug_mode) {
                        $XML = new DOMDocument();
                        $XML->loadXML($result->asXML());
                        $XML->formatOutput = true;

                        self::p(sprintf('%s(%d)', basename(__FILE__), __LINE__));
                        self::p(htmlentities($XML->saveXML()));
                    }

                    foreach ($result as $getMyPriceItem) {
                        $probeCount++;
                        $getMyPriceItem->registerXPathNamespace('xmlns', 'http://mws.amazonservices.com/schema/Products/2011-10-01');
                        $xpath_identifier = $getMyPriceItem->xpath('xmlns:Product/xmlns:Identifiers/xmlns:MarketplaceASIN/xmlns:ASIN/text()');
                        $xpath_sku = $getMyPriceItem->xpath('xmlns:Product/xmlns:Identifiers/xmlns:SKUIdentifier/xmlns:SellerSKU/text()');
                        $xpath_offer = $getMyPriceItem->xpath('xmlns:Product/xmlns:CompetitivePricing/xmlns:CompetitivePrices');

                        $best_offer = reset($xpath_offer);

                        if ($best_offer instanceof SimpleXMLElement) {
                            $belongsToMe = (string)$best_offer->CompetitivePrice->attributes()->belongsToRequester == 'false' ? false :  true;
                            $ASIN = (string)reset($xpath_identifier);
                            $SKU = (string)reset($xpath_sku);
                            $price = (float)$best_offer->CompetitivePrice->Price->ListingPrice->Amount;
                            $shipping = (float)$best_offer->CompetitivePrice->Price->Shipping->Amount;

                            if (!$belongsToMe) {
                                $competition = array('ASIN' => $ASIN, 'SKU' => $SKU, 'price' => $price, 'shipping' => $shipping);

                                if (Amazon::$debug_mode) {
                                    self::p(sprintf('%s(%d)', basename(__FILE__), __LINE__));
                                    self::p('Competition:'.print_r($competition, true));
                                }
                                $competitions[$SKU] = $competition;
                            }
                        }
                    }
                }
            }
        }
        return($competitions);
    }

    /**
     *
     */
    public function initReprice()
    {
        $this->initAmazonMarketplaceAPI();

        if (Tools::getValue('cron')) {
            $this->logOutputStart();
        }

        $this->setAwsSettings();

        $this->conditionMap = array_flip(AmazonConfiguration::get('CONDITION_MAP'));
        $this->specials = (int)AmazonConfiguration::get('SPECIALS') ? true : false;
        $this->useTax = (int)AmazonConfiguration::get('TAXES') ? true : false;

        $this->formulas = AmazonConfiguration::get('PRICE_FORMULA');
        $this->price_rules = AmazonConfiguration::get('PRICE_RULE');

        $params = AmazonConfiguration::get('PRICE_ROUNDING');
        $this->rounding = isset($params[$this->amazon_id_lang]) && is_numeric($params[$this->amazon_id_lang]) ? $params[$this->amazon_id_lang] : null;

        // Profiles
        //
        $this->categories = AmazonConfiguration::get('categories');
        $this->profile = AmazonConfiguration::get('profiles');
        $this->profile2category = AmazonConfiguration::get('profiles_categories');
        $this->default_strategies = AmazonConfiguration::get('default_strategies');

        $this->strategies = $this->getStrategies($this->amazon_id_lang);

        if (!is_array($this->categories) || !count($this->categories)) {
            die('Amazon categories are not yet configured');
        }

        if (!$this->id_address = AmazonRepricing::getIdAdressForTaxes()) {
            die('unable to determined id_address for your shop');
        }

        $pass = true;

        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
        $this->id_shop = $this->id_shop = $this->context->shop->id;
        $this->id_warehouse = null;


        $this->toCurrency = new Currency(Currency::getIdByIsoCode($this->currency_code));
        $this->fromCurrency = new Currency((int)(Configuration::get('PS_CURRENCY_DEFAULT')));

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $cart = $this->context->cart;
            $cookie = $this->context->cookie;
            $cart->id_currency = $cookie->id_currency = $this->fromCurrency->id;
        } else {
            AmazonContext::restore($this->context);

            $employee = null;
            $id_employee = Configuration::get('AMAZON_EMPLOYEE');

            if ($id_employee) {
                $employee = new Employee($id_employee);
            }

            if (!Validate::isLoadedObject($employee)) {
                die($this->l('Wrong Employee, please save the module configuration'));
            }

            $this->id_warehouse = (int)Configuration::get('AMAZON_WAREHOUSE');

            if (!$this->id_shop) {
                $this->id_shop = 1;
            }
            $this->context->customer->is_guest = true;
            $this->context->customer->id_default_group = (int)Configuration::get('AMAZON_CUSTOMER_GROUP');
            $this->context->cart = new Cart();
            $this->context->employee = $employee;
            $this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
        $product_options_fields = AmazonProduct::getProductOptionFields();

        if (is_array($product_options_fields) && count($product_options_fields) && in_array('repricing_min', $product_options_fields) && in_array('repricing_max', $product_options_fields)) {
            $this->hasPerProductRepricing = true;
        } else {
            $this->hasPerProductRepricing = false;
        }

        // FBA
        //
        $FBA = (bool)$this->amazon_features['fba'];

        $params = AmazonConfiguration::get('FBA_PRICE_FORMULA');

        if (!empty($params) || $params != '@' && $FBA) {
            $this->fbaFormula = $params;
        } else {
            $this->fbaFormula = null;
        }
    }

    /**
     *
     */
    public function reprice()
    {
        $this->initReprice();

        if (!$this->amazon_features['demo_mode']) {
            $sqs = new AmazonSQS($this->awsKeyId, $this->awsSecretKey);

            $message_set = AmazonRepricing::retrieveMessages($this->awsKeyId, $this->awsSecretKey, $this->UrlQueueIn, $this->script_start_time, $this->max_execution_time, $this->verbose);
        } else {
            $message = $this->returnDemo(__FUNCTION__);

            $message_set = array();
            $message_set[][] = array('ReceiptHandle' => '', 'MessageId' => '123', 'Body' => $message, 'MD5OfBody' => md5($message));
        }

        $notifications = array();
        $probeASINs = array();

        $skuItems = array();

        if (is_array($message_set) && count($message_set)) {
            self::p("Messages Sets:".count($message_set));

            // First Loop - Checking Notifications
            foreach ($message_set as $messages) {
                foreach ($messages as $message) {
                    if (!isset($message['ReceiptHandle']) || !isset($message['MessageId']) || !isset($message['Body']) || !isset($message['MD5OfBody'])) {
                        continue;
                    }

                    if (Amazon::$debug_mode) {
                        $XML = new DOMDocument();
                        $XML->loadXML($message['Body']);
                        $XML->formatOutput = true;

                        self::p(sprintf('%s(%d)', basename(__FILE__), __LINE__));
                        self::p(htmlentities($XML->saveXML()));
                    }

                    $Body = &$message['Body'];
                    $md5 = $message['MD5OfBody'];

                    if (md5($message['Body']) != $md5) {
                        if (Amazon::$debug_mode) {
                            self::p(sprintf('%s(%d): wrong md5', basename(__FILE__), __LINE__));
                        }
                        continue;
                    }

                    $ReceiptHandle = (string)$message['ReceiptHandle'];
                    $MessageId = (string)$message['MessageId'];

                    $notification_content = &$message['Body'];

                    $notificationTag = html_entity_decode('&lt;Notification&gt;');
                    if (strpos($notification_content, $notificationTag) === false) {
                        if (Amazon::$debug_mode) {
                            self::p(sprintf('%s(%d): wrong Notification', basename(__FILE__), __LINE__));
                        }
                        continue;
                    }

                    $notification = simplexml_load_string($notification_content);

                    if (!property_exists($notification, 'NotificationPayload') || !property_exists($notification->NotificationPayload, 'AnyOfferChangedNotification')) {
                        continue;
                    }

                    $uniqueId = (string)$notification->NotificationMetaData->UniqueId;
                    $dateTime = (string)$notification->NotificationMetaData->PublishTime;
                    $marketplaceId = (string)$notification->NotificationMetaData->MarketplaceId;

                    self::p("Marketplace Id:".$marketplaceId);
                    self::p("Messages Id:".$uniqueId);
                    self::p("Messages Date/Time:".$dateTime);

                    $notification->addChild('ReceiptHandle', $ReceiptHandle);
                    $notification->addChild('MessageId', $MessageId);

                    $notifications[] = $notification;
                }
            }

            // Second Loop - Find Missing SKUs for ASINs
            foreach ($notifications as $notification) {
                $ASIN = (string)$notification->NotificationPayload->AnyOfferChangedNotification->OfferChangeTrigger->ASIN;

                if (!AmazonProduct::checkAsin($this->amazon_id_lang, $ASIN)) {
                    $probeASINs[$ASIN] = true;
                }
            }
            if (count($probeASINs) && $this->verbose) {
                self::p("ASINs/SKU to probe:");
                self::p($probeASINs);
            }

            // Third Loop - Grab SKU from Product API and fill the database
            $remainingASINs = array_keys($probeASINs);

            $this->asinResolution($remainingASINs);

            if ($this->verbose) {
                $count_notifications = count($notifications);
                self::p("Starting Repricing for : $count_notifications");
            }

            // Forth Loop - Calculate
            foreach ($notifications as $notification) {
                $result = $this->repricing($notification);

                if (is_array($result) && count($result)) {
                    $skuItems = array_merge($skuItems, $result);
                }

                if (!$this->amazon_features['demo_mode']) {
                    $delete_result = $sqs->deleteMessage($this->UrlQueueIn, (string)$notification->ReceiptHandle);

                    if (!(is_array($delete_result) && isset($delete_result['RequestId']) && preg_match('/([a-z0-9]*-){4,}/', $delete_result['RequestId']))) {
                        if (Amazon::$debug_mode) {
                            self::p(sprintf('%s(%d): Error', basename(__FILE__), __LINE__));
                            self::p($delete_result);
                        }
                        if ($this->verbose) {
                            self::p('ERROR: Failed to delete message Id:'.$notification->MessageId);
                        }
                    }
                }
            }
        }

        if (is_array($skuItems) && count($skuItems)) {
            $this->sendToQueue($skuItems);
        }
    }

    /**
     * @param $asins
     *
     * @return int
     */
    public function asinResolution($asins)
    {
        $probeCount = 0;
        $ASINresolutions = array();
        $loop_start_time = microtime(true);
        $remainingASINs = $asins;

        while (is_array($remainingASINs) && count($remainingASINs)) {
            $i = 0;
            $slice = array_slice($remainingASINs, 0, 20);
            $remainingASINs = array_slice($remainingASINs, 20);

            if (count($slice)) {
                $i++;
                $loop_average = (microtime(true) - $loop_start_time) / ($i + 1);
                $total_elapsed = microtime(true) - $this->script_start_time;
                $max_estimated = (($loop_start_time - $this->script_start_time) + $loop_average * $i * 1.4);

                if ($this->verbose) {
                    self::p(sprintf('Loop average: %.02f, Max estimated: %.02f, Total Elapsed: %.02f', $loop_average, $max_estimated, $total_elapsed));
                }

                if ($this->max_execution_time && ($max_estimated >= $this->max_execution_time || $total_elapsed >= $this->max_execution_time)) {
                    if ($this->verbose) {
                        self::p(sprintf('%s(%d): %s (%d/%d/%d)', basename(__FILE__), __LINE__, 'Warning: time allowed is about to be reached, loop aborted', $this->max_execution_time, $max_estimated, $total_elapsed));
                        break;
                    }
                }
                $result = $this->amazonApi->getMyPriceForASIN($slice);

                if ($result instanceof SimpleXMLElement) {
                    foreach ($result as $getMyPriceItem) {
                        $probeCount++;
                        $getMyPriceItem->registerXPathNamespace('xmlns', 'http://mws.amazonservices.com/schema/Products/2011-10-01');
                        $xpath_identifier = $getMyPriceItem->xpath('xmlns:Product/xmlns:Identifiers/xmlns:MarketplaceASIN/xmlns:ASIN/text()');
                        $xpath_offer = $getMyPriceItem->xpath('xmlns:Product/xmlns:Offers');

                        $offer = reset($xpath_offer);

                        if (!is_object($offer) || !property_exists($offer, 'Offer')) {
                            continue;
                        }

                        $ASIN = (string)reset($xpath_identifier);

                        $ItemCondition = (string)$offer->Offer->ItemCondition;//TODO: check if it is possible to have the same ASIN for different condition in our database
                        $SellerSKU = (string)$offer->Offer->SellerSKU;

                        if (!Tools::strlen($SellerSKU) || !AmazonTools::validateSKU($SellerSKU)) {
                            if ($this->verbose) {
                                self::p(sprintf('%s(%d): %s "%s"', basename(__FILE__), __LINE__, 'SKU is not valid', $SellerSKU));
                                break;
                            }
                            continue;
                        }

                        $product = new AmazonProduct($SellerSKU);

                        if (Validate::isLoadedObject($product)) {
                            $ASINresolutions[$ASIN] = $SellerSKU;
                            $result = AmazonProduct::updateProductOptions($product->id, $this->amazon_id_lang, 'asin1', $ASIN, $product->id_product_attribute);

                            if (!$result && $this->verbose) {
                                self::p(sprintf('%s(%d): %s "%s"', basename(__FILE__), __LINE__, 'Unable to save product options', $SellerSKU));
                                break;
                            }
                        } else {
                            if ($this->verbose) {
                                self::p(sprintf('%s(%d): %s "%s"', basename(__FILE__), __LINE__, 'Unable to load product', $SellerSKU));
                                break;
                            }
                        }
                    }
                }
            }
        }
        if (count($ASINresolutions) && $this->verbose) {
            self::p("SKU/ASIN solved:");
            self::p($ASINresolutions);
        }
        return($probeCount);
    }

    /**
     * @param $skuItems
     */
    public function sendToQueue($skuItems)
    {
        echo str_repeat('-', 160)."\n";
        self::p("Ready to send message to queue:".$this->UrlQueueOut);

        if (is_array($skuItems) && count($skuItems)) {
            if ($this->verbose) {
                echo "Amazon Price Feed:";
                self::p($skuItems);
            }

            $message = array(
                'Date' => date('c'),
                'From' => AmazonTools::encodeText(Configuration::get('PS_SHOP_NAME'), true),
                'Count' => count($skuItems),
                'Data' => $skuItems
            );

            $json_message = Tools::jsonEncode($message);

            $sqs = new AmazonSQS($this->awsKeyId, $this->awsSecretKey);

            if (!$this->UrlQueueOut) {
                die('Missing expected queue');
            }

            $result = $sqs->sendMessage($this->UrlQueueOut, $json_message);

            if (!(is_array($result) && isset($result['RequestId']) && isset($result['MessageId']) && preg_match('/([a-z0-9]*-){4,}/', $result['RequestId']))) {
                if (Amazon::$debug_mode) {
                    self::p(sprintf('%s(%d): Error'."\n", basename(__FILE__), __LINE__));
                    self::p($result);
                }
                die('Failed to send message to the queue:'.$this->UrlQueueOut);
            }

            if ($this->verbose) {
                self::p("Message successfully sent to message queue, Id:".$result['MessageId']);
            }
        }
    }

    /**
     * @param $notification
     *
     * @return array|bool
     */
    public function repricing(&$notification)
    {
        $handle_combinations = !(bool)Configuration::get('AMAZON_NO_COMBINATIONS');
        $skuItems = array();
        $u = 0;

        $ASIN = (string)$notification->NotificationPayload->AnyOfferChangedNotification->OfferChangeTrigger->ASIN;
        $NotificationDateTime = (string)$notification->NotificationMetaData->PublishTime;
        $condition = (string)$notification->NotificationPayload->AnyOfferChangedNotification->OfferChangeTrigger->ItemCondition;

        $myMerchantId = $notification->NotificationMetaData->SellerId;
        $myOffer = $this->merchantOfferLookup($notification, $myMerchantId);

        if ($myOffer && $myOffer->IsBuyBoxWinner == 'true') {
            if ($this->verbose) {
                echo str_repeat('-', 160)."\n";
                self::p(sprintf('Im Buybox winner for ASIN: %s'."\n", $ASIN));
            }

            return (true);
        } elseif (!$myOffer) {
            if ($this->verbose) {
                echo str_repeat('-', 160)."\n";
                self::p(sprintf('Unable to find my offer for ASIN: %s'."\n", $ASIN));
            }

            return (false);
        }


        if ($this->verbose) {
            echo str_repeat('-', 160)."\n";
            self::p(sprintf('Computing ASIN: %s, Condition: %s', $ASIN, $condition));
        }

        if (Amazon::$debug_mode) {
            echo 'My Offer:'."\n";
            self::p($myOffer);
        }

        $buyBoxOffers = $this->buyBoxOffersLookup($notification, $myMerchantId);

        $price = $reprice = null;

        if ($buyBoxOffers === true) {
            $price = null;

            printf('Current Price: %.2f - Our Offer has the Buybox', (float)$myOffer->ListingPrice->Amount);
            if ($this->verbose) {
                self::p($myOffer);
            }

            return (null);
            // We are the cheaper
        }

        $reprice = true;

        if ($reprice) {
            $product_idenfier = AmazonProduct::getIdByAsin($this->amazon_id_lang, $ASIN);

            if ($product_idenfier instanceof stdClass) {
                $id_product = $product_idenfier->id_product;
                $id_product_attribute = $product_idenfier->id_product_attribute;
                $has_attributes = $id_product_attribute ? true : false;

                $product = new Product($id_product);

                $product_data = new stdClass;
                $product_data->ean13 = trim($product->ean13);
                $product_data->upc = trim($product->upc);
                $product_data->reference = trim($product->reference);
                $product_data->wholesale_price = (float)trim($product->wholesale_price);
                $product_data->available_date = trim($product->available_date);
                $product_data->date_add = trim($product->date_add);
                $product_data->condition = trim($product->condition);
                $product_data->active = trim($product->active);

                $product_option = AmazonProduct::getProductOptions($id_product, $this->amazon_id_lang, $id_product_attribute);

                if ($this->verbose) {
                    self::p($product_idenfier);
                    self::p($product_data);
                    self::p($product_option);
                }

                if ((bool)$product_option['disable']) {
                    return (false);
                }

                if ((bool)$product_option['force']) {
                    $force = true;
                } else {
                    $force = false;
                }

                // Apply Mappings
                $condition = !empty($product->condition) && isset($this->conditionMap[$product->condition]) && !empty($this->conditionMap[$product->condition]) ? $this->conditionMap[$product->condition] : 'New';

                if (Tools::strtoupper($condition) != Tools::strtoupper($product->condition)) {
                    return (false);
                }

                if ($handle_combinations && $has_attributes) {
                    // Product Combinations
                    //
                    if (version_compare(_PS_VERSION_, '1.5', '<')) {
                        $combinations = $product->getAttributeCombinaisons($this->amazon_id_lang);
                    } else {
                        $combinations = $product->getAttributeCombinations($this->amazon_id_lang);
                    }

                    if (is_array($combinations) && count($combinations)) {
                        foreach ($combinations as $combination) {
                            if ((int)$combination['id_product_attribute'] === (int)$id_product_attribute) {
                                $product_data->ean13 = trim($combination['ean13']);
                                $product_data->upc = trim($combination['upc']);
                                $product_data->reference = trim($combination['reference']);
                                $product_data->available_date = trim($combination['available_date']);

                                if (($wholesale_price = (float)$combination['wholesale_price']) > 0) {
                                    $product_data->wholesale_price = $wholesale_price;
                                }

                                break;
                            }
                        }
                    }
                }

                if (!Tools::strlen($product_data->reference) || !AmazonTools::validateSKU($product_data->reference)) {
                    if ($this->verbose) {
                        self::p(sprintf('Missing or wrong reference for id_product: %d/%d', $id_product, $id_product_attribute));
                    }

                    return (false);
                }

                $SKU = $product_data->reference;

                // switch to the right category
                $category_set = AmazonProduct::marketplaceGetCategory($id_product);

                if (is_array($category_set) && count($category_set)) {
                    $id_category = reset($category_set);

                    if (count($category_set) > 1) {
                        if (in_array($product->id_category_default, $category_set)) {
                            $id_category = (int)$product->id_category_default;
                        } elseif (is_array($this->profile2category) && is_array($this->categories)) {
                            // Product has multiple categories in category selection
                            if (count(array_intersect($category_set, $this->categories)) > 1) {
                                if (count(array_intersect($category_set, array_keys(array_unique($this->profile2category)))) > 1) {
                                    self::p(sprintf($this->l('Product "%s" has several profiles in several categories !'), $id_product));
                                }
                            }
                        }
                    }
                } elseif ($product->id_category_default) {
                    $id_category = (int)$product->id_category_default;
                } else {
                    if ($this->verbose) {
                        self::p(sprintf('Product has no category: %d', $id_product));
                    }

                    return (false);
                }

                if (!in_array($id_category, $this->categories)) {
                    if ($this->verbose) {
                        self::p(sprintf('Product is not in selected categories: %d %d', $id_product, $id_category));
                    }

                    return (false);
                }

                $profile_id = null;
                $profile_name = null;

                if (isset($this->profile2category[$id_category])) {
                    if (in_array($id_category, $this->categories)) {
                        $profile_name = $this->profile2category[$id_category];
                        $profile_id = false;

                        if ($this->profile['name']) {
                            foreach ($this->profile['name'] as $profile_id => $profile) {
                                if ($profile == $profile_name) {
                                    break;
                                }
                            }
                        }

                        if ($profile_id !== false && !empty($profile_name)) {
                            if ($this->verbose) {
                                self::p(sprintf('Using profile [%s] ID: %s', $profile_name, $profile_id));
                            }
                        }
                    } else {
                        if ($this->verbose) {
                            self::p(sprintf('Profil is not in profiles list [%s] id: %s', $profile_name, $profile_id));
                        }
                    }
                }
                $p_repricing = isset($this->profile['repricing'][$profile_id][$this->amazon_id_lang]) ? $this->profile['repricing'][$profile_id][$this->amazon_id_lang] : null;

                if (!$p_repricing) {
                    if ($this->verbose) {
                        self::p(sprintf('Repricing is not selected for profile: %s(%d)', $profile_name, $profile_id));
                    }
                }

                if (!(is_array($this->strategies) && count($this->strategies) && isset($this->strategies[$p_repricing]) && is_array($this->strategies[$p_repricing]))) {
                    self::p(sprintf('No repricing strategy available for this profile: %s(%d)', $profile_name, $profile_id));
                }

                if ($p_repricing) {
                    $strategy = $this->strategies[$p_repricing];
                } elseif (isset($this->default_strategies[$this->amazon_id_lang]) && !empty($this->default_strategies[$this->amazon_id_lang])) {
                    $strategy = $this->strategies[$this->default_strategies[$this->amazon_id_lang]];
                } else {
                    if ($this->verbose) {
                        self::p(sprintf('No default strategy found'));
                    }
                    return(false);
                }

                if (!$strategy['active']) {
                    if ($this->verbose) {
                        self::p(sprintf('Strategy %s is inactive', $strategy['name']));
                    }

                    return (false);
                }

                $stdPrice = $product->getPrice($this->useTax, ($id_product_attribute ? (int)$id_product_attribute : null), 6, null, false, !$product->on_sale && $this->specials);

                $wholeSalePriceOrig = null;
                $stdPriceOrig = null;

                // Convert to platform currency
                //
                if ($this->fromCurrency->iso_code != $this->toCurrency->iso_code) {
                    $stdPriceOrig = $stdPrice;
                    $stdPrice = Tools::convertPrice($stdPrice, $this->toCurrency);

                    $wholeSalePriceOrig = $product_data->wholesale_price;
                    $product_data->wholesale_price = Tools::convertPrice($product_data->wholesale_price, $this->toCurrency);

                    if (Amazon::$debug_mode) {
                        printf('Convert from currency: %s to currency: %s - price is: %s and was %s', $this->fromCurrency->iso_code, $this->toCurrency->iso_code, $stdPrice, $stdPriceOrig);
                    }
                }
                $current_price = $stdPrice;

                if (is_array($this->price_rules) && isset($this->price_rules[$this->amazon_id_lang])) {
                    $current_price = AmazonTools::priceRule($stdPrice, $this->price_rules[$this->amazon_id_lang]);
                } elseif (isset($this->formulas[$this->amazon_id_lang]) && !empty($this->formulas[$this->amazon_id_lang])) {
                    $current_price = AmazonTools::formula($stdPrice, $this->formulas[$this->amazon_id_lang]);
                }

                // Price Override
                if (!empty($product_option['price']) && (float)$product_option['price']) {
                    $current_price = (float)$product_option['price'];
                }

                $fba = (bool)$product_option['fba'];
                $value_added = 0;

                if ($this->amazon_features['fba'] && $fba) {
                    // FBA Value Added
                    if ((float)$product_option['fba_value'] > 0) {
                        $current_price += (float)$product_option['fba_value'];
                        $value_added = (float)$product_option['fba_value'];
                    } elseif ($this->fbaFormula) {
                        // FBA formula
                        $current_price = AmazonTools::formula($current_price, $this->fbaFormula);
                    }
                } else {
                    if (version_compare(_PS_VERSION_, '1.5', '<')) {
                        $productQuantity = Product::getQuantity($id_product, $id_product_attribute);
                    } else {
                        $productQuantity = Product::getRealQuantity($id_product, $id_product_attribute, $this->id_warehouse, $this->id_shop);
                    }

                    if (!$productQuantity && !$force) {
                        if ($this->verbose) {
                            self::p(sprintf('No stock for product %d/%d, skipped....', $id_product, $id_product_attribute));
                        }

                        return (false);
                    }
                }

                $current_price = sprintf('%.02f', Tools::ps_round($current_price, 2));
                $base_price = null;

                switch ($strategy['base']) {
                    case AmazonRepricing::REPRICING_WHOLESALE_PRICE:
                        if (!($tax = Tax::getProductTaxRate($id_product, $this->id_address))) {
                            if ($this->verbose) {
                                self::p(sprintf('Unable to determine product tax for id_product:%d/%d', $id_product, $id_product_attribute));
                            }
                            continue;
                        }

                        if (!$product_data->wholesale_price) {
                            if ($this->verbose) {
                                self::p(sprintf('Missing wholesale price id_product:%d', $id_product));
                            }
                            continue;
                        }
                        $base_price = Tools::ps_round($product_data->wholesale_price * (1 + ($tax / 100)), 2);
                        break;
                    case AmazonRepricing::REPRICING_REGULAR_PRICE:
                        $base_price = Tools::ps_round($current_price, 2);
                        break;
                    default:
                        if ($this->verbose) {
                            self::p(sprintf('Repricing base is not selected for strategy: %s', $strategy['name']));
                        }

                        return (false);
                }

                if (!$base_price) {
                    if ($this->verbose) {
                        self::p(sprintf('Missing base price for product: %d/%d', $id_product, $id_product_attribute));
                    }
                }

                if ($this->verbose) {
                    echo "Product Data:\n";
                    self::p($product_idenfier);
                    self::p($product_data);
                    echo "Repricing Strategy:\n";
                    self::p($strategy);
                }


                $agressivity = max(1, (int)$strategy['agressivity']);
                $limit = (int)$strategy['limit'];

                $strategy_overrides = AmazonStrategy::getProductStrategyV4($id_product, $id_product_attribute, $this->amazon_id_lang);

                $product_option_strategy = false;

                if ($this->hasPerProductRepricing && (float)$product_option['repricing_min'] && (float)$product_option['repricing_max']) {
                    $product_option_strategy = true;

                    $delta_min = 0;
                    $delta_max = 0;

                    $price_min = sprintf('%.02f', Tools::ps_round($product_option['repricing_min'], 2));
                    $price_max = sprintf('%.02f', Tools::ps_round($product_option['repricing_max'], 2));

                    // Convert to platform currency
                    //
                    if ($this->fromCurrency->iso_code != $this->toCurrency->iso_code) {
                        $price_min = Tools::convertPrice($price_min, $this->toCurrency);
                        $price_max = Tools::convertPrice($price_max, $this->toCurrency);
                    }
                } elseif (is_array($strategy_overrides) && count($strategy_overrides)) {
                    $strategy_override = reset($strategy_overrides);

                    $delta_min = 0;
                    $delta_max = 0;

                    $price_min = sprintf('%.02f', Tools::ps_round($strategy_override['minimum_price'], 2));
                    $price_max = sprintf('%.02f', Tools::ps_round($strategy_override['target_price'], 2));

                    // Convert to platform currency
                    //
                    if ($this->fromCurrency->iso_code != $this->toCurrency->iso_code) {
                        $price_min = Tools::convertPrice($price_min, $this->toCurrency);
                        $price_max = Tools::convertPrice($price_max, $this->toCurrency);
                    }
                } else {
                    $delta_min = (int)$strategy['delta_min'];
                    $delta_max = (int)$strategy['delta_max'];

                    $price_min = sprintf('%.02f', Tools::ps_round($current_price * (1 + ($delta_min / 100)), 2));
                    $price_max = sprintf('%.02f', Tools::ps_round($current_price * (1 + ($delta_max / 100)), 2));
                }

                if (Amazon::$debug_mode) {
                    self::p("price_min:".$price_min);
                    self::p("price_max:".$price_max);
                    self::p("repricing:".print_r($product_option_strategy, true));
                }
                $price_max += $value_added;
                $price_min += $value_added;
                $base_price += $value_added;

                $base_price_limit = sprintf('%.02f', Tools::ps_round($base_price * (1 + ($limit / 100)), 2));

                if ($product_option_strategy && $price_min > 0) {
                    // Overrides limit

                    $base_price_limit = sprintf('%.02f', Tools::ps_round($price_min, 2));
                }

                if (!is_numeric($limit) || $base_price_limit <= 0) {
                    $base_price_limit = $price_min;
                }

                $calculated = $this->getBestPrice($notification, $myOffer, $ASIN, $agressivity, $value_added);

                if (!$calculated) {
                    $safe_price = sprintf('%.02f', max($base_price_limit, $current_price));
                    $action = sprintf('No competition, skipping offer...', $current_price);
                    $reprice = false;
                } elseif ($calculated <= $base_price_limit || $calculated <= $price_min) {
                    $safe_price = sprintf('%.02f', max($base_price_limit, $price_min));
                    $action = sprintf('Sending Price Min.: %.02f', $safe_price);
                    $reprice = $safe_price;
                } elseif ($calculated >= $price_max) {
                    $safe_price = sprintf('%.02f', max($base_price_limit, $price_max));
                    $action = sprintf('Sending Price Max.: %.02f', $safe_price);
                    $action = 'Reprice - Sending Price Max.';
                    $reprice = $safe_price;
                } else {
                    $safe_price = sprintf('%.02f', max($base_price_limit, $calculated));
                    $action = 'Repriced';
                    $reprice = $safe_price;
                }

                if ($this->rounding && in_array($this->rounding, array(
                            Amazon::ROUNDING_ONE_DIGIT,
                            Amazon::ROUNDING_TWO_DIGITS
                        ))
                ) {
                    $smart_price = sprintf('%.02f', Tools::ps_round($reprice, $this->rounding));
                } elseif ($this->rounding && $this->rounding == Amazon::ROUNDING_SMART) {
                    $smart_price = sprintf('%.02f', AmazonTools::smartRounding($reprice));
                } else {
                    $smart_price = sprintf('%.02f', $reprice);
                }

                if ($this->verbose) {
                    $text_delta_min = $delta_min ? ($delta_min > 0 ? sprintf(' (+%02d&#37;)', $delta_min) : sprintf(' (%02d&#37;)', $delta_min)) : null;
                    $text_delta_max = $delta_max ? ($delta_max > 0 ? sprintf(' (+%02d&#37;)', $delta_max) : sprintf(' (%02d&#37;)', $delta_max)) : null;

                    $currency_verbose = '';
                    if ($this->fromCurrency->iso_code != $this->toCurrency->iso_code) {
                        $currency_verbose .= sprintf("Currency Conversions:\n");
                        $currency_verbose .= sprintf(" - Base Price:%s %.02f to %s %.02f\n", $this->fromCurrency->iso_code, $stdPriceOrig, $this->toCurrency->iso_code, $stdPrice);

                        if ($wholeSalePriceOrig) {
                            $currency_verbose .= sprintf(" - Wholesale Price:%s %.02f to %s %.02f\n", $this->fromCurrency->iso_code, $wholeSalePriceOrig, $this->toCurrency->iso_code, $product_data->wholesale_price);
                        }
                    }

                    self::p(array(
                        "Action:".$action."\n",
                        "Final Price:".Tools::displayPrice($smart_price, $this->toCurrency)."\n",
                        "Repriced:".Tools::displayPrice($reprice, $this->toCurrency)."\n",
                        "Calculated:".Tools::displayPrice($calculated, $this->toCurrency)."\n",
                        "Base Price:".Tools::displayPrice($current_price, $this->toCurrency)."\n",
                        "Range(min):".Tools::displayPrice($price_min, $this->toCurrency).$text_delta_min."\n",
                        "Range(max):".Tools::displayPrice($price_max, $this->toCurrency).$text_delta_max."\n",
                        "Base:".Tools::displayPrice($base_price, $this->toCurrency)."\n",
                        "Limit:".Tools::displayPrice($base_price_limit, $this->toCurrency).($limit > 0 ? sprintf(' (+%02d&#37;)', $limit) : sprintf(' (%02d&#37;)', $limit))."\n",
                        $currency_verbose,
                        "Strategy Source:".($product_option_strategy ? 'Product Option' : 'Profile')."\n",
                        "Notification Date/Time:".$NotificationDateTime."\n"
                    ));
                }

                if ($reprice) {
                    $skuItems[$SKU]['SKU'] = $SKU;
                    $skuItems[$SKU]['Price'] = sprintf('%.02f', $smart_price);
                }
            } else {
                if ($this->verbose) {
                    self::p("Product not found for ASIN:".$ASIN);
                }
            }
        }
        if (is_array($skuItems) && count($skuItems)) {
            return ($skuItems);
        } else {
            return (array());
        }
    }

    /**
     * @param $notification
     * @param $myOffer
     * @param $ASIN
     * @param $agressivity_param
     * @param int $fba_value
     *
     * @return null|string
     */
    public function getBestPrice($notification, $myOffer, $ASIN, $agressivity_param, $fba_value = 0)
    {
        $myMerchantId = $myOffer->SellerId;
        $offers = $notification->xpath('NotificationPayload/AnyOfferChangedNotification/Offers/Offer');

        if (!is_array($offers) || !count($offers)) {
            return (false);
        }

        $summary = &$notification->NotificationPayload->AnyOfferChangedNotification->Summary;

        $agressivities = array();

        for ($i = 10, $rindex = 10; $i < 110; $i += 10, $rindex--) {
            $calculated_agressivity = round($agressivity_param / $rindex, 2);
            $agressivities[$i] = $calculated_agressivity;
        }

        $imFulfilledByAmazon = $myOffer->IsFulfilledByAmazon == 'true' ? true : false;
        $imFeaturedMerchant = $myOffer->IsFeaturedMerchant == 'true' ? true : false;
        $myFeedbackRating = $myOffer->SellerFeedbackRating ? (int)$myOffer->SellerFeedbackRating->attributes()->SellerPositiveFeedbackRating : null;
        $myFeedbackCount = $myOffer->SellerFeedbackRating ? (int)$myOffer->SellerFeedbackRating->attributes()->FeedbackCount : null;
        $iShipFrom = $myOffer->ShipsFrom ? $myOffer->ShipsFrom->attributes()->Country : null;
        $myOfferAvailabilityType = $myOffer->ShippingTime && $myOffer->ShippingTime->attributes()->availabilityType;
        $myShipping = (float)$myOffer->Shipping->Amount;
        $myPrice = (float)$myOffer->ListingPrice->Amount + $fba_value;
        $myCondition = (string)$myOffer->SubCondition;

        $bestSellerFeedBackCount = 0;
        $bestSellerFeedBackRating = 0;
        $bestSellerPrice = 0;
        $bestSellerAvailabilityType = null;
        $bestSellerShipsFrom = null;

        $calculated_price = null;
        $cheaper_price = 0;
        $cheaper_shipping = 0;
        $bestOffer = null;

        if (Amazon::$debug_mode) {
            self::p("Notification:".print_r($notification));
            self::p("OfferCount:".print_r($summary->NumberOfOffers->OfferCount, true));
            self::p("All Offers:");
            self::p($offers);
        }

        foreach ($summary->LowestPrices->LowestPrice as $lowestPriceItem) {
            if ((string)$lowestPriceItem->attributes()->SubCondition != $myCondition) {
                continue;
            }

            $currentPrice = (float)$lowestPriceItem->ListingPrice->Amount + (float)$lowestPriceItem->Shipping->Amount;

            if ($bestSellerPrice < $currentPrice) {
                $bestSellerPrice = $currentPrice;

                $cheaper_price = (float)$lowestPriceItem->ListingPrice->Amount;
                $cheaper_shipping = (float)$lowestPriceItem->Shipping->Amount;
            }
        }

        foreach ($offers as $offer) {
            if (Amazon::$debug_mode) {
                self::p('Competition on Offer:');
                self::p($offer);
            }
            $merchantId = (string)$offer->SellerId;
            $sellerPrice = (float)$offer->ListingPrice->Amount;
            $sellerShipping = (float)$offer->Shipping->Amount;

            $sellerFeedBackRating = $offer->SellerFeedbackRating && (int)$offer->SellerFeedbackRating->attributes()->SellerPositiveFeedbackRating;
            $sellerFeedBackCount = $offer->SellerFeedbackRating && (int)$offer->SellerFeedbackRating->attributes()->FeedbackCount;
            $sellerAvailabilityType = $offer->ShippingTime && $offer->ShippingTime->attributes()->availabilityType;
            $sellerShipsFrom = $offer->ShipsFrom ? Tools::strtolower($offer->ShipsFrom->attributes()->Country) : null;

            if ($merchantId == $myMerchantId) {
                continue;
            }

            if ($sellerPrice + $sellerShipping < $bestSellerPrice) {
                $bestSellerPrice = $sellerPrice + $sellerShipping;
            }

            if ($sellerFeedBackRating > $bestSellerFeedBackRating) {
                $bestSellerFeedBackRating = $sellerFeedBackRating;
            }

            if ($sellerFeedBackCount > $bestSellerFeedBackCount) {
                $bestSellerFeedBackCount = $sellerFeedBackCount;
            }

            if ($sellerAvailabilityType == 'NOW') {
                $bestSellerAvailabilityType = $sellerAvailabilityType;
            }

            if ($sellerShipsFrom == $this->region && $sellerShipsFrom != $iShipFrom) {
                $bestSellerShipsFrom = $sellerShipsFrom;
            }

            if ($offer->IsBuyBoxWinner == 'true') {
                $bestOffer = $offer;
            }
            if ($sellerPrice + $sellerShipping < $cheaper_price + $cheaper_shipping || $cheaper_price + $cheaper_shipping == 0) {
                if (!$bestOffer) {
                    $bestOffer = $offer;
                }

                $cheaper_price = $sellerPrice;
                $cheaper_shipping = $sellerShipping;
            }
        }
        if (!$bestOffer) {
            $bestOffer = $offers[0];
        }

        if (Amazon::$debug_mode) {
            self::p("Best Offer:\n");
            self::p($bestOffer);

            self::p(sprintf('Cheaper Price: %.02f'."\n", $cheaper_price));
        }

        $score = 0;

        if ($bestOffer) {
            $sellerPrice = (float)$bestOffer->ListingPrice->Amount;
            $sellerShipping = (float)$bestOffer->Shipping->Amount;

            $sellerFeedBackRating = $bestOffer->SellerFeedbackRating ? (int)$bestOffer->SellerFeedbackRating->attributes()->SellerPositiveFeedbackRating : null;
            $sellerFeedBackCount = $bestOffer->SellerFeedbackRating ? (int)$bestOffer->SellerFeedbackRating->attributes()->FeedbackCount : null;
            $sellerAvailabilityType = $bestOffer->ShippingTime ? $bestOffer->ShippingTime->attributes()->availabilityType : null;
            $sellerShipsFrom = $bestOffer->ShipsFrom ? Tools::strtolower($bestOffer->ShipsFrom->attributes()->Country) : null;

            if ($bestOffer->IsBuyBoxWinner == 'true') {
                $score += 20;
            }

            if ($bestOffer->IsFulfilledByAmazon == 'true') {
                $score += 20;
            }

            if ($bestOffer->IsFeaturedMerchant == 'true') {
                $score += 10;
            }

            if ($sellerFeedBackRating && $sellerFeedBackRating < $myFeedbackRating) {
                $score -= 10;
            }

            if ($sellerFeedBackCount && $sellerFeedBackCount < $myFeedbackCount) {
                $score -= 10;
            }

            if ($sellerAvailabilityType && $sellerAvailabilityType == 'NOW') {
                $score += 10;
            }

            if ($sellerPrice && $sellerPrice + $sellerShipping < $myPrice + $myShipping) {
                $score += 10;
            }

            if ($sellerShipping == 0) {
                $score += 10;
            }

            if ($sellerShipsFrom == $this->region && $sellerShipsFrom != $iShipFrom) {
                $score += 10;
            }
        }

        $myscore = 100;
        $myscore -= !$imFulfilledByAmazon ? 30 : 0;
        $myscore -= !$imFeaturedMerchant ? 20 : 0;
        $myscore -= $bestSellerPrice && $bestSellerPrice < $myPrice + $myShipping ? 10 : 0;
        $myscore -= $bestSellerFeedBackRating && $myFeedbackRating < $bestSellerFeedBackRating ? 10 : 0;
        $myscore -= $bestSellerFeedBackCount && $myFeedbackCount < $bestSellerFeedBackCount ? 10 : 0;
        $myscore -= $myOfferAvailabilityType != 'NOW' && $bestSellerAvailabilityType == 'NOW' ? 10 : 0;
        $myscore -= $myShipping > 0 && !$imFulfilledByAmazon ? 10 : 0;
        $myscore -= $bestSellerShipsFrom == $this->region && $bestSellerShipsFrom != $iShipFrom ? 10 : 0;

        $base_score = $score;
        $base_price = $cheaper_price;
        $base_shipping = $cheaper_shipping;

        if (Amazon::$debug_mode) {
            self::p(sprintf('Base Price: %.02f, Base Shipping: %.02f'."\n", $base_price, $base_shipping));
        }

        if ($base_price) {
            // Adjust to minimum
            $score_gap = $base_score - $myscore;
            $agressivity_level = 0;

            if ($myscore < $base_score) {
                // Adjust agressivly
                foreach ($agressivities as $agressivity_level => $agressivity) {
                    if ($score_gap <= $agressivity_level) {
                        break;
                    }
                }
            } else {
                // Minimum Agressivity

                $agressivity = reset($agressivities);
                $agressivity_level = key($agressivities);
            }
            $agressivity /= 100;

            $shipping_diff = $myShipping - $base_shipping;

            $raw_price = ($base_price - $shipping_diff);
            $calculated_price = sprintf('%.02f', ($raw_price / (1 + $agressivity)));

            self::p(sprintf('ASIN: %s, agressivity: %d'."\n", $ASIN, $agressivity_param));
            if (Amazon::$debug_mode) {
                self::p($agressivities);
            }
            self::p(sprintf('Competition on Price: %.02f, Shipping: %.02f, Price+Shipping: %.02f, Score: %d', $base_price, $base_shipping, $base_price + $base_shipping, $base_score));
            self::p(sprintf('My Price: %.02f Shipping: %.02f, Price+Shipping: %.02f, My Score: %d, Agressivity: Level: %d / Rate: %.04f, Calculated: %.02f', $myPrice, $myShipping, $myPrice + $myShipping, $myscore, $agressivity_level, $agressivity, $calculated_price));
            self::p(sprintf('Competition Result: %.02f against %.02f', $calculated_price + $myShipping, $base_price + $base_shipping));
        }

        return ($calculated_price);
    }

    /**
     * @param $notification
     * @param $merchantId
     *
     * @return bool
     */
    public function merchantOfferLookup(&$notification, $merchantId)
    {
        $offers = &$notification->NotificationPayload->AnyOfferChangedNotification->Offers;

        if (property_exists($offers, 'Offer') && is_array($offers->Offer) && count($offers->Offer)) {
            foreach ($offers->Offer as $offer) {
                if ((string)$offer->SellerId == (string)$merchantId) {
                    return ($offer);
                }
            }
        }

        return (false);
    }

    /**
     * @param $notification
     * @param $merchantId
     *
     * @return array|bool|null
     */
    public function buyBoxOffersLookup(&$notification, $merchantId)
    {
        $offers = &$notification->NotificationPayload->AnyOfferChangedNotification->Offers;
        $buyBoxEligibleOffers = isset($notification->NotificationPayload->AnyOfferChangedNotification->Summary->BuyBoxEligibleOffers) ? $notification->NotificationPayload->AnyOfferChangedNotification->Summary->BuyBoxEligibleOffers : null;
        $returnedBuyBoxOffers = array();

        if (!is_array($buyBoxEligibleOffers) || !count($buyBoxEligibleOffers)
            || !property_exists($buyBoxEligibleOffers, 'OfferCount')
            || !is_array($buyBoxEligibleOffers->OfferCount) || !count($buyBoxEligibleOffers->OfferCount)) {
            return (false);
        }

        if (property_exists($offers, 'Offer') && is_array($offers->Offer) && count($offers->Offer)
            && property_exists($buyBoxEligibleOffers, 'OfferCount') && is_array($buyBoxEligibleOffers->OfferCount) && count($buyBoxEligibleOffers->OfferCount)) {
            foreach ((array)$buyBoxEligibleOffers->OfferCount as $key => $offerCount) {
                if (!is_numeric($key)) {
                    continue;
                }

                $idOffer = (int)$offerCount;
                $offers_array = (array)$offers;

                if (!isset($offers_array['Offer'][$idOffer])) {
                    continue;
                }

                $targetOffer = $offers_array['Offer'][$idOffer];

                if ((string)$targetOffer->SellerId == (string)$merchantId && $targetOffer->IsBuyBoxWinner == 'true') {
                    return (true);
                } //We are the buybox winner

                $returnedBuyBoxOffers[] = $targetOffer;
            }
            if (count($returnedBuyBoxOffers)) {
                return ($returnedBuyBoxOffers);
            }
        }

        return (null);
    }

    /**
     *
     */
    public function logOutputStart()
    {
        $action = Tools::getValue('action');
        $output_dir = _PS_MODULE_DIR_.'/amazon/log/';
        $log = $output_dir.date('Ymd_His').'.'.$action.'-'.$this->region.'.log';

        self::$logfilename = $log;

        if (!is_dir($output_dir)) {
            mkdir($output_dir);
        }
        if (is_dir($output_dir) && is_writable($output_dir)) {
            $files = glob($output_dir.'*.'.$action.'-'.$this->region.'.log');

            if (is_array($files) && count($files)) {
                foreach ($files as $key => $file) {
                    if (filemtime($file) < time() - (86400 * 3)) {
                        unlink($file);
                    }
                }
            }

            ob_start('endLog');
        }
    }

    /**
     * @param $buffer
     */
    public static function logOutputEnd($buffer)
    {
        if (self::$logfilename) {
            $ob_file = fopen(self::$logfilename, 'w+');
            $preBegin = html_entity_decode('&lt;pre&gt;');
            $preEnd = html_entity_decode('&lt;/pre&gt;');
            $br1 = html_entity_decode('&lt;br&gt;');
            $br2 = html_entity_decode('&lt;br /&gt;');
            $buffer = str_replace(array($preBegin, $preEnd), '', $buffer);
            $buffer = str_replace(array($br1, $br2), "\n", $buffer);
            fwrite($ob_file, $buffer);
        }
    }

    /**
     * @param $action
     * @param null $param
     *
     * @return null|string
     */
    public function returnDemo($action, $param = null)
    {
        $data = null;
        $directory = dirname(__FILE__).'/../demo';

        switch ($action) {
            case 'Reprice':
                $file = sprintf('%s/%s-%s.xml', $directory, 'reprice', $this->merchantId);
                if (file_exists($file)) {
                    $data = Tools::file_get_contents($file);
                }
                break;
        }
        return($data);
    }

    /**
     * Only use this to print in this file
     * @param $content
     */
    public static function p($content)
    {
        if (is_array($content)) {
            print_r($content);
        } else {
            echo trim($content, Amazon::LF).Amazon::LF;
        }
    }
}

/**
 * @param $buffer
 */
function endLog($buffer)
{
    AmazonRepricingAutomaton::logOutputEnd($buffer);
}

$amazonRepricing = new AmazonRepricingAutomaton();
$amazonRepricing->dispatch($start_time);

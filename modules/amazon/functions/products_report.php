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

require_once(dirname(__FILE__).'/../classes/amazon.batch.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');

class AmazonProductReport extends Amazon
{
    public static $errors   = array();
    public static $warnings = array();
    public static $messages = array();
    public static $report   = array();

    private $_debug = false;

    public function __construct()
    {
        parent::__construct();

        if ((int)Tools::getValue('id_lang')) {
            $this->id_lang = (int)Tools::getValue('id_lang');
        }

        AmazonContext::restore($this->context);
    }

    public function doIt()
    {
        ob_start();

        $id_lang = (int)Tools::getValue('amazon_lang');

        $callback = Tools::getValue('callback');
        $action = (string)Tools::getValue('action');
        $cr = nl2br(Amazon::LF);

        $this->_debug = (bool)Configuration::get('AMAZON_DEBUG_MODE');

        if (Tools::getValue('debug')) {
            $this->_debug = true;
        }

        if ($this->_debug) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
        // Init
        //
        $amazon = AmazonTools::selectPlatforms($id_lang, $this->_debug);

        if ($this->_debug) {
            echo nl2br(print_r($amazon['auth'], true).print_r($amazon['params'], true).print_r($amazon['platforms'], true));
        }

        if (!($this->_amazonApi = new AmazonWebService($amazon['auth'], $amazon['params'], $amazon['platforms'], $this->_debug))) {
            echo $this->l('Unable to login').$cr;
            die;
        }
        $token = Tools::getValue('instant_token');

        if (!$token || $token != Configuration::get('AMAZON_INSTANT_TOKEN', null, 0, 0)) {
            print('Wrong Token');
            die;
        }

        switch ($action) {
            case 'display-statistics':
                $this->displayStatistics();
                break;
            case 'purge':
                $this->purge();
                break;
            case 'list-reports':
                $this->listReports();
                break;
            case 'one-report':
                $html = '';
                $pass = false;


                $reportid = Tools::getValue('reportid');
                $type = Tools::getValue('type');

                // Submission Report
                //
                $response = $this->_amazonApi->getFeedSubmissionResult($reportid);

                if (isset($response->Error->Code) && (string)$response->Error->Code == 'FeedProcessingResultNotReady') {
                    $pass = true;

                    if ($this->ps16x) {
                        $class_error = 'alert alert-warning';
                    } else {
                        $class_error = 'warn';
                    }

                    $html .= html_entity_decode('&lt;div class="'.$class_error.'"&gt;'.$this->l('Report is not available yet, please wait few minutes').'&lt;/div&gt;');
                } elseif ($response instanceof SimpleXMLElement) {
                    $html .= $this->displayGetFeedSubmissionResult($response, sprintf('Feed - %s: %s %s', AmazonTools::ucfirst($type), $this->l('ID'), $reportid));
                    $pass = true;
                }

                // Debug Messages
                //
                if (!$pass) {
                    echo $this->l('No available reports').$cr;
                }

                $output = html_entity_decode('&lt;div class="report-debug-div"&gt;');
                $output .= ob_get_clean();
                $output .= html_entity_decode('&lt;/div&gt;');

                echo $html;

                echo $output;

                break;
        }
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $id_lang));
    }

    public function displayStatistics()
    {
        $i = 0;
        $full_batch_list = array();
        $html = null;
        $stats = AmazonProduct::marketplaceCountProducts();

        $actives = AmazonConfiguration::get('ACTIVE');

        $statistics = array();

        foreach (array('session_products', 'session_offers', 'session_repricing', 'session_status', 'session_import') as $batch_type) {
            $batches = new AmazonBatches($batch_type);
            $batches_list = $batches->load();

            switch ($batch_type) {
                case 'session_products':
                    $type = $this->l('Products');
                    break;
                case 'session_offers':
                    $type = $this->l('Offers');
                    break;
                case 'session_repricing':
                    $type = $this->l('Repricing');
                    break;
                case 'session_status':
                    $type = $this->l('Statuses (Orders)');
                    break;
                case 'session_import':
                    $type = $this->l('Orders (Import)');
                    break;
                default:
                    $type = $this->l('Other');
                    break;
            }

            foreach ($batches_list as $batch) {
                $index = $batch->timestart.'.'.$i;

                $statistics[$index] = $batch->format();
                $statistics[$index]['records'] = $batch->created + $batch->updated + $batch->deleted;
                $statistics[$index]['mode'] = $type;

                $i++;
            }
        }
        krsort($statistics);

        $this->smarty->assign(
            array(
                'productReportStats' => $stats,
                'productReportStatistics' => $statistics,
                'productReportText1' => $this->l('Statistics'),
                'productReportText2' => sprintf(
                    $this->l('There are %d synchronizable products and %d combinations in %d languages'),
                    $stats['products'],
                    $stats['attributes'],
                    is_array($actives) ? count($actives) : 0
                ),
                'productReportText3' => $this->l('Latest Updates'),
                'productReportText4' => $this->l('Action'),
                'productReportText5' => $this->l('Type'),
                'productReportText6' => $this->l('Data'),
                'productReportText7' => $this->l('Date/Time'),
                'productReportText8' => $this->l('Items'),
                'productReportText9' => $this->l('Duration'),
                'productReportText10' => $this->l('No recorded updates'),
            )
        );

        $html .= $this->display($this->path.'amazon.php', 'views/templates/admin/functions/product_report_statistics.tpl');

        echo $html;
    }

    private function displayGetFeedSubmissionResult($obj, $title)
    {
        $html = '';
        $errors = '';
        $result = null;

        if (isset($obj->Message->ProcessingReport->Result->ResultCode) && ($obj->Message->ProcessingReport->Result->ResultCode == 'Warning' || $obj->Message->ProcessingReport->Result->ResultCode == 'Error')) {
            if (is_object($obj->Message->ProcessingReport->Result)) {
                foreach ($obj->Message->ProcessingReport->Result as $result) {
                    if (isset($result->AdditionalInfo->SKU)) {
                        $additionnal_information = sprintf('SKU: %s - ', $result->AdditionalInfo->SKU);
                    } else {
                        $additionnal_information = '';
                    }

                    $errors .= nl2br(sprintf("Message: %d %s %d: %s%s".Amazon::LF.Amazon::LF, $result->MessageID, $result->ResultCode, $result->ResultMessageCode, $additionnal_information, $result->ResultDescription));
                }
            } else {
                if (isset($result->AdditionalInfo->SKU)) {
                    $additionnal_information = sprintf('SKU: %s - ', $result->AdditionalInfo->SKU);
                } else {
                    $additionnal_information = '';
                }

                $errors .= nl2br(sprintf("Message: %d %s %d: %s%s".Amazon::LF.Amazon::LF, $result->MessageID, $obj->Message->ProcessingReport->Result->ResultCode, $obj->Message->ProcessingReport->Result->ResultMessageCode, $additionnal_information, $obj->Message->ProcessingReport->Result->ResultDescription));
            }
        } elseif (isset($obj->Message->ProcessingReport->StatusCode) && $obj->Message->ProcessingReport->StatusCode == 'Complete') {
            $errors = '';
        } else {
            $errors .= nl2br(print_r($obj, true));
        }

        $this->smarty->assign(array(
            'feedSubmissionResultTitle' => $title,
            'feedSubmissionResultText1' => $this->l('Entry'),
            'feedSubmissionResultText2' => $this->l('Result'),
            'feedSubmissionResultText3' => $this->l('Entries Processed'),
            'feedSubmissionResultText4' => $this->l('Entries processed successfully'),
            'feedSubmissionResultText5' => $this->l('Entries with Error'),
            'feedSubmissionResultText6' => $this->l('Entries with Warning'),
            'feedSubmissionResultText7' => $this->l('Messages Logs'),
            'feedSubmissionResultText8' => $this->l('Error'),
            'feedSubmissionResultText9' => $this->l('*** This is not an error *** Please DO NOT contact the support :'),
            'feedSubmissionResultText10' => $this->l('Amazon is currently processing your request, please wait a while, the report will be available soon.'),
            'feedSubmissionResultTextError' => $errors,
        ));

        if (isset($obj->Message) && isset($obj->Message->ProcessingReport->Summary->ProcessingSummary)) {
            $summary = $obj->Message->ProcessingReport->Summary->ProcessingSummary;
            $this->smarty->assign(array(
                'feedSubmissionResultSummary' => $summary
            ));
        } elseif (isset($obj->Message) && isset($obj->Message->ProcessingReport->ProcessingSummary)) {
            $summary = $obj->Message->ProcessingReport->ProcessingSummary;
            $this->smarty->assign(array(
                'feedSubmissionResultSummary' => $summary
            ));
        } elseif (isset($obj->Error)) {
            $this->smarty->assign(array(
                'feedSubmissionResultObject' => $obj,
                'feedSubmissionResultIsError' => true,
                'feedSubmissionResultTextError' => $obj->Error->Message,
            ));

            // Display an interpretation of this usual case
            //
            if ((string)$obj->Error->Code == 'FeedProcessingResultNotReady') {
                $this->smarty->assign(array(
                    'feedSubmissionResultIsNotReady' => true
                ));
            }
        } else {
            $this->smarty->assign(array(
                'feedSubmissionResultObjectText' => nl2br(print_r($obj, true))
            ));
        }

        $html .= $this->display($this->path.'amazon.php', 'views/templates/admin/functions/feed_submission_result.tpl');

        return ($html);
    }

    public function purge()
    {
        foreach (
            array(
                'batch_products_cron',
                'batch_products',
                'batch_offers_cron',
                'batch_offers',
                'batch_repricing',
                'batch_status',
                'session_products',
                'session_offers',
                'session_repricing',
                'session_status',
                'session_import'
            ) as $batch_type) {
            $batches = new AmazonBatches($batch_type);
            $batches->deleteKey();
        }
        die($this->l('Batches entries have been deleted'));
    }

    public function listReports()
    {
        $i = 0;
        $batch_by_type = array(
            'product' => array('batch_products_cron', 'batch_products', 'batch_offers_cron', 'batch_offers', 'batch_repricing'),
            'order'   => array('batch_status', 'batch_acknowledge')
        );

        ob_start();
        $id_lang = Tools::getValue('amazon_lang');
        $type    = Tools::getValue('type');
        $full_batch_list = array();

        // Regions
        //
        $marketPlaceRegion = AmazonConfiguration::get('REGION');

        if (isset($batch_by_type[$type])) {
            $batch_types = $batch_by_type[$type];
        } else {
            $batch_types = array_merge($batch_by_type['product'], $batch_by_type['order']);
        }

        foreach ($batch_types as $batch_type) {
            $batches = new AmazonBatches($batch_type);
            $batches_list = $batches->load();
            $full_batch_list = array_merge($full_batch_list, $batches_list);
        }

        if (is_array($full_batch_list) && count($full_batch_list)) {
            foreach ($full_batch_list as $key => $batch) {
                if (isset($marketPlaceRegion[$id_lang]) && $marketPlaceRegion[$id_lang] != $batch->region) {
                    unset($full_batch_list[$key]);
                    continue;
                }
                $index = sprintf('%016s.%03s', $batch->timestart, $i);

                self::$report[$index] = $batch->format();
                self::$report[$index]['records'] = $batch->created + $batch->updated + $batch->deleted;

                $i++;
            }
            krsort(self::$report);
        }

        $result = trim(ob_get_clean());

        if (!empty($result)) {
            self::$warnings[] = trim($result);
        }

        $json = Tools::jsonEncode(array(
            'count' => count(self::$report),
            'reports' => self::$report,
            'error' => (count(self::$errors) ? true : false),
            'errors' => self::$errors,
            'warning' => (count(self::$warnings) ? true : false),
            'warnings' => self::$warnings,
            'message' => count(self::$messages),
            'messages' => self::$messages
        ));

        if (($callback = Tools::getValue('callback'))) {
            // jquery

            echo $callback.'('.$json.')';
        } else {
            CommonTools::p($json);
        }
    }
}

$apr = new AmazonProductReport();

$apr->doIt();

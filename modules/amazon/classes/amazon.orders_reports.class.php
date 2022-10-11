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

if (!class_exists('AmazonWebService')) {
    require_once('amazon.webservice.class.php');
}

class AmazonOrdersReports extends AmazonWebService
{
    const ONE_DAY = 86400;
    const LAG = 2300;
    const EXPIRES = 3600;

    const STATUS_SUBMITTED  = '_SUBMITTED_';
    const STATUS_IN_PROGRESS  = '_IN_PROGRESS_';
    const STATUS_CANCELLED  = '_CANCELLED_';
    const STATUS_DONE  = '_DONE_';
    const STATUS_DONE_NO_DATA  = '_DONE_NO_DATA_';

    const TYPE_ORDER_REPORT         = '_GET_FLAT_FILE_ORDERS_DATA_';
    const TYPE_SETTLEMENT_REPORT    = '_GET_V2_SETTLEMENT_REPORT_DATA_FLAT_FILE_';
    const TYPE_VAT_REPORT           = '_GET_FLAT_FILE_SALES_TAX_DATA_';

    public static $errors = array();

    // May-28: Remove unused $orders_fields

    protected static $settle_report_fields = array(
        'settlement_id',
        'settlement_start_date',
        'settlement_end_date',
        'deposit_date',
        'total_amount',
        'currency',
        'transaction_type',
        'order_id',
        'merchant_order_id',
        'adjustment_id',
        'shipment_id',
        'marketplace_name',
        'shipment_fee_type',
        'shipment_fee_amount',
        'order_fee_type',
        'order_fee_amount',
        'fulfillment_id',
        'posted_date',
        'order_item_code',
        'merchant_order_item_id',
        'merchant_adjustment_item_id',
        'sku',
        'quantity_purchased',
        'price_type',
        'price_amount',
        'item_related_fee_type',
        'item_related_fee_amount',
        'misc_fee_amount',
        'other_fee_amount',
        'other_fee_reason_description',
        'promotion_id',
        'promotion_type',
        'promotion_amount',
        'direct_payment_type',
        'direct_payment_amount',
        'other_amount'
    );

    /**
     * Order of item is important
     * Each field is match with one from order_report (type: order)
     * @var array
     */
    protected static $order_report_fields = array(
        'order_id',
        'order_item_id',
        'purchase_date',
        'payments_date',
        'buyer_email',
        'buyer_name',
        'buyer_phone_number',
        'sku',
        'product_name',
        'quantity_purchased',
        'currency',
        'item_price',
        'item_tax',
        'shipping_price',
        'shipping_tax',
        'ship_service_level',
        'recipient_name',
        'ship_address_1',
        'ship_address_2',
        'ship_address_3',
        'ship_city',
        'ship_state',
        'ship_postal_code',
        'ship_country',
        'ship_phone_number',
        'delivery_start_date',
        'delivery_end_date',
        'delivery_time_zone',
        'delivery_Instructions',
        'sales_channel',
        'order_channel',
        'order_channel_instance',
        'external_order_id',
        'is_business_order',
        'purchase_order_number',
        'price_designation',
        'customized_url',
        'customized_page'
    );

    protected static $vat_report_fields = array(
        'marketplace',
        'order_id',
        'order_date',
        'tax_calculated_date (utc)',
        'tax_collection_model',
        'tax_collection_responsible_party',
        'currency',
        'display_price',
        'display_price_tax_inclusive',
        'taxexclusive_selling_price',
        'total_tax',
        'display_promo_amount',
        'display_promo_tax_inclusive',
    );

    //todo: change this function to static, we don't need to initial the class to call this function
    public function parseReport($result, $type)
    {
        if ($result == null or empty($result)) {
            printf('%s(#%d): processReport - Report is empty!', basename(__FILE__), __LINE__);
            return (false);
        }

        $lines = explode("\n", $result);

        if (!is_array($lines) || !count($lines)) {
            printf('%s(#%d): processReport - Report is empty!', basename(__FILE__), __LINE__);
            return (false);
        }

        if (Amazon::$debug_mode) {
            AmazonTools::p(sprintf('Report: %s lines', is_array($lines) ? count($lines) : 0));
        }

        $header = reset($lines);


        if (Amazon::$debug_mode) {
            AmazonTools::p(sprintf('Header: %s', nl2br(print_r(explode("\t", $header), true))));
        }

        // Format header: To lower and transform - to _
        $header = explode("\t", str_replace('-', '_', strtolower($header)));

        // June-26-2018: Remove parse type, already get from input

        // todo: Just a reverse of header, need reliable way of mapping. Based on text not index like currently
        $field_map = array();
        foreach ($header as $index => $title) {
            $field_map[$title] = $index;
        }
        $count = 0;
        $orders = array();

        foreach ($lines as $line) {
            if (empty($line) || ($count++ < 1)) {
                continue;
            }

            // todo: Warning, same name variable
            $result = explode("\t", $line);

            if (is_array(array_keys($result)) && count(array_keys($result)) < 4) {
                continue;
            }

            // Get mapping
            $mapping = array();
            if (self::TYPE_ORDER_REPORT == $type) {
                $mapping = self::$order_report_fields;
            } elseif (self::TYPE_SETTLEMENT_REPORT == $type) {
                $mapping = self::$settle_report_fields;
            } elseif (self::TYPE_VAT_REPORT == $type) {
                $mapping = self::$vat_report_fields;
            }

            // Build and report record
            $record = array();
            foreach ($mapping as $field) {
                $index = isset($field_map[$field]) ? $field_map[$field] : null;
                if ($index !== null && isset($result[$index])) {
                    $record[$field] = $result[$index];
                }
            }

            // Grab record to final result
            $orders[] = $record;
            if (Amazon::$debug_mode) {
                AmazonTools::p(sprintf('Report: %s', nl2br(print_r($record, true))));
            }
        }
        /* Array of :
            '305-0743990-0302739' =>
                array (size=27)
                  'order_id' => string '305-0743990-0302739' (length=19)
                  'purchase_date' => string '2017-06-20T12:51:03+00:00' (length=25)
                  'payments_date' => string '2017-06-20T12:51:03+00:00' (length=25)
                  'buyer_email' => string 'l6g84rjz41ywg6p@marketplace.amazon.de' (length=37)
                  'buyer_name' => string 'Kristof' (length=7)
                  'buyer_phone_number' => string '+49 123456789' (length=13)
                  'recipient_name' => string 'Kristof Dieckmann' (length=17)
                  'ship_address_1' => string 'c/o DUKAL W�SCHE GmbH' (length=23)
                  'ship_address_2' => string 'Stiergasse 10' (length=13)
                  'ship_address_3' => string '' (length=0)
                  'ship_city' => string 'Durchhausen' (length=11)
                  'ship_state' => string 'Baden-W�rttemberg' (length=19)
                  'ship_postal_code' => string '78591' (length=5)
                  'ship_country' => string 'DE' (length=2)
                  'ship_phone_number' => string '074641336' (length=9)
                  'bill_address_1' => string 'musterstr. 1' (length=12)
                  'bill_address_2' => string '' (length=0)
                  'bill_address_3' => string '' (length=0)
                  'bill_city' => string 'trossingen' (length=10)
                  'bill_state' => string '' (length=0)
                  'bill_postal_code' => string '78647' (length=5)
                  'bill_country' => string 'DE' (length=2)
                  'delivery_start_date' => string '' (length=0)
                  'delivery_end_date' => string '' (length=0)
                  'delivery_time_zone' => string '' (length=0)
                  'delivery_Instructions' => string '' (length=0)
                  'sales_channel' => string '
            ' (length=1)
        */

        return($orders);
    }

    /**
     * June-26-2018: Remove unused function
     */

    /**
     * Get report data by report Id
     * @param $reportId
     * @param string $reportType
     *
     * @return bool|mixed|null|SimpleXMLElement|string
     */
    public function getOrdersReport($reportId, $reportType)
    {
        // Get report data
        $reportPlain = null;

        // July-04-2018: Fix save demo if not exist
        $demoExist = false;
        $demoParam = array('id' => $reportId, 'type' => $this->_getReportTypeFileName($reportType));

        if ($this->demo) {
            $reportPlain = $this->returnDemo(__FUNCTION__, $demoParam);
            $demoExist = (bool)$reportPlain;
        }

        if (! $reportPlain) {
            $params = array();
            $params['Version'] = '2009-01-01';
            $params['Action'] = 'GetReport';
            $params['ReportId'] = (string)$reportId;

            if (Amazon::$debug_mode) {
                CommonTools::p('getOrdersReport()');
            }

            $reportPlain = $this->simpleCallWS($params, 'Reports', false);

            // Unicode encoding if server response different charset
            $charset = null;
            $curlInfo = $this->getCurlInfo();
            if ($curlInfo && is_array($curlInfo) && count($curlInfo) && isset($curlInfo['content_type'])) {
                $contentType = $curlInfo['content_type'];
                foreach (explode(';', $contentType) as $segment) {
                    if (strpos($segment, 'charset=') !== false) {
                        $charset = trim(str_replace('charset=', '', $segment));
                    }
                }
            }
            if ($charset) {
                $reportPlain = iconv($charset, 'utf-8', $reportPlain);
            }
        }

        if ($reportPlain && !$demoExist) {
            $this->saveDemo(__FUNCTION__, $demoParam, $reportPlain);
        }

        // Return report data | false
        if ($reportPlain instanceof SimpleXMLElement) {
            CommonTools::p(sprintf('%s(#%d): getReport - An error occured: %s', basename(__FILE__), __LINE__, print_r($reportPlain, true)));
            return(false);
        }

        if (empty($reportPlain)) {
            CommonTools::p(sprintf('%s(#%d): getReport - no result', basename(__FILE__), __LINE__));
            return (false);
        }

        return ($reportPlain);
    }

    /**
     * UpdateReportAcknowledgements
     * @param $reportId
     *
     * @return bool
     */
    public function ackReport($reportId)
    {
        if ($this->demo) {
            return true;
        }

        $params = array();
        $params['Version'] = '2009-01-01';
        $params['Action'] = 'UpdateReportAcknowledgements';
        $params['Acknowledged'] = 'true';
        $params['ReportIdList.Id.1'] = (string)$reportId;

        if (Amazon::$debug_mode) {
            CommonTools::p('ackReport()');
        }

        $result = $this->simpleCallWS($params, 'Reports', true);

        if ($result instanceof SimpleXMLElement && (int)$result->UpdateReportAcknowledgementsResult->Count) {
            CommonTools::p(sprintf('%s(#%d): getReport - successfully acknowledged', basename(__FILE__), __LINE__));
            CommonTools::p($result);
            return(true);
        } else {
            CommonTools::p(sprintf('%s(#%d): getReport - acknowledge failed: %s', basename(__FILE__), __LINE__, print_r($result, true)));
            return (false);
        }
    }

    /**
     * Get report list
     * @param $last
     * @param $report_type
     *
     * @return array|bool|SimpleXMLElement[]
     */
    public function reportList($last, $report_type = self::TYPE_ORDER_REPORT)
    {
        $reportListXml = null;
        // July-04-2018: Fix save demo if not exist
        $demoExist = false;

        // 1) Get report list xml

        $demoParam = array('type' => $this->_getReportTypeFileName($report_type));
        // Get demo data
        if ($this->demo) {
            $reportListXml = $this->returnDemo(__FUNCTION__, $demoParam);
            $demoExist = (bool)$reportListXml;
        }

        // Not demo || demo not exist, call API
        if (! $reportListXml) {
            $params = array();
            $params['Version'] = '2009-01-01';
            $params['Action'] = 'GetReportList';
            $params['MaxCount'] = 10;
            $params['ReportTypeList.Type.1'] = $report_type;
            $params['ReportProcessingStatusList.Status.1'] = '_DONE_';
            //$params['AvailableFromDate'] = gmdate('Y-m-d\TH:i:s\Z', strtotime('now -2 hours'));
            if ($last) {
                $params['AvailableFromDate'] = gmdate('c', strtotime($last));
            }
            $params['Acknowledged'] = 'false';

            if (Amazon::$debug_mode) {
                AmazonTools::p('reportList()');
            }

            $reportListXml = $this->simpleCallWS($params, 'Reports');
        }

        if (!$reportListXml instanceof SimpleXMLElement or isset($reportListXml->Error)) {
            AmazonTools::p(sprintf('%s(#%d): ReportList Failed - %s', basename(__FILE__), __LINE__, print_r($reportListXml, true)));

            return (false);
        }

        if (Amazon::$debug_mode) {
            AmazonTools::p(sprintf('%s(#%d): - reportList:', basename(__FILE__), __LINE__));

            echo $this->debugXML($reportListXml);
        }

        $reportListXml->registerXPathNamespace('xmlns', 'http://mws.amazonaws.com/doc/2009-01-01/');

        $xpath_result = $reportListXml->xpath('//xmlns:GetReportListResponse/xmlns:GetReportListResult/xmlns:ReportInfo');

        if (Amazon::$debug_mode) {
            AmazonTools::p(sprintf('%s(#%d): reportList result: %s', basename(__FILE__), __LINE__, nl2br(print_r($xpath_result, true))));
        }

        if (is_array($xpath_result) && count($xpath_result)) {
            if (Amazon::$debug_mode) {
                AmazonTools::p(sprintf('%s(#%d): reportList - reports: %s', basename(__FILE__), __LINE__, print_r($xpath_result, true)));
            }

            // If API success with data && demo not exist, save demo
            if (!$demoExist) {
                $this->saveDemo(__FUNCTION__, $demoParam, $reportListXml->saveXML());
            }

            return($xpath_result);
        } else {
            if (Amazon::$debug_mode) {
                AmazonTools::p(sprintf('%s(#%d): reportList - no report available', basename(__FILE__), __LINE__));
            }
            return(false);
        }
    }

    public function reportRequestList($from_date = null, $to_date = null, $report_request_id = null, $report_type = '_GET_FLAT_FILE_ORDERS_DATA_')
    {
        $params = array();
        $params['Version'] = '2009-01-01';
        $params['Action'] = 'GetReportRequestList';
        $params['ReportTypeList.Type.1'] = $report_type;

        if ($from_date) {
            $params['RequestedFromDate'] = date('Y-m-d\TH:i:s\+00:00', strtotime($from_date));
        }
        if ($to_date) {
            $params['RequestedToDate'] = date('Y-m-d\TH:i:s\+00:00', strtotime($to_date));
        }
        if ((int)$report_request_id && is_numeric($report_request_id)) {
            $params['ReportRequestIdList.Id.1'] = $report_request_id;
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): - reportRequestList params: %s', basename(__FILE__), __LINE__, print_r($params, true)));
        }

        $xml = $this->simpleCallWS($params, 'Reports');

        if (!$xml instanceof SimpleXMLElement or isset($xml->Error)) {
            CommonTools::p(sprintf('%s(#%d): - reportRequestList Failed', basename(__FILE__), __LINE__));

            return (false);
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): - reportRequestList:', basename(__FILE__), __LINE__));

            echo $this->debugXML($xml);
        }

        $xml->registerXPathNamespace('xmlns', 'http://mws.amazonaws.com/doc/2009-01-01/');

        $xpath_result = $xml->xpath('//xmlns:GetReportRequestListResponse/xmlns:GetReportRequestListResult/xmlns:ReportRequestInfo');

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): reportRequestList result: %s', basename(__FILE__), __LINE__, nl2br(print_r($xpath_result, true))));
        }

        if (is_array($xpath_result) && !count($xpath_result)) {
            CommonTools::p(sprintf('%s(#%d): reportRequestList - no report available', basename(__FILE__), __LINE__));

            return(false);
        } else {
            foreach ($xpath_result as $key => $report_data) {
                if ($report_data instanceof SimpleXMLElement) {
                    if (!isset($report_data->ReportRequestId) || !(int)$report_data->ReportRequestId) {
                        unset($xpath_result[$key]);
                        continue;
                    }
                    if (!isset($report_data->CompletedDate) || strtotime($report_data->CompletedDate) < time() - (self::ONE_DAY * self::EXPIRES)) {
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('%s(#%d): reportRequestList - skipping expired: %s', basename(__FILE__), __LINE__, print_r($report_data, true)));
                        }
                        unset($xpath_result[$key]);
                        continue;
                    }
                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s(#%d): reportRequestList - selected report: %s', basename(__FILE__), __LINE__, print_r($report_data, true)));
                    }
                }
            }
            $xpath_result = array_values(array_filter($xpath_result));

            if (!is_array($xpath_result) || !count($xpath_result)) {
                CommonTools::p(sprintf('%s(#%d): reportRequestList - no report available', basename(__FILE__), __LINE__));
                return(false);
            }
            return($xpath_result);
        }

        return (false);
    }

    /**
     * Note: Settlement reports cannot be requested or scheduled. They are automatically scheduled by Amazon.
     * Note: Sales Tax Report cannot be requested or scheduled. Generate it from the Tax Document Library in Seller Central.
     * @param $date_start
     * @param null $date_end
     * @param string $report_type
     * @return bool|string
     */
    public function reportRequest($date_start, $date_end = null, $report_type = '_GET_FLAT_FILE_ORDERS_DATA_')
    {
        $params = array();
        $params['Action'] = 'RequestReport';
        $params['ReportType'] = $report_type;
        $params['Version'] = '2009-01-01';
        if ($report_type == '_GET_FLAT_FILE_ORDERS_DATA_') {
            $params['ReportOptions'] = 'ShowSalesChannel=true';
        }
        $params['StartDate'] = date('c', strtotime($date_start));

        if ($date_end) {
            $params['EndDate'] = date('c', strtotime($date_end));
        }

        if (Amazon::$debug_mode) {
            CommonTools::p('reportRequest()');
            CommonTools::p(sprintf('%s(#%d): reportRequest: %s', basename(__FILE__), __LINE__, print_r($params, true)));
        }

        $xml = $this->simpleCallWS($params, 'Reports');

        if (!$xml instanceof SimpleXMLElement or isset($xml->Error)) {
            CommonTools::p(sprintf('%s(#%d): reportRequest Failed: %s', basename(__FILE__), __LINE__, print_r($xml, true)));

            return (false);
        }

        if (Amazon::$debug_mode) {
            echo  $this->debugXML($xml);
        }

        if (!isset($xml->RequestReportResult->ReportRequestInfo->ReportProcessingStatus) || !isset($xml->RequestReportResult->ReportRequestInfo->ReportRequestId)) {
            CommonTools::p(sprintf('%s(#%d): reportRequest Failed', basename(__FILE__), __LINE__));

            return (false);
        }

        if ((string)$xml->RequestReportResult->ReportRequestInfo->ReportProcessingStatus == '_SUBMITTED_') {
            CommonTools::p(sprintf('%s(#%d):', basename(__FILE__), __LINE__));
            CommonTools::p(sprintf('%s(#%d): reportRequest Status: %s', basename(__FILE__), __LINE__, (string)$xml->RequestReportResult->ReportRequestInfo->ReportProcessingStatus));
            CommonTools::p(sprintf('%s(#%d): reportRequest ReportId: %s', basename(__FILE__), __LINE__, (string)$xml->RequestReportResult->ReportRequestInfo->ReportRequestId));
            CommonTools::p(sprintf('%s(#%d): reportRequest SubmittedDate: %s', basename(__FILE__), __LINE__, (string)$xml->RequestReportResult->ReportRequestInfo->SubmittedDate));

            $report_request_id = (string)$xml->RequestReportResult->ReportRequestInfo->ReportRequestId;

            return ($report_request_id);
        } else {
            CommonTools::p(sprintf('%s(#%d): %s - reportRequest FAILED', basename(__FILE__), __LINE__));

            return (false);
        }
    }


    public function getReportScheduleList()
    {
        $params = array();
        $params['Version'] = '2009-01-01';
        $params['Action'] = 'GetReportScheduleList';
        $params['ReportTypeList.Type.1'] = '_GET_ORDERS_DATA_';

        if (Amazon::$debug_mode) {
            CommonTools::p('getReportScheduleList()');
        }
        $report_explainations = $this->l('Here is displayed the report scheduler status, more details at the following URL');
        $url = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_ORDERS_REPORT);

        self::$messages[] = sprintf('%s: %s', $report_explainations, $url);



        $xml = $this->simpleCallWS($params, 'Reports');

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('getReportScheduleList() - result: %s', print_r($xml, true)));
        }

        if (!$xml instanceof SimpleXMLElement or isset($xml->Error)) {
            $error = 'API Error';

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf(sprintf('%s(#%d): GetReportScheduleListResponse Failed - %s', basename(__FILE__), __LINE__), $error));
            }
            die;
        }

        if (Amazon::$debug_mode) {
            CommonTools::p('getReportScheduleList() - report');
            echo $this->debugXML($xml);
        }

        $xml->registerXPathNamespace('xmlns', 'http://mws.amazonaws.com/doc/2009-01-01/');

        $xpath_result = $xml->xpath('//xmlns:GetReportScheduleListResponse/xmlns:GetReportScheduleListResult/xmlns:ReportSchedule');

        if (Amazon::$debug_mode) {
            echo CommonTools::p('GetReportScheduleListResponse:');
            CommonTools::p(sprintf('%s(#%d): reportList result: %s', basename(__FILE__), __LINE__, nl2br(print_r($xpath_result, true))));
        }
        self::$next_action = null;

        if (is_array($xpath_result) && !count($xpath_result)) {
            if ($xml instanceof SimpleXMLElement && isset($xml->GetReportScheduleListResult)) { // HasNext: doesn't matter the value
                $frequency_text = $this->l('Activation pending...');
                self::$next_action = self::ACTION_ACTIVATE;
                $pending = true;

                self::$messages[] = $message = $frequency_text;

                if (Amazon::$debug_mode) {
                    CommonTools::p('Current Status:'.$message);
                }
                return(true);
            } else {
                if (Amazon::$debug_mode) {
                    CommonTools::p('No Result');
                }
                return(false);
            }
        } elseif (is_array($xpath_result) && count($xpath_result)) {
            // the report is available, take the first one :
            $schedule_response = reset($xpath_result);

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('Schedule - getReportScheduleList() Error: %s', print_r($schedule_response, true)));
            }

            if ($schedule_response instanceof SimpleXMLElement) {
                switch ($schedule_response->Schedule) {
                    case '_30_MINUTES_':
                        $frequency_text = $this->l('30 minutes');
                        $pending = false;
                        break;
                    case '_1_HOUR_':
                        $frequency_text = $this->l('1 hour');
                        $pending = false;
                        break;
                    default:
                        $frequency_text = $this->l('Activation pending...');
                        self::$next_action = self::ACTION_ACTIVATE;
                        $pending = true;
                        break;
                }

                if ($pending == true) {
                    self::$messages[] = $message = $frequency_text;

                    if (Amazon::$debug_mode) {
                        CommonTools::p('Current Status:'.$message);
                    }

                    return(true);
                } else {
                    self::$messages[] = $message = sprintf('%s: %s, %s: %s', $this->l('Frequency'), $frequency_text, $this->l('Next Report'), AmazonTools::displayDate(date('Y-m-d H:i:s', strtotime($schedule_response->ScheduledDate)), $this->id_lang, true));

                    if (Amazon::$debug_mode) {
                        CommonTools::p('Current Status:'.$message);
                    }
                    return(false);
                }
            } else {
                $error = 'No Result';

                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('getReportScheduleList() Error: %s', $error));
                }
                return(false);
            }
        }

        return (false);
    }


    public function manageReportSchedule()
    {
        $params = array();
        $params['Version'] = '2009-01-01';
        $params['Action'] = 'ManageReportSchedule';
        $params['ReportType'] = '_GET_ORDERS_DATA_';
        $params['Schedule'] = '_1_HOUR_';

        if (Amazon::$debug_mode) {
            CommonTools::p('manageReportSchedule()');
        }

        $xml = $this->simpleCallWS($params, 'Reports');

        if (!$xml instanceof SimpleXMLElement or isset($xml->Error)) {
            die(sprintf('%s(#%d): ManageReportScheduleResultResponse Failed', basename(__FILE__), __LINE__));
        }

        if (Amazon::$debug_mode) {
            CommonTools::p('manageReportSchedule() - report');
            echo $this->debugXML($xml);
        }

        $xml->registerXPathNamespace('xmlns', 'http://mws.amazonaws.com/doc/2009-01-01/');

        $xpath_result = $xml->xpath('//xmlns:ManageReportScheduleResponse/xmlns:ManageReportScheduleResult/xmlns:ReportSchedule');

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('ManageReportScheduleResultResponse - %s(#%d): result: %s', basename(__FILE__), __LINE__, nl2br(print_r($xpath_result, true))));
        }
        self::$next_action = null;

        $next_schedule = null;

        if (is_array($xpath_result) && !count($xpath_result)) {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s(#%d): manageReportSchedule(): Failed - %s', basename(__FILE__), __LINE__, 'No Result'));
            }
            die;
        } elseif (is_array($xpath_result) && count($xpath_result)) {
            $pass = false;

            foreach ($xpath_result as $report_schedule) {
                if ($report_schedule instanceof SimpleXMLElement) {
                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('ReportSchedule - entry: %s', print_r($report_schedule->asXML(), true)));
                    }
                    if ($report_schedule->Schedule == '_NEVER_') {
                        continue;
                    }
                }
                $next_schedule = $report_schedule->ScheduledDate;

                switch ($report_schedule->Schedule) {
                    case '_30_MINUTES_':
                        $frequency_text = $this->l('30 minutes');
                        $pass = true;
                        break;
                    case '_1_HOUR_':
                        $frequency_text = $this->l('1 hour');
                        $pass = true;
                        break;
                    default:
                        $frequency_text = $this->l('No schedule found, please try to reload the page...');
                        break;
                }
                break;
            }

            if ($pass == true) {
                self::$messages[] = $message = sprintf('%s: %s, %s: %s', $this->l('Frequency'), $frequency_text, $this->l('Next Schedule'), AmazonTools::displayDate(date('Y-m-d H:i:s', strtotime($next_schedule)), $this->id_lang, true));

                if (Amazon::$debug_mode) {
                    CommonTools::p('Current Schedule:'.$message);
                }
                return(true);
            } else {
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): manageReportSchedule(): Failed - %s', basename(__FILE__), __LINE__, 'No Result'));
                }

                die;
            }
        }

        return (false);
    }

    /**
     * @param $action
     * @param $param
     * @param SimpleXMLElement|string $data
     *
     * @return null
     */
    public function saveDemo($action, $param, $data)
    {
        $file = $this->_demoFileName($action, $param);

        if ($file) {
            file_put_contents($file, $data);
        } else {
            parent::saveDemo($action, $param, $data);
        }
    }

    /**
     * Get demo file content
     * @param $action
     * @param null $param
     *
     * @return bool|null|SimpleXMLElement|string
     */
    public function returnDemo($action, $param = null)
    {
        $data = null;

        $file = $this->_demoFileName($action, $param);

        if ($file) {
            if (file_exists($file)) {
                if ('reportList' == $action) {
                    $data = simplexml_load_file($file);
                } elseif ('getReport' == $action) {
                    $data = Tools::file_get_contents($file);
                }
            }
        } else {
            $data = parent::returnDemo($action, $param);
        }

        return $data;
    }

    /**
     * Get demo file name based on action
     * @param $action
     * @param $param
     * @return string
     */
    private function _demoFileName($action, $param)
    {
        $directory  = dirname(__FILE__).'/../demo';
        $merchantId = $this->getMerchantId();

        switch ($action) {
            case 'reportList':
                $file = sprintf('%s/%s-%s-%s.xml', $directory, 'report_list', $merchantId, $param['type']);
                break;
            case 'getReport':
                $file = sprintf('%s/%s-%s-%s-%s.txt', $directory, 'get_report', $this->getMerchantId(), $param['id'], $param['type']);
                break;
            default:
                $file = '';
                break;
        }

        return $file;
    }

    protected function debugXML($xml)
    {
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        return AmazonTools::pre(array(htmlspecialchars($dom->saveXML())), true);
    }

    /**
     * Get human-readable of report type
     * @param $reportType
     * @return string
     */
    private function _getReportTypeFileName($reportType)
    {
        switch ($reportType) {
            case AmazonOrdersReports::TYPE_ORDER_REPORT:
                $type = 'order';
                break;
            case AmazonOrdersReports::TYPE_SETTLEMENT_REPORT:
                $type = 'settle';
                break;
            case AmazonOrdersReports::TYPE_VAT_REPORT:
                $type = 'vat';
                break;
            default:
                $type = 'unknown';
                break;
        }

        return $type;
    }
}

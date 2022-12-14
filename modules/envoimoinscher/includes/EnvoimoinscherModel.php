<?php
/**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    EnvoiMoinsCher <api@boxtal.com>
* @copyright 2007-2018 PrestaShop SA / 2011-2018 EnvoiMoinsCher
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registred Trademark & Property of PrestaShop SA
*/

class EnvoimoinscherModel
{

    private $db;
    protected $module_name;
    public static $id_shop = null;
    public static $id_shop_group = null;
    private $api_status_cache = null;

    /* About price */
    const RATE_PRICE = 0;
    const REAL_PRICE = 1;

    const WITHOUT_CHECK = 0;
    const WITH_CHECK = 1;
    /* Family */
    const FAM_ECONOMIQUE = 1;
    const FAM_EXPRESSISTE = 2;
    /* Module mode */
    const MODE_CONFIG = 'config';
    const MODE_ONLINE = 'online';
    /* Track */
    const TRACK_EMC_TYPE = 1;
    const TRACK_OPE_TYPE = 2;

    public function __construct($db, $module)
    {
        $this->db = $db;
        $this->module_name = $module;
        include_once _PS_MODULE_DIR_ . 'envoimoinscher/includes/EnvoimoinscherHelper.php';
    }

    /**
     * Return the partnership code of the account
     *
     * @access public
     * @return string
     */
    public function getPartnership()
    {
        $partnership = self::getConfig('EMC_PARTNERSHIP');
        if ($partnership == '') {
            $lib = new Emc\User();
            $lib->setLocale(Context::getContext()->language->language_code);
            $lib->getPartnership();
            self::logLastRequest($lib->last_request, 'partnership');
            
            $partnership = $lib->partnership;

            Configuration::updateValue('EMC_PARTNERSHIP', $partnership);
        }
        return $partnership;
    }

    /**
     * Return an array with the news for the module
     *
     * @param  $platform : platform name (will be prestashop here)
     * @param  $version : version of the module
     * @access public
     * @return array
     */
    public function getApiNews($platform, $version)
    {
        if (EMC_USER != '' && EMC_PASS != '' && EMC_KEY != '') {
            $lib = new Emc\News();
            $lib->setLocale(Context::getContext()->language->language_code);
            $lib->loadNews($platform, $version);
            self::logLastRequest($lib->last_request, 'news');
            
            return $lib->news;
        } else {
            return array();
        }
    }

    /**
     * Return the actual API server's status
     *
     * @param  $platform : platform name (will be prestashop here)
     * @param  $version : version of the module
     * @access public
     * @return array of error messages (empty array if server status is OK)
     */
    public function getApiStatus($platform, $version)
    {
        if ($this->api_status_cache !== null) {
            return $this->api_status_cache;
        }

        // if the configuration is not done, no need to check if the server is OK
        if ((EMC_USER == '' && EMC_PASS == '' && EMC_KEY == '')
            || (int)EnvoimoinscherModel::getConfig('EMC_USER') == 0
            || ((int)EnvoimoinscherModel::getConfig('EMC_USER') == 1 && EMC_KEY == '')) {
            return array();
        }

        // execute a simple API request (with an english response)
        $lib = new Emc\User();
        $lib->setPlatformParams($platform, _PS_VERSION_, $version);
        $lib->setLocale("en_US");
        $lib->getUserDetails();
        self::logLastRequest($lib->last_request, 'userDetails');
        
        // parse result
        $result = array();
        if ($lib->curl_error) {
            $result[] = array(
              'id' => false,
              'message' => $lib->curl_error_text
            );
        }
        if ($lib->resp_error) {
            foreach ($lib->resp_errors_list as $error) {
                $result[] = array(
                  'id' => $this->getApiErrorCode($error['message']),
                  'message' => $error['message']
                );
            }
        }

        $this->api_status_cache = $result;
        return $result;
    }

    public function getApiErrorCode($message)
    {
        $error_list = array(
            'access_denied - API key' => 'API error : Invalid API key',
            'access_denied - Password is incorrect' => 'API error : Invalid password',
            'access_denied - Login or password' => 'API error : Invalid login',
            'access_denied - wrong credentials' => 'API error : Wrong credentials',
            'access_denied - Invalid payment method' => 'API error : Invalid account payment method',
        );

        foreach ($error_list as $err => $id) {
            if (strpos($message, $err) !== false) {
                return $id;
            }
        }

        return false;
    }

    /**
     * Get id shop and id shop group if exists
     *
     * @acces  public
     * @return void
     */
    public static function computeShopId()
    {
        /* If multi shop */
        if (Shop::isFeatureActive()) {
            self::$id_shop_group = (int)Shop::getContextShopGroupID();
            self::$id_shop = (int)Shop::getContextShopID(true);
        }
    }

    /**
     * Gets API keys from server and updates them
     *
     * @acces  public
     * @return void
     */
    public static function getAPIkeys($login, $password)
    {
        $user = new Emc\User();

        // we need to set them here because they've been added to database in the same instance of envoimoinscher
        $user->setLogin($login);
        $user->setPassword($password);

        $user->setLocale(Context::getContext()->language->language_code);
        $user->getUserDetails();
        self::logLastRequest($user->last_request, 'userDetails');
        
        return array(
            'prod' => (isset($user->user_configuration['api_key_prod']) &&
                $user->user_configuration['api_key_prod'] != "") ?
                $user->user_configuration['api_key_prod'] : null,
            'test' => (isset($user->user_configuration['api_key_test']) &&
                $user->user_configuration['api_key_test'] != "") ?
                $user->user_configuration['api_key_test'] : null
        );
    }

    /**
     * Gets configuration data for the module.
     *
     * @access public
     * @return array List with configutarion data
     */
    public static function getConfigData()
    {
        self::computeShopId();

        $shop_config = Configuration::getMultiple(
            EnvoimoinscherHelper::$config_keys,
            null,
            self::$id_shop_group,
            self::$id_shop
        );

        $default_config = Configuration::getMultiple(EnvoimoinscherHelper::$config_keys, null, 0, 0);
        array_walk($default_config, 'EnvoimoinscherModel::fillConfig', $shop_config);
        return $default_config;
    }

    /**
     * Callback function to fill config with default value if not defined
     * mixed value of current array pointer
     * string key of current array pointer
     * array of shop configuration
     */
    public static function fillConfig(&$default_config, $key, $shop_config)
    {
        if (!empty($shop_config[$key])) {
            $default_config = $shop_config[$key];
        }
    }


    /**
     * Get EMC configuration
     *
     * @string key configuration name
     * @access public
     * @return string
     */
    public static function getConfig($key)
    {
        self::computeShopId();
        return Configuration::get($key, null, self::$id_shop_group, self::$id_shop);
    }

    /**
     * Get EMC configuration
     *
     * @array  $array array of configuration name
     * @access public
     * @return string
     */
    public static function getConfigMultiple($array)
    {
        self::computeShopId();
        $shop_config = Configuration::getMultiple($array, null, self::$id_shop_group, self::$id_shop);
        $default_config = Configuration::getMultiple($array, null, 0, 0);
        array_walk($default_config, 'EnvoimoinscherModel::fillConfig', $shop_config);
        return $default_config;
    }

    /**
     * Update EMC configuration
     *
     * @string key configuration name
     * @mixed  values
     * @access public
     * @return string
     */
    public static function updateConfig($key, $values)
    {
        if (!self::getConfig($key) || $key == 'EMC_USER') {
            return Configuration::updateValue($key, $values, false, 0, 0);
        }
        return Configuration::updateValue($key, $values, false, self::$id_shop_group, self::$id_shop);
    }

    /**
     * Get active languages for the shop
     *
     * @access public
     * @return array
     */
    public static function getLanguages()
    {
        return Language::getLanguages(true, self::$id_shop, false);
    }

    /**
     * Gets EnvoiMoinsCher's offers.
     *
     * @access public
     * @param  string $where Query clause.
     * @return array List with offers.
     */
    public function getOffers($family = false, $where = false)
    {
        $query = '
     SELECT *, es.`pricing_es`, CONCAT_WS("_", es.emc_operators_code_eo, es.code_es) AS `offerCode`
     FROM `' . _DB_PREFIX_ . 'emc_services` es
     JOIN `' . _DB_PREFIX_ . 'emc_operators` eo
     ON eo.`code_eo` = es.`emc_operators_code_eo`
     LEFT JOIN `' . _DB_PREFIX_ . 'carrier` c
     ON c.`id_reference` = es.`ref_carrier` AND c.`deleted` = 0 AND c.`external_module_name` = "envoimoinscher"
     WHERE 1';

        // Get By family
        if ($family !== false && Validate::isInt($family)) {
            $query .= ' AND es.`family_es` = "' . (int)$family . '" ';
        }

        if ($where !== false) {
            $query .= ' ' . $where . ' ';
        }

        $query .= '
      GROUP BY es.`id_es`
      ORDER BY eo.`name_eo` ASC, es.`label_es` ASC';

        return $this->db->query($query);
    }

    /**
     * Get EnvoiMoinsCher's offers to display in module config
     *
     * @param  [type] $family [description]
     * @return [type]                 [description]
     */
    public function getOffersByFamily($family = false)
    {
        $query = '
        SELECT *, es.`pricing_es`, CONCAT_WS("_", es.emc_operators_code_eo, es.code_es) AS `offerCode`
        FROM `' . _DB_PREFIX_ . 'emc_services` es
        JOIN `' . _DB_PREFIX_ . 'emc_operators` eo
        ON eo.`code_eo` = es.`emc_operators_code_eo`
        LEFT JOIN `' . _DB_PREFIX_ . 'carrier` c
        ON c.`id_reference` = es.`ref_carrier` AND c.`deleted` = 0 AND c.`external_module_name` = "envoimoinscher"
        WHERE 1';

        // Get By family
        if ($family !== false && Validate::isInt($family)) {
            $query .= ' AND es.`family_es` = "' . (int)$family . '" ';
        }
        $query .= '
        GROUP BY es.`id_es`';

        $offers = $this->db->executeS($query);

        $lang = Context::getContext()->language->language_code;
        for ($i = 0; $i < count($offers); $i++) {
            $offers[$i]["desc_store_es"] = EnvoimoinscherHelper::getTranslation($offers[$i]["details_es"], $lang);
            $offers[$i]["label_store_es"] = EnvoimoinscherHelper::getTranslation($offers[$i]["label_store_es"], $lang);
            $offers[$i]["srv_name_fo_es"] = EnvoimoinscherHelper::getTranslation($offers[$i]["srv_name_fo_es"], $lang);
            $offers[$i]["zone_restriction_es"] =
              EnvoimoinscherHelper::getTranslation($offers[$i]["zone_restriction_es"], $lang);
            $offers[$i]["delivery_due_time_es"] =
              EnvoimoinscherHelper::getTranslation($offers[$i]["delivery_due_time_es"], $lang);
            $offers[$i]["pickup_place_es"] =
              EnvoimoinscherHelper::getTranslation($offers[$i]["pickup_place_es"], $lang);
            $offers[$i]["dropoff_place_es"] =
              EnvoimoinscherHelper::getTranslation($offers[$i]["dropoff_place_es"], $lang);
            $offers[$i]["details_es"] = EnvoimoinscherHelper::getTranslation($offers[$i]["details_es"], $lang);
        }

        // alphabetical sort on carrier name first
        $carriersAlpha = array();
        $i = 0; // add index to prevent having carriers with the same name for sorting
        foreach ($offers as $carrier) {
            $carriersAlpha[$carrier["srv_name_fo_es"].' '.$carrier["label_store_es"].' '.$i] = $carrier;
            $i++;
        }
        ksort($carriersAlpha);

        // index carrier by type
        $carriersByType = array();

        foreach ($carriersAlpha as $carrier) {
            $carriersByType[$carrier['delivery_type_es']][] = $carrier;
        }
        ksort($carriersByType);

        return $carriersByType;
    }

    public function getOperatorsForType($type)
    {
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'emc_operators_authorized_contents` WHERE id_eca = "' .
            (int)$type . '" ';

        $results = $this->db->executeS($query);
        $operators = array();

        foreach ($results as $result) {
            $operators[] = $result['ope_code'];
        }

        return $operators;
    }

    /**
     * Gets all dimensions.
     *
     * @access public
     * @return array List with dimensions.
     */
    public function getDimensions()
    {
        return $this->db->query('SELECT * FROM ' . _DB_PREFIX_ . 'emc_dimensions');
    }

    /**
     * Gets the number of EnvoiMoinsCher orders and other orders which haven't been sent yet.
     *
     * @access public
     * @return int Orders count.
     */
    public function getEligibleOrdersCount($params)
    {
        return count($this->getEligibleOrders($params));
    }

    /**
     * Gets all EnvoiMoinsCher orders and other orders which haven't been send yet.
     *
     * @access public
     * @param  array $params Query parameters.
     * @return array Orders list.
     */
    public function getEligibleOrders($params, $limits = '')
    {
        $this->db->query('SET SESSION SQL_BIG_SELECTS = 1');

        $sql = 'SELECT osl.name, c.external_module_name, o.total_paid, o.total_shipping, cr.email,
         d.generated_ed, er.errors_eoe,   o.date_add AS order_date_add, oc.id_carrier AS carrierId,
         c.name AS carrierName,
         cur.id_currency,
         o.id_order AS idOrder, SUBSTRING(a.firstname, 1, 1) AS firstNameShort,
         a.firstname AS toFirstname, a.lastname AS toLastname,
         DATE_FORMAT(eo.date_del_eor, \'%d-%m-%Y\') AS dateDel,
         DATE_FORMAT(eo.date_collect_eor, \'%d-%m-%Y\') AS dateCol,
         DATE_FORMAT(eo.date_order_eor, \'%d-%m-%Y\') AS dateCom,
         (UNIX_TIMESTAMP("' . date('Y-m-d H:i:s') . '") - UNIX_TIMESTAMP(eo.date_order_eor)) AS timeDifference,
         ROUND(eo.price_ttc_eor, 2) AS priceRound,
         IF( eopt.type ="timeout" , 1, 0 ) AS isSendLocked
       FROM ' . _DB_PREFIX_ . 'orders o
       LEFT JOIN ' . _DB_PREFIX_ . 'currency cur
         ON cur.id_currency = o.id_currency
       LEFT JOIN ' . _DB_PREFIX_ . 'order_carrier oc
         ON oc.id_order = o.id_order
       LEFT JOIN ' . _DB_PREFIX_ . 'carrier c
         ON (c.id_carrier = oc.id_carrier AND oc.id_carrier IS NOT NULL)
         OR (c.id_carrier = o.id_carrier AND oc.id_carrier IS NULL)
       LEFT JOIN ' . _DB_PREFIX_ . 'carrier_lang cl
         ON cl.id_carrier = c.id_carrier
       JOIN ' . _DB_PREFIX_ . 'address a
         ON a.id_address = o.id_address_delivery
       JOIN ' . _DB_PREFIX_ . 'customer cr
         ON cr.id_customer = a.id_customer
       LEFT JOIN ' . _DB_PREFIX_ . 'emc_services es
         ON es.id_carrier = c.id_carrier
       LEFT JOIN ' . _DB_PREFIX_ . 'emc_operators eop
         ON eop.code_eo = es.emc_operators_code_eo
       LEFT JOIN ' . _DB_PREFIX_ . 'order_history oh
         ON oh.id_order = o.id_order AND oh.id_order_history = (
           SELECT MAX(id_order_history)
           FROM ' . _DB_PREFIX_ . 'order_history moh
           WHERE moh.id_order = o.id_order
           GROUP BY moh.id_order)
       LEFT JOIN ' . _DB_PREFIX_ . 'order_state_lang osl
         ON osl.id_order_state = oh.id_order_state AND osl.id_lang = ' . (int)$params['lang'] . '
       LEFT JOIN ' . _DB_PREFIX_ . 'emc_orders eo
         ON eo.' . _DB_PREFIX_ . 'orders_id_order = o.id_order
       LEFT JOIN ' . _DB_PREFIX_ . 'emc_documents d
         ON d.' . _DB_PREFIX_ . 'orders_id_order = o.id_order AND type_ed = "label"
       LEFT JOIN ' . _DB_PREFIX_ . 'emc_orders_errors er
         ON er.' . _DB_PREFIX_ . 'orders_id_order = o.id_order
       LEFT JOIN '._DB_PREFIX_ . 'emc_orders_post eopt
         ON  eopt.' . _DB_PREFIX_ . 'orders_id_order = o.id_order AND eopt.type = "timeout"
                AND DATE_ADD(eopt.date_eopo, INTERVAL 5 MINUTE) > NOW()
             WHERE eo.ref_emc_eor IS NULL';

        //apply filters
        if (!empty($params['filterBy'])) {
            //by order type
            if (isset($params['filterBy']['type_order']) && isset($params['filterBy']['type_order'][0])
              && $params['filterBy']['type_order'][0] != "") {
                $typeFilter = array();
                foreach ($params['filterBy']['type_order'] as $type) {
                    switch ($type) {
                        case '0':
                            array_push($typeFilter, '(c.external_module_name = "envoimoinscher"
                              AND (er.errors_eoe = "" OR er.errors_eoe is NULL))');
                            break;
                        case '1':
                            array_push($typeFilter, '(c.external_module_name != "envoimoinscher"
                              AND (er.errors_eoe = "" OR er.errors_eoe is NULL))');
                            break;
                        case '2':
                            array_push($typeFilter, '(er.errors_eoe != "")');
                            break;
                        default:
                            break;
                    }
                }
                if (count($typeFilter) > 0) {
                    $sql .= ' AND ('.implode(' OR ', $typeFilter).')';
                }
            }

            //by order id
            if (isset($params['filterBy']['filter_id_order'])) {
                $sql .= ' AND o.id_order = ' . (int)$params['filterBy']['filter_id_order'];
            }

            //by order status
            if (count($params['filterBy']['status']) > 0) {
                $sql .= ' AND o.current_state IN (' .
                  implode(',', array_map('intval', $params['filterBy']['status'])) . ')';
            }

            //by carrier
            if ($params['filterBy']['carriers'] != 'all') {
                if ($params['filterBy']['carriers'] == 'del') {
                    $sql .= ' AND c.name NOT IN (SELECT name FROM ' . _DB_PREFIX_ . 'carrier WHERE deleted=0)';
                } else {
                    $sql .= ' AND c.name LIKE "' . pSQL($params['filterBy']['carriers']) . '"';
                }
            }

            //by date
            if (isset($params['filterBy']['start_order_date'])) {
                $sql .= " AND o.date_add >= STR_TO_DATE('" . pSQL($params['filterBy']['start_order_date']) .
                  "', '%Y-%m-%d')";
            }

            if (isset($params['filterBy']['end_order_date'])) {
                $sql .= " AND o.date_add <= DATE_ADD(STR_TO_DATE('" . pSQL($params['filterBy']['end_order_date']) .
                  "', '%Y-%m-%d'), INTERVAL 1 DAY)";
            }

            //by recipient (string contained in company, first name, last name or email)
            if (isset($params['filterBy']['recipient']) && !empty($params['filterBy']['recipient'])) {
                foreach ($params['filterBy']['recipient'] as $value) {
                    $sql .= ' AND (INSTR(a.firstname, "' . pSQL($value) . '") > 0
                      OR INSTR(a.lastname, "' . pSQL($value) . '") > 0
                      OR INSTR(cr.email, "' . pSQL($value) . '") > 0)';
                }
            }
        }

        $sql .= ' GROUP BY o.id_order
              ORDER BY o.id_order DESC ' . $limits;

        $final = $this->db->query($sql)->fetchAll();

        foreach ($final as $key => $order) {
            $currency = new Currency($order['id_currency']);
            $final[$key]['currency_sign'] = $currency->sign;
            if ($final[$key]['errors_eoe'] != null) {
                $final[$key]['order_type'] = "error";
            } elseif ($final[$key]['external_module_name'] == "envoimoinscher") {
                $final[$key]['order_type'] = "envoimoinscher";
            } else {
                $final[$key]['order_type'] = "other";
            }
        }
        return $final;
    }

    /**
     * Gets EnvoiMoinsCher realized orders.
     *
     * @access public
     * @param  array $params Query parameters.
     * @return array Orders list.
     */
    public function getDoneOrders($params)
    {
        $sql = 'SELECT eo.' . _DB_PREFIX_ . 'orders_id_order AS idOrder,
                a.lastname,
                a.firstname,
                cr.email,
                osl.name AS state,
                cur.id_currency,
                DATE_FORMAT(o.date_add, \'%d-%m-%Y\') AS dateAdd,
                DATE_FORMAT(eo.date_order_eor, \'%d-%m-%Y\') AS dateCom,
                DATE_FORMAT(eo.date_collect_eor, \'%d-%m-%Y\') AS dateCol,
                DATE_FORMAT(eo.date_del_eor, \'%d-%m-%Y\') AS dateDel,
                ROUND(eo.price_ttc_eor, 2) AS priceRound,
                ROUND(o.total_shipping, 2) AS total_shipping,
                ROUND(o.total_paid, 2) AS total_paid,
                eo.ref_emc_eor,
                c.name AS carrierName,
                eo.parcels_eor
                FROM ' . _DB_PREFIX_ . 'emc_orders eo
                JOIN ' . _DB_PREFIX_ . 'orders o ON eo.' . _DB_PREFIX_ . 'orders_id_order = o.id_order
                JOIN ' . _DB_PREFIX_ . 'address a ON o.id_address_delivery = a.id_address
                JOIN ' . _DB_PREFIX_ . 'customer cr ON o.id_customer = cr.id_customer
                LEFT JOIN ' . _DB_PREFIX_ . 'currency cur ON o.id_currency = cur.id_currency
                LEFT JOIN ' . _DB_PREFIX_ . 'order_history oh ON eo.' . _DB_PREFIX_ . 'orders_id_order = oh.id_order
                AND oh.id_order_history = (
                           SELECT MAX(id_order_history)
                           FROM ' . _DB_PREFIX_ . 'order_history moh
                           WHERE moh.id_order = o.id_order
                           GROUP BY moh.id_order)
                JOIN ' . _DB_PREFIX_ . 'order_state_lang osl ON osl.id_order_state = oh.id_order_state
                AND osl.id_lang = ' . (int)$params['lang'] . '
                LEFT JOIN ' . _DB_PREFIX_ . 'order_carrier oc ON oc.id_order = o.id_order
                JOIN ' . _DB_PREFIX_ . 'carrier c ON (c.id_carrier = oc.id_carrier AND oc.id_carrier IS NOT NULL)
                        OR (c.id_carrier = o.id_carrier AND oc.id_carrier IS NULL)
                LEFT JOIN ' . _DB_PREFIX_ . 'emc_documents d ON d.' . _DB_PREFIX_ . 'orders_id_order = o.id_order
                AND type_ed = "label"
                WHERE eo.ref_emc_eor != ""
                AND c.external_module_name = "envoimoinscher" ';

        if ('' != $params['filters']) {
            $sql .= $this->generateOrderFilter($params['filters']) . ' ';
        }

        $sql .= 'GROUP BY o.id_order
      ORDER BY o.id_order DESC LIMIT ' . (int)$params['start'] . ', ' . (int)$params['limit'] . ' ';

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Gets product weight by product id.
     *
     * @access public
     * @param  int $productId          the product id.
     * @param  int $productAttributeId the product attribute id.
     * @return float $weight the product weight.
     */
    public function getProductWeight($productId, $productAttributeId)
    {
        // get base product weight
        $product = new Product($productId);
        $weight = (float)$product->weight;

        // add attribute weight
        $attributes = $this->getProductAttributes($productId);

        foreach ($attributes as $attribute) {
            if ($attribute['id_product_attribute'] == $productAttributeId) {
                $weight += (float)$attribute['weight'];
            }
        }

        // if 0 use average weight instead
        if ($weight == 0) {
            if (self::getConfig('EMC_AVERAGE_WEIGHT')) {
                return (float)self::getConfig('EMC_AVERAGE_WEIGHT');
            } else {
                return 0;
            }
        }

        return $weight;
    }

    /**
     * Gets cart weight in kg + apply minimum weight.
     *
     * @access public
     * @param  int $cartId the cart id.
     * @return array $weight the cart weight in kg.
     */
    public function getCartWeight($cartId)
    {
        $weight = EnvoimoinscherHelper::normalizeToKg(
            self::getConfig('PS_WEIGHT_UNIT'),
            $this->getCartWeightRaw($cartId)
        );
        // if shipping weight < 100g weight is set to 100g
        if ($weight != 0 && $weight < 0.1) {
            $weight = 0.1;
        }
        return $weight;
    }

    /**
     * Gets cart weight without conversion to kg.
     *
     * @access public
     * @param  int $cartId the cart id.
     * @return array $weight the cart weight in the weight unit configured.
     */
    public function getCartWeightRaw($cartId)
    {
        $rows = $this->db->query(
            'SELECT *, cp.quantity AS productQuantity FROM ' . _DB_PREFIX_ .
            'cart_product cp
            WHERE cp.id_cart = ' . (int)$cartId
        );

        $weight = 0;
        if ($rows && count($rows) > 0) {
            foreach ($rows as $row) {
                $weight += $row['productQuantity'] * (float)$this->getProductWeight(
                    $row['id_product'],
                    $row['id_product_attribute']
                );
            }
        }

        return $weight;
    }

    /**
     * Gets dimensions by $weight parameter.
     *
     * @access public
     * @param  float $weight Weight used to get the dimensions.
     * @return array Dimensions array.
     */
    public function getDimensionsByWeight($weight)
    {
        return $this->db->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'emc_dimensions
     WHERE weight_from_ed < ' . (float)$weight . ' AND weight_ed >= ' . (float)$weight . ''
        );
    }

    /**
     * Gets product attribute id from product ids defined in tests().
     *
     * @access public
     * @param  int   $id         the product id.
     * @param  array $attributes the attribute ids.
     * @return int product attribute id.
     */
    public function getProductAttributeId($id, $attributes)
    {
        if (!empty($attributes)) {
            $sql = 'SELECT pac1.id_product_attribute
            FROM ' . _DB_PREFIX_ . 'product_attribute as pa';
            $i = 1;
            foreach ($attributes as $value) {
                $sql .= ' JOIN ' . _DB_PREFIX_ . 'product_attribute_combination as pac' . $i;
                if ($i == 1) {
                    $sql .= ' ON pac' . $i .'.id_product_attribute = pa.id_product_attribute';
                } else {
                    $sql .= ' ON pac' . $i .'.id_product_attribute = pac1.id_product_attribute';
                }
                $sql .= ' AND pac' . $i .'.id_attribute = '.(int)$value;
                $i++;
            }
            $sql .= ' WHERE pa.id_product = '.(int)$id;

            $result = $this->db->getRow($sql);

            if (isset($result['id_product_attribute'])) {
                return $result['id_product_attribute'];
            } else {
                return false;
            }
        } else {
            // return 0 for products without attributes (PS 1.5)
            return 0;
        }
    }

    /**
     * Prepares data used in the quotation and shipping order process.
     *
     * @access public
     * @param  int   $order_id Id of order.
     * @param  array $config   Configuration data.
     * @param  bool  $init     first display
     * @param  bool  $active   if carrier returned must be active
     * @return array List with data used to quotation and shipping order.
     */
    public function prepareOrderInfo($order_id, $config, $init = false, $active = false)
    {
        /* the BINARY in the request is here to solve some encoding issues */
        $active = $active ? 'AND  c.active = 1 AND c.deleted = 0' : '';
        $sql = 'SELECT o.id_order, o.id_cart, o.total_products_wt AS totalOrder, o.total_products, o.total_shipping,
         c.id_carrier AS carrierId, c.name, c.external_module_name,
         a.firstname, a.lastname, a.address1, a.address2, a.postcode, a.city, a.company,
         a.phone, a.phone_mobile, a.other,
         cu.id_gender, cu.email,
         co.iso_code,
         od.product_id, od.product_name, od.unit_price_tax_excl, od.product_quantity, od.product_weight,
         es.emc_operators_code_eo AS emc_operators_code_eo, es.is_parcel_pickup_point_es,
         es.is_parcel_dropoff_point_es, es.code_es,
         CONCAT_WS("_", es.emc_operators_code_eo, es.code_es) AS offerCode,
         ep.point_ep, ct.selected_point
       FROM ' . _DB_PREFIX_ . 'orders o
       LEFT JOIN ' . _DB_PREFIX_ . 'order_carrier oc
         ON oc.id_order = o.id_order
       LEFT JOIN ' . _DB_PREFIX_ . 'carrier c
         ON (c.id_carrier = oc.id_carrier AND oc.id_carrier IS NOT NULL)
         OR (c.id_carrier = o.id_carrier AND oc.id_carrier IS NULL)
       JOIN ' . _DB_PREFIX_ . 'address a
         ON a.id_address = o.id_address_delivery
       LEFT JOIN ' . _DB_PREFIX_ . 'customer cu
         ON cu.id_customer = a.id_customer
       JOIN ' . _DB_PREFIX_ . 'country co
         ON co.id_country = a.id_country
       LEFT JOIN ' . _DB_PREFIX_ . 'order_detail od
         ON od.id_order = o.id_order
       LEFT JOIN ' . _DB_PREFIX_ . 'emc_services es
         ON (es.ref_carrier = c.id_reference OR (BINARY es.label_es = BINARY c.name)) '. $active .'
       LEFT JOIN ' . _DB_PREFIX_ . 'emc_operators eo
         ON eo.code_eo = es.emc_operators_code_eo
       LEFT JOIN ' . _DB_PREFIX_ . 'emc_points ep
         ON ep.' . _DB_PREFIX_ . 'orders_id_order = o.id_order
       LEFT JOIN ' . _DB_PREFIX_ . 'emc_cart_tmp ct
         ON ct.id_cart = o.id_cart
       WHERE o.id_order = ' . (int)$order_id . ' GROUP BY od.id_order_detail';

        $row = $this->db->query($sql)->fetchAll();

        if (isset($row) === false || count($row) == 0) {
            return array();
        }

        $row[0]['id_carrier'] = (int)isset($row[0]['carrierId']) ? $row[0]['carrierId'] : 0;
        $cart = new Cart((int)$row[0]['id_cart']);

        //POST weight value
        $products_desc = array();
        if (Tools::isSubmit('weight')) {
            $product_weight = (float)str_replace(',', '.', Tools::getValue('weight'));
        } else {
            $product_weight = 0;
            $product_info =  $cart->getProducts();
            if (!empty($product_info)) {
                $product_info_weight = 'weight';
                $product_info_quantity= 'cart_quantity';
            } else {
                $order = new Order((int)$row[0]['id_order']);
                $product_info = $order->getProducts();
                $product_info_weight = 'product_weight';
                $product_info_quantity = 'product_quantity';
            }
            foreach ($product_info as $product) {
                if ($product[$product_info_weight] != 0) {
                    $product_weight += EnvoimoinscherHelper::normalizeToKg(
                        self::getConfig('PS_WEIGHT_UNIT'),
                        $product[$product_info_quantity] * $product[$product_info_weight]
                    );
                } else {
                    $product_weight += EnvoimoinscherHelper::normalizeToKg(
                        self::getConfig('PS_WEIGHT_UNIT'),
                        $product[$product_info_quantity] * (float)$config['EMC_AVERAGE_WEIGHT']
                    );
                }
                $u = 0;
                foreach ($row as $line) {
                    if ($row[$u]['product_id'] ==  $product['id_product']) {
                        $products_desc[] = $line['product_name'];
                        $row[$u]['product_shop_weight'] = EnvoimoinscherHelper::normalizeToKg(
                            self::getConfig('PS_WEIGHT_UNIT'),
                            $product[$product_info_weight]
                        );
                    }
                    $u++;
                }
            }
            // if shipping weight < 100g weight is set to 100g
            if ((float)$product_weight != 0 && (float)$product_weight < 0.1) {
                (float)$product_weight = 0.1;
            }
        }

        // get send value
        $order_value = 0.0;
        foreach ($row as $line) {
            $order_value += $line['unit_price_tax_excl'] * $line['product_quantity'];
        }

        //delivery
        $addresses = $row[0]['address1'];
        if ($row[0]['address2'] != '') {
            $addresses .= '|' . $row[0]['address2'];
        }
        $del_type = 'particulier';
        if ($row[0]['company'] != '' && (int)$config['EMC_INDI'] != 1) {
            $del_type = 'entreprise';
        }
        $delivery = array(
            'pays' => $row[0]['iso_code'],
            'adresse' => $addresses,
            'code_postal' => $row[0]['postcode'],
            'ville' => $row[0]['city'],
            'civilite' => $row[0]['id_gender'] == '1' ? 'M.' : 'Mme',
            'nom' => $row[0]['lastname'],
            'prenom' => $row[0]['firstname'],
            'societe' => $row[0]['company'],
            'tel' => $row[0]['phone'],
            'email' => $row[0]['email'],
            'other' => $row[0]['other'],
            'type' => $del_type
        );
        $delivery['phoneAlert'] = false;
        if ($delivery['tel'] == '') {
            $delivery['tel'] = EnvoimoinscherHelper::normalizeTelephone($row[0]['phone_mobile']);
            if ($delivery['tel'] == '') {
                //if phone number isn't indicated, put it the shipper's number
                $delivery['tel'] = EnvoimoinscherHelper::normalizeTelephone($config['EMC_TEL']);
                $delivery['phoneAlert'] = true;
            }
        }

        $parcels_weight = array();
        $parcels_height = array();
        $parcels_width = array();
        $parcels_length = array();
        $j = 1;

        if (Tools::isSubmit('multiParcel') && (int)Tools::getValue('multiParcel') > 1) {
            $nb = (int)Tools::getValue('multiParcel');
            $parcels_w = array();
            if (Tools::isSubmit('parcels_w') && Tools::getValue('parcels_w') != '') {
                $parcels_w = explode(';', Tools::getValue('parcels_w'));
            } else {
                for ($i = 0; $i < $nb; $i++) {
                    $parcels_w[$i] = Tools::getValue('parcel_w_' . ($i + 1));
                }
            }

            if (Tools::isSubmit('parcels_h') && Tools::getValue('parcels_h') != '') {
                $parcels_h = explode(';', Tools::getValue('parcels_h'));
            } else {
                for ($i = 0; $i < $nb; $i++) {
                    $parcels_h[$i] = Tools::getValue('parcel_h_' . ($i + 1));
                }
            }

            if (Tools::isSubmit('parcels_d') && Tools::getValue('parcels_d') != '') {
                $parcels_d = explode(';', Tools::getValue('parcels_d'));
            } else {
                for ($i = 0; $i < $nb; $i++) {
                    $parcels_d[$i] = Tools::getValue('parcel_d_' . ($i + 1));
                }
            }

            if (Tools::isSubmit('parcels_l') && Tools::getValue('parcels_l') != '') {
                $parcels_l = explode(';', Tools::getValue('parcels_l'));
            } else {
                for ($i = 0; $i < $nb; $i++) {
                    $parcels_l[$i] = Tools::getValue('parcel_l_' . ($i + 1));
                }
            }
            foreach ($parcels_w as $parcel_weight) {
                $parcels_weight[$j] = (float)$parcel_weight;
                $j++;
            }

            $j = 1;
            foreach ($parcels_h as $parcel_height) {
                $parcels_height[$j] = (float)$parcel_height;
                $j++;
            }

            $j = 1;
            foreach ($parcels_d as $parcel_width) {
                $parcels_width[$j] = (float)$parcel_width;
                $j++;
            }

            $j = 1;
            foreach ($parcels_l as $parcel_length) {
                $parcels_length[$j] = (float)$parcel_length;
                $j++;
            }
        } else {
            $parcels_weight[1] = $product_weight;
            if (Tools::isSubmit('length')) {
                $parcels_length[1] = Tools::getValue('length');
            }
            if (Tools::isSubmit('width')) {
                $parcels_width[1] = Tools::getValue('width');
            }
            if (Tools::isSubmit('height')) {
                $parcels_height[1] = Tools::getValue('height');
            }
        }

        $final_parcels = array();

        foreach ($parcels_weight as $k => $one_parcel_weight) {
            $dimensions = array();
            //get dimensions by weight
            if (isset($parcels_height[$k]) && $parcels_height[$k] != ''
                && isset($parcels_width[$k]) && $parcels_width[$k] != ''
                && isset($parcels_length[$k]) && $parcels_length[$k] != ''
            ) {
                $dimensions['length_ed'] = $parcels_length[$k];
                $dimensions['width_ed'] = $parcels_width[$k];
                $dimensions['height_ed'] = $parcels_height[$k];
            } else {
                $dimensions = $this->db->getRow(
                    'SELECT * FROM ' . _DB_PREFIX_ . 'emc_dimensions
          WHERE weight_from_ed < ' . $one_parcel_weight . ' AND weight_ed >= ' . $one_parcel_weight . ''
                );
            }
            if ($dimensions) {
                $final_parcels[$k] = array(
                    'poids' => $one_parcel_weight,
                    'longueur' => (float)$dimensions['length_ed'],
                    'largeur' => (float)$dimensions['width_ed'],
                    'hauteur' => (float)$dimensions['height_ed']
                );
            }
        }
        // prepare pro forma informations
        $proforma = $this->makeProforma($row);
        // put default information
        if (self::getConfig('EMC_PP_' . Tools::strtoupper(Tools::substr($row[0]['offerCode'], -25)))) {
            $default_point = self::getConfig('EMC_PP_' . Tools::strtoupper(Tools::substr($row[0]['offerCode'], -25)));
        } else {
            $default_point = null;
        }

        $insurance = false;
        if (Tools::isSubmit('insurance')) {
            $insurance = (bool)Tools::getValue('insurance');
        } elseif (isset($config['EMC_ASSU']) && (int)$config['EMC_ASSU'] == 1 && $init) {
            $insurance = true;
        }
        $defaults = array(
            'disponibilite.HDE' => $config['EMC_DISPO_HDE'],
            'disponibilite.HLE' => $config['EMC_DISPO_HLE'],
            'depot.pointrelais' => $default_point,
            'retrait.pointrelais' => $row[0]['point_ep'],
            'type_emballage.emballage' => $config['EMC_WRAPPING'],
            $config['EMC_TYPE'] . '.description' => implode(',', $products_desc),
            $config['EMC_TYPE'] . '.valeur' => $order_value,
            'assurance.selection' => $insurance,
            'assurance.emballage' => 'Caisse',
            'assurance.materiau' => 'Carton',
            'assurance.protection' => 'Carton antichoc',
            'assurance.fermeture' => 'Clous'
        );

        // if parcel point wasn't saved
        if (trim($defaults['retrait.pointrelais']) == '') {
            $point = explode('-', $row[0]['selected_point']);
            if (strpos(trim($point[0]), $row[0]['emc_operators_code_eo']) !== false) {
                $defaults['retrait.pointrelais'] = trim($point[1]);
                $data = array(
                    _DB_PREFIX_ . 'orders_id_order' => (int)$order_id,
                    'point_ep' => pSQL(trim($point[1])),
                    'emc_operators_code_eo' => pSQL(trim($point[0]))
                );
                $this->db->insert('emc_points', $data);
            }
        }
        //If option 'use content as parcel description' is checked
        if ((int)$config['EMC_CONTENT_AS_DESC'] == 1) {
            $category_row = $this->getNameCategory((int)$config['EMC_NATURE']);
            if ($category_row) {
                $defaults['colis.description'] = $category_row;
            }
        }
        $defaults['raison'] = 'sale';

        return array(
            'order' => $row,
            'productWeight' => $product_weight,
            'default' => $defaults,
            'dimensions' => $dimensions,
            'parcels' => $final_parcels,
            'config' => $config,
            'delivery' => $delivery,
            'proforma' => $proforma,
            'code_eo' => $row[0]['emc_operators_code_eo'],
            'is_pp' => $row[0]['is_parcel_pickup_point_es'],
            'is_dp' => $row[0]['is_parcel_dropoff_point_es'],
            'isEMCCarrier' => (bool)$row[0]['external_module_name'] == $this->module_name
        );
    }

    /**
     * Prepares pro forma array.
     *
     * @param  Mage_Sales_Model_Order_Item $items Array of orders' items.
     * @return array Proformas array.
     */
    public function makeProforma($items)
    {
        $s = 1;
        $proforma = array();
        foreach ($items as $item) {
            $proforma[$s] = array(
                'description_en' => $item['product_name'],
                'description_fr' => $item['product_name'],
                'nombre' => $item['product_quantity'],
                'valeur' => $item['unit_price_tax_excl'],
                'origine' => EnvoimoinscherModel::getConfig('EMC_COUNTRY'),
                'poids' => $item['product_weight']);
            $s++;
        }
        return $proforma;
    }

    /**
     * Updates configured dimensions.
     *
     * @access public
     * @param  array $data New dimensions data.
     * @param  int   $id   Id of dimensions to update.
     * @return void
     */
    public function updateDimensions($data, $id)
    {
        $this->db->update('emc_dimensions', $data, 'id_ed = ' . (int)$id);
    }

    /**
     * Insert new service.
     *
     * @access public
     * @param  array $service Service data.
     * @param  array $carrier Carrier data.
     * @return bool True if inserted correctly, false otherwise
     */
    public function insertService($service, $carrier)
    {
        //check if carrier is installed on emc_operators
        $db_carrier = $this->getCarrierByCode($carrier['code']);
        //if not exists, install it and get the last inserted id
        if (!isset($db_carrier['code_eo'])) {
            $this->insertCarrier($carrier);
        }
        //finally, install service
        $data = array(
            'id_carrier' => 0,
            'code_es' => pSQL($service['code']),
            'emc_operators_code_eo' => pSQL($carrier['code']),
            'label_es' => pSQL($service['label']),
            'desc_store_es' => pSQL(($service['srvInfos']['label_store'])),
            'label_store_es' => pSQL($service['label']),
            'is_parcel_dropoff_point_es' => (int)$service['delivery'] == 'DROPOFF_POINT',
            'is_parcel_pickup_point_es' => (int)$service['delivery'] == 'PICKUP_POINT',
            'family_es' => (int)$service['srvInfos']['offer_family'],
            'pricing_es' => EnvoimoinscherModel::REAL_PRICE
        );

        return $this->db->insert('emc_services', $data);
    }

    /**
     * Update new service.
     *
     * @access public
     * @param  array  $data         Updated data.
     * @param  string $carrier_code Carrier code.
     * @param  string $service_code Service code.
     * @return bool True if updated correctly, false otherwise
     */
    public function updateService($data, $carrier_code, $service_code)
    {
        if (!ctype_alnum($carrier_code) || !ctype_alnum($service_code)) {
            return false;
        }

        return $this->db->update(
            'emc_services',
            $data,
            'code_es = "' . pSQL(trim($service_code)) . '" AND emc_operators_code_eo = "'
            . pSQL(trim($carrier_code)) . '"'
        );
    }

    /**
     * Insert new carrier.
     *
     * @access public
     * @param  array $carrier Carrier data.
     * @return bool True if inserted correctly, false otherwise
     */
    public function insertCarrier($carrier)
    {
        $data = array('name_eo' => pSQL($carrier['label']), 'code_eo' => pSQL($carrier['code']));
        $this->db->insert('emc_operators', $data, true);
        return $this->db->Insert_ID();
    }

    /**
     * Tells if a carrier is on rate price or real price.
     *
     * @access public
     * @param  int $carrierId Carrier id.
     * @return 0 if real price, 1 if rate price based on weight, 2 if rate price based on price
     */
    public function isRatePrice($carrierId)
    {
        $row = $this->db->getrow(
            'SELECT pricing_es FROM ' . _DB_PREFIX_ . 'emc_services where id_carrier = ' . (int)$carrierId
        );
        if (isset($row['pricing_es'])) {
            if ($row['pricing_es']) {
                return 0;
            } else {
                $carrier = new Carrier($carrierId);
                if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT) {
                    return 1;
                } else {
                    return 2;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * Delete service.
     *
     * @access public
     * @param  string $code Service code.
     * @return bool True if deleted correctly, false otherwise
     */
    public function uninstallService($code)
    {
        $parts = explode('_', $code);
        $car_class = new CarrierCore;
        $service = $this->getServiceByCode($parts[1], $parts[0]);
        $data = array('active' => 0, 'deleted' => (int)$car_class->deleted);
        $this->db->update('carrier', $data, 'emc_services_id_es = ' . (int)$service[0]['id_es'], 0, true);
        $r = $this->db->Execute(
            'DELETE FROM ' . _DB_PREFIX_ . 'emc_services WHERE id_es = ' . (int)$service['id_es']
        );
        //if no more service attached to this operator, delete it too
        $r2 = true;
        if (!$this->hasServices($parts[0])) {
            $r2 = $this->db->Execute(
                'DELETE FROM ' . _DB_PREFIX_ . 'emc_operators WHERE code_eo = "' .
                pSQL($parts[0]) . '"'
            );
        }
        return $r && $r2;
    }

    /**
     * Checks if service exists in the database.
     *
     * @access public
     * @param  string $code Service code.
     * @return bool True if exists, false otherwise
     */
    public function hasServices($code)
    {
        if (!ctype_alnum($code)) {
            return;
        }
        $c = $this->db->getRow(
            'SELECT COUNT(id_es) AS offers FROM ' . _DB_PREFIX_ . 'emc_services
            WHERE emc_operators_code_eo = "' . $code . '"'
        );
        return $c['offers'] > 0;
    }

    /**
     * Gets carrier by code.
     *
     * @access public
     * @param  string $code Carrier code.
     * @return array Carrier data
     */
    public function getCarrierByCode($code)
    {
        if (Tools::strlen($code) != 4) {
            return array();
        }
        return $this->db->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . 'emc_operators
            WHERE code_eo = "' . pSQL($code) . '"'
        );
    }

    /**
     * Gets carrier by id.
     *
     * @access public
     * @param  string $id Carrier id.
     * @return array Carrier data
     */
    public function getCarrierById($id)
    {
        //return 'SELECT * FROM ' . _DB_PREFIX_ . 'emc_services WHERE id_carrier = ' . (int)$id;
        return $this->db->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'emc_services WHERE id_carrier = ' . (int)$id
        );
    }

    /**
     * Gets service by code.
     *
     * @access public
     * @param  string $ser_code Service code.
     * @param  string $ope_code Operator code.
     * @return array Service data
     */
    public function getServiceByCode($ser_code, $ope_code)
    {
        if (!ctype_alnum($ope_code) || !ctype_alnum($ser_code)) {
            return array();
        }
        return $this->db->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . 'emc_services
            WHERE code_es = "' . $ser_code . '" AND emc_operators_code_eo = "' . $ope_code . '"'
        );
    }

    /**
     * Gets carrierId from srv and ope code.
     *
     * @access public
     * @param  string $ser_code Service code.
     * @param  string $ope_code Operator code.
     * @return array Service data
     */
    public function getCarrierIdByCode($ser_code, $ope_code)
    {
        if (!ctype_alnum($ope_code) || !ctype_alnum($ser_code)) {
            return array();
        }

        $return = $this->db->getRow(
            'SELECT c.id_carrier FROM ' . _DB_PREFIX_ . 'carrier c
            JOIN ' . _DB_PREFIX_ . 'emc_services ON ref_carrier = c.id_reference
            WHERE c.active = 1 AND c.deleted = 0 AND code_es = "' . pSQL($ser_code) . '"
            AND emc_operators_code_eo = "' . pSQL($ope_code) . '"'
        );

        if (isset($return['id_carrier'])) {
            return $return['id_carrier'];
        } else {
            return false;
        }
    }

    /**
     * Given a product and a carrier, tells if the carrier is active for at least one warehouse where the product is.
     *
     * @access public
     * @param  int $productId the product id.
     * @param  int $carrierId the carrier id.
     * @return boolean
     */
    public function checkWarehouseProductCarrier($productId, $carrierId)
    {
        $return = $this->db->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . 'warehouse_product_location wpl
            JOIN ' . _DB_PREFIX_ . 'warehouse_carrier wc
            ON wc.id_warehouse = wpl.id_warehouse AND wpl.id_product = ' . (int)$productId . '
            JOIN ' . _DB_PREFIX_ . 'carrier c
            ON c.id_reference = wc.id_carrier AND c.id_carrier = ' . (int)$carrierId
        );

        if (count($return) == 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Indexes offers array with carrier id.
     *
     * @param  array $offers api returned offers.
     * @return array New list of offers, indexed with carrier id.
     */
    public function makeCarrierIdKeys($offers)
    {
        $indexedOffers = array();
        foreach ($offers as $offer) {
            $carrierId = $this->getCarrierIdByCode($offer['service']['code'], $offer['operator']['code']);
            $indexedOffers[(int)$carrierId] = $offer;
        }
        return $indexedOffers;
    }

    /**
     * Gets new carrier Id from old carrier Id (PS increments carrier id for every change made).
     *
     * @param  int $oldCarrierId the old carrier id.
     * @return int $newCarrierId the new carrier id.
     */
    public function getActiveCarrierId($oldCarrierId)
    {
        $newCarrier = $this->db->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'carrier
            WHERE active = 1 AND deleted = 0
            AND id_reference =
            (SELECT id_reference FROM ' . _DB_PREFIX_ . 'carrier WHERE id_carrier = ' . (int)$oldCarrierId . ')'
        );

        if (isset($newCarrier['id_carrier'])) {
            return $newCarrier['id_carrier'];
        } else {
            return false;
        }
    }

    /**
     * Inserts shippment order informations into the database.
     *
     * @access public
     * @param  int   $order_id Id of order.
     * @param  array $data     List of order data (Prestashop order).
     * @param  array $emcData  List of order data (EnvoiMoinsCher order).
     * @param  array $post     List of post data.
     * @return void
     */
    public function insertOrder($order_id, $data, $emc_order, $post)
    {
        $emc = Module::getInstanceByName('envoimoinscher');
        $cookie = $emc->getContext()->cookie;

        //insert into emc_orders
        $date_collect_eor = $emc_order['collection']['date'] . ' ' . (isset($emc_order['collection']['time']) ?
          $emc_order['collection']['time'] : '');
        $date_del_eor = $emc_order['delivery']['date'] . ' ' . (isset($emc_order['delivery']['time']) ?
          $emc_order['delivery']['time'] : '');
        $order_data = array(
            _DB_PREFIX_ . 'orders_id_order' => (int)$order_id,
            'emc_operators_code_eo' => pSQL($emc_order['offer']['operator']['code']),
            'price_ht_eor' => (float)$emc_order['price']['tax-exclusive'],
            'price_ttc_eor' => (float)$emc_order['price']['tax-inclusive'],
            'ref_emc_eor' => pSQL($emc_order['ref']),
            'service_eor' => pSQL($emc_order['service']['label']),
            'date_order_eor' => date('Y-m-d H:i:s'),
            'date_collect_eor' => pSQL($date_collect_eor),
            'date_del_eor' => pSQL($date_del_eor),
            'tracking_eor' => pSQL((isset($data['tracking_key']) ? $data['tracking_key'] : null)),
            'parcels_eor' => count($data['parcels'])
        );
        $this->db->insert('emc_orders', $order_data, false, true, Db::REPLACE);

        //insert parcels
        $totalweight = 0;
        foreach ($data['parcels'] as $p => $parcel) {
            $parcel_data = array(
                '' . _DB_PREFIX_ . 'orders_id_order' => (int)$order_id,
                'number_eop' => (int)$p,
                'weight_eop' => pSQL($parcel['poids']),
                'length_eop' => (int)$parcel['longueur'],
                'width_eop' => (int)$parcel['largeur'],
                'height_eop' => (int)$parcel['hauteur']
            );
            $this->db->insert('emc_orders_parcels', $parcel_data, false, true, Db::REPLACE);
            $totalweight .= (float)$parcel['poids'];
        }

        //update order (shipping_number with EnvoiMoinsCher referency)
        DB::getInstance()->update(
            'orders',
            array(
                'shipping_number' => pSQL($emc_order['ref'])
            ),
            'id_order = ' . (int)$order_id . '',
            0,
            true
        );
        DB::getInstance()->update(
            'order_carrier',
            array(
                'weight' => (float)$totalweight,
                'date_add' => date('Y-m-d H:i:s')
            ),
            'id_order = ' . (int)$order_id,
            0,
            true
        );

        //insert the new order state into order_history
        $history = new OrderHistory();
        $history->id_order = $order_id;
        $history->changeIdOrderState($data['new_order_state'], $order_id);

        $history->id_employee = (int)$cookie->id_employee;
        $history->addWithemail();
        //update parcel point info (even when it wasn't modified)
        if (isset($post['retrait_pointrelais']) && $post['retrait_pointrelais'] != '') {
            $this->db->insert(
                'emc_points',
                array(
                   _DB_PREFIX_ . 'orders_id_order' => (int)$order_id,
                  'point_ep' => pSQL($post['retrait_pointrelais']),
                  'emc_operators_code_eo' => pSQL($post['opeCode'])
                ),
                false,
                true,
                Db::REPLACE // in case there's been an uninstall/reinstall, so that a line is added nonetheless
            );
        }

        $this->db->delete('emc_orders_errors', _DB_PREFIX_ . 'orders_id_order = ' . (int)$order_id);
        // Remove temporary Post data if exists
        $this->removeTemporaryPost($order_id, 'timeout');
    }

    /**
     * Inserts shipment order informations into the database from url push.
     *
     * @access public
     * @param  int $order_id Id of order.
     * @return void
     */
    public function insertOrderFromPush($order_id)
    {
        $type = 'eem';
        $data = $this->getPostData($order_id);
        if (empty($data['delivery'])) {
            $data = $this->getPostData($order_id, 'timeout');
            $type = 'timeout';
        }
        if (isset($data['order'][0])) {
            $emc = Module::getInstanceByName('envoimoinscher');
            $cookie = $emc->getContext()->cookie;
            $reference = urldecode(Tools::getValue('emc_reference'));
            $emc_order = $data['order'][0];
            $quotation = $data['tmp_quote'];
            //insert into emc_orders
            $date_collect_eor = str_replace('_', ' ', Tools::getValue('dateCollecte'));
            $date_del_eor = str_replace('_', ' ', Tools::getValue('dateDelivery'));
            $order_data = array(
                '' . _DB_PREFIX_ . 'orders_id_order' => (int)$order_id,
                'emc_operators_code_eo' => pSQL($quotation['operateur']),
                'price_ht_eor' => (float)Tools::getValue('priceHT'),
                'price_ttc_eor' => (float)Tools::getValue('priceTTC'),
                'ref_emc_eor' => pSQL($reference),
                'service_eor' => pSQL($quotation['service']),
                'date_order_eor' => $data['date_order'],
                'date_collect_eor' => pSQL($date_collect_eor),
                'date_del_eor' => pSQL($date_del_eor),
                'tracking_eor' => pSQL((isset($emc_order['secure_key']) ? $emc_order['secure_key'] : null)),
                'parcels_eor' => count($data['parcels'])
            );

            $this->db->insert('emc_orders', $order_data, false, true, Db::REPLACE);
            //insert parcels
            foreach ($data['parcels'] as $p => $parcel) {
                $parcel_data = array(
                    '' . _DB_PREFIX_ . 'orders_id_order' => (int)$order_id,
                    'number_eop' => (int)$p,
                    'weight_eop' => pSQL($parcel['poids']),
                    'length_eop' => (int)$parcel['longueur'],
                    'width_eop' => (int)$parcel['largeur'],
                    'height_eop' => (int)$parcel['hauteur']
                );
                $this->db->insert('emc_orders_parcels', $parcel_data, false, true, Db::REPLACE);
            }

            //update order (shipping_number with EnvoiMoinsCher referency)
            $this->db->update(
                'orders',
                array(
                    'shipping_number' => pSQL($reference)
                ),
                'id_order = ' . (int)$order_id . '',
                0,
                true
            );
            //insert the new order state into order_history
            $history = new OrderHistory();
            $history->id_order = $order_id;
            $history->changeIdOrderState($data['config']['EMC_CMD'], $order_id);
            $history->id_employee = (int)$cookie->id_employee;
            $history->addWithemail();
            //update parcel point info (even when it wasn't modified)
            if (isset($emc_order['point_ep']) && $emc_order['point_ep'] != '') {
                $this->db->update(
                    'emc_points',
                    array(
                        'point_ep' => pSQL(trim($data['retrait_pointrelais'])),
                        'emc_operators_code_eo' => pSQL($emc_order['emc_operators_code_eo'])
                    ),
                    _DB_PREFIX_ . 'orders_id_order = ' . (int)$order_id
                );
            }

            $this->db->delete('emc_orders_errors', _DB_PREFIX_ . 'orders_id_order = ' . (int)$order_id);
            $this->removeTemporaryPost($order_id, $type);
        }
    }

    /**
     * Inserts order error message.
     *
     * @access public
     * @param  int    $order_id Order id.
     * @param  string $message  Error message.
     * @return void
     */
    public function insertOrderError($order_id, $message)
    {
        $this->db->delete('emc_orders_errors', _DB_PREFIX_ . 'orders_id_order = ' . (int)$order_id);
        $error_data = array(_DB_PREFIX_ . 'orders_id_order' => (int)$order_id, 'errors_eoe' => pSQL($message));
        $this->db->insert('emc_orders_errors', $error_data);
    }

    /**
     * Constructs categories tree.
     *
     * @access public
     * @param  array $config Config array used to web service connection.
     * @return array List with categories
     */
    public function getCategoriesTree()
    {
        $emc = Module::getInstanceByName('envoimoinscher');
        $rows = $this->db->query('SELECT * FROM ' . _DB_PREFIX_ . 'emc_categories');
        $categories = array();
        $category_groups = array();
        if (!empty($rows)) {
            foreach ($rows as $category) {
                if ($category['emc_categories_id_eca'] != 0) {
                    $categories[$category['emc_categories_id_eca']]['categories'][$category['id_eca']] = array(
                        'label' => $emc->l($category['name_eca']),
                        'forbidden' => $category['prohibited_eca']
                    );
                } else {
                    $category_groups[$category['id_eca']] = $emc->l($category['name_eca']);
                }
            }
        }
        // alphabetical sorting
        $return = array();
        asort($category_groups);
        foreach ($category_groups as $category_group_id => $category_name) {
            $return[$category_group_id]['name'] = $category_name;
            $return[$category_group_id]['categories'] = $categories[$category_group_id]['categories'];
            asort($return[$category_group_id]['categories']);
        }

        return $return;
    }

    /**
     * Updates categories.
     *
     * @access public
     * @return boolean
     */
    public function updateCategories()
    {
        $emc = Module::getInstanceByName('envoimoinscher');
        $config = self::getConfigData(); // Get configs
        $config['wsName'] = $emc->ws_name; // add wsName to config
        $config['localVersion'] = $emc->local_version; // Add localVersionto config

        // check new categories
        if (isset($config['EMC_KEY_TEST']) && isset($config['EMC_KEY_PROD']) && isset($config['EMC_ENV'])) {
            $content_cl = new Emc\ContentCategory();
            $content_cl->setEnv('test');
            $content_cl->setKey($config['EMC_KEY_TEST']);
            $content_cl->setLogin($config['EMC_LOGIN']);
            $content_cl->setPassword($config['EMC_PASS']);
            $content_cl->setPlatformParams($emc->ws_name, _PS_VERSION_, $emc->version);
            $content_cl->setParam(array('module' => $config['wsName'], 'version' => $config['localVersion']));
            // set language to fr for translation to work
            $content_cl->setLocale('fr-fr');
            $content_cl->setGetParams();
            $content_cl->getCategories();
            self::logLastRequest($content_cl->last_request, 'categories');
            $content_cl->getContents();
            self::logLastRequest($content_cl->last_request, 'contents');
            
            // empty category table
            $this->db->execute('
            TRUNCATE '._DB_PREFIX_.'emc_categories
            ');

            // fill it again
            foreach ($content_cl->categories as $categoryId => $category) {
                $this->db->insert('emc_categories', array(
                    'id_eca' => (int)$categoryId,
                    'emc_categories_id_eca' => 0,
                    'name_eca' => pSQL($category['label']),
                    'prohibited_eca' => 0
                ));
                if (isset($content_cl->contents[$categoryId])) {
                    foreach ($content_cl->contents[$categoryId] as $content) {
                        $this->db->insert('emc_categories', array(
                            'id_eca' => (int)$content['code'],
                            'emc_categories_id_eca' => (int)$content['category'],
                            'name_eca' => pSQL($content['label']),
                            'prohibited_eca' => (int)$content['prohibited']
                        ));
                    }
                }
            }
        }
        return true;
    }

    public function getUserDefaultShippingCountry($login, $password)
    {
        $user = new Emc\User();
        // we need to set them here because they've been added to database in the same instance of envoimoinscher
        $user->setLogin($login);
        $user->setPassword($password);
        $emc = Module::getInstanceByName('envoimoinscher');
        $user->setPlatformParams($emc->ws_name, _PS_VERSION_, $emc->version);
        $user->setLocale(Context::getContext()->language->language_code);
        $user->getUserDetails();
        self::logLastRequest($user->last_request, 'userDetails');
        
        return isset($user->user_configuration['default_shipping_country'])?
            $user->user_configuration['default_shipping_country']:false;
    }

    /**
     * Gets tracking informations.
     *
     * @access public
     * @param  int $order Order id.
     * @return array Tracking data
     */
    public function getTrackingInfos($order)
    {
        return $this->db->query(
            'SELECT *, DATE_FORMAT(date_et, \'%d-%m-%Y\') AS date
            FROM ' . _DB_PREFIX_ . 'emc_tracking
            WHERE ' . _DB_PREFIX_ . 'orders_id_order = ' . (int)$order . ' ORDER BY id_et DESC'
        );
    }

    /**
     * Get tracking informations by order and customer ids.
     *
     * @access public
     * @param  int $order    Order id.
     * @param  int $customer Customer id.
     * @return array Tracking data
     */
    public function getTrackingByOrderAndCustomer($order, $customer)
    {
        return $this->db->query(
            'SELECT *, DATE_FORMAT(et.date_et, \'%d-%m-%Y\') AS date
            FROM ' . _DB_PREFIX_ . 'emc_tracking et
            JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_order = et.' . _DB_PREFIX_ . 'orders_id_order
            WHERE et.' . _DB_PREFIX_ . 'orders_id_order = ' . (int)$order . ' AND o.id_customer = ' . (int)$customer .
            ' ORDER BY id_et DESC'
        );
    }

    /**
     * Get tracking informations by order and customer ids.
     *
     * @access public
     * @param  int $order    Order id.
     * @param  int $customer Customer id.
     * @return array Tracking data
     */
    public function getParcelsInfos($order)
    {
        return $this->db->query(
            'SELECT * FROM ' . _DB_PREFIX_ . 'emc_orders_parcels
            WHERE ' . _DB_PREFIX_ . 'orders_id_order = ' . (int)$order . ' ORDER BY number_eop ASC'
        );
    }

    /**
     * Gets order data to tracking.
     *
     * @access public
     * @param  int $order Order id.
     * @return array Tracking data
     */
    public function getOrderData($order)
    {
        return $this->db->getRow(
            'SELECT a.*, co.iso_code, o.id_order, es.is_parcel_pickup_point_es,
            ep.point_ep, es.emc_operators_code_eo FROM ' . _DB_PREFIX_ . 'orders o
            JOIN ' . _DB_PREFIX_ . 'address a ON o.id_address_delivery = a.id_address
            JOIN ' . _DB_PREFIX_ . 'country co ON co.id_country = a.id_country
            LEFT JOIN ' . _DB_PREFIX_ . 'order_carrier oc
             ON oc.id_order = o.id_order
            LEFT JOIN ' . _DB_PREFIX_ . 'carrier c
             ON (c.id_carrier = oc.id_carrier AND oc.id_carrier IS NOT NULL)
             OR (c.id_carrier = o.id_carrier AND oc.id_carrier IS NULL)
            LEFT JOIN ' . _DB_PREFIX_ . 'emc_services es ON c.id_carrier = es.id_carrier
            LEFT JOIN ' . _DB_PREFIX_ . 'emc_points ep
            ON ep.' . _DB_PREFIX_ . 'orders_id_order = o.id_order
            AND es.is_parcel_pickup_point_es = 1
            WHERE o.id_order = ' . (int)$order . ''
        );
    }

    /**
     * Gets carrier with cart info.
     *
     * @access public
     * @param  int $cartId    Cart id.
     * @param  int $carrierId Carrier id.
     * @return array Pricing data.
     */
    public function getCartCarrier($cartId, $carrierId)
    {
        return $this->db->getRow(
            'SELECT ct.selected_point, c.id_carrier,
            es.emc_operators_code_eo,
            es.is_parcel_pickup_point_es, es.is_parcel_dropoff_point_es
            FROM ' . _DB_PREFIX_ . 'carrier c
            JOIN ' . _DB_PREFIX_ . 'emc_services es
            ON es.ref_carrier = c.id_reference AND c.active = 1 AND deleted = 0
            LEFT JOIN ' . _DB_PREFIX_ . 'emc_cart_tmp ct ON ct.id_cart = ' . (int)$cartId . '
            WHERE c.id_carrier = ' . (int)$carrierId . ' '
        );
    }

    /**
     * Gets carrier with cart info.
     *
     * @access public
     * @param  int $cartId Cart id.
     * @return array Pricing data.
     */
    public function getCartCarrierByCart($cartId)
    {
        return $this->db->getRow(
            'SELECT ct.selected_point, c.id_carrier, c.external_module_name, es.emc_operators_code_eo
            FROM ' . _DB_PREFIX_ . 'cart ca
            LEFT JOIN ' . _DB_PREFIX_ . 'emc_cart_tmp ct ON ca.id_cart = ct.id_cart
            JOIN ' . _DB_PREFIX_ . 'carrier c ON c.id_carrier = ca.id_carrier
            LEFT JOIN ' . _DB_PREFIX_ . 'emc_services es
            ON es.ref_carrier = c.id_reference AND c.active = 1 AND deleted = 0
            WHERE ca.id_cart = ' . (int)$cartId
        );
    }

    /**
     * Gets EnvoiMoinsCher's carrier by zone and language.
     *
     * @access public
     * @param  int $zone Zone id.
     * @param  int $lang Lang id.
     * @return array Carrier data.
     */
    public function getEmcCarriersByZone($zone, $lang)
    {
        return $this->db->query(
            'SELECT * FROM ' . _DB_PREFIX_ . 'carrier c
            LEFT JOIN ' . _DB_PREFIX_ . 'emc_services es ON c.id_reference = es.ref_carrier
            JOIN ' . _DB_PREFIX_ . 'carrier_zone cz ON cz.id_carrier = c.id_carrier
            JOIN ' . _DB_PREFIX_ . 'carrier_lang cl ON cl.id_carrier = c.id_carrier
            WHERE c.external_module_name = "envoimoinscher"
            AND cz.id_zone = ' . (int)$zone . ' AND c.shipping_external = 1
            AND c.deleted = 0
            AND c.active = 1
            AND cl.id_lang = ' . (int)$lang . ' '
        );
    }

    /**
     * Gets EnvoiMoinsCher's carrier by language.
     *
     * @access public
     * @param  int $lang Lang id.
     * @return array Carrier data.
     */
    public function getEmcCarriersWithoutZone($lang)
    {
        return $this->db->query(
            'SELECT * FROM ' . _DB_PREFIX_ . 'carrier c
            LEFT JOIN ' . _DB_PREFIX_ . 'emc_services es ON c.id_carrier = es.id_carrier
            JOIN ' . _DB_PREFIX_ . 'carrier_lang cl ON cl.id_carrier = c.id_carrier
            WHERE c.external_module_name = "envoimoinscher"
            AND c.shipping_external = 1
            AND c.deleted = 0
            AND c.active = 1
            AND cl.id_lang = ' . (int)$lang . ' '
        );
    }

    /**
     * Return the attributes of a product
     *
     * @access public
     * @param  int $productId : product id.
     * @return array Attribute data.
     */
    public function getProductAttributes($productId)
    {
        return $this->db->query(
            'SELECT * FROM ' . _DB_PREFIX_ . 'product_attribute WHERE id_product = ' .
            (int)$productId . ''
        );
    }

    /**
     * Returns sender address.
     *
     * @access public
     * @return array Array with address.
     */
    public function getSender()
    {
        $config = self::getConfigData();
        return array(
            'pays' => $config['EMC_COUNTRY'],
            'code_postal' => $config['EMC_POSTALCODE'],
            'ville' => $config['EMC_CITY'],
            'type' => 'entreprise',
            'societe' => $config['EMC_COMPANY'],
            'adresse' => $config['EMC_ADDRESS'],
            'civilite' => $config['EMC_CIV'],
            'prenom' => $config['EMC_FNAME'],
            'nom' => $config['EMC_LNAME'],
            'email' => $config['EMC_MAIL'],
            'tel' => EnvoimoinscherHelper::normalizeTelephone($config['EMC_TEL']),
            'infos' => $config['EMC_COMPL']
        );
    }

    /**
     * Returns recipient address from cart.
     *
     * @access public
     * @param  int   $cartId       Id of current cart.
     * @param  array $addressParam Array with address (from 1.5 and multi-shipping delivery option)
     * @return array Array with address.
     */
    public function getRecipient($cartId, $addressParam)
    {

        // find address id
        if (isset($addressParam->id)) {
            $id_address = $addressParam->id;
        } elseif (is_array($addressParam)) {
            return $this->getRecipient($cartId, $addressParam[0]);
        } else {
            $id_address = $addressParam;
        }
        if ($id_address) {
            $address_clause = 'a.id_address = ' . (int)$id_address;
            $current = $this->db->getRow(
                'SELECT a.firstname, a.lastname,  a.company, a.address1, a.address2, c.email, a.postcode, a.city,
                co.iso_code, co.id_zone, co.id_country
                FROM ' . _DB_PREFIX_ . 'cart ct
                JOIN ' . _DB_PREFIX_ . 'address a ON ' . $address_clause . '
                LEFT JOIN ' . _DB_PREFIX_ . 'customer c ON c.id_customer = a.id_customer
                JOIN ' . _DB_PREFIX_ . 'country co ON co.id_country = a.id_country
                WHERE ct.id_cart = ' . (int)$cartId
            );

            if (!$current || count($current) == 0) {
                return array();
            }

            // delivery address
            $street = $current['address1'];

            if ($current['address2'] != '') {
                $street .= $current['address2'];
            }

            $type = 'particulier';
            if ($current['company'] != '' && self::getConfig('EMC_INDI') != 1) {
                $type = 'entreprise';
            }

            return array(
                'prenom' => $current['firstname'],
                'nom' => $current['lastname'],
                'societe' => $current['company'],
                'email' => $current['email'],
                'adresse' => $street,
                'code_postal' => $current['postcode'],
                'ville' => $current['city'],
                'pays' => $current['iso_code'],
                'id_country' => $current['id_country'],
                'id_zone' => $current['id_zone'],
                'type' => $type,
            );
        } else {
            return array();
        }
    }

    public function getIdZoneByIso($iso)
    {
        return $this->db->getValue('SELECT id_zone FROM ' . _DB_PREFIX_ . 'country WHERE iso_code = "'.pSQL($iso).'"');
    }


    /**
     * Return the default address
     *
     * @access public
     * @return array
     */
    public function getDefaultAddress()
    {
        $country = self::getConfig('EMC_COUNTRY');
        $zoneId = $this->getIdZoneByIso($country);

        switch ($country) {
            case 'FR':
                $address = array(
                'prenom' => 'default',
                'nom' => 'address',
                'email' => 'dummy@boxtal.com',
                'adresse' => 'Rue de la paix',
                'code_postal' => '75002',
                'ville' => 'Paris',
                'pays' => 'FR',
                'id_zone' => $zoneId,
                'type' => 'particulier'
                );
                break;
            case 'ES':
                $address = array(
                'prenom' => 'default',
                'nom' => 'address',
                'email' => 'dummy@boxtal.com',
                'adresse' => 'Plaza Mayor',
                'code_postal' => '28012',
                'ville' => 'Madrid',
                'pays' => 'ES',
                'id_zone' => $zoneId,
                'type' => 'particulier'
                );
                break;
        }
        return $address;
    }

    /**
     * Returns country id from iso
     *
     * @access public
     * @param  string $countryIso Country ISO code
     * @return int country id
     */
    public function getCountryIdFromIso($countryIso)
    {
        $countryId = $this->db->getRow(
            'SELECT id_country
            FROM ' . _DB_PREFIX_ . 'country
            WHERE iso_code = "' . pSQL($countryIso) .'"'
        );
        if (isset($countryId['id_country'])) {
            return $countryId['id_country'];
        } else {
            return false;
        }
    }

    /**
     * Returns country iso from name
     *
     * @access public
     * @param  string $countryName Country name
     * @return string country iso
     */
    public function getCountryIsoFromName($countryName)
    {
        $countryIso = $this->db->getRow(
            'SELECT c.iso_code
            FROM ' . _DB_PREFIX_ . 'country c
            JOIN ' . _DB_PREFIX_ . 'country_lang cl ON c.id_country = cl.id_country
            WHERE cl.name = "' . pSQL($countryName) .'"'
        );
        if (isset($countryIso['iso_code'])) {
            return $countryIso['iso_code'];
        } else {
            return false;
        }
    }

    /**
     * Returns country id from iso
     *
     * @access public
     * @param  id $countryId Country Id
     * @return string country ISO code
     */
    public function getCountryIsoFromId($countryId)
    {
        $countryIso = $this->db->getRow(
            'SELECT iso_code
            FROM ' . _DB_PREFIX_ . 'country
            WHERE id_country = "' . (int)$countryId .'"'
        );
        if (isset($countryIso['iso_code'])) {
            return $countryIso['iso_code'];
        } else {
            return false;
        }
    }

    /**
     * Gets order references to download.
     *
     * @access public
     * @param  array $orders Orders of labels to download.
     * @return array Order references.
     */
    public function getReferencesToLabels($orders)
    {
        $orders = array_map('intval', $orders);

        return $this->db->query(
            'SELECT * FROM ' . _DB_PREFIX_ . 'emc_orders eo
            JOIN ' . _DB_PREFIX_ . 'emc_documents ed ON eo.' . _DB_PREFIX_ . 'orders_id_order = ed.' . _DB_PREFIX_ .
            'orders_id_order
            AND ed.type_ed = "label" WHERE eo.' . _DB_PREFIX_ . 'orders_id_order IN (' . implode(', ', $orders) . ')
            AND ed.generated_ed = 1 GROUP BY ed.' . _DB_PREFIX_ . 'orders_id_order'
        );
    }

    public function getPointInfos($order)
    {
        $order = $this->getOrderData($order);
        if (isset($order['id_order']) && $order['point_ep'] != '' && !empty($order['point_ep'])) {
            //get parcel point information
            $poi_cl = new Emc\ParcelPoint();
            $emc = Module::getInstanceByName('envoimoinscher');
            $poi_cl->setPlatformParams($emc->ws_name, _PS_VERSION_, $emc->version);
            $poi_cl->setLocale(Context::getContext()->language->language_code);
            $poi_cl->getParcelPoint(
                'dropoff_point',
                $order['emc_operators_code_eo'] . '-' . $order['point_ep'],
                $order['iso_code']
            );
            self::logLastRequest($poi_cl->last_request, 'parcelPoint');
            
            if (isset($poi_cl->points) && isset($poi_cl->points['dropoff_point'])
              && isset($poi_cl->points['dropoff_point'][0])) {
                return $poi_cl->points['dropoff_point'][0];
            }
        }
        return array();
    }

    /**
     * Gets last planning data.
     *
     * @access public
     * @return array Planning data
     */
    public function getLastPlanning()
    {
        $row = $this->db->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'emc_orders_plannings
            ORDER BY id_eopl DESC'
        );
        return $row;
    }

    /**
     * Updates order planning data.
     *
     * @access public
     * @param  array $data New planning data.
     * @param  int   $id   Planning id.
     * @return void
     */
    public function updateOrdersList($data, $id)
    {
        $sql_data = array(
            'orders_eopl' => pSQL(Tools::jsonEncode($data['orders'])),
            'stats_eopl' => pSQL(Tools::jsonEncode($data['stats'])),
            'errors_eopl' => pSQL(Tools::jsonEncode($data['errors']))
        );
        $this->db->update('emc_orders_plannings', $sql_data, 'id_eopl = ' . (int)$id);
    }

    /**
     * Makes new order planning data.
     *
     * @access public
     * @param  array $data New planning data.
     * @param  int   $type Planning type  : 0 => separate order
     *                                      1 => from EMC orders table
     *                                      2 => from no EMC orders table
     *                                      3 => from errors table
     * @return void
     */
    public function makeNewPlanning($orders, $type)
    {
        $sql_data = array(
            'orders_eopl' => pSQL(Tools::jsonEncode(array('todo' => $orders, 'done' => array()))),
            'stats_eopl' => pSQL(Tools::jsonEncode(array(
              'total' => count($orders), 'ok' => 0,
              'skipped' => 0,
              'errors' => 0
            ))),
            'errors_eopl' => pSQL(Tools::jsonEncode(array())),
            'date_eopl' => date('Y-m-d H:i:s'),
            'type_eopl' => (int)$type
        );
        $this->db->insert('emc_orders_plannings', $sql_data);
    }

    /**
     * Removes all plannings.
     *
     * @access public
     * @return void
     */
    public function removePlanning()
    {
        $this->db->delete('emc_orders_plannings');
    }

    /**
     * Adds the temporary PostData
     *
     * @param int    $order     id order
     * @param array  $post_data
     * @param string $type      eem :envoi en masse
     */
    public function addPostData($order, $post_data, $type = 'eem')
    {
        $data = array(
            _DB_PREFIX_ . 'orders_id_order' => (int)$order,
            'data_eopo' => pSQL(Tools::jsonEncode($post_data)),
            'date_eopo' => date('Y-m-d H:i:s'),
            'type' => $type
        );
        $this->db->insert('emc_orders_post', $data, true, true, Db::REPLACE);
    }
    /**
     * Gets the temporary PostData
     *
     * @param  int    $order id order
     * @param  string $type  eem :envoi en masse
     * @return array
     */
    public function getPostData($order, $type = 'eem')
    {
        $row = $this->db->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'emc_orders_post WHERE ' . _DB_PREFIX_ .
            'orders_id_order = ' . (int)$order . ' AND type = "'. $type .'"'
        );
        if (isset($row['data_eopo'])) {
            return Tools::jsonDecode($row['data_eopo'], true);
        }
        return array(
            'delivery' => array(),
            'quote' => array(),
            'parcels' => array(),
            'proforma' => array(),
            'emcErrorTxt' => '',
            'emcErrorSend' => ''
        );
    }
    /**
     * Removes the TemporaryPost entry
     *
     * @param  int    $order id order
     * @param  string $type  eem :envoi en masse
     * @return void
     */
    public function removeTemporaryPost($order, $type = 'eem')
    {
        $where = _DB_PREFIX_ . 'orders_id_order = ' . (int)$order . ' AND type = "'. $type .'"';
        $this->db->delete('emc_orders_post', $where);
    }

    /**
     * Gets EnvoiMoinsCher's carriers.
     *
     * @access public
     * @return array Carriers list.
     */
    public function getEmcCarriers()
    {
        return $this->db->query(
            'SELECT * FROM ' . _DB_PREFIX_ . 'carrier c
            LEFT JOIN ' . _DB_PREFIX_ . 'emc_services es ON c.id_reference = es.ref_carrier
            JOIN ' . _DB_PREFIX_ . 'carrier_lang cl ON cl.id_carrier = c.id_carrier
            WHERE c.external_module_name = "envoimoinscher"
            AND c.shipping_external = 1
            AND c.deleted = 0
            AND c.active = 1'
        );
    }

    /**
     * Updates order delivery address.
     *
     * @access public
     * @param  int   $order Order id.
     * @param  array $data  Data of new address.
     * @param  array $old   Data of old address.
     * @return void
     */
    public function putNewAddress($order_id, $data, $old)
    {
        if ($old['alias'] != $data['alias']) {
            $this->db->insert('address', $data, true, true, Db::INSERT);
            $id = $this->db->Insert_ID();
            //update id_address_delivery
            $data = array('id_address_delivery' => (int)$id);
            $this->db->update('orders', $data, 'id_order = ' . (int)$order_id, 0, true);
        } else {
            $this->db->update('address', $data, 'id_address = ' . (int)$old['id_address'], 0, true);
        }
    }

    public function getOffersFamilies()
    {
        $emc = Module::getInstanceByName('envoimoinscher');
        return array(
            self::FAM_ECONOMIQUE => $emc->l('Economic offers'),
            self::FAM_EXPRESSISTE => $emc->l('Express offers'),
        );
    }

    public function getTrackingModes()
    {
        $emc = Module::getInstanceByName('envoimoinscher');
        return array(
            self::TRACK_EMC_TYPE => $emc->l('Boxtal'),
            self::TRACK_OPE_TYPE => $emc->l('Carrier')
        );
    }

    /**
     * Adds new carrier into carrier table.
     *
     * @access public
     * @param  array $data New carrier's informations.
     * @return int Carrier's id
     */
    public function saveCarrier($data, $service)
    {
        $langs = Language::getLanguages(true); // get all enabled languages
        $zones = Zone::getZones(true);    // get all enabled zones
        $emc = Module::getInstanceByName('envoimoinscher');
        // we add <> 0 condition because of frequent bugs in databases
        $old_carrier = $this->db->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'carrier WHERE id_reference = "' . (int)$service['ref_carrier'] .
            '" AND id_reference <> 0 ORDER BY id_carrier DESC'
        );

        // if old carrier is not deleted, we keep the current information
        if (isset($old_carrier['id_reference']) && $old_carrier['deleted'] == 0) {
            return $old_carrier['id_carrier'];
        }

        // set carrier
        $carrier = new Carrier();

        $carrier->name = $data['name'];
        $carrier->active = (int)$data['active'];
        $carrier->is_module = (int)$data['is_module'];
        $carrier->need_range = (int)$data['need_range'];
        $carrier->range_behavior = (int)$data['range_behavior'];
        $carrier->shipping_external = (int)$data['shipping_external'];
        $carrier->external_module_name = $data['external_module_name'];
        $carrier->url = $data['url'];
        $carrier->delay = array();
        if ($langs && count($langs) > 0) {
            foreach ($langs as $lang) {
                $carrier->delay[$lang['id_lang']] = Tools::substr(
                    EnvoimoinscherHelper::getTranslation($service['desc_store_es'], $lang['language_code']),
                    0,
                    128
                );
            }
        }

        // save carrier
        if ($carrier->save() === false) {
            return false;
        }

        // copy old carriers data if the carrier has some and has only been deactivated
        if (isset($old_carrier['id_reference']) && $old_carrier['deleted'] == 1) {
            $carrier->copyCarrierData($old_carrier['id_carrier']);
        } else {
            //update carrier reference
            $carrier->id_reference = (int)$carrier->id;
            $carrier->save();
        }

        // get carrier id and ref
        $carrier_id = (int)$carrier->id;
        $row = $this->db->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ .
            'carrier WHERE deleted = 0 AND id_carrier = ' . $carrier_id
        );
        $this->db->update(
            'emc_services',
            array(
              'id_carrier' => (int)$carrier_id,
              'ref_carrier' => $row['id_reference'],
              'pricing_es' => pSQL($data['pricing_es'])
            ),
            'id_es = ' . pSQL($data['id_es'])
        );

        if ((int)$service['id_carrier'] === 0) {
            $groups = Group::getGroups((int)$emc->getContext()->language->id);
            $datas = array();

            if ($groups && count($groups) > 0) {
                foreach ($groups as $group) {
                    $datas[] = array(
                      'id_carrier' => (int)$carrier_id,
                      'id_group' => (int)$group['id_group']
                    );
                }
            }

            $this->db->insert('carrier_group', $datas, false, true, Db::INSERT_IGNORE);
        }

        // add price range if there is not one
        $ranges_price = RangePrice::getRanges((int)$carrier_id);
        if (count($ranges_price) === 0) {
            $ranges_price[] = array('id_range_price' => null);
            $range_price = new RangePrice((int)$ranges_price[0]['id_range_price']);
            $range_price->id_carrier = (int)$carrier_id;
            $range_price->delimiter1 = 0;
            $range_price->delimiter2 = 10000;
            $range_price->save();
        }

        // add weight range if there is not one
        $ranges_weight = RangeWeight::getRanges((int)$carrier_id);
        if (count($ranges_weight) === 0) {
            $ranges_weight[] = array('id_range_weight' => null);
            $range_weight = new RangeWeight((int)$ranges_weight[0]['id_range_weight']);
            $range_weight->id_carrier = (int)$carrier_id;
            $range_weight->delimiter1 = 0;
            $range_weight->delimiter2 = 10000;
            $range_weight->save();
        }

        if ($zones && count($zones) > 0) {
            foreach ($zones as $zone) {
                if (count($carrier->getZone((int)$zone['id_zone'])) === 0) {
                    $carrier->addZone((int)$zone['id_zone']);
                }
            }
        }

        // check if there is an image specifically for this service
        $base_url = _PS_MODULE_DIR_ . $this->module_name . '/views/img/';
        if (file_exists(
            $base_url . 'detail_'.Tools::strtolower($service['code_eo']) . '_' .
            Tools::strtolower($service['code_es']).'.jpg'
        )
        ) {
            $img = $base_url . 'detail_' . Tools::strtolower($service['code_eo']) . '_' .
            Tools::strtolower($service['code_es']).'.jpg';
        } else {
            $img = $base_url . 'detail_' . Tools::strtolower($service['code_eo']) . '.jpg';
        }
        copy(
            $img,
            _PS_IMG_DIR_ . 's/' . (int)$carrier_id . '.jpg'
        );
        return $carrier_id;
    }

    /**
     * Makes plugin at 'online' mode.
     *
     * @access public
     * @return void
     */
    public function passToOnlineMode()
    {
        //update every range = 1 EMC carrier
        $this->db->update(
            'carrier',
            array('need_range' => 1),
            'external_module_name = "envoimoinscher" AND need_range = 1'
        );

        // clean the cache
        $this->cleanCache();
    }

    /**
     * Gets order carriers.
     *
     * @access public
     * @param  int $order Order id
     * @return array Array with keys the same as two tables with carrier informations :
     * 'orders' for orders table and 'order_carrier' for order_carriers table
     */
    public function getOrderCarriers($order_id)
    {
        $order = $this->db->getRow(
            'SELECT o.id_carrier AS oCarrier, o.total_shipping AS oShipping,
            total_shipping_tax_incl AS oShippingInc, carrier_tax_rate AS oCarrierTax,
            total_shipping_tax_excl AS oShippingExc, o.id_cart AS oCart,
            c.id_carrier AS cCarrier, c.delivery_option AS cDelivery,
            o.total_paid AS oTotal, o.total_paid_tax_incl AS oTotalIncl,
            o.total_paid_tax_excl AS oTotalExc,
            o.total_products AS oProducts, o.total_products_wt AS oProductsWt,
            o.total_wrapping AS oWrapping, o.total_wrapping_tax_incl AS oWrappingInc,
            o.total_wrapping_tax_excl AS oWrappingExc
            FROM ' . _DB_PREFIX_ . 'orders o
            JOIN ' . _DB_PREFIX_ . 'cart c ON c.id_cart = o.id_cart
            WHERE o.id_order = ' . (int)$order_id
        );
        $orders_carriers = $this->db->query(
            'SELECT * FROM ' . _DB_PREFIX_ . 'order_carrier WHERE id_order = ' . (int)$order
        );
        return array('orders' => $order, 'order_carriers' => $orders_carriers);
    }

    /**
     * Checks if $carrier belongs to EMC carriers.
     *
     * @access public
     * @param  int $carrier Carrier's id.
     * @return boolean True if $carrier belongs to EMC, false otherwise.
     */
    public function isEmcCarrier($carrier)
    {
        $row = $this->db->getRow(
            'SELECT external_module_name FROM ' . _DB_PREFIX_ . 'carrier WHERE id_carrier = ' . (int)$carrier
        );
        return ($row['external_module_name'] == 'envoimoinscher');
    }

    /**
     * Gets commands by cart_id (specially used by multi-delivery option.
     *
     * @access public
     * @param  int $cart Cart id.
     * @return array Orders of $cart.
     */
    public function getOrdersByCart($cart)
    {
        $orders = $this->db->query(
            'SELECT o.id_order, o.id_address_delivery AS oAddress,
            o.id_carrier AS oCarrier,  o.total_shipping AS oShipping,
            total_shipping_tax_incl AS oShippingInc, carrier_tax_rate AS oCarrierTax,
            total_shipping_tax_excl AS oShippingExc, o.id_cart AS oCart,
            c.id_carrier AS cCarrier, c.delivery_option AS cDelivery,
            o.total_paid AS oTotal, o.total_paid_tax_incl AS oTotalIncl,
            o.total_paid_tax_excl AS oTotalExc,
            o.total_products AS oProducts, o.total_products_wt AS oProductsWt,
            o.total_wrapping AS oWrapping, o.total_wrapping_tax_incl AS oWrappingInc,
            o.total_wrapping_tax_excl AS oWrappingExc
            FROM ' . _DB_PREFIX_ . 'orders o
            JOIN ' . _DB_PREFIX_ . 'cart c ON c.id_cart = o.id_cart
            WHERE o.id_cart = ' . (int)$cart
        );
        $orders_carriers = array();
        foreach ($orders as $order) {
            $orders_carriers[$order['id_order']] = $this->db->query(
                'SELECT * FROM ' . _DB_PREFIX_ . 'order_carrier WHERE id_order = ' . (int)$order['id_order']
            );
        }
        //get cart rules too (by now only for free shipping)
        $rules_rows = $this->db->query(
            'SELECT * FROM ' . _DB_PREFIX_ . 'order_cart_rule ocr
             JOIN ' . _DB_PREFIX_ . 'orders ord ON ord.id_order = ocr.id_order
             JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON cr.id_cart_rule = ocr.id_cart_rule
             WHERE ord.id_cart = ' . (int)$cart
        );
        $rules = array();
        foreach ($rules_rows as $rule) {
            $rules[$rule['id_order']] = $rule;
        }
        return array('orders' => $orders, 'order_carriers' => $orders_carriers, 'rules' => $rules);
    }

    /**
     * Get shipping costs from order_carrier table by cart id.
     *
     * @deprecated Is making in override/controller/front/OrderConfirmationController.php
     * @access     public
     * @param      int $id_cart Id cart.
     * @return     array List with order_carrier informations.
     */
    public function getCarriersCostByCart($id_cart)
    {
        $orders = $this->db->query(
            'SELECT oc.*, car.*, api.*, o.id_address_delivery AS oAddress,
            o.total_paid AS oTotal, o.total_paid_tax_incl AS oTotalIncl,
            o.total_paid_tax_excl AS oTotalExc,
            o.total_products AS oProducts, o.total_products_wt AS oProductsWt,
            o.total_wrapping AS oWrapping, o.total_wrapping_tax_incl AS oWrappingInc,
            o.total_shipping AS oShipping, o.total_shipping_tax_excl AS oShippingExc,
            o.total_wrapping_tax_excl AS oWrappingExc, total_shipping_tax_incl AS oShippingInc
            FROM ' . _DB_PREFIX_ . 'order o
            LEFT JOIN ' . _DB_PREFIX_ . 'order_carrier oc ON o.id_order = oc.id_order
            JOIN ' . _DB_PREFIX_ . 'carrier car ON oc.id_carrier = car.id_carrier
            JOIN ' . _DB_PREFIX_ . 'emc_cart_tmp api ON api.id_cart = o.id_cart
            WHERE o.id_cart = ' . (int)$id_cart
        );
        return $orders;
    }

    private function canBeFreeShipping($carrier_id)
    {
        $rows = $this->db->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'configuration WHERE name = "EMC_NO_FREESHIP"');
        if (!isset($rows['value'])) {
            $rows['value'] = array();
        } else {
            $rows['value'] = Tools::jsonDecode($rows['value'], true);
        }
        $carrier = $this->db->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'carrier c
            JOIN ' . _DB_PREFIX_ . 'emc_services es ON c.id_carrier = es.id_carrier
            WHERE c.id_carrier = ' . (int)$carrier_id
        );
        if (in_array($carrier['id_es'], $rows['value'])) {
            return false;
        }
        return true;
    }

    /**
     * Returns cached offers from pricing code
     *
     * @access public
     * @return array cached values or false
     */
    public function getCache($cacheKey)
    {
        $row = $this->db->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'emc_cache WHERE cache_key = "' . pSQL($cacheKey)
            . '" AND (expiration_date > NOW() OR expiration_date = "'.date('Y-m-d H:i:s', 0).'")'
            . ' ORDER BY expiration_date DESC'
        );

        if ($row !== false) {
            $cached = unserialize(base64_decode($row['cache_data']));
            return $cached;
        } else {
            return false;
        }
    }

    /**
     * Stores values in cache
     *
     * @access public
     * @param  string $cacheKey  the sha1 cache key.
     * @param  array  $cacheData data.
     * @param  int    $seconds   the number of seconds the data is considered valid.
     *                           Use 0 for value that's always valid.
     * @return array cached values or false
     */
    public function setCache($cacheKey, $cacheData, $seconds = null)
    {
        if ($seconds) {
            $expirationDate = date('Y-m-d H:i:s', time() + $seconds);
        } else {
            $expirationDate = date('Y-m-d H:i:s', 0);
        }

        $data = array(
          'cache_key' => pSQL($cacheKey),
          'cache_data' => base64_encode(serialize($cacheData)),
          'expiration_date' => $expirationDate
        );

        return $this->db->insert('emc_cache', $data, false, true, Db::REPLACE);
    }

    /**
     * Deletes values in cache
     *
     * @access public
     * @param  string $cacheKey the sha1 cache key.
     * @return boolean
     */
    public function deleteFromCache($cacheKey)
    {
        return $this->db->delete('emc_cache', 'cache_key = "' . pSQL($cacheKey) . '"');
    }

    /**
     * Deletes expired values in cache
     *
     * @access public
     * @return boolean
     */
    public function deleteExpiredFromCache()
    {
        return $this->db->delete('emc_cache', 'expiration_date < NOW()');
    }

    /**
     * Returns point id from cart
     *
     * @access public
     * @param  int $cartId the cart id.
     * @return string the point id
     */
    public function getSelectedPoint($cartId)
    {
        $point = $this->db->getRow(
            'SELECT selected_point FROM ' . _DB_PREFIX_ . 'emc_cart_tmp WHERE id_cart = '.(int)$cartId
        );

        if (isset($point['selected_point'])) {
            return $point['selected_point'];
        } else {
            return '';
        }
    }

    /**
     * Returns euro currency object
     *
     * @access public
     * @return currency object
     */
    public function getEuro()
    {
        $euroId = $this->db->getRow(
            'SELECT id_currency FROM ' . _DB_PREFIX_ . 'currency WHERE iso_code = "EUR"'
        );

        if (isset($euroId['id_currency'])) {
            return new Currency($euroId['id_currency']);
        } else {
            return false;
        }
    }

    public function cleanCache()
    {
        return $this->db->delete('emc_cache');
    }

    public function getNameCategory($id_eca)
    {
        $query = 'SELECT `name_eca` FROM `' . _DB_PREFIX_ . 'emc_categories` WHERE `id_eca` = "' . (int)$id_eca . '" ';
        return $this->db->getValue($query);
    }

    public function orderWithKeyExists($order, $key)
    {
        return $this->db->getValue(
            'SELECT count(*) FROM ' . _DB_PREFIX_ . 'emc_orders eo
                JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_order = eo.' . _DB_PREFIX_ . 'orders_id_order
                WHERE eo.' . _DB_PREFIX_ . 'orders_id_order = ' . $order . ' AND eo.tracking_eor = "' . $key . '" '
        ) > 0;
    }
    public function orderWithTimeoutError($order)
    {
        return $this->db->getValue(
            'SELECT count(*) FROM ' . _DB_PREFIX_ . 'emc_orders_post WHERE ' . _DB_PREFIX_ .
            'orders_id_order = ' . (int)$order . ' AND type = "timeout"'
        ) > 0;
    }

    public function handlePush()
    {
        $type = Tools::getValue('type');
        $key = Tools::getValue('key');
        $order = (int)Tools::getValue('order');
        $return = false;
        $emc = Module::getInstanceByName('envoimoinscher');
        $error_message = '';
        // Check if order exists
        if (($this->orderWithKeyExists($order, $key)) || ($this->orderWithTimeoutError($order) && $type == "status")) {
            // Execute the push request
            switch ($type) {
                case 'tracking':
                    $text = urldecode(Tools::getValue('text'));
                    $localisation = urldecode(Tools::getValue('localisation'));
                    $state = Tools::getValue('etat');
                    $date = strtotime(Tools::getValue('date'));
                    $return = $this->updateTracking($order, $text, $localisation, $state, $date);
                    if ($return == false) {
                        $error_message = $emc->l('Unable to update order\'s tracking data');
                    }
                    break;
                case 'status':
                    $emc_ref = urldecode(Tools::getValue('emc_reference'));
                    $ope_ref = urldecode(Tools::getValue('carrier_reference'));
                    //Insert order
                    if ($emc_ref != '') {
                        $this->insertOrderFromPush($order);
                    }
                    $documents = array();
                    $documents['label'] = urldecode(Tools::getValue('label_url'));
                    if (Tools::isSubmit('remise')) {
                        $documents['remise'] = urldecode(Tools::getValue('remise'));
                    }
                    if (Tools::isSubmit('manifest')) {
                        $documents['manifest'] = urldecode(Tools::getValue('manifest'));
                    }
                    if (Tools::isSubmit('connote')) {
                        $documents['connote'] = urldecode(Tools::getValue('connote'));
                    }
                    if (Tools::isSubmit('proforma')) {
                        $documents['proforma'] = urldecode(Tools::getValue('proforma'));
                    }
                    if (Tools::isSubmit('b13a')) {
                        $documents['b13a'] = urldecode(Tools::getValue('b13a'));
                    }
                    $return = $this->updateStatus($order, $emc_ref, $ope_ref, $documents);
                    if ($return === false) {
                        $error_message = $emc->l('Unable to update order\'s status data');
                    }
                    break;
                default:
                    $error_message = $emc->l('Uknown push command : ') . $type;
                    break;
            }
        } else {
            $error_message = $emc->l('Order not found');
            $return = false;
        }

        // The push request has not been done correctly
        if ($return === false) {
            $ip_address = $emc->l('Unknown address');
            if (preg_match('/^([A-Za-z0-9.]+)$/', Tools::getRemoteAddr())) {
                $ip_address = Tools::getRemoteAddr();
            }
            $error_msg = sprintf(
                $emc->l('%s. Target order : %s.Caller IP address : %s'),
                $error_message,
                $order,
                $ip_address
            );
            Logger::addLog('['.$emc->l('ENVOIMOINSCHER').'][' . time() . '] ' . $error_msg, 4, 1);
        }
        return $return;
    }

    /**
     * Return first admin employee
     *
     * @return Employee or false
     */
    public function getFirstAdminEmployee()
    {
        return $this->db->getRow(
            'SELECT id_employee FROM '._DB_PREFIX_.'employee '
            . 'WHERE id_profile=1 '
            . 'AND active=1 ORDER BY id_employee DESC'
        );
    }

    public function updateTracking($order, $text, $localisation, $state, $date)
    {
        $emc = Module::getInstanceByName('envoimoinscher');
        $cookie = $emc->getContext()->cookie;

        // Get module tracking configs
        $confs = self::getConfigMultiple(array('EMC_ANN', 'EMC_ENVO', 'EMC_CMD', 'EMC_LIV'));

        // Get the new state
        $new_state = 0;
        switch ($state) {
            case 'CMD':
                $new_state = $confs['EMC_CMD'];
                break;
            case 'ENV':
                $new_state = $confs['EMC_ENVO'];
                break;
            case 'ANN':
                $message = new Message();
                $texte = $emc->l('EnvoiMoinsCher : Dispatch cancelled');
                $message->message = htmlentities($texte, ENT_COMPAT, 'UTF-8');
                $message->id_order = $order;
                $message->private = 1;
                $message->add();
                $new_state = $confs['EMC_ANN'];
                break;
            case 'LIV':
                $new_state = $confs['EMC_LIV'];
                break;
            default:
                return false;
        }

        // Get the last order state (prevent repeat of the same state)
        $history_row = $this->db->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'order_history
          WHERE id_order = ' . $order . ' ORDER BY id_order_history DESC'
        );

        // The order has already been delivered, no need to add more tracking info
        if ($history_row['id_order_state'] == $confs['EMC_LIV']) {
            return true;
        }

        // Update the order's history
        if ((int)$new_state > 0 && $new_state != $history_row['id_order_state']) {
            $history = new OrderHistory();
            $history->id_order = $order;
            $history->changeIdOrderState($new_state, $order);
            $employee = $this->getFirstAdminEmployee();
            $history->id_employee = (int)$employee["id_employee"];
            $history->addWithemail();
        }

        // Generate a tracking message if no one was given
        if ($text == '') {
            $cmd_row = $this->db->getRow(
                'SELECT * FROM ' . _DB_PREFIX_ . 'order_state_lang
                WHERE id_order_state = ' . (int)$new_state . ' AND id_lang = ' . (int)$cookie->id_lang
            );
            $text = $emc->l('Order\'s state : ') . $cmd_row['name'];
        }

        // Generate the default date if no one was given
        if ($date == false) {
            $date = time();
        }

        // Add tracking info
        $data = array(
          _DB_PREFIX_ . 'orders_id_order' => (int)$order,
          'state_et' => pSQL($state),
          'date_et' =>pSQL(date('Y-m-d H:i:s', $date)),
          'text_et' => pSQL($text),
          'localisation_et' => pSQL($localisation)
        );
        $this->db->insert('emc_tracking', $data);

        return true;
    }

    public function updateStatus($order, $emc_ref, $ope_ref, $documents)
    {
        // Add all documents's urls
        foreach ($documents as $name => $url) {
            $this->db->insert(
                'emc_documents',
                array(
                    _DB_PREFIX_ . 'orders_id_order' => (int)$order,
                    'link_ed' => pSQL(trim($url)),
                    'type_ed' => pSQL($name),
                    'generated_ed' => 1,
                    _DB_PREFIX_ . 'cart_id_cart' => 0,
                )
            );
        }

        // Update the emc's order
        $this->db->update(
            'emc_orders',
            array('ref_ope_eor' => pSQL($ope_ref)),
            _DB_PREFIX_ . 'orders_id_order = ' . (int)$order
        );

        // Update the prestashop order
        $tracking_mode = self::getConfig('EMC_TRACK_MODE');
        $shipping_number = EnvoimoinscherModel::TRACK_OPE_TYPE == $tracking_mode ? $ope_ref : $emc_ref;
        $this->db->update(
            'orders',
            array('shipping_number' => pSQL($shipping_number)),
            'id_order = ' . (int)$order
        );

        $this->db->update(
            'order_carrier',
            array('tracking_number' => pSQL($shipping_number)),
            'id_order = ' . (int)$order
        );
        return true;
    }

    private function generateOrderFilter($filters)
    {
        $sql = '';
        if (!empty($filters['filterBy'])) {
            //by order id
            if (isset($filters['filterBy']['filter_id_order'])) {
                $sql .= ' AND o.id_order = ' . (int)$filters['filterBy']['filter_id_order'];
            }

            //by carrier
            if (isset($filters['filterBy']['carriers'])) {
                if ($filters['filterBy']['carriers'] == 'del') {
                    $sql .= ' AND c.name NOT IN (SELECT name FROM ' . _DB_PREFIX_ . 'carrier WHERE deleted=0)';
                } elseif ($filters['filterBy']['carriers'] != 'all') {
                    $sql .= ' AND c.name LIKE "' . pSQL($filters['filterBy']['carriers']) . '"';
                }
            }

            //by order date
            if (isset($filters['filterBy']['start_order_date'])) {
                $sql .= " AND eo.date_order_eor >= STR_TO_DATE('" . pSQL($filters['filterBy']['start_order_date']) .
                "', '%Y-%m-%d')";
            }

            if (isset($filters['filterBy']['end_order_date'])) {
                $sql .= " AND eo.date_order_eor < DATE_ADD(STR_TO_DATE('" .
                pSQL($filters['filterBy']['end_order_date']) . "', '%Y-%m-%d'), INTERVAL 1 DAY)";
            }

            //by creation date
            if (isset($filters['filterBy']['start_creation_date'])) {
                $sql .= " AND o.date_add >= STR_TO_DATE('" . pSQL($filters['filterBy']['start_creation_date']) .
                "', '%Y-%m-%d')";
            }

            if (isset($filters['filterBy']['end_creation_date'])) {
                $sql .= " AND o.date_add < DATE_ADD(STR_TO_DATE('" . pSQL($filters['filterBy']['end_creation_date']) .
                "', '%Y-%m-%d'), INTERVAL 1 DAY)";
            }

            //by recipient (string contained in company, first name, last name or email)
            if (isset($filters['filterBy']['recipient']) && !empty($filters['filterBy']['recipient'])) {
                foreach ($filters['filterBy']['recipient'] as $value) {
                    $sql .= ' AND (INSTR(a.firstname, "' . pSQL($value) . '") > 0
                  OR INSTR(a.lastname, "' . pSQL($value) . '") > 0
                  OR INSTR(cr.email, "' . pSQL($value) . '") > 0)';
                }
            }
        }
        return $sql;
    }

    public function getOrderCount($filters)
    {
        return $this->db->getValue(
            'SELECT COUNT(eo.' . _DB_PREFIX_ . 'orders_id_order) AS allCmd
          FROM ' . _DB_PREFIX_ . 'emc_orders eo
          JOIN ' . _DB_PREFIX_ . 'orders o ON eo.' . _DB_PREFIX_ . 'orders_id_order = o.id_order
          LEFT JOIN ' . _DB_PREFIX_ . 'order_carrier oc
           ON oc.id_order = o.id_order
          LEFT JOIN ' . _DB_PREFIX_ . 'carrier c
           ON (c.id_carrier = oc.id_carrier AND oc.id_carrier IS NOT NULL)
           OR (c.id_carrier = o.id_carrier AND oc.id_carrier IS NULL)
          JOIN ' . _DB_PREFIX_ . 'address a ON a.id_address = o.id_address_delivery
          JOIN ' . _DB_PREFIX_ . 'customer cr ON cr.id_customer = a.id_customer
          WHERE eo.ref_emc_eor != ""' . $this->generateOrderFilter($filters)
        );
    }
    
    public static function logLastRequest($string, $request)
    {
        // if option does not exist, create an empty return.xml file
        if (self::getconfig('EMC_LOGS_'.Tools::strtoupper($request)) === false) {
            @file_put_contents($_SERVER['DOCUMENT_ROOT'].'/return.xml', '');
        }
        self::updateConfig('EMC_LOGS_'.Tools::strtoupper($request), htmlentities($string));
    }
}

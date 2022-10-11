<?php
/**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2015 silbersaiten
 * @version   1.0.3
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 */

require_once(dirname(__FILE__).'/../../classes/FormMakerForm.php');
require_once(dirname(__FILE__).'/../../classes/FormMakerElement.php');
require_once(dirname(__FILE__).'/../../classes/FormMakerElementValue.php');
require_once(dirname(__FILE__).'/../../classes/FormMakerReport.php');

class AdminFormReportController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'fm_form_report';
        $this->className = 'FormMakerReport';
        $this->lang = false;
        $this->bootstrap = true;
        if (method_exists('Context', 'getTranslator')) {
            $this->translator = Context::getContext()->getTranslator();
        }

        $this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected')));

        $this->addRowAction('view');
        $this->addRowAction('delete');
        
        $this->_select = '
        CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`, c.`email`';
        
        $this->_join = '
        LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`)';

        $this->fields_list = array(
            'id_fm_form_report' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 20
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'havingFilter' => true,
            ),
            'name' => array(
                'title' => $this->l('Form'),
                'width' => 'auto'
            ),
            'date_add' => array(
                'title' => $this->l('Created'),
                'type' => 'date',
                'filter_key' => 'a!date_add',
                'align' => 'text-left'
            ),
            'email' => array(
                'title' => $this->l('Reply'),
                'orderby' => false,
                'search' => false,
                'width' => 'auto',
                'callback' => 'generateReplyLink'
            )
        );

        parent::__construct();
    }
    
    public function generateReplyLink($email, $tr)
    {
        if ($tr['send'] == 0) {
            $link = '<a class="btn btn-warning">'.$this->l('The form has not been send').'</a>';
        } else {
            $link = $email
                ? '<a href="mailto:'.$email.'" class="btn btn-default">'.$this->l('Reply').'</a>'
                : '<a class="btn btn-warning">'.$this->l('Unregistered customer').'</a>';
        }
        
        return $link;
    }

    public function renderList()
    {
        if (Tools::isSubmit('export')) {
            $reports = Db::getInstance()->ExecuteS(
                'SELECT `id_fm_form_report` FROM `'._DB_PREFIX_.'fm_form_report`'
            );

            if (count($reports)) {
                $file_name = 'export'.(date('Y.m.d')).'_'.uniqid().'.csv';
                $file_path = dirname(__FILE__).'/../../export/'.$file_name;

                $file = fopen($file_path, 'w');

                $head = array(
                    $this->l('Customer ID'),
                    $this->l('Customer name'),
                    $this->l('Customer e-mail'),
                    $this->l('Product'),
                    $this->l('Form name'),
                    $this->l('Submitted on'),
                    $this->l('Form values'),
                );

                fputcsv($file, $head, ';');

                foreach ($reports as $report_id) {
                    $report_to_csv = array();

                    $report = new FormMakerReport($report_id['id_fm_form_report']);
                    $customer = new Customer((int)$report->id_customer);
                    $form = new FormMakerForm((int)$report->id_fm_form, $this->context->language->id);
                    $product = false;
                    $form_fields = $report->getReportData();
                    $values = '';
                    foreach ($form_fields as $field) {
                        $values .= '\''.$field['field'].' - '.$field['value'].'\' ';
                    }

                    if ($report->id_product
                        && Validate::isLoadedObject($product_obj = new Product(
                            (int)$report->id_product,
                            false,
                            $this->context->language->id
                        ))) {
                        $product = $product_obj;
                    }

                    if (Validate::isLoadedObject($customer)) {
                        $customer_to_csv = array(
                            $customer->id,
                            $customer->lastname.' '.$customer->firstname,
                            $customer->email
                        );
                    } else {
                        $customer_to_csv = array('', '', '');
                    }

                    $form_to_csv = array(
                        Validate::isLoadedObject($product) ? $product->name.' (#'.$product->id.')' : '',
                        $form->name,
                        $report->date_add,
                        $values
                    );

                    $report_to_csv = array_merge($customer_to_csv, $form_to_csv);
                    fputcsv($file, $report_to_csv, ';');
                }

                fclose($file);
                $this->context->smarty->assign(array(
                    'down_export' => $file_name,
                ));
            }
        } elseif ($download_file = Tools::getValue('download')) {
            $path = $this->module->getLocalPath().'export/'.$download_file;
            
            if (Validate::isFileName($download_file) && file_exists($path)) {
                header('Pragma: private');
                header('Cache-control: private, must-revalidate');
                header('Content-Type: application/octet-stream');
                header('Content-Length: '.(string)filesize($path));
                header('Content-Disposition: attachment; filename="'.($download_file).'"');
                readfile($path);
            }
        }
        
        return parent::renderList();
    }
    
    public function renderView()
    {
        $report = new FormMakerReport(Tools::getValue('id_fm_form_report'));
        
        if (!Validate::isLoadedObject($report)) {
            $this->errors[] = Tools::displayError('The report cannot be found within your database.');
        }
            
        $customer = new Customer((int)$report->id_customer);
        $gender = Validate::isLoadedObject($customer)
            ? new Gender((int)$customer->id_gender, $this->context->language->id)
            : false;
        $form = new FormMakerForm((int)$report->id_fm_form, $this->context->language->id);
        $product = false;
        $product_image = false;
        
        if ($report->id_product
            && Validate::isLoadedObject(
                $product_obj = new Product((int)$report->id_product, false, $this->context->language->id)
            )) {
            $product = $product_obj;
            
            $image = Product::getCover($product->id);
            
            if ($image && Validate::isLoadedObject($image_obj = new Image($image['id_image']))) {
                $name = 'product_mini_'.(int)$product->id.'.jpg';
                // generate image cache, only for back office
                $image_tag = ImageManager::thumbnail(
                    _PS_IMG_DIR_.'p/'.$image_obj->getExistingImgPath().'.jpg',
                    $name,
                    45,
                    'jpg'
                );
                
                if (file_exists(_PS_TMP_IMG_DIR_.$name)) {
                    $image_size = getimagesize(_PS_TMP_IMG_DIR_.$name);
                    
                    $product_image = array(
                        'name' => $name,
                        'tag' => $image_tag,
                        'size' => $image_size
                    );
                }
            }
        }
        
        $this->context->smarty->assign(array(
            'currency' => new Currency(Configuration::Get('PS_CURRENCY_DEFAULT')),
            'product' => $product,
            'product_image' => $product_image,
            'report' => $report,
            'customer' => Validate::isLoadedObject($customer) ? $customer : false,
            'gender' => $gender && Validate::isLoadedObject($gender) ? $gender : false,
            'customerStats' => Validate::isLoadedObject($customer) ? $customer->getStats() : false,
            'form' => Validate::isLoadedObject($form) ? $form : false,
            'form_products' => Validate::isLoadedObject($form)
                ? $form->getFormProducts($this->context->language->id)
                : false,
            'form_fields' => $report->getReportData()
        ));
        
        return parent::renderView();
    }
}

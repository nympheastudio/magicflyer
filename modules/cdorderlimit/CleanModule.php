<?php
/**
* 2013 - 2015 CleanDev
*
* NOTICE OF LICENSE
*
* This file is proprietary and can not be copied and/or distributed
* without the express permission of CleanDev
*
* @author    CleanPresta : www.cleanpresta.com <contact@cleanpresta.com>
* @copyright 2013 - 2015 CleanDev.net
* @license   You only can use module, nothing more!
*/

class  CleanModule extends Module
{
	protected $_errors   = array();
	//list of hooks :: array('hook1', 'hook2')
	public $hooks = array();
	public $new_hooks = array();
	
	// list of new tabs :: array('class1' => 'name1', ...,'class2' => 'name2');
	public $tabs = array();
	
	// list of exchanged tab :: array('oldTab1' => 'newTab1', ...,'oldTabn' => 'newTabn');
	public $exchanged_tabs = array(); 
	
	//coping files :: array('name1' => '/path1/',...,'namen' => '/pathn/')
	public $files = array(); 
	
	public $configValues = false; //For configuration values
	
	/**
	* getContent form
	* 'associative' => true, for associate array key
	*/
	public $config_form = array(); 
	
	// create sub dir in dir (element of tab)
	public $extra_dir = array(); 
	
	//retour notice
	public $html = ''; 
	
	//retour notice
	public $customerModule = false; 
	
	//tab of config elements
	public $tab_elts = array();
	
	public $full_description; 
	
	public $required_parameters = array();
	
	public function __construct() 
	{
		$this->secure_key = Tools::encrypt($this->name);
		$this->need_instance = 0;
		$this->bootstrap = true;
		$this->trusted = false;
		$this->cleanVersion = '3.0.0';
		$this->is_configurable = 1;
		$this->author = $this->l('CleanPresta');
		$this->ps_versions_compliancy['max'] = _PS_VERSION_;
		$this->confirmUninstall = $this->l('Are you sure you want to delete this module? This removes the files and tables related modules.');
		parent::__construct();  
		$this->author = $this->l('CleanPresta');
		$this->defaultLan = (int)Configuration::get('PS_LANG_DEFAULT');
		$this->lang = Context::getContext()->language;
		$this->shop = Context::getContext()->shop;
		$this->context = Context::getContext();
		$this->formBoolType = ($this->is_16())?'switch':'radio';
		$this->warningDisplay();
	}
	
	public function install()
	{
		$this->_clearCache('*');
		return $this->cdManageDb(true) && 
			$this->cdManageConfigs(true) && 
			$this->cdManageTabs(true) && 
			$this->cdManageExchangedTabs(true)&&
			$this->cdManageFiles(true) &&
			$this->cdManageDir(true) &&
			parent::install() &&
			$this->cdManageHooks(true) &&
			$this->cdClearCache();
	}
	
	public function uninstall()
	{
		$this->_clearCache('*');
		return $this->cdManageDb(false) && 
			$this->cdManageConfigs(false) && 
			$this->cdManageTabs(false) && 
			$this->cdManageExchangedTabs(false) &&
			$this->cdManageFiles(false) &&
			$this->cdManageDir(false) &&
			parent::uninstall() &&
			$this->cdManageHooks(false) &&
			$this->cdClearCache();
	}
	 
	/*Gestion des tables supplémentaires*/
	protected function cdManageDb($install = true)
	{
		$file = (!$install)?$this->local_path.'sql'.DIRECTORY_SEPARATOR.'uninstall.sql':$this->local_path.'sql'.DIRECTORY_SEPARATOR.'install.sql';
		
		//var_dump($file, file_exists($file), Tools::file_get_contents($file));die(); 
		if (file_exists($file) && ($sql = Tools::file_get_contents($file))) { // pas de fichier, tant mieux!
			$sql = str_replace(array('PREFIX_', 'ps_', 'ENGINE_TYPE'), array(_DB_PREFIX_, _DB_PREFIX_, _MYSQL_ENGINE_), $sql);
			$sql = preg_split("/;\s*[\r\n]+/", trim($sql)); 
			foreach ($sql as $query) {
				if (!Db::getInstance()->execute(trim($query))) {
					return false;
				}
			}
		}
		return true;
	}
	
	/*Gestion de la configuration*/
	protected function cdManageConfigs($install = true)
	{
		if (!empty($this->config_form)) {
			if (isset($this->config_form['form'])) {
				$this->config_form = array($this->config_form);
			}
			foreach ($this->config_form as $configs) {
				foreach ($configs as $form) {
					if (!empty($form['input'])) {
						foreach ($form['input'] as $input) {
							if (empty($install)) { //désintallation
								Configuration::deleteByName($input['name']);
							} elseif (!empty($input['default'])) {
								if (!empty($input['multiple']) || !empty($input['associative'])) {
									/*$input_name = Tools::substr($input['name'], 0, strpos($input['name'],'[')); var_dump($input['default'], $input_name, Tools::getValue($input_name));die();
									Configuration::updateValue($input_name, serialize(Tools::getValue($input_name)));*/
								} else {
									Configuration::updateValue($input['name'], $input['default']);
								}
							}
						}
					}
				}
			}
		}
		return true;
	}
	
	/*Gestion de nouveaux menus*/
	protected function cdManageTabs($install = true)
	{
		if (!empty($this->tabs)) {
			foreach ($this->tabs as $elt) {
				$id_tab = (int)Tab::getIdFromClassName($elt['class']);
				if (!empty($id_tab)) {
					$tab = new Tab($id_tab);
				}
				
				if ($install) { // installation
					if (empty($tab) || !Validate::isLoadedObject($tab))
						$tab = new Tab();
					$tab->class_name = $elt['class'];
					$tab->id_parent = (int)Tab::getIdFromClassName($elt['parent']);
					foreach (Language::getLanguages(true) as $lang)
						$tab->name[$lang['id_lang']] = $elt['name'];
					unset($lang);
					$tab->module = $this->name; 
					$tab->save();
				}
				elseif (!empty($tab) && Validate::isLoadedObject($tab)) {
					return $tab->delete();
				}
			}
		}
		return true;
	}
	
	/*Gestion de permutation des menus*/
	protected function cdManageExchangedTabs($install = true)
	{
		if (!empty($this->exchanged_tabs)) {
			foreach ($this->exchanged_tabs as $old => $new) {
				if ($install) { // installation
					$tab = new Tab((int)Tab::getIdFromClassName($old));
					$tab->class_name = $new;
					$tab->module = $this->name; 
				} else {
					$tab = new Tab((int)Tab::getIdFromClassName($new));
					$tab->class_name = $old;
					$tab->module = ''; 
				}
				if (!empty($tab->id)) { // si l'onglet est trouvé
					$tab->save();
				}
			}
		}
		return true;
	}
	
	/*Gestion de du menu*/
	protected function cdManageDir($install = true)
	{
		if (!empty($install)) { //Installation
			if (!empty($this->extra_dir)) {
				foreach ($this->extra_dir as $val) {
					$dir = _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.$val.DIRECTORY_SEPARATOR.$this->name;
					if (!file_exists($dir)) @mkdir($dir, 0777);
				}
			}
		}
		return true;
	}
	
	protected function cdManageFiles($install = true)
	{
		if (!empty($this->files)) {
			$sep = DIRECTORY_SEPARATOR;
			foreach ($this->files as $file => $path) {
				$path = str_replace(array('default-bootstrap','/','\\'), array(_THEME_NAME_,$sep,$sep), $path); //ad=>basename(_PS_ADMIN_DIR_)
				$src = $this->local_path.'files'.$sep.$file; 
				$dest = _PS_ROOT_DIR_.$path.$file;
				if (!file_exists(dirname($dest))) @mkdir(dirname($dest), 0777); //on cree le dossier s'il n'existe pas.
				if (is_writable(dirname($dest))) {
					if (empty($install)) { //désintallation
						//@unlink($dest); // on supprime le fichier copié
						@rename($dest.'.CDBACK', $dest); // on remet le fichier de départ
					}
					else
					{ // installation
						@rename($dest, $dest.'.CDBACK'); // on renomme le fichier si existe
						if (!Tools::copy($src, $dest)) { // avant de copier le nouveau
							throw new Exception(sprintf(Tools::displayError('directory (%s) not writable'), dirname($dest)));
						}
					}
				}
				else
					throw new Exception(sprintf(Tools::displayError('directory (%s) not writable'), dirname($dest))); 
			}
		}
		return true;
	}
	
	/*GEstion des hooks*/
	protected function cdManageHooks($install = true)
	{
		if ($install) {
			$this->registerHook('header');
			$this->registerHook('backOfficeHeader');
			foreach ($this->hooks as $hook) {
				$this->registerHook($hook); 
			}
			foreach ($this->new_hooks as $hook) {
				if (!Hook::getIdByName($hook)) { // create if hook no exist
					$newHook = new Hook();
					$newHook->name = $hook;
					$newHook->title = preg_replace('/(?<=\\w)(?=[A-Z])/'," $1", $hook);
					$newHook->description = $newHook->title.' for CleanPresta modules';
					$newHook->save();
				}
				$this->registerHook($hook);
			}
		} else {
			$this->unregisterHook('header');
			$this->unregisterHook('backOfficeHeader');
			foreach ($this->hooks as $hook) {
				$this->unregisterHook($hook); 
			}
			foreach ($this->new_hooks as $hook) {
				$newHookId = (int)Hook::getIdByName($hook);
				$newHook = new Hook($newHookId);
				$newHook->delete();
				$this->unregisterHook($hook);
			}
		}
		return true;
	}
	
	/*Formulaire de configuration*/
	protected function renderForm()
	{
		if (isset($this->config_form['form'])) {
			$this->config_form = array($this->config_form);
		}
		$helper = new HelperForm(); 
		$helper->module = $this;
		$helper->name_controller = $this->name; 
		$helper->identifier = $this->identifier;
		$helper->token = Tools::getAdminTokenLite('AdminModules').'#cdTabConfig';
		$helper->table = 'module';
		$lang = new Language($this->defaultLan);
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = $this->config_form;
 
		$helper->submit_action = 'submitConf'.$this->name;
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name; 
		/*1.5 compliantion*/
		$helper->toolbar_scroll = false;
		$helper->show_toolbar = false;
		$helper->toolbar_btn = $this->initToolbar();
		$helper->title = $this->displayName;
		
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		); 
		return $helper->generateForm($this->fields_form);
	}
	
	protected function initToolbar()
	{
		// on met juste le bouton de validation des formulaires
		$this->toolbar_btn['save'] = array(
			'href' => '#',
			'desc' => $this->l('Save')
		);
		return $this->toolbar_btn;
	}
	
	/*Récupération des valeurs de configuration*/
	public function getConfigValues()
	{
		$configs = array();
		$param = '';
		$unserialized = array();
		if (!empty($this->config_form)) {
			if (isset($this->config_form['form'])) {
				$this->config_form = array($this->config_form);
			}
			foreach ($this->config_form as $configs) {
				foreach ($configs as $form) {
					if (!empty($form['input'])) {
						foreach ($form['input'] as $input) {
							if (!empty($input['multiple']) || !empty($input['associative']) || !empty($input['tree'])) { // unserialized values
								$input['name'] = Tools::substr($input['name'], 0, strpos($input['name'],'[')); 
								$unserialized[$input['name']] = $input['name'];
							}
							$param .= $input['name'].',';
						}
					}
				}
			}
			
			if (!empty($param)) {
				$param = trim($param, ', ');
				$configs = Configuration::getMultiple(explode(',', $param));
				if (!empty($unserialized))
					foreach ($unserialized as $val) {
						$configs[$val] = Tools::unSerialize($configs[$val]);
					}
			}
		}
		$configs['is_16'] = $this->is_16();
		$configs['module_path'] = $this->_path;
		$configs['module_name'] = $this->name;
		return array_change_key_case($configs, CASE_LOWER);
	}
	
	/*Assignation des values de configuration des formulaire*/
	protected function getConfigFieldsValues()
	{
		$configTab = array(); 
		if (!empty($this->config_form)) {
			if (isset($this->config_form['form'])) {
				$this->config_form = array($this->config_form);
			}
			$languages = Language::getLanguages(false);
			foreach ($this->config_form as $configs) {
				foreach ($configs as $form) {
					foreach ($form['input'] as $input) {
						if (!empty($input['lang'])) {
							foreach ($languages as $lang) {
								$configTab[$input['name']][$lang['id_lang']] = Tools::getValue($input['name'], Configuration::get($input['name'], $lang['id_lang']));
							}
						}
						elseif ($input['type'] == 'group') {
							$groups = Group::getGroups($this->context->language->id);
							$existGroups = Tools::getValue('groupBox', Tools::unSerialize(Configuration::get($input['name'])));
							foreach ($groups as $group) {
								$configTab['groupBox_'.$group['id_group']] = Tools::getValue('groupBox_'.$group['id_group'], (!empty($existGroups) && in_array($group['id_group'], $existGroups)));
							}
						} elseif (!empty($input['multiple']) || !empty($input['associative']) || ($input['type'] == 'categories')) {
							$input_name = Tools::substr($input['name'], 0, strpos($input['name'],'[')); 
							$configTab[$input['name']] = Tools::getValue($input_name, Tools::unSerialize(Configuration::get($input_name)));
							if (!empty($input['associative']) && count($configTab[$input['name']]) > 0 && !empty($configTab[$input['name']])) {
								foreach ($configTab[$input['name']] as $key=>$val) {
									$configTab[$input_name.'['.$key.']'] = $val;
								}
								//unset($configTab[$input['name']]);
							}
						}
						else
							$configTab[$input['name']] = Tools::getValue($input['name'], Configuration::get($input['name']));
					}
				}
			}
		}//echo'<pre>';print_r($configTab);echo'</pre>';die();
		return $configTab;
	}
	
	/*Validation du formulaire*/
	protected function processForm()
	{
		if (!empty($this->config_form) && Tools::isSubmit('submitConf'.$this->name)) {
			//echo'<pre>';print_r($_POST);echo'</pre>';die();
			if (isset($this->config_form['form'])) {
				$this->config_form = array($this->config_form);
			}
			$languages = Language::getLanguages(false);
			foreach ($this->config_form as $configs) {
				foreach ($configs as $form) {
					foreach ($form['input'] as $input) {
						$html = (!empty($input['autoload_rte']))?true:false;
						if (!empty($input['lang'])) {
							$text = array();
							foreach ($languages as $lang)
								$text[$lang['id_lang']] = Tools::getValue($input['name'].'_'.$lang['id_lang']);
							Configuration::updateValue($input['name'], $text, $html); 
						} elseif ($input['type'] == 'group') {
							Configuration::updateValue($input['name'], serialize(Tools::getValue('groupBox')));
						} elseif ($input['type'] == 'categories') {
							Configuration::updateValue($input['name'], serialize(Tools::getValue($input['name'])));
						} elseif (!empty($input['multiple']) || !empty($input['associative'])) {
							$input_name = Tools::substr($input['name'], 0, strpos($input['name'],'['));
							Configuration::updateValue($input_name, serialize(Tools::getValue($input_name)));
						} else {
							Configuration::updateValue($input['name'], Tools::getValue($input['name']), $html);
						}
					}
				}
			}
			return $this->displayConfirmation($this->l('Configuration updated'));
		}
		return '';
	}
	
	public function getContent()
	{
		if (count($this->config_form) > 0) {
			$this->html .= $this->processForm(); 
			if (count($this->tab_elts))
				array_unshift($this->tab_elts, array('id'=>'cdTabConfig', 'title'=>$this->l('Settings'), 'content'=>$this->renderForm())); // add config table4
			else	
				$this->tab_elts[] = array('id'=>'cdTabConfig', 'title'=>$this->l('Settings'), 'content'=>$this->renderForm());
		}
		
		if(!$this->customerModule){
			//adding doc 
			if (file_exists($this->local_path.'cleanpresta/readme/'.$this->lang->iso_code.'.pdf')) {
				$this->context->smarty->assign('readme', _MODULE_DIR_.$this->name.'/cleanpresta/readme/'.$this->lang->iso_code.'.pdf');
			} else {
				$this->context->smarty->assign('readme', _MODULE_DIR_.$this->name.'/cleanpresta/readme/fr.pdf');
			}
		
		
			//adding log
			if (file_exists($this->local_path.'cleanpresta/changelog/'.$this->lang->iso_code.'.txt')) {
				$this->context->smarty->assign('change_log', nl2br(Tools::file_get_contents($this->local_path.'cleanpresta/changelog/'.$this->lang->iso_code.'.txt')));
			} else {
				$this->context->smarty->assign('change_log', nl2br(Tools::file_get_contents($this->local_path.'cleanpresta/changelog/fr.txt')));
			}
			
			//features
			if (file_exists($this->local_path.'cleanpresta/features/'.$this->lang->iso_code.'.xml')) 
				$features = $this->local_path.'cleanpresta/features/'.$this->lang->iso_code.'.xml'; 
			else 
				$features = $this->local_path.'cleanpresta/features/fr.xml'; 
			
			if (($xml = simplexml_load_file($features)))
				$this->context->smarty->assign('features', Tools::jsonDecode(Tools::jsonEncode((array)$xml), 1));
		}
		
		if (!$this->is_16()) {
			// Clean the code use tpl file for html
			$tab = '&tab_module='.$this->tab;
			$token_mod = '&token='.Tools::getAdminTokenLite('AdminModules');
			$token_pos = '&token='.Tools::getAdminTokenLite('AdminModulesPositions');
			$token_trad = '&token='.Tools::getAdminTokenLite('AdminTranslations');

			$this->context->smarty->assign(array( 
				'module_trad' => 'index.php?controller=AdminTranslations'.$token_trad.'&type=modules&lang=',
				'module_hook' => 'index.php?controller=AdminModulesPositions'.$token_pos.'&show_modules='.$this->id,
				'module_back' => 'index.php?controller=AdminModules'.$token_mod.$tab.'&module_name='.$this->name,
			));
			// Clean memory
			unset($tab, $token_mod, $token_pos, $token_trad);
		}
		
		$this->context->smarty->assign(
			array(
				'module_dir' => $this->_path, 
				'customer_module' => $this->customerModule, 
				'reference' => $this->reference, 
				'is_16' => $this->is_16(), 
				'current_id_tab' => (int)$this->context->controller->id,
				'notice' => $this->html, 
				'name' => $this->name, 
				'display_name' => $this->displayName, 
				'tabConfig' => $this->tab_elts, 
				'description' => (empty($this->full_description))?$this->description:$this->full_description,
				'base_dir' => _PS_BASE_URL_.__PS_BASE_URI__, 
				'module_name' => $this->name, 
				'addon_link' => $this->name, 
				'addon_ratting' =>  'http://addons.prestashop.com/contact-community.php?id_product='.$this->addon_id, 
				'version' => $this->version
			)
		);
		
		return $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');//.$this->context->smarty->fetch($this->local_path.'views/templates/admin/prestui/ps-tags.tpl');
		//return $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl').$this->display(__FILE__, 'views/templates/admin/prestui/ps-tags.tpl');; 
	}
	
	
	public function hookDisplayHeader($params)
	{
		if (!$this->is_16()) { //1.5 compatibility
			$this->context->controller->addCSS($this->_path.'css/compatibily16.css', 'all');
		}
		$this->context->controller->addJS($this->_path.'views/js/'.$this->name.'.js');
		$this->context->controller->addCSS($this->_path.'views/css/'.$this->name.'.css', 'all');
	}
	
	public function hookDisplayBackOfficeHeader()
	{
		if (Tools::getValue('configure') == $this->name) {
			$this->context->controller->addJquery(); // addin jquery if not exist
			if (!$this->is_16()) { //1.5 compatibility
				$this->context->controller->addJS($this->_path.'views/js/bootstrap.min.js'); 
				$this->context->controller->addCSS($this->_path.'views/css/bootstrap.min.css', 'all'); 
				$this->context->controller->addCSS($this->_path.'views/css/bootstrap.extend.css', 'all'); 
				$this->context->controller->addCSS($this->_path.'views/css/font-awesome.min.css', 'all'); 
			}
			$this->context->controller->addJS($this->_path.'views/js/cleanadmin.js');  //dont rename this file
			$this->context->controller->addCSS($this->_path.'views/css/cleanadmin.css', 'all');  //dont rename this file  
			//$this->context->controller->addJS($this->_path.'views/js/riot+compiler.min.js'); // for prestui  
			//$this->context->controller->addjQueryPlugin('tagify', null, false); // for prestui 
		}
		//global js
		$this->context->controller->addJS($this->_path.'views/js/'.$this->name.'-admin.js'); 
		$this->context->controller->addCSS($this->_path.'views/css/'.$this->name.'-admin.css', 'all');
	}
	
	/**
	* Tools functions
	*/
	
	public function addJsVar($tabVar)
	{
		$return = '';
		foreach ($tabVar as $key => $value) {
			switch (gettype($value)) {
				case 'string' :
					$return .= "var ".$key." = '".$value."';";
				break;
				
				case 'boolean' :
				case 'integer' :
					$return .= 'var '.$key.' = '.(int)$value.';';
				break;
				
				case 'float' :
					$return .= 'var '.$key.' = '.(float)$value.';';
				break;
				
				case 'array' :
				case 'object' :
					$return .= 'var '.$key.' = ('.Tools::jsonEncode($value).');';
				break;
				
				default:
					$return .= 'var '.$key.' = '.$value.';';
			}
		}
		
		return '
			<script type="text/javascript"> '.$return.' </script>
		';
	}
	
	public function createTransactionProductCharge() 
	{
        $chargeExist = false;
        if ($productCharge = Configuration::get('CD_TCHARGE_PRODUCT')) 
		{
            if (Db::getInstance()->getValue('SELECT 1 FROM `' . _DB_PREFIX_ . 'product` WHERE id_product = ' . (int) $productCharge)){
                $chargeExist = true;
            }
        } 
        if (empty($chargeExist)) {//on crée un nouveau produit
			$languages = Language::getLanguages(true);
            $product = new Product();
            $product->active = $product->id_tax_rules_group = $product->available_for_order = $product->show_price = 1;
            $product->visibility = 'both';
			foreach ($languages as $lan) {
				$product->name[$lan['id_lang']] = $this->l('Transaction');
				$product->link_rewrite[$lan['id_lang']] = Tools::link_rewrite("transaction").'-'.$lan['id_lang'];
			}
            $product->reference = "CDTC";
            $product->id_category_default = 2;
            $product->price = 0;
            if ($product->add() && $product->id) {
                $group = new AttributeGroup();
				foreach ($languages as $lan)
					$group->name[$lan['id_lang']] = $group->public_name[$lan['id_lang']] = $this->l('Amount');
                $group->group_type = 'select';
                if ($group->add() && $group->id) {
                    Configuration::updateValue('CD_TCHARGE_ATTR', $group->id); // on sauvegarde le groupe d'atribut
                    $attr = new Attribute();
					foreach ($languages as $lan)
						$attr->name[$lan['id_lang']] = 0;
                    $attr->id_attribute_group = $group->id;
                    if ($attr->add() && $attr->id) 
					{
                        $combination = new Combination();
                        $combination->minimal_quantity = $combination->default_on = 1;
                        $combination->id_product = $product->id;
                        $combination->quantity = 99999;
                        $combination->price = $combination->weight = $combination->ecotax = $combination->quantity = 0;
                        $combination->available_date = '0000-00-00';
                        if ($combination->save() && $combination->id) {
                            if (Db::getInstance()->insert('product_attribute_combination', array('id_product_attribute' => (int) $combination->id, 'id_attribute' => (int) $attr->id))) {
                                StockAvailable::updateQuantity($product->id, 0, 1);
                                Configuration::updateValue('CD_TCHARGE_PRODUCT', $product->id);
                                return true;
                            }else{
								return false;
							}
                        } else{
							return false;
						}
                    }
                }
            } else {
				return false;
			}
        } else {
            return true;
        }
        return false;
    }
	
	public function getParamValue($name, $is_array = false)
	{
		$values = Configuration::get($name);
		if ($is_array) {
			$values = Tools::unSerialize($values);
			$values = !empty($values)?$values:array();
		}
		return Tools::getValue($name, $values);
	}
	
	public function warningDisplay()
	{
		if (!empty($this->required_parameters)) {
			$param = Configuration::getMultiple($this->required_parameters);
			foreach ($this->required_parameters as $rp) {
				if (empty($param[$rp])) {
					$this->warning = $this->l('Parameter details must be configured before using this module.');
					return false;
				}
			}
		}
		return true;
	}
	
	public function less_than_16()
	{
		return (version_compare(_PS_VERSION_, '1.6.0', '<') === true)?true:false;
	}
	
	public function is_16()
	{
		return (version_compare(_PS_VERSION_, '1.6.0', '>=') === true)?true:false;
	}
	
	protected function cdClearCache()
	{
		/*if((bool)(version_compare(_PS_VERSION_, '1.5.0', '>=') === true)){
			@unlink(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'/cache'.DIRECTORY_SEPARATOR.'class_index.php');
		}*/
		return true;
	}
}
<?php
/**
 * Responsive gallery
 *
 * @author    Studio Kiwik
 * @copyright Studio Kiwik 2013-2015
 * @license   http://licences.studio-kiwik.fr/responsivegallery
 */

if (defined('_CAN_LOAD_FILES_')) {

    class ResponsiveGallery extends Module
    {
        public $bootstrap = true;

        public function __construct()
        {
            $this->name = 'responsivegallery';
            $this->tab = 'front_office_features';
            $this->version = '3.0.2';
            $this->author = 'Studio Kiwik';
            $this->module_key = 'b94c904928f4485af02385d609251189';
            $this->need_instance = 0;
            $this->is_1_6 = version_compare('1.6', _PS_VERSION_, '<');

            parent::__construct();
            /*if (isset($this->controllers) && is_array($this->controllers))
            $this->controllers = array('default');*/

            $this->displayName = $this->l('Photo Gallery Responsive');
            $this->description =
                $this->l('Display your photos in this fully responsive grid gallery using infinite scorlling on it.');
            $this->secure_key = Tools::encrypt($this->name);
            $this->ps_versions_compliancy = array('min' => '1.5.1.0', 'max' => _PS_VERSION_ . '.99');

            $this->bootstrap = version_compare(_PS_VERSION_, '1.6', 'ge');

        }

        public function install()
        {
            if (Shop::isFeatureActive()) {
                Shop::setContext(Shop::CONTEXT_ALL);
            }

            $languages = Language::getLanguages(false);
            $titles = array();
            $preambles = array();
            foreach ($languages as $language) {
                $titles[$language['id_lang']] = $this->l('Our galleries');
                $preambles[$language['id_lang']] = $this->l('Here you can enjoy our galleries !');
            }

            return parent::install()
            && $this->installMeta()
            && $this->registerHook('DisplayAdminListBefore')
            && Configuration::updateValue('RG_MAX_IMAGE_SIDE_SIZE', 1200)
            && Configuration::updateValue('RG_TITLE', $titles)
            && Configuration::updateValue('RG_PREAMBLE', $preambles)
            && $this->installModuleTab(
                'AdminResponsiveGalleryGallery',
                'PGR ' . $this->l('galleries'),
                Tab::getIdFromClassName('AdminParentPreferences')
            )
            && $this->installModuleTab(
                'AdminResponsiveGallery',
                'PGR ' . $this->l('images'),
                Tab::getIdFromClassName('AdminParentPreferences')
            )
            && $this->createDb();
        }

        /**
         * Désinstallation du module.
         * - Désinstallation du Tab
         * - Suppression de la table responsivegallery_item
         * - Suppression des paramètres de configuration
         * - Suppression des images du répertoire d'upload
         */
        public function uninstall()
        {
            return $this->uninstallModuleTab('AdminResponsiveGallery')
            && Configuration::deleteByName('RG_MAX_IMAGE_SIDE_SIZE')
            && Configuration::deletebyName('RG_TITLE')
            && Configuration::deletebyName('RG_PREAMBLE')
            && $this->uninstallModuleTab('AdminResponsiveGalleryGallery')
            && $this->dropDb()
            && $this->clearUploads(_PS_MODULE_DIR_ . $this->name . '/views/img/uploads/')
            && parent::uninstall()
            && $this->uninstallMeta();
        }

        /**
         * Installation de l'onglet d'administration du module
         *
         * @param $tab_class
         * @param $tab_name
         * @param $id_tab_parent
         * @return bool
         */
        private function installModuleTab($tab_class, $tab_name, $id_tab_parent)
        {
            $tab = new Tab();
            $langs = Language::getLanguages();
            foreach ($langs as $l) {
                $tab->name[$l['id_lang']] = $tab_name;
            }

            $tab->class_name = $tab_class;
            $tab->module = $this->name;
            //Admin>Preferences : 16
            $tab->id_parent = $id_tab_parent;
            if (!$tab->save()) {
                return false;
            }

            return true;
        }

        /**
         * Désinstallation de l'onglet d'administration du module
         *
         * @param $tab_class
         * @return bool
         */
        private function uninstallModuleTab($tab_class)
        {
            $id_tab = Tab::getIdFromClassName($tab_class);
            if ($id_tab != 0) {
                $tab = new Tab($id_tab);
                return $tab->delete();
            }
            return true;
        }

        /**
         * Création de la table responsivegallery_item
         *
         * @return bool réussite de la requête
         */
        private function createDb()
        {
            $result = true;

            //galleries
            $result &= Db::getInstance()->execute(
                'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'responsivegallery_gallery` (
    				`id_gallery` int(10) unsigned NOT NULL AUTO_INCREMENT,
    				`id_shop_default` int(10) unsigned NOT NULL,
    				`active` boolean,
    				`image` text NOT NULL,
    				`legend_on_photo` boolean,
    				`nb_item_per_page` int(10),
    				`horizontal_margin` int(10),
    				`fadein_speed` int(10),
    				`vertical_margin` int(10),
    				`inner_margin` int(10),
    				`max_image_side_size` int(10),
    				`max_image_side_size_bo` int(10),
    				`breakpoint_1` int(10),
    				`nb_item_per_line_1` int(10),
    				`breakpoint_2` int(10),
    				`nb_item_per_line_2` int(10),
    				`breakpoint_3` int(10),
    				`nb_item_per_line_3` int(10),
    				`breakpoint_4` int(10),
    				`nb_item_per_line_4` int(10),
    				`breakpoint_5` int(10),
    				`nb_item_per_line_5` int(10),

    				PRIMARY KEY (`id_gallery`)
    			) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;'
            );
            $result &= Db::getInstance()->execute(
                'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'responsivegallery_gallery_lang` (
    				`id_gallery` int(10) unsigned NOT NULL AUTO_INCREMENT,
    				`id_lang` int(10) unsigned NOT NULL,
    				`id_shop` int(10) unsigned NOT NULL,
    				`title` text,
    				`preamble` text,
    				PRIMARY KEY (`id_gallery`,`id_lang`)
    			) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;'
            );

            $result &= Db::getInstance()->execute(
                'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'responsivegallery_gallery_shop` (
    					`id_gallery` int(11) unsigned NOT NULL,
    					`id_shop` int(11) unsigned NOT NULL,
    					`position` int(10) unsigned NOT NULL DEFAULT 0,
    				PRIMARY KEY (`id_gallery`,`id_shop`)
    			) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;'
            );

            //items
            $result &= Db::getInstance()->execute(
                'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'responsivegallery_item` (
    				`id_item` int(10) unsigned NOT NULL AUTO_INCREMENT,
    				`id_shop_default` int(10) unsigned NOT NULL,
    				`id_gallery` int(10) unsigned NOT NULL,
    				`image` text NOT NULL,
    				`link` text,
    				`active` tinyint(3) unsigned NOT NULL DEFAULT 1,
    				`legend_on_hover` tinyint(3) unsigned NOT NULL DEFAULT 1,
    				`legend_under_photo` tinyint(3) unsigned NOT NULL DEFAULT 1,
    				`date_add` datetime NOT NULL,
    				PRIMARY KEY (`id_item`)
    			) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;'
            );

            $result &= Db::getInstance()->execute(
                'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'responsivegallery_item_lang` (
    				`id_item` int(10) unsigned NOT NULL,
    				`id_shop` int(10) unsigned NOT NULL DEFAULT 1,
    				`id_lang` int(10) unsigned NOT NULL,
    				`legend` text CHARACTER SET utf8,
    				PRIMARY KEY (`id_item`,`id_shop`,`id_lang`)
    			) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;'
            );

            $result &= Db::getInstance()->execute(
                'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'responsivegallery_item_shop` (
    				`id_item` int(11) unsigned NOT NULL,
    				`id_shop` int(11) unsigned NOT NULL,
    				`position` int(10) unsigned NOT NULL DEFAULT 0,
    			PRIMARY KEY (`id_item`,`id_shop`)
    			) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;'
            );

            return $result;
        }

        /**
         * Suppression de la table responsivegallery_item
         *
         * @return bool réussite de la requête
         */
        private function dropDb()
        {
            $result = true;

            $result &= Db::getInstance()->execute(
                'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'responsivegallery_gallery_lang`'
            );
            $result &= Db::getInstance()->execute(
                'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'responsivegallery_gallery_shop`'
            );
            $result &= Db::getInstance()->execute(
                'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'responsivegallery_gallery`'
            );
            $result &= Db::getInstance()->execute(
                'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'responsivegallery_item_lang`'
            );
            $result &= Db::getInstance()->execute(
                'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'responsivegallery_item_shop`'
            );
            $result &= Db::getInstance()->execute(
                'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'responsivegallery_item`'
            );

            return $result;
        }

        /**
         * Permet d'instraller les Méta et de modifier l'url_rewrite
         *
         * @return bool
         */
        public function installMeta()
        {
            $page = 'module-' . $this->name . '-default';
            $result = Db::getInstance()->getValue(
                'SELECT id_meta FROM ' . _DB_PREFIX_ . 'meta WHERE page="' . pSQL($page) . '"'
            );
            //Si l'attribut controllers n'existe pas, on crée nous même la Méta
            $langs = Language::getLanguages();

            if ((int) $result == 0) {
                $meta = new Meta();
                $meta->page = $page;
                $meta->configurable = 1;
                foreach ($langs as $l) {
                    $meta->title[$l['id_lang']] = $this->displayName;
                    $meta->url_rewrite[$l['id_lang']] = $this->l('gallery');
                }

                return $meta->save();
            } else {
                // Sinon on ajoute uniquement l'url_rewrite à la meta
                $meta = new Meta($result);
                foreach ($langs as $l) {
                    $meta->title[$l['id_lang']] = $this->displayName;
                    $meta->url_rewrite[$l['id_lang']] = $this->l('gallery');
                }

                return $meta->save();
            }
            return false;
        }

        /**
         * Permet de désinstaller les Méta si la version de prestashop ne supporte pas l'attribut controller
         * @return bool
         */
        public function uninstallMeta()
        {
            $page = 'module-' . $this->name . '-default';
            $result = Db::getInstance()->getValue(
                'SELECT id_meta FROM ' . _DB_PREFIX_ . 'meta WHERE page="' . pSQL($page) . '"'
            );
            // Si il reste des Métas après désinstallation, on supprime
            // valable pour les versions de prestashop ne gérant pas les métas des controleurs
            if ((int) $result > 0) {
                $meta = new Meta($result);
                return $meta->delete();
            }
            return true;
        }
        /**
         * Suppression de toutes les images du répertoire passé en paramètre.
         *
         * @param $folder
         */
        private function clearUploads($folder)
        {
            // Ouvre le dossier
            $dossier = opendir($folder);
            // Tant que le dossier est pas vide
            while ($fichier = readdir($dossier)) {
                // Sans compter . et .. et index.php
                if ($fichier != '.' && $fichier != '..' && $fichier != 'index.php') {
                    //On selectionne le fichier et on le supprime
                    $courant = $folder . $fichier;
                    unlink($courant);
                }
            }
            //Fermer le dossier vide
            closedir($dossier);

            return true;
        }

        /**
         * Affichage du contenu du BO
         *
         * @return string contenu du BO
         */
        public function getContent()
        {
            $output = '';
            $languages = Language::getLanguages(false);

            //Ajout du css à la fin pour empêcher le modules.css de modifier l'entete
            if (!$this->is_1_6) {
                $output .= '<link href="' . $this->_path .
                'views/css/compatibility-1.5.css" rel="stylesheet" type="text/css" media="all" />';
            } elseif ($this->is_1_6 && version_compare(_PS_VERSION_, '1.6.0.4', '<')) {
                $output .= '<link href="' . $this->_path .
                'views/css/compatibility-1.6.css" rel="stylesheet" type="text/css" media="all" />';
            }

            //traitement de la configuration (1.5 : submitOptions)
            if (Tools::isSubmit('submitOptionsconfiguration') || Tools::isSubmit('submitOptions')) {

                //Récupération des informations $_post
                $rg_max_image_side_size = Tools::getValue(
                    'RG_MAX_IMAGE_SIDE_SIZE',
                    Configuration::get('RG_MAX_IMAGE_SIDE_SIZE')
                );

                Configuration::updateValue('RG_MAX_IMAGE_SIDE_SIZE', $rg_max_image_side_size);
                // Tableaux pour récupérer les valeurs des champs
                $rg_title = array();
                $rg_preamble = array();

                foreach ($languages as $language) {
                    $rg_title[$language['id_lang']] = Tools::getValue('RG_TITLE_' . $language['id_lang']);
                    $rg_preamble[$language['id_lang']] = htmlentities(
                        Tools::getValue('RG_PREAMBLE_' . $language['id_lang'])
                    );
                }
                Configuration::updateValue('RG_TITLE', $rg_title);
                Configuration::updateValue('RG_PREAMBLE', $rg_preamble);
            }

            return $output . $this->getHeader() . $this->renderForm() . $this->getFooter();
        }

        /**
         * Rendu du formulaire de parametrage
         *
         * @return formulaire de parametrage
         */
        public function renderForm()
        {
            $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

            $fields_form = array(
                'tinymce' => true,
                'legend' => array(
                    'title' => $this->l('Parameters'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    'RG_TITLE' => array(
                        'label' => $this->l('Title of gallery.'),
                        'type' => 'text',
                        'lang' => true,
                        'name' => 'RG_TITLE',
                    ),
                    'RG_PREAMBLE' => array(
                        'type' => 'textarea',
                        'label' => $this->l('Description of gallery'),
                        'lang' => true,
                        'name' => 'RG_PREAMBLE',
                        'cols' => 40,
                        'rows' => 10,
                        'class' => 'rte',
                        'autoload_rte' => true,
                    ),
                    'RG_MAX_IMAGE_SIDE_SIZE' => array(
                        'label' => $this->l('The max square box in which your gallery cover images will be fitted.'),
                        'desc' => $this->l('In pixels. The image will be resized if needed.'),
                        'type' => 'text',
                        'name' => 'RG_MAX_IMAGE_SIDE_SIZE',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitOptionsconfiguration',
                ),
            );
            if (Shop::isFeatureActive()) {
                $fields_form['input'][] = array(
                    'type' => 'shop',
                    'label' => $this->l('Shop association'),
                    'name' => 'checkBoxShopAsso',
                );
            }
            $helper = new HelperForm();
            $helper->module = $this;
            //$helper->name_controller = 'demotutotinymce';
            $helper->identifier = $this->identifier;
            $helper->token = Tools::getAdminTokenLite('AdminModules');
            foreach (Language::getLanguages(false) as $lang) {
                $helper->languages[] = array(
                    'id_lang' => $lang['id_lang'],
                    'iso_code' => $lang['iso_code'],
                    'name' => $lang['name'],
                    'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0),
                );
            }

            $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
            $helper->default_form_language = $default_lang;
            $helper->allow_employee_form_lang = $default_lang;
            $helper->toolbar_scroll = true;
            $helper->title = $this->displayName;
            $helper->submit_action = 'submitFormChamps';

            $helper->fields_value = $this->getFormValues();

            return $helper->generateForm(array(array('form' => $fields_form)));
        }

        public function getFormValues()
        {
            $fields_value = array();

            foreach (Language::getLanguages(false) as $lang) { // Si besoin d'utiliser la traduction
                $fields_value['RG_PREAMBLE'][(int) $lang['id_lang']] = html_entity_decode(
                    Tools::getValue(
                        'RG_PREAMBLE_' . (int) $lang['id_lang'],
                        Configuration::get(
                            'RG_PREAMBLE',
                            (int) $lang['id_lang']
                        )
                    )
                );

                $fields_value['RG_TITLE'][(int) $lang['id_lang']] = Tools::getValue(
                    'RG_TITLE_' . (int) $lang['id_lang'],
                    Configuration::get(
                        'RG_TITLE',
                        (int) $lang['id_lang']
                    )
                );
            }

            $fields_value['RG_MAX_IMAGE_SIDE_SIZE'] = Configuration::get('RG_MAX_IMAGE_SIDE_SIZE');

            return $fields_value;

        }

        /**
         * Bloc d'entete
         *
         * @return string entete
         */
        public function getHeader()
        {
            //Lien pour visualiser la galerie
            $link_gallery = $this->getLinkGallery('responsivegallery', 'default');

            $meta = Meta::getMetaByPage('module-responsivegallery-default', $this->context->language->id);

            $output = '<div class="page-bar toolbarBox">
    					<div class="btn-toolbar">
    						<a href="#" class="toolbar_btn dropdown-toolbar navbar-toggle"
                                data-toggle="collapse" data-target="#toolbar-nav">
    							 <i class="process-icon-dropdown"></i><span>Menu</span>
    						</a>
    						<ul id="toolbar-nav" class="nav nav-pills pull-right collapse navbar-collapse">';

            $output .= '<li>
    						<a id="page-header-desc-responsivegallery_item-config" class="toolbar_btn "
    						href="index.php?controller=AdminResponsiveGallery&amp;token=' .
            Tools::getAdminTokenLite('AdminResponsiveGallery') . '"
    						title="' . $this->l('Add or edit image') . '">
    							<i class="process-icon-upload"></i>
    							<span>' . $this->l('Add or edit image') . '</span>
    						</a>
    					</li>';

            if (!Shop::isFeatureActive() || (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP)) {
                $output .= '<li>
    							<a id="page-header-desc-responsivegallery_item-preview" class="toolbar_btn  _blank"
    							href="' . $link_gallery . '" title="' . $this->l('Preview gallery') . '">
    								<i class="process-icon-preview previewUrl"></i>
    								<span>' . $this->l('Preview gallery') . '</span>
    							</a>
    						</li>';
            }

            $output .= '<li>
    						<a id="page-header-desc-responsivegallery_item-new_item" class="toolbar_btn "
    						href="index.php?controller=AdminMeta&amp;id_meta=' . $meta['id_meta'] .
            '&amp;updatemeta&amp;token=' . Tools::getAdminTokenLite('AdminMeta') . '"
    						title="' . $this->l('Edit URL') . '">
    							<i class="process-icon-cogs"></i>
    							<span>' . $this->l('Edit URL') . '</span>
    						</a>
    					</li>';
            $output .= '</ul>
    				</div>
    			</div>';

            return $output;
        }

        /**
         * Récupération du pied de page
         *
         * @return string footer
         */
        public function getFooter()
        {
            return $this->getTranslatedAdminTemplate('footer');

        }

        /**
         * Modification du chemin vers dnd.js pour pouvoir
         * mettre à jour les positions en commencant à 1 et non 0 comme par défaut
         * pour prestashop après 1.6.0.4
         * @param $params
         */
        public function hookDisplayAdminListBefore()
        {
            $maj = $this->l('Successful update');
            if (isset($this->context->controller->module->name)
                && $this->context->controller->module->name == $this->name
                && version_compare(_PS_VERSION_, '1.6.0.4', '>=')) {
                return '<script>var update_success_msg = "' . $maj . '";</script>
    					<script type="text/javascript"
                            src="../modules/responsivegallery/views/js/admin/dnd.js"></script>';
            } elseif (isset($this->context->controller->module->name)
                && $this->context->controller->module->name == $this->name) {
                return '<script>var update_success_msg = "' . $maj . '";</script>
    					<script type="text/javascript"
                            src="../modules/responsivegallery/views/js/admin/dnd.old.js"></script>';
            }

        }

        /**
         * Renvoie le lien de la galerie. Pour des raisons de compatibilité
         * on oublie le getModuleLink...
         *
         * @return string
         */
        public function getLinkGallery($module, $controller)
        {
            $id_shop = Context::getContext()->shop->id;
            $id_lang = $this->context->language->id;

            $params = array();
            // Set available keywords
            $params['module'] = $module;
            $params['controller'] = $controller;

            $allow = (int) Configuration::get('PS_REWRITING_SETTINGS');

            if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $id_shop !== null) {
                $shop = new Shop($id_shop);
            } else {
                $shop = Context::getContext()->shop;
            }

            return __PS_BASE_URI__ . $shop->virtual_uri . Dispatcher::getInstance()->createUrl(
                'module',
                $id_lang,
                $params,
                $allow,
                '',
                $id_shop
            );
        }

        protected function getTranslatedAdminTemplate($template, $default_lang_iso_code = 'en')
        {
            $template = Tools::strtolower($template);
            $bootstrap_ext = isset($this->bootstrap) && $this->bootstrap ? '.bootstrap' : '.no_bootstrap';
            $iso_codes = array_filter(
                array(
                    Tools::strtolower(Context::getContext()->language->iso_code),
                    Tools::strtolower($default_lang_iso_code),
                )
            );

            foreach ($iso_codes as $lang_iso_code) {

                $path = '/views/templates/admin/' . $template . '_' . $lang_iso_code . $bootstrap_ext . '.tpl';

                if (file_exists(dirname(__FILE__) . $path)) {
                    $this->smarty->assign(array(
                        'module' => $this,
                        'module_path' => Context::getContext()->shop->getBaseUrL() . '/modules/' . $this->name,
                    ));

                    return $this->display(__FILE__, $path);
                }
            }
            return '';
        }
    }
}

<?php
/**
 * Responsive gallery
 *
 * @author    Studio Kiwik
 * @copyright Studio Kiwik 2013-2015
 * @license   http://licences.studio-kiwik.fr/responsivegallery
 */

class AdminResponsiveGalleryGalleryController extends ModuleAdminController
{
    public $bootstrap = true;
    private $available_ext;

    /**
     * @var string uploadPath chemin vers le répertoire d'upload
     */
    private $upload_path;

    public function __construct()
    {

        include_once dirname(__FILE__) . '/../../responsivegallery.php';
        include_once dirname(__FILE__) . '/../../classes/ResponsiveGalleryItem.php';
        include_once dirname(__FILE__) . '/../../classes/ResponsiveGalleryGallery.php';

        parent::__construct();
        $this->upload_path = 'modules/' . $this->module->name . '/views/img/uploads/gallery/';
        $this->context = Context::getContext();
        $this->table = 'responsivegallery_gallery'; //define the main table

        $this->identifier = 'id_gallery'; //the primary key
        $this->className = 'ResponsiveGalleryGallery'; //define the module entity
        $this->orderBy = 'position';
        $this->orderWay = 'DESC';
        $this->id_lang = $this->context->language->id;
        $this->lang = true;
        $this->path = _MODULE_DIR_ . 'responsivegallery';
        $this->default_form_language = $this->context->language->id;
        $this->_defaultOrderBy = 'position';
        $this->_default_pagination = 10;
        $this->_pagination = array(5, 10, 20, 50, 100, 300, 1000);
        $this->available_ext = array('png', 'jpg', 'jpeg');
        $this->position_identifier = 'id_gallery';
        $this->context = Context::getContext();
        //association des tables
        Shop::addTableAssociation($this->table, array('type' => 'shop'));
        Shop::addTableAssociation($this->table . '_lang', array('type' => 'fk_shop'));

        $this->bulk_actions = array('delete' => array(
            'text' => $this->l('Delete selected'),
            'icon' => 'icon-trash',
            'confirm' => $this->l('Delete selected items?')));

        $this->fields_list = array(
            'id_gallery' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'search' => false,
                'class' => 'fixed-width-xs',
            ),
            'image' => array(
                'title' => $this->l('Thumbnail'),
                'align' => 'center',
                'callback' => 'getThumbnail',
                'callback_object' => new ResponsiveGalleryGallery(),
                'search' => false,
            ),
            'title' => array(
                'title' => $this->l('Title'),
                'align' => 'center',
                'search' => false,
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'align' => 'center',
                'search' => false,
                'position' => 'position',
                'class' => 'fixed-width-sm',
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'align' => 'center',
                'active' => 'status',
                'type' => 'bool',
                'class' => 'fixed-width-xs',
                'search' => false,
            ),
        );

    }

    /**
     * AdminController::renderList() override
     * @see AdminController::renderList()
     */
    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    /**
     * Ajout du css de compatibilité 1.5
     */
    public function setMedia()
    {
        parent::setMedia();

        $responsive_gallery = Module::getInstanceByName('responsivegallery');
        if (!$responsive_gallery->is_1_6) {
            $this->addCSS(
                $responsive_gallery->getPathUri() . 'views/css/font-awesome-4.3.0/css/font-awesome.min.css',
                'all'
            );
            $this->addCSS($responsive_gallery->getPathUri() . 'views/css/compatibility-1.5.css', 'all');
        } elseif ($responsive_gallery->is_1_6 && version_compare(_PS_VERSION_, '1.6.0.4', '<')) {
            $this->addCSS($responsive_gallery->getPathUri() . 'views/css/compatibility-1.6.css', 'all');
        }

        if (version_compare(_PS_VERSION_, '1.6.0.4', '<')) {
            $this->addCSS($responsive_gallery->getPathUri() . 'views/css/compatibility-drag-n-drop.css', 'all');
        }

    }

    public function init()
    {
        parent::init();

        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
            unset($this->fields_list['position']);
        }

        $this->_select = 'sa.position position';

        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'responsivegallery_gallery_shop` sa
								ON (a.`id_gallery` = sa.`id_gallery` AND sa.id_shop = ' . (int) $this->context->shop->id . ') ';
        } else {
            $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'responsivegallery_gallery_shop` sa
								ON (a.`id_gallery` = sa.`id_gallery` AND sa.id_shop = a.id_shop_default) ';
        }

        // we add restriction for shop
        if (Shop::getContext() == Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->_where = ' AND sa.`id_shop` = ' . (int) Context::getContext()->shop->id;
        }

    }

    public function renderForm()
    {
        // loads current warehouse
        if (!($obj = $this->loadObject(true))) {
            return;
        }

        //Si l'objet est déjà chargé (mise à jour) on génère le thumbnail
        if (isset($obj->id)) {
            $thumbnail = '<img src="../' . $obj->image . '" style="max-width:' .
            Configuration::get('RG_MAX_IMAGE_SIDE_SIZE_BO') .
            'px;" class="imgm img-thumbnail" alt=""/>';
        }

        $options = array(
            array(
                'id_option' => 'field',
                'name' => '',
            ),
        );

        //Inputs generation
        $a_inputs = array(
            array(
                'type' => 'file',
                'label' => $this->l('Cover image'),
                'name' => 'image',
                'desc' => isset($thumbnail) ? $thumbnail : false,
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Gallery name'),
                'name' => 'title',
                'lang' => true,
            ),
            array(
                'type' => 'textarea',
                'label' => $this->l('Gallery preamble'),
                'name' => 'preamble',
                'lang' => true,
                'autoload_rte' => true,
            ),
            array(
                'type' => 'checkbox',
                'label' => $this->l('Enable legend on hover in this gallery'),
                'name' => 'legend_on_photo',
                'values' => array(
                    'query' => $options,
                    'id' => 'id_option',
                    'name' => 'name',
                ),
            ),
            array(
                'type' => 'text',
                'label' => $this->l('The horizontal margin between your images'),
                'name' => 'horizontal_margin',
            ),
            array(
                'type' => 'text',
                'label' => $this->l('The vertical margin between your images'),
                'name' => 'vertical_margin',
            ),
            array(
                'type' => 'text',
                'label' => $this->l('The inner margin for your images texts'),
                'name' => 'inner_margin',
            ),
            array(
                'type' => 'text',
                'label' => $this->l('The fadein speed when loading is done'),
                'name' => 'fadein_speed',
            ),
            array(
                'type' => 'text',
                'label' => $this->l('The max square box in which your image will be fitted'),
                'name' => 'max_image_side_size',
                'desc' => $this->l('In pixels. The image will be resized if needed.'),
            ),
            array(
                'type' => 'text',
                'label' => $this->l('The max square box in which your image will be fitted in the back office'),
                'name' => 'max_image_side_size_bo',
                'desc' => $this->l('In pixels. The image will be resized if needed.'),
            ),
            array(
                'type' => 'text',
                'label' => $this->l('The number of item per page'),
                'name' => 'nb_item_per_page',
            ),
            array(
                'type' => 'text',
                'label' => $this->l('The max width for the first screen size (e.g. smartphone).'),
                'name' => 'breakpoint_1',
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Number of item per line for the first screen size.'),
                'name' => 'nb_item_per_line_1',
            ),
            array(
                'type' => 'text',
                'label' => $this->l('The max width for the second screen size (e.g. tablet).'),
                'name' => 'breakpoint_2',
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Number of item per line for the second screen size.'),
                'name' => 'nb_item_per_line_2',
            ),
            array(
                'type' => 'text',
                'label' => $this->l('The max width for the third screen size (e.g. laptop).'),
                'name' => 'breakpoint_3',
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Number of item per line for the third screen size.'),
                'name' => 'nb_item_per_line_3',
            ),
            array(
                'type' => 'text',
                'label' => $this->l('The max width for the fourth screen size (e.g. desktop).'),
                'name' => 'breakpoint_4',
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Number of item per line for the fourth screen size.'),
                'name' => 'nb_item_per_line_4',
            ),
            array(
                'type' => 'text',
                'label' => $this->l('The max width for the fifth screen size (e.g. widescreen).'),
                'name' => 'breakpoint_5',
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Number of item per line for the fifth screen size.'),
                'name' => 'nb_item_per_line_5',
            ),
            array(
                'type' => 'hidden',
                'name' => 'image_id',
            ),
        );

        // Display this field only if multistore option is enabled AND there are several stores configured
        if (Shop::isFeatureActive()) {
            $a_inputs[] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association'),
                'name' => 'checkBoxShopAsso',
            );
        }

        //define the field to display with the form helper
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Add or edit gallery'),
                'icon' => 'icon-file',
            ),
            'input' => $a_inputs,
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $this->default_form_language = $lang->id;

        $this->fields_value['image_id'] = -1;
        //Si l'objet est déjà chargé (mise à jour) on coche si besoin les checkbox
        if (isset($obj->id)) {
            $this->fields_value['legend_on_photo_field'] = $obj->legend_on_photo;
        }

        return parent::renderForm();
    }

    /**
     * Ajout des boutons pour visualiser la galerie, configurer ou ajouter une image
     * (N'existe pas sous presta 1.5.1.0)
     */
    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_item'] = array(
                'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                'desc' => $this->l('Add new'),
                'icon' => 'process-icon-new',
            );

            // adding button for preview
            if (!Shop::isFeatureActive() || (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP)) {
                if ($url_preview = $this->module->getLinkGallery('responsivegallery', 'default')) {
                    $this->page_header_toolbar_btn['preview'] = array(
                        'short' => $this->l('Preview'),
                        'href' => $url_preview,
                        'desc' => $this->l('Preview'),
                        'target' => true,
                        'class' => 'previewUrl',
                    );
                }
            }

            // adding button for settings
            $this->page_header_toolbar_btn['config'] = array(
                'href' => 'index.php?controller=AdminModules&token=' .
                Tools::getAdminTokenLite('AdminModules') . '&configure='
                . $this->module->name . '&tab_module=front_office_features&module_name=' . $this->module->name,
                'desc' => $this->l('Configuration'),
                'icon' => 'process-icon-cogs',
            );
        }
        parent::initPageHeaderToolbar();
    }

    /**
     * Ajout des boutons pour visualiser la galerie, configurer ou ajouter une image (utile surtout pour 1.5)
     */
    public function initToolbar()
    {
        parent::initToolbar();

        // adding button for preview
        if (!Shop::isFeatureActive() || (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP)) {
            if ($url_preview = $this->module->getLinkGallery('responsivegallery', 'default')) {
                $this->toolbar_btn['preview'] = array(
                    'short' => $this->l('Preview'),
                    'href' => $url_preview,
                    'desc' => $this->l('Preview'),
                    'target' => true,
                );
            }
        }

        // adding button for settings
        $this->toolbar_btn['cogs'] = array(
            'href' => 'index.php?controller=AdminModules&token=' .
            Tools::getAdminTokenLite('AdminModules') . '&configure='
            . $this->module->name . '&tab_module=front_office_features&module_name=' . $this->module->name,
            'desc' => $this->l('Configuration'),
        );
    }

    /**
     * Called before Add
     *
     * @param object $object Object
     * @return boolean
     */
    protected function beforeAdd($object)
    {
        $max_position = (int) Db::getInstance()->getValue(
            'SELECT MAX(position)
            FROM ' . _DB_PREFIX_ . 'responsivegallery_gallery_shop'
        );
        $max_id = (int) Db::getInstance()->getValue(
            'SELECT MAX(id_gallery)
            FROM ' . _DB_PREFIX_ . 'responsivegallery_gallery'
        );
        // Champs par défaut
        $object->id = $max_id + 1;
        $object->position = $max_position + 1;
        $object->id_shop_default = Context::getContext()->shop->id;
        $object->active = 1;
        $object->legend_on_photo = (boolean) Tools::getValue('legend_on_photo_field');
        //On recupere les infos de l'image
        $object->image = $this->processingAddUpdateImage($object->id);
    }

    /**
     * Check si un fichier doit etre uploadé et si il correspond aux fichiers autorisés
     *
     * @return int  0 : Fichier non présent dans la var globale
     *              1 : Fichier présent avec extension autorisée
     *              2 : Fichier présent sans extension autorisée
     */
    private function checkCorrectFile()
    {
        //Check if there is file to upload
        if (isset($_FILES['image']['name']) && !empty($_FILES['image']['name'])) {
            $ext = Tools::strtolower(Tools::substr(strrchr($_FILES['image']['name'], '.'), 1));
            //Si l'extension fait partie des extensions autorisées alors return true
            if (is_array($this->available_ext) && $ext != '' && in_array($ext, $this->available_ext)) {
                return 1;
            } else {
                $list_ext = implode(',', $this->available_ext);
                $this->errors[] = Tools::displayError(
                    sprintf(
                        $this->l('File extension is not valid. File must be in the following extensions :%s'),
                        $list_ext
                    )
                );
                return 2;
            }
        }
        return 0;
    }

    /**
     * Call the right method for creating or updating object
     *
     * @return mixed
     */
    public function processSave()
    {
        if ($this->id_object) {
            $this->object = $this->loadObject();
            //MAJ title behavior
            $this->object->legend_on_photo = (boolean) Tools::getValue('legend_on_photo_field');
            $this->object->update();

            //Si modification de l'image, on charge les infos de l'image modifiée pour éviter une requête en +
            if ($this->checkCorrectFile() === 1) {
                //Suppression de l'image existante
                $this->object->deleteImage();
                //Attribution du nouveau chemin vers l'image uploadée
                $this->object->image = $this->processingAddUpdateImage($this->object->id);
                //MAJ
                $this->object->update();
            }

            return $this->processUpdate();

        } elseif ($this->checkCorrectFile() === 1) {
            return $this->processAdd();
        } elseif ($this->checkCorrectFile() === 0) {
            $this->errors[] = Tools::displayError($this->l('There is no file to upload.'));
        }

        $this->errors = array_unique($this->errors);
        if (!empty($this->errors)) {
            // if we have errors, we stay on the form instead of going back to the list
            $this->display = 'edit';
            return false;
        }

    }

    /**
     * Traitement de la requête d'ajout ou de modification d'une image,
     * évite les contraintes liées à l'upload directement via la méthode
     * de la classe ModuleAdminController et permet d'effectuer le redimensionnement souhaité.
     *
     * @return String : chemin vers l'image uploadé si elle l'est, false sinon
     */
    private function processingAddUpdateImage($id)
    {
        $ext = Tools::substr(strrchr($_FILES['image']['name'], '.'), 1);
        //Chemin vers le fichier à créer
        $original_filename = _PS_ROOT_DIR_ . '/' . $this->upload_path . $id . '_original.' . $ext;
        //Processus d'upload de l'image
        $uploaded = $this->upload('image', $original_filename, false, $this->available_ext);
        //Redimensionnement de l'image
        $this->resize($original_filename, _PS_ROOT_DIR_ . '/' . $this->upload_path . $id . '.' . $ext);

        //On renvoie le chemin de l'image chargée sur le serveur
        if ($uploaded) {
            return $this->upload_path . $id . '.' . $ext;
        } else {
            $this->errors[] = Tools::displayError(
                sprintf(
                    $this->l('An error occured. Please verify that the directory %s is writable.'),
                    $this->upload_path
                )
            );
            return false;
        }
    }

    /**
     * Traitement de l'upload d'une image.
     * Test si le fichier a correctement été uploadé,
     *      si la taille est correcte
     *      si c'est une image valide
     *      si l'extension est valide
     *      si le déplacement s'est bien effectué
     *
     * @param $index nom de l'input file
     * @param $destination répertoire de destination de l'image
     * @param bool $maxsize taille maximale
     * @param bool $extensions tableau d'extension autorisées, sinon false
     * @return bool true si l'upload s'est bien passé, false sinon.
     */
    private function upload($index, $destination, $maxsize = false, $extensions = false)
    {
        //Test1: fichier correctement uploadé
        if (!isset($_FILES[$index]) || $_FILES[$index]['error'] > 0) {
            return false;
        }

        //Test2: taille limite
        if ($maxsize !== false && $_FILES[$index]['size'] > $maxsize) {
            return false;
        }

        //Test3: image valide
        if (!ImageManager::isRealImage($_FILES[$index]['tmp_name'], $_FILES[$index]['type'])) {
            return false;
        }

        //Test4: extension valide
        $ext = Tools::strtolower(Tools::substr(strrchr($_FILES[$index]['name'], '.'), 1));
        if ($extensions !== false && !in_array($ext, $extensions)) {
            return false;
        }

        //Déplacement et test si le déplacement s'est bien passé
        if (!move_uploaded_file($_FILES[$index]['tmp_name'], $destination)) {
            return false;
        }

        return true;
    }

    /**
     * Redimensionnement de l'image.
     *
     * L'image redimensionnée est de meilleure qualité que dans la version précédente du
     * module grâce à l'utilisation de la méthode ImageManager::resize
     * Pour éviter l'ajout de bandes blanches sur les bords par défaut de ImageManager::resize
     * il faut calculer la nouvelle taille de l'image et lui passer en paramètres
     *
     * @param $source Source
     * @param $destination Destination
     */
    private function resize($source, $destination)
    {
        $max_image_side_size = Configuration::get('RG_MAX_IMAGE_SIDE_SIZE');
        list($width, $height) = getimagesize($source);
        $ext = Tools::strtolower(Tools::substr(strrchr($source, '.'), 1));

        //Calcul de la nouvelle taille de l'image après redimensionnement
        if ($width > $max_image_side_size || $height > $max_image_side_size) {
            if ($width > $height) {
                $percent = $max_image_side_size / $width;
                $newwidth = $width * $percent;
                $newheight = $height * $percent;
            } else {
                $percent = $max_image_side_size / $height;
                $newwidth = $width * $percent;
                $newheight = $height * $percent;
            }
        } else {
            $newwidth = $width;
            $newheight = $height;
        }
        //Redimensionnement de l'image
        ImageManager::resize($source, $destination, (int) $newwidth, (int) $newheight, $ext);
    }

    public function processPosition()
    {
        $object = new ResponsiveGalleryGallery((int) Tools::getValue('id_gallery'));
        if (!Validate::isLoadedObject($object)) {
            $this->errors[] = Tools::displayError('An error occurred while updating the status for an object.') .
            ' <b>' . $this->table . '</b> ' . Tools::displayError('(cannot load object)');
        } elseif (!$object->updatePosition((int) Tools::getValue('way'), (int) Tools::getValue('position'))) {
            $this->errors[] = Tools::displayError('Failed to update the position.');
        } else {
            $id_identifier_str = ($id_identifier = (int) Tools::getValue($this->identifier)) ? '&' .
            $this->identifier . '=' . $id_identifier : '';

            $redirect = self::$currentIndex . '&' . $this->table . 'Orderby=position&' . $this->table .
            'Orderway=asc&conf=5' . $id_identifier_str . '&token=' . $this->token;

            $this->redirect_after = $redirect;
        }
        return $object;
    }

    public function getList(
        $id_lang,
        $order_by = null,
        $order_way = null,
        $start = 0,
        $limit = null,
        $id_lang_shop = false
    ) {
        parent::getList(
            $id_lang,
            $order_by,
            $order_way,
            $start,
            $limit,
            Context::getContext()->shop->id ? Context::getContext()->shop->id : $id_lang_shop
        );
    }

    /**
     * Fonction permettant de traiter la requête ajax pour la modification
     * de la position de l'élément.
     */
    public function ajaxProcessUpdatePositions()
    {
        $id_item_to_move = (int) Tools::getValue('id');
        $way = (int) Tools::getValue('way');
        $positions = Tools::getValue('gallery');
        if (is_array($positions)) {
            foreach ($positions as $key => $value) {
                $pos = explode('_', $value);
                if (isset($pos[2]) && $pos[2] == $id_item_to_move) {
                    $position = $key + 1;
                    break;
                }
            }
        }

        // Si on est dans une version qui commence les positions à 0, on décrémente la position
        if (version_compare(_PS_VERSION_, '1.6.0.4', '<')) {
            $position = $position - 1;
        }

        $gallery = new ResponsiveGalleryGallery($id_item_to_move);
        if (Validate::isLoadedObject($gallery)) {
            if (isset($position) && $gallery->updatePosition($way, $position)) {
                die(true);
            } else {
                die('{"hasError" : true, errors : "Cannot update gallery position"}');
            }
        } else {
            die('{"hasError" : true, "errors" : "This gallery cannot be loaded"}');
        }

    }

    public function getTemplatePath()
    {
        return _PS_BO_ALL_THEMES_DIR_ . $this->bo_theme . '/template/';
    }
}

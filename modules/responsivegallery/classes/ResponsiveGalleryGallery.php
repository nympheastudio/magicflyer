<?php
/**
 * Responsive gallery
 *
 * @author    Studio Kiwik
 * @copyright Studio Kiwik 2013-2015
 * @license   http://licences.studio-kiwik.fr/responsivegallery
 */

class ResponsiveGalleryGallery extends ObjectModel
{

    public $id_shop_default;
    public $active;
    public $legend_on_photo;
    public $horizontal_margin = 5;
    public $fadein_speed = 1000;
    public $vertical_margin = 5;
    public $inner_margin = 15;
    public $max_image_side_size = 1200;
    public $max_image_side_size_bo = 350;
    public $nb_item_per_page = 12;
    public $breakpoint_1 = 320;
    public $nb_item_per_line_1 = 1;
    public $breakpoint_2 = 480;
    public $nb_item_per_line_2 = 2;
    public $breakpoint_3 = 768;
    public $nb_item_per_line_3 = 3;
    public $breakpoint_4 = 992;
    public $nb_item_per_line_4 = 4;
    public $breakpoint_5 = 9999;
    public $nb_item_per_line_5 = 5;
    public $title;
    public $preamble;
    public $position;
    public $image;

    public static $definition = array(
        'table' => 'responsivegallery_gallery',
        'primary' => 'id_gallery',
        'multilang' => true,
        'fields' => array(
            'id_shop_default' => array('type' => self::TYPE_INT, 'required' => false),
            'active' => array('type' => self::TYPE_BOOL),
            'image' => array('type' => self::TYPE_STRING, 'required' => false),
            'legend_on_photo' => array('type' => self::TYPE_BOOL),
            'horizontal_margin' => array('type' => self::TYPE_INT),
            'fadein_speed' => array('type' => self::TYPE_INT),
            'vertical_margin' => array('type' => self::TYPE_INT),
            'inner_margin' => array('type' => self::TYPE_INT),
            'max_image_side_size' => array('type' => self::TYPE_INT),
            'max_image_side_size_bo' => array('type' => self::TYPE_INT),
            'nb_item_per_page' => array('type' => self::TYPE_INT),
            'breakpoint_1' => array('type' => self::TYPE_INT),
            'nb_item_per_line_1' => array('type' => self::TYPE_INT),
            'breakpoint_2' => array('type' => self::TYPE_INT),
            'nb_item_per_line_2' => array('type' => self::TYPE_INT),
            'breakpoint_3' => array('type' => self::TYPE_INT),
            'nb_item_per_line_3' => array('type' => self::TYPE_INT),
            'breakpoint_4' => array('type' => self::TYPE_INT),
            'nb_item_per_line_4' => array('type' => self::TYPE_INT),
            'breakpoint_5' => array('type' => self::TYPE_INT),
            'nb_item_per_line_5' => array('type' => self::TYPE_INT),
            'title' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
            'preamble' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
        ),
    );

    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
    }

    public function add($autodate = true, $null_values = false)
    {
        $ret = parent::add($autodate, $null_values);

        foreach (Shop::getShops(true) as $shop) {
            $position = self::getNewLastPosition($shop['id_shop']);
            // Avant 1.6.0.4, la position était additionnée à 1
            // On met donc la position à partir de 0 pour ces versions
            if (!$position) {
                if (version_compare(_PS_VERSION_, '1.6.0.4', '<')) {
                    $position = 0;
                } else {
                    $position = 1;
                }
            }

            $this->addPosition($position, $shop['id_shop']);
        }

        return $ret;
    }

    /**
     * @see ObjectModel::delete()
     */
    public function delete()
    {
        $tmp_object = new ResponsiveGalleryGallery($this->id);
        //Mise à jour des positions
        $this->updatePositionAfterDelete();

        //Vérification si l'image est utilisée par une autre boutique avant de supprimer l'image du serveur
        if (parent::delete()) {
            if ($this->hasMultishopEntries()) {
                return true;
            } else {
                return $tmp_object->deleteImage();
            }
        }

    }

    public static function getNewLastPosition($id_shop)
    {
        $last_position = Db::getInstance()->getValue(
            'SELECT MAX(cs.`position`)
            FROM `' . _DB_PREFIX_ . 'responsivegallery_gallery` c
            LEFT JOIN `' . _DB_PREFIX_ . 'responsivegallery_gallery_shop` cs
                ON (c.`id_gallery` = cs.`id_gallery` AND cs.`id_shop` = ' . (int) $id_shop . ')'
        );

        // Si c'est 'peut etre' le premier enregistrement
        if ($last_position == 0) {
            $counter = Db::getInstance()->getValue(
                'SELECT COUNT(*)
                FROM `' . _DB_PREFIX_ . 'responsivegallery_gallery` c
                LEFT JOIN `' . _DB_PREFIX_ . 'responsivegallery_gallery_shop` cs
                    ON (c.`id_gallery` = cs.`id_gallery` AND cs.`id_shop` = ' . (int) $id_shop . ')
                WHERE cs.`position` = 0'
            );
            // Si on trouve uniquement l'enregistrement en cours, on retourne 0
            if ($counter == 1) {
                return 0;
            } else {
                return 1 + (int) $last_position;
            }

        } else {
            return 1 + (int) $last_position;
        }

    }

    /**
     * Permet de metter en forme la miniature
     *
     * @param $row Adresse de l'image
     * @return string
     */
    public function getThumbnail($row)
    {
        return '<img class="imgm img-thumbnail" style="height:75px" src="../' . $row . '" alt="" title="" />';
    }

    public static function getGalleries($active = true)
    {
        return self::getGallery(null, $active);
    }

    public static function getGallery($id_gallery, $active = true)
    {
        $id_lang = Context::getContext()->cookie->id_lang;
        $sql = 'SELECT *
            FROM ' . _DB_PREFIX_ . 'responsivegallery_gallery g
            LEFT JOIN ' . _DB_PREFIX_ . 'responsivegallery_gallery_lang gl
                ON g.id_gallery=gl.id_gallery AND gl.id_lang=' . (int) $id_lang;

        if (Shop::getContext() == Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'responsivegallery_gallery_shop` gs
                                ON (g.`id_gallery` = gs.`id_gallery`
                                    AND gs.id_shop = ' . (int) Context::getContext()->shop->id . ') ';
        } else {
            $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'responsivegallery_gallery_shop` gs
                                ON (g.`id_gallery` = gs.`id_gallery`)';
        }

        $sql .= ' WHERE 1 ';

        if ($active) {
            $sql .= ' AND g.active=1 ';
        }

        if (!empty($id_gallery)) {
            $sql .= ' AND  g.id_gallery=' . (int) $id_gallery;
        }

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Récupère les éléments à afficher en fonction de la page demandée.
     *
     * Par défaut la fonction renvoie les éléments activés,
     * sinon on peut récupérer uniquement les éléments non activés.
     *
     * @param $page page demandée
     * @param null $size nombre d'éléments souhaité
     * @param bool $b_active_only indique si on recherche uniquement les éléments activés ou non
     * @return array tableau d'éléments pour la page demandée
     */
    public function getGalleryPage($page, $size = null, $b_active_only = true)
    {
        if ($size === null) {
            $size = $this->nb_item_per_page;
        }

        return $this->getGalleryItemRange(($page - 1) * $size, $size, $b_active_only);
    }

    /**
     * Récupère les éléments de la galerie en fonction de la position de départ,
     * du nombre d'éléments que l'on souhaite et si l'on souhaite uniquement les
     * éléments actifs.
     *
     *
     * Par défaut la fonction renvoie les éléments activés,
     * sinon on peut récupérer uniquement les éléments non activés.
     *
     * @param $start début du range souhaité
     * @param null $size taille du range
     * @param bool $b_active_only indique si on recherche uniquement les éléments activés ou non
     * @return array tableau des éléments
     */
    public function getGalleryItemRange($start, $size = null, $b_active_only = true)
    {
        if ($size === null) {
            $size = $this->nb_item_per_page;
        }

        //Récupération de la boutique
        $id_lang_shop = Context::getContext()->shop->id;
        //Récupération de la langue
        $id_lang = Context::getContext()->cookie->id_lang;

        $req = 'SELECT b.*, a.*, c.position
                FROM ' . _DB_PREFIX_ . 'responsivegallery_item a
                LEFT JOIN ' . _DB_PREFIX_ . 'responsivegallery_item_lang b
                ON (b.id_item = a.id_item AND b.id_lang = ' . (int) $id_lang;

        //Test si le multishop est activé pour rechercher uniquement les éléments
        //correspondant à la boutique souhaitée.
        if ($id_lang_shop) {
            if (!Shop::isFeatureActive()) {
                $req .= ' AND b.`id_shop` = ' . (int) Configuration::get('PS_SHOP_DEFAULT');
            } elseif (Shop::getContext() == Shop::CONTEXT_SHOP) {
                $req .= ' AND b.`id_shop` = ' . (int) $id_lang_shop;
            } else {
                $req .= ' AND b.`id_shop` = a.id_shop_default';
            }

        }

        $req .= ') JOIN ' . _DB_PREFIX_ . 'responsivegallery_item_shop c
                ON (a.id_item = c.id_item AND c.id_shop = ' . (int) $id_lang_shop . ')
                WHERE id_gallery=' . (int) $this->id . ($b_active_only ? ' AND active=1 ' : '');

        if (Shop::isFeatureActive()) {
            $req .= ' AND a.id_item IN (
                        SELECT sa.id_item
                        FROM `' . _DB_PREFIX_ . 'responsivegallery_item_shop` sa
                        WHERE sa.id_shop IN (' . implode(', ', Shop::getContextListShopID()) . ')
                    ) ';
        }

        $req .= 'ORDER BY position LIMIT ' . $start . ',' . $size;

        return Db::getInstance()->executeS($req);
    }

    /**
     * Redéfinition de la fonction pour supprimer l'image originale
     * et aussi l'image redimensionnée
     *
     * Si une des deux n'existe pas, la fonction renvoie false.
     * @see ObjectModel::deleteImage()
     *
     */
    public function deleteImage($force_delete = false)
    {
        if (!$force_delete) {
            $path_image_reduite = _PS_ROOT_DIR_ . '/' . $this->image;
            $end_file = strrchr($path_image_reduite, '.');
            $path_image = str_replace($end_file, '_original' . $end_file, $path_image_reduite);

            if (file_exists($path_image_reduite)) {
                $res_image_originale = unlink($path_image_reduite);
            }

            if (file_exists($path_image)) {
                $res_image_reduite = unlink($path_image);
            }

        }

        return isset($res_image_originale) && isset($res_image_reduite);
    }

    /**
     * Met à jour la position après suppression de l'objet
     *
     * @param boolean $way Up (1)  or Down (0)
     * @param integer $position
     * @return boolean Update result
     */
    public function updatePositionAfterDelete()
    {
        //Si plusieurs boutiques sont concernées
        if (Shop::getContext() != Shop::CONTEXT_SHOP) {
            foreach (Shop::getContextListShopID() as $id_shop) {
                $req = 'SELECT b.position, a.id_gallery
                FROM ' . _DB_PREFIX_ . 'responsivegallery_gallery a
                JOIN ' . _DB_PREFIX_ . 'responsivegallery_gallery_shop b
                ON (b.id_gallery = a.id_gallery AND b.id_shop = ' . (int) $id_shop . ')';

                if (!$res = Db::getInstance()->executeS($req)) {
                    continue;
                }

                $moved_item = false;
                foreach ($res as $item) {
                    if ((int) $item['id_gallery'] == (int) $this->id) {
                        $moved_item = $item;
                    }
                }

                if ($moved_item === false) {
                    continue;
                }

                Db::getInstance()->execute(
                    'UPDATE `' . _DB_PREFIX_ . 'responsivegallery_gallery` a
                    JOIN ' . _DB_PREFIX_ . 'responsivegallery_gallery_shop b
                    ON (b.id_gallery = a.id_gallery AND b.id_shop = ' . (int) $id_shop . ')
                    SET b.`position`= b.`position` -1
                    WHERE b.`position` > ' . (int) $moved_item['position']
                );
            }
        } else {
            // Sinon on modifie que ceux liés à la boutique en cours
            $id = Context::getContext()->shop->id;
            $id_shop = $id ? $id : Configuration::get('PS_SHOP_DEFAULT');

            $req = 'SELECT b.position, a.id_gallery
                FROM ' . _DB_PREFIX_ . 'responsivegallery_gallery a
                JOIN ' . _DB_PREFIX_ . 'responsivegallery_gallery_shop b
                ON (b.id_gallery = a.id_gallery AND b.id_shop = ' . (int) $id_shop . ')';

            if (!$res = Db::getInstance()->executeS($req)) {
                return false;
            }

            $moved_item = false;
            foreach ($res as $item) {
                if ((int) $item['id_gallery'] == (int) $this->id) {
                    $moved_item = $item;
                }
            }

            if ($moved_item === false) {
                return false;
            }

            $result = Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'responsivegallery_gallery` a
                JOIN ' . _DB_PREFIX_ . 'responsivegallery_gallery_shop b
                ON (b.id_gallery = a.id_gallery AND b.id_shop = ' . (int) $id_shop . ')
                SET b.`position`= b.`position` -1
                WHERE b.`position` > ' . (int) $moved_item['position']
            );
            return $result;
        }
        return true;
    }

    /**
     * Ajoute la position à l'élément inséré
     *
     * @param $position
     * @param null $id_shop
     * @return bool
     */
    public function addPosition($position, $id_shop = null)
    {
        $return = true;
        if (is_null($id_shop)) {
            if (Shop::getContext() != Shop::CONTEXT_SHOP) {
                foreach (Shop::getContextListShopID() as $id_shop) {
                    $return &= Db::getInstance()->execute('
                        INSERT INTO `' . _DB_PREFIX_ . 'responsivegallery_gallery_shop`
                         (`id_gallery`, `id_shop`, `position`)
                         VALUES
                        (' . (int) $this->id . ', ' . (int) $id_shop . ', ' . (int) $position . ')
                        ON DUPLICATE KEY UPDATE `position` = ' . (int) $position);
                }
            } else {
                $id = Context::getContext()->shop->id;
                $id_shop = $id ? $id : Configuration::get('PS_SHOP_DEFAULT');
                $return &= Db::getInstance()->execute('
                    INSERT INTO `' . _DB_PREFIX_ . 'responsivegallery_gallery_shop`
                    (`id_gallery`, `id_shop`, `position`)
                    VALUES
                    (' . (int) $this->id . ', ' . (int) $id_shop . ', ' . (int) $position . ')
                    ON DUPLICATE KEY UPDATE `position` = ' . (int) $position);
            }
        } else {
            $return &= Db::getInstance()->execute('
            INSERT INTO `' . _DB_PREFIX_ . 'responsivegallery_gallery_shop`
            (`id_gallery`, `id_shop`, `position`)
            VALUES
            (' . (int) $this->id . ', ' . (int) $id_shop . ', ' . (int) $position . ')
            ON DUPLICATE KEY UPDATE `position` = ' . (int) $position);
        }

        return $return;
    }

    /**
     * Move an attribute inside its group
     * @param boolean $way Up (1)  or Down (0)
     * @param integer $position
     * @return boolean Update result
     */
    public function updatePosition($way, $position)
    {
        //Récupération de l'id de la boutique
        $id_lang_shop = Context::getContext()->shop->id;

        $req = 'SELECT b.position, a.id_gallery
                FROM ' . _DB_PREFIX_ . 'responsivegallery_gallery a
                JOIN ' . _DB_PREFIX_ . 'responsivegallery_gallery_shop b
                ON (b.id_gallery = a.id_gallery AND b.id_shop = ' . (int) $id_lang_shop . ')';

        if (!$res = Db::getInstance()->executeS($req)) {
            return false;
        }

        $moved_item = false;
        foreach ($res as $item) {
            if ((int) $item['id_gallery'] == (int) $this->id) {
                $moved_item = $item;
            }
        }

        if ($moved_item === false) {
            return false;
        }

        // < and > statements rather than BETWEEN operator
        // since BETWEEN is treated differently according to databases
        $result = (Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'responsivegallery_gallery` a
            JOIN ' . _DB_PREFIX_ . 'responsivegallery_gallery_shop b
            ON (b.id_gallery = a.id_gallery AND b.id_shop = ' . (int) $id_lang_shop . ')
            SET b.`position`= b.`position` ' . ($way ? '- 1' : '+ 1') . '
            WHERE b.`position`
            ' . ($way
                ? '> ' . (int) $moved_item['position'] . ' AND b.`position` <= ' . (int) $position
                : '< ' . (int) $moved_item['position'] . ' AND b.`position` >= ' . (int) $position)
        )
            && Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'responsivegallery_gallery` a
                JOIN ' . _DB_PREFIX_ . 'responsivegallery_gallery_shop b
                ON (b.id_gallery = a.id_gallery AND b.id_shop = ' . (int) $id_lang_shop . ')
                SET b.`position`= ' . (int) $position . '
                WHERE b.`id_gallery` = ' . (int) $moved_item['id_gallery']
            )
        );
        return $result;
    }
}

<?php
/**
 * Responsive gallery
 *
 * @author    Studio Kiwik
 * @copyright Studio Kiwik 2013-2015
 * @license   http://licences.studio-kiwik.fr/responsivegallery
 */

class ResponsiveGalleryItem extends ObjectModel
{

    public $id;

    /** @var integer id_shop_default */
    public $id_shop_default;

    /** @var integer id_gallery */
    public $id_gallery;

    /** @var string legend */
    public $legend;

    /** @var string link */
    public $link;

    /** @var string image */
    public $image;

    /** @var boolean active */
    public $active;

    /** @var boolean legend_on_hover */
    public $legend_on_hover;

    /** @var boolean legend_under_photo */
    public $legend_under_photo;

    /** @var string Object creation date */
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'responsivegallery_item',
        'primary' => 'id_item',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => array(
            'id_shop_default' => array('type' => self::TYPE_INT, 'required' => false),
            'id_gallery' => array('type' => self::TYPE_INT, 'required' => true),
            'image' => array('type' => self::TYPE_STRING, 'required' => false),
            'legend' => array(
                'type' => self::TYPE_HTML, 'lang' => true, 'required' => false, 'validate' => 'isCleanHtml',
            ),
            'link' => array('type' => self::TYPE_STRING, 'required' => false),
            'active' => array('type' => self::TYPE_BOOL),
            'legend_under_photo' => array('type' => self::TYPE_BOOL),
            'legend_on_hover' => array('type' => self::TYPE_BOOL),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    /**
     * @see ObjectModel::add()
     */
    public function add($autodate = true, $null_values = false)
    {
        $ret = parent::add($autodate, $null_values);

        foreach (Shop::getShops(true) as $shop) {
            $position = self::getNewLastPosition($this->id_gallery, $shop['id_shop']);
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
        $tmp_object = new ResponsiveGalleryItem($this->id);
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

    /**
     * Supprime les boutiques associées pour 1.5
     *
     */
    public function deleteBoutiquesAssiciees($tmp_object)
    {
        $return = true;
        //Si plusieurs boutiques sont concernées
        if (Shop::getContext() != Shop::CONTEXT_SHOP) {
            foreach (Shop::getContextListShopID() as $id_shop) {
                $return &= Db::getInstance()->execute('
				DELETE FROM `' . _DB_PREFIX_ . 'responsivegallery_item_shop`
				WHERE `id_item` = ' . (int) $tmp_object->id_item . '
				AND `id_shop` = ' . (int) $id_shop);
            }
        } else {
            // Sinon on supprime que ceux liés à la boutique en cours
            $id = Context::getContext()->shop->id;
            $id_shop = $id ? $id : Configuration::get('PS_SHOP_DEFAULT');
            $return &= Db::getInstance()->execute('
				DELETE FROM `' . _DB_PREFIX_ . 'responsivegallery_item_shop`
				WHERE `id_item` = ' . (int) $tmp_object->id_item . '
				AND `id_shop` = ' . (int) $id_shop);
        }

        return $return;
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
     * Move an attribute inside its group
     * @param boolean $way Up (1)  or Down (0)
     * @param integer $position
     * @return boolean Update result
     */
    public function updatePosition($way, $position)
    {
        //Récupération de l'id de la boutique
        $id_lang_shop = Context::getContext()->shop->id;

        $req = 'SELECT b.position, a.id_item
				FROM ' . _DB_PREFIX_ . 'responsivegallery_item a
				JOIN ' . _DB_PREFIX_ . 'responsivegallery_item_shop b
				ON (b.id_item = a.id_item AND b.id_shop = ' . (int) $id_lang_shop . ')';

        if (!$res = Db::getInstance()->executeS($req)) {
            return false;
        }

        $moved_item = false;
        foreach ($res as $item) {
            if ((int) $item['id_item'] == (int) $this->id) {
                $moved_item = $item;
            }
        }

        if ($moved_item === false) {
            return false;
        }

        // < and > statements rather than BETWEEN operator
        // since BETWEEN is treated differently according to databases
        $result = (Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'responsivegallery_item` a
			JOIN ' . _DB_PREFIX_ . 'responsivegallery_item_shop b
			ON (b.id_item = a.id_item AND b.id_shop = ' . (int) $id_lang_shop . ')
			SET b.`position`= b.`position` ' . ($way ? '- 1' : '+ 1') . '
			WHERE a.id_gallery=' . (int) $this->id_gallery . ' AND b.`position`
			' . ($way
                ? '> ' . (int) $moved_item['position'] . ' AND b.`position` <= ' . (int) $position
                : '< ' . (int) $moved_item['position'] . ' AND b.`position` >= ' . (int) $position)
        )
            && Db::getInstance()->execute('
			UPDATE `' . _DB_PREFIX_ . 'responsivegallery_item` a
			JOIN ' . _DB_PREFIX_ . 'responsivegallery_item_shop b
			ON (b.id_item = a.id_item AND b.id_shop = ' . (int) $id_lang_shop . ')
			SET b.`position`= ' . (int) $position . '
			WHERE a.id_gallery=' . (int) $this->id_gallery . ' AND b.`id_item` = ' . (int) $moved_item['id_item'] . '
			'));
        return $result;
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
                $req = 'SELECT b.position, a.id_item
				FROM ' . _DB_PREFIX_ . 'responsivegallery_item a
				JOIN ' . _DB_PREFIX_ . 'responsivegallery_item_shop b
				ON (b.id_item = a.id_item AND b.id_shop = ' . (int) $id_shop . ')';

                if (!$res = Db::getInstance()->executeS($req)) {
                    continue;
                }

                $moved_item = false;
                foreach ($res as $item) {
                    if ((int) $item['id_item'] == (int) $this->id) {
                        $moved_item = $item;
                    }
                }

                if ($moved_item === false) {
                    continue;
                }

                Db::getInstance()->execute(
                    'UPDATE `' . _DB_PREFIX_ . 'responsivegallery_item` a
					JOIN ' . _DB_PREFIX_ . 'responsivegallery_item_shop b
					ON (b.id_item = a.id_item AND b.id_shop = ' . (int) $id_shop . ')
					SET b.`position`= b.`position` -1
					WHERE a.id_gallery=' . (int) $this->id_gallery . ' AND b.`position` > ' . (int) $moved_item['position']
                );
            }
        } else {
            // Sinon on modifie que ceux liés à la boutique en cours
            $id = Context::getContext()->shop->id;
            $id_shop = $id ? $id : Configuration::get('PS_SHOP_DEFAULT');

            $req = 'SELECT b.position, a.id_item
				FROM ' . _DB_PREFIX_ . 'responsivegallery_item a
				JOIN ' . _DB_PREFIX_ . 'responsivegallery_item_shop b
				ON (b.id_item = a.id_item AND b.id_shop = ' . (int) $id_shop . ')';

            if (!$res = Db::getInstance()->executeS($req)) {
                return false;
            }

            $moved_item = false;
            foreach ($res as $item) {
                if ((int) $item['id_item'] == (int) $this->id) {
                    $moved_item = $item;
                }
            }

            if ($moved_item === false) {
                return false;
            }

            $result = Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'responsivegallery_item` a
				JOIN ' . _DB_PREFIX_ . 'responsivegallery_item_shop b
				ON (b.id_item = a.id_item AND b.id_shop = ' . (int) $id_shop . ')
				SET b.`position`= b.`position` -1
				WHERE a.id_gallery=' . (int) $this->id_gallery . ' AND b.`position` > ' . (int) $moved_item['position']
            );
            return $result;
        }
        return true;
    }

    /** this function return the number of item + 1.
     *
     * @param int $id_shop
     * @return int
     */
    public static function getNewLastPosition($id_gallery, $id_shop)
    {
        $last_position = Db::getInstance()->getValue(
            'SELECT MAX(cs.`position`)
			FROM `' . _DB_PREFIX_ . 'responsivegallery_item` c
			LEFT JOIN `' . _DB_PREFIX_ . 'responsivegallery_item_shop` cs
                ON (c.`id_item` = cs.`id_item` AND cs.`id_shop` = ' . (int) $id_shop . ')
			WHERE c.id_gallery=' . (int) $id_gallery
        );

        // Si c'est 'peut etre' le premier enregistrement
        if ($last_position == 0) {
            $counter = Db::getInstance()->getValue(
                'SELECT COUNT(*)
				FROM `' . _DB_PREFIX_ . 'responsivegallery_item` c
				LEFT JOIN `' . _DB_PREFIX_ . 'responsivegallery_item_shop` cs
                    ON (c.`id_item` = cs.`id_item` AND cs.`id_shop` = ' . (int) $id_shop . ')
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
						INSERT INTO `' . _DB_PREFIX_ . 'responsivegallery_item_shop` (`id_item`, `id_shop`, `position`) VALUES
						(' . (int) $this->id . ', ' . (int) $id_shop . ', ' . (int) $position . ')
						ON DUPLICATE KEY UPDATE `position` = ' . (int) $position);
                }
            } else {
                $id = Context::getContext()->shop->id;
                $id_shop = $id ? $id : Configuration::get('PS_SHOP_DEFAULT');
                $return &= Db::getInstance()->execute('
					INSERT INTO `' . _DB_PREFIX_ . 'responsivegallery_item_shop` (`id_item`, `id_shop`, `position`) VALUES
					(' . (int) $this->id . ', ' . (int) $id_shop . ', ' . (int) $position . ')
					ON DUPLICATE KEY UPDATE `position` = ' . (int) $position);
            }
        } else {
            $return &= Db::getInstance()->execute('
			INSERT INTO `' . _DB_PREFIX_ . 'responsivegallery_item_shop` (`id_item`, `id_shop`, `position`) VALUES
			(' . (int) $this->id . ', ' . (int) $id_shop . ', ' . (int) $position . ')
			ON DUPLICATE KEY UPDATE `position` = ' . (int) $position);
        }

        return $return;
    }

    /**
     * Permet de récupérer le lien de l'image
     *
     * @param $field_value Valeur du champ en cours (Pas utile pour cette fonction...)
     * @param $row Ligne en cours
     * @return mixed
     */
    public function getUrl($fieds_value, $row)
    {
        if ($fieds_value) {
            return $row['image'];
        } else {
            return $row['image'];
        }

    }

    public function getLegend($fieds_value)
    {
        return strip_tags($fieds_value);
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

    public function getGalleryName($row)
    {
        $galleries = ResponsiveGalleryGallery::getGallery((int) $row);
        if (count($galleries) == 1) {
            $gallery = $galleries[0];
            if (isset($gallery) && !empty($gallery['title'])) {
                return $gallery['title'];
            }

        }
        return $row;
    }
}

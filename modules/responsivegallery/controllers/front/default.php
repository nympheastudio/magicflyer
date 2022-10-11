<?php
/**
 * Responsive gallery
 *
 * @author    Studio Kiwik
 * @copyright Studio Kiwik 2013-2015
 * @license   http://licences.studio-kiwik.fr/responsivegallery
 */

class ResponsiveGalleryDefaultModuleFrontController extends ModuleFrontController
{

    public function setMedia()
    {
        parent::setMedia();

        $responsive_gallery = Module::getInstanceByName('responsivegallery');
        $this->addjqueryPlugin('fancybox');

        if (!$responsive_gallery->is_1_6) {
            $this->addCSS(
                $responsive_gallery->getPathUri() . 'views/css/font-awesome-4.3.0/css/font-awesome.min.css',
                'all'
            );
        }

        $this->addJS($responsive_gallery->getPathUri() . 'views/js/responsivegallery.js');
        $this->addCSS($responsive_gallery->getPathUri() . 'views/css/responsivegallery.css', 'all');
    }

    public function initContent()
    {
        parent::initContent();

        include_once dirname(__FILE__) . '/../../responsivegallery.php';
        include_once dirname(__FILE__) . '/../../classes/ResponsiveGalleryItem.php';
        include_once dirname(__FILE__) . '/../../classes/ResponsiveGalleryGallery.php';

        if ($id_gallery = (int) Tools::getValue('id_gallery')) {
            $this->initGallery($id_gallery);
        } else {
            $this->initGalleries();
        }
    }

    public function initGallery($id_gallery)
    {

        $id_lang = $this->context->cookie->id_lang;
        $o_responsive_gallery = new ResponsiveGalleryGallery($id_gallery, $id_lang);

        if (!$o_responsive_gallery->active) {
            $this->initGalleries();
            return;
        }

        if (Tools::getValue('ajax', 0) == 1 && Tools::getValue('page', false) !== false) {
            $page = (int) Tools::getValue('page', false);
            //Charger les images suivantes (retourner un tableau au format qu'il faut, avec image, url...)
            $gallery_items = $o_responsive_gallery->getGalleryPage($page);

            $result_html = '';
            foreach ($gallery_items as $item) {
                $tpl = $this->context->smarty->createTemplate(
                    $this->getTemplatePath('gallery-item.tpl')
                );
                $tpl->assign(
                    array(
                        'item' => $item,
                        'page' => $page,
                        'ps_base_uri' => __PS_BASE_URI__,
                    )
                );

                $result_html .= $tpl->fetch();
            }

            echo $result_html;
            die();
        } else {
            //ajout des variables JS
            $this->context->smarty->assign(
                array(
                    'current_gallery' => $id_gallery,
                    'RG_HORIZONTAL_MARGIN' => $o_responsive_gallery->horizontal_margin,
                    'RG_VERTICAL_MARGIN' => $o_responsive_gallery->vertical_margin,
                    'RG_INNER_MARGIN' => $o_responsive_gallery->inner_margin,
                    'RG_FADEIN_SPEED' => $o_responsive_gallery->fadein_speed,
                    'RG_CURRENT_PAGE' => 1,
                    'ps_base_uri' => __PS_BASE_URI__,
                    'RG_BREAKPOINT_1' => $o_responsive_gallery->breakpoint_1,
                    'RG_BREAKPOINT_2' => $o_responsive_gallery->breakpoint_2,
                    'RG_BREAKPOINT_3' => $o_responsive_gallery->breakpoint_3,
                    'RG_BREAKPOINT_4' => $o_responsive_gallery->breakpoint_4,
                    'RG_BREAKPOINT_5' => $o_responsive_gallery->breakpoint_5,
                    'RG_NB_ITEM_1' => $o_responsive_gallery->nb_item_per_line_1,
                    'RG_NB_ITEM_2' => $o_responsive_gallery->nb_item_per_line_2,
                    'RG_NB_ITEM_3' => $o_responsive_gallery->nb_item_per_line_3,
                    'RG_NB_ITEM_4' => $o_responsive_gallery->nb_item_per_line_4,
                    'RG_NB_ITEM_5' => $o_responsive_gallery->nb_item_per_line_5,
                    'RG_LEGEND_ON_PHOTO' => $o_responsive_gallery->legend_on_photo,
                    'RG_TITLE' => $o_responsive_gallery->title,
                    'RG_PREAMBLE' => $o_responsive_gallery->preamble,
                )
            );

            $gallery_items = $o_responsive_gallery->getGalleryPage(1);

            $this->context->smarty->assign('gallery_items', $gallery_items);

        }

        $this->setTemplate('default-gallery.tpl');
    }

    public function initGalleries()
    {

        $id_lang = $this->context->cookie->id_lang;

        $galleries = ResponsiveGalleryGallery::getGalleries();

        foreach ($galleries as $id => $gallery) {
            $galleries[$id]['link'] = $this->context->link->getModuleLink(
                'responsivegallery',
                'default',
                array('id_gallery' => $gallery['id_gallery'])
            );
        }

        $this->context->smarty->assign(
            array(
                'ps_base_uri' => __PS_BASE_URI__,
                'galleries' => $galleries,
                'RG_TITLE' => Configuration::get('RG_TITLE', $id_lang),
                'RG_PREAMBLE' => html_entity_decode(Configuration::get('RG_PREAMBLE', $id_lang)),
            )
        );
        $this->setTemplate('default-galleries.tpl');
    }
}

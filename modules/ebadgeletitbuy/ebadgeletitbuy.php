<?php
/**
* NOTICE OF LICENSE
*
*  @author    YuhaoZHANG
*  @copyright Altares
*  @license
*/

if (!defined('_PS_VERSION_')) {
    exit;
}
define("WSLETITBUY", "https://www.adminiz.fr");  //prod
//define("WSLETITBUY", "http://staging-adminiz.elasticbeanstalk.com");  //staging
//define("WSLETITBUY", "http://localhost:8080");  //localhost

class EBadgeLetitbuy extends Module
{
    public function __construct()
    {
        $this->name = 'ebadgeletitbuy';
        $this->tab = 'front_office_features';
        $this->module_key = '9229d94ccfeacd45ab6b06688d3890ab';
        $this->version = '1.0.5';
        $this->author = 'Altares';
        $this->need_instance = 0;
        parent::__construct();
        $this->displayName = $this->l('E-Badge Letitbuy');
        $this->description = $this->l("Développez votre clientèle et votre chiffre d'affaires grâce à LetItBuy ! https://www.adminiz.fr/letitbuy/");
        $this->confirmUninstall = $this->l('Etes vous sûr de vouloir supprimer notre module ?');
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        if (!parent::install() || !$this->registerHook('leftColumn') || !$this->registerHook('rightColumn') || !$this->registerHook('footer') || !Configuration::updateValue('letitbuy_name', 'ebadgeletitbuy')) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !Configuration::deleteByName('letitbuy_name') || !Configuration::deleteByName('letitbuy_idWebsite')) {
            return false;
        }
        return true;
    }

    public function hookDisplayLeftColumn($params)
    {
        if (Configuration::get('letitbuy_idWebsite')!="") {
            $this->context->smarty->assign(
                array(
                'my_module_name' => Configuration::get('letitbuy_name'),
                'letitbuy_idWebsite' => Configuration::get('letitbuy_idWebsite'),
                'my_module_link' => $this->context->link->getModuleLink('ebadgeletitbuy', 'display'),
                'wsletitbuy' => WSLETITBUY
                )
            );
            return $this->display(__FILE__, 'ebadgeletitbuy.tpl');
        }
    }

    public function hookDisplayRightColumn($params)
    {
        if (Configuration::get('letitbuy_idWebsite')!="") {
            return $this->hookDisplayLeftColumn($params);
        }
    }

    public function hookDisplayFooter()
    {
        if (Configuration::get('letitbuy_idWebsite')!="") {
            $this->context->smarty->assign(
                array(
                    'my_module_name' => Configuration::get('letitbuy_name'),
                    'letitbuy_idWebsite' => Configuration::get('letitbuy_idWebsite'),
                    'my_module_link' => $this->context->link->getModuleLink('ebadgeletitbuy', 'display'),
                    'wsletitbuy' => WSLETITBUY
                )
            );
            return $this->display(__FILE__, 'ebadgeletitbuyFooter.tpl');
        }
    }
    public function getContent()
    {
        $url = Tools::getHttpHost(true).__PS_BASE_URI__;
        $output = null;
        $email = null;
        $siren = null;
        if (Configuration::get('letitbuy_idWebsite')!="") {
            $this->smarty->assign(array('output' => $output, 'letitbuy_idWebsite' => Configuration::get('letitbuy_idWebsite')));
            return $this->display(__FILE__, '/views/templates/admin/admin.tpl');
        }
        if (Tools::isSubmit('Connexion')) {
            $letitbuy_idWebsite = (string)(Tools::getValue('letitbuy_idWebsite'));
            $email = (string)(Tools::getValue('EMAIL'));
            $pwd = (string)(Tools::getValue('PASSWORD'));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)!=true) {
                $output .= $this->displayError($this->l('Veuillez saisir une adresse e-mail valide.'));
            } elseif (!$pwd || empty($pwd)) {
                $output .= $this->displayError($this->l('Veuillez saisir un mot de passe valide.'));
            } else {
                $ch_connexion = curl_init();
                curl_setopt($ch_connexion, CURLOPT_URL, WSLETITBUY."/ws/auth/login");
                curl_setopt($ch_connexion, CURLOPT_POST, 1);
                curl_setopt($ch_connexion, CURLOPT_POSTFIELDS, 'login='.$email.'&pwd='.$pwd);
                curl_setopt($ch_connexion, CURLOPT_RETURNTRANSFER, true);
                $server_output_json = curl_exec($ch_connexion);
                $data = Tools::jsonDecode($server_output_json, true);
                curl_close($ch_connexion);
                if ($data["codeRetour"] =="0") {
                    $letitbuy_idWebsite=$data["idWebsite"];
                    if ($letitbuy_idWebsite==null) {
                        $output .= $this->displayError($this->l('Bienvenue '.$email.', Vous n\'avez pas encore votre URL et votre siren. Merci de mettre à jour. '));
                        $tmp=$data["tmp"];
                        $this->smarty->assign(array('output' => $output, 'email' => $email, 'tmp' =>$tmp, 'url' =>$url));
                        return $this->display(__FILE__, '/views/templates/admin/update.tpl');
                    }
                    $output .= $this->displayConfirmation($this->l('Félicitation'));
                    $this->smarty->assign(array('output' => $output, 'letitbuy_idWebsite' => $letitbuy_idWebsite));
                    Configuration::updateValue('letitbuy_idWebsite', $data["idWebsite"]);

                    return $this->display(__FILE__, '/views/templates/admin/success.tpl');
                } else {
                    $output .= $this->displayError($this->l('Votre email ou votre mot de passe est incorrect. Merci de saisir à nouveau vos informations.'));
                }
            }
        }
        if (Tools::isSubmit('Inscription')) {
            $email = (string)(Tools::getValue('EMAIL'));
            $pwd = (string)(Tools::getValue('PASSWORD'));
            $url = (string)(Tools::getValue('URL'));
            $siren = (string)(Tools::getValue('SIREN'));
            $origin = 'Presta';
            $headers = @get_headers($url);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)!=true) {
                $output .= $this->displayError($this->l('Veuillez saisir une adresse e-mail valide.'));
            } elseif (!$pwd || empty($pwd)) {
                $output .= $this->displayError($this->l('Veuillez saisir un mot de passe valide.'));
            } elseif (strpos($headers[0], '404')) {
                $output .= $this->displayError($this->l('Veuillez saisir un url en cours de validité.'));
            } elseif (Tools::strlen($siren)!= 9) {
                $output .= $this->displayError($this->l('Veuillez saisir un siren valide (9 chiffres).'));
            } else {
                $ch_register = curl_init();
                curl_setopt($ch_register, CURLOPT_URL, WSLETITBUY."/ws/auth/register");
                curl_setopt($ch_register, CURLOPT_POST, 1);
                curl_setopt($ch_register, CURLOPT_POSTFIELDS, 'login='.$email.'&pwd='.$pwd.'&url='.$url.'&siren='.$siren.'&origin='.$origin);
                curl_setopt($ch_register, CURLOPT_RETURNTRANSFER, true);
                $server_output_json = curl_exec($ch_register);
                $data = Tools::jsonDecode($server_output_json, true);
                curl_close($ch_register);
                if ($data["codeRetour"]==0) {
                    $output .= $this->displayConfirmation($this->l('Félicitation'));
                    $output .= $this->displayConfirmation($data["libelleRetour"]);
                    $this->smarty->assign(array('output' => $output, 'letitbuy_idWebsite' => $data["idWebsite"]));
                    Configuration::updateValue('letitbuy_idWebsite', $data["idWebsite"]);

                    return $this->display(__FILE__, '/views/templates/admin/success.tpl');
                }
                $output .= $this->displayError($data["titreErreur"]." <br> ".$data["listeErreur"][0]["libelleErreur"]);
            }
            $this->smarty->assign(array('output' => $output, 'url' =>$url, 'email' =>$email, 'siren' =>$siren));
            return $this->display(__FILE__, '/views/templates/admin/inscription.tpl');
        }

        if (Tools::isSubmit('Update')) {
            $email = (string)(Tools::getValue('EMAIL'));
            $tmp = (string)(Tools::getValue('TMP'));
            $url = (string)(Tools::getValue('URL'));
            $siren = (string)(Tools::getValue('SIREN'));
            $origin = 'Presta';
            $headerss = @get_headers($url);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)!=true) {
                $output .= $this->displayError($this->l('Veuillez saisir une adresse e-mail valide.'));
            } elseif (!$tmp || empty($tmp)) {
                $output .= $this->displayError($this->l('Veuillez vérifier votre identifiant .'));
            } elseif ((!$url || empty($url)) || strpos($headerss[0], '404')) {
                $output .= $this->displayError($this->l('Veuillez saisir un url en cours de validité.'));
            } elseif ((!$siren || empty($siren)) || Tools::strlen($siren)!= 9) {
                $output .= $this->displayError($this->l('Veuillez saisir un siren valide (9 chiffres).'));
            } else {
                $ch_update = curl_init();
                curl_setopt($ch_update, CURLOPT_URL, WSLETITBUY."/ws/auth/update");
                curl_setopt($ch_update, CURLOPT_POST, 1);
                curl_setopt($ch_update, CURLOPT_POSTFIELDS, 'login='.$email.'&token='.$tmp.'&url='.$url.'&siren='.$siren.'&origin='.$origin);
                curl_setopt($ch_update, CURLOPT_RETURNTRANSFER, true);
                $server_output_json = curl_exec($ch_update);
                $data = Tools::jsonDecode($server_output_json, true);
                curl_close($ch_update);
                if ($data["codeRetour"]==0) {
                    //$output .= $this->displayConfirmation($data["message"]);
                    $output .= $this->displayConfirmation($this->l('Félicitation'));
                    $output .= $this->displayConfirmation($data["libelleRetour"]);
                    $this->smarty->assign(array('output' => $output, 'letitbuy_idWebsite' => $data["idWebsite"]));
                    Configuration::updateValue('letitbuy_idWebsite', $data["idWebsite"]);
                    return $this->display(__FILE__, '/views/templates/admin/success.tpl');
                } else {
                    $output .= $this->displayError($data["listeErreur"][0]["libelleErreur"]);
                }
            }
            $this->smarty->assign(array('output' => $output, 'email' => $email, 'tmp' =>$tmp, 'url' =>$url, 'siren'=>$siren));
            return $this->display(__FILE__, '/views/templates/admin/update.tpl');
        }
        $this->smarty->assign(array('output' => $output, 'url' =>$url, 'email' =>$email, 'siren' =>$siren));
        return $this->display(__FILE__, '/views/templates/admin/inscription.tpl');
    }
}

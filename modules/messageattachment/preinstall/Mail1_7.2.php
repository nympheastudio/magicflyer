<?php
	/**
	 * 2007-2016 PrestaShop
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
	 * @author    PrestaShop SA <contact@prestashop.com>
	 * @copyright 2007-2016 PrestaShop SA
	 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
	 *  International Registered Trademark & Property of PrestaShop SA
	 */

	class Mail extends MailCore
	{
		
		 public static function Send(
        $idLang,
        $template,
        $subject,
        $templateVars,
        $to,
        $toName = null,
        $from = null,
        $fromName = null,
        $fileAttachment = null,
        $mode_smtp = null,
        $templatePath = _PS_MAIL_DIR_,
        $die = false,
        $idShop = null,
        $bcc = null,
        $replyTo = null,
        $replyToName = null)
    {
        if (!$idShop) {
            $idShop = Context::getContext()->shop->id;
        }
				if (($template=='reply_msg' || $template == 'order_merchant_comment') && Module::isInstalled('messageattachment') && Module::isEnabled('messageattachment') && (int)Context::getContext()->employee->id>0)
			{
				$mess = Module::getInstanceByName('messageattachment');
				$fileAttachment = $mess::getFilesAttachement(Tools::getValue('id_order'));
				
			}
        $skip = array_reduce(Hook::exec(
           'actionEmailSendBefore',
            array(
                'idLang' => &$idLang,
                'template' => &$template,
                'subject' => &$subject,
                'templateVars' => &$templateVars,
                'to' => &$to,
                'toName' => &$toName,
                'from' => &$from,
                'fromName' => &$fromName,
                'fileAttachment' => &$fileAttachment,
                'mode_smtp' => &$mode_smtp,
                'templatePath' => &$templatePath,
                'die' => &$die,
                'idShop' => &$idShop,
                'bcc' => &$bcc,
                'replyTo' => &$replyTo
            ),
            null,
            true
        ), function ($carry, $item) {
            return $carry && $item;
        }, true);

        if (!$skip) {
	        return true;
        }

        if (is_numeric($idShop) && $idShop) {
            $shop = new Shop((int) $idShop);
        }

        $configuration = Configuration::getMultiple(
            array(
                'PS_SHOP_EMAIL',
                'PS_MAIL_METHOD',
                'PS_MAIL_SERVER',
                'PS_MAIL_USER',
                'PS_MAIL_PASSWD',
                'PS_SHOP_NAME',
                'PS_MAIL_SMTP_ENCRYPTION',
                'PS_MAIL_SMTP_PORT',
                'PS_MAIL_TYPE'
            ),
            null,
            null,
            $idShop
        );

        // Returns immediately if emails are deactivated
        if ($configuration['PS_MAIL_METHOD'] == 3) {
            return true;
        }

        // Hook to alter template vars
        Hook::exec(
            'sendMailAlterTemplateVars',
            array(
                'template' => $template,
                'template_vars' => &$templateVars,
            )
        );

        if (!isset($configuration['PS_MAIL_SMTP_ENCRYPTION']) || Tools::strtolower($configuration['PS_MAIL_SMTP_ENCRYPTION']) === 'off') {
            $configuration['PS_MAIL_SMTP_ENCRYPTION'] = false;
        }
        if (!isset($configuration['PS_MAIL_SMTP_PORT'])) {
            $configuration['PS_MAIL_SMTP_PORT'] = 'default';
        }

        // Sending an e-mail can be of vital importance for the merchant, when his password is lost for example, so we must not die but do our best to send the e-mail

        if (!isset($from) || !Validate::isEmail($from)) {
            $from = $configuration['PS_SHOP_EMAIL'];
        }

        if (!Validate::isEmail($from)) {
            $from = null;
        }

        // $from_name is not that important, no need to die if it is not valid
        if (!isset($fromName) || !Validate::isMailName($fromName)) {
            $fromName = $configuration['PS_SHOP_NAME'];
        }
        if (!Validate::isMailName($fromName)) {
            $fromName = null;
        }

        // It would be difficult to send an e-mail if the e-mail is not valid, so this time we can die if there is a problem
        if (!is_array($to) && !Validate::isEmail($to)) {
            Tools::dieOrLog(Context::getContext()->getTranslator()->trans('Error: parameter "to" is corrupted', array(), 'Admin.Advparameters.Notification'), $die);

            return false;
        }

        // if bcc is not null, make sure it's a vaild e-mail
        if (!is_null($bcc) && !is_array($bcc) && !Validate::isEmail($bcc)) {
            Tools::dieOrLog(Context::getContext()->getTranslator()->trans('Error: parameter "bcc" is corrupted', array(), 'Admin.Advparameters.Notification'), $die);
            $bcc = null;
        }

        if (!is_array($templateVars)) {
            $templateVars = array();
        }

        // Do not crash for this error, that may be a complicated customer name
        if (is_string($toName) && !empty($toName) && !Validate::isMailName($toName)) {
            $toName = null;
        }

        if (!Validate::isTplName($template)) {
            Tools::dieOrLog(Context::getContext()->getTranslator()->trans('Error: invalid e-mail template', array(), 'Admin.Advparameters.Notification'), $die);

            return false;
        }

        if (!Validate::isMailSubject($subject)) {
            Tools::dieOrLog(Context::getContext()->getTranslator()->trans('Error: invalid e-mail subject', array(), 'Admin.Advparameters.Notification'), $die);

            return false;
        }

        /* Construct multiple recipients list if needed */
        $message = \Swift_Message::newInstance();
        if (is_array($to) && isset($to)) {
            foreach ($to as $key => $addr) {
                $addr = trim($addr);
                if (!Validate::isEmail($addr)) {
                    Tools::dieOrLog(Context::getContext()->getTranslator()->trans('Error: invalid e-mail address', array(), 'Admin.Advparameters.Notification'), $die);

                    return false;
                }

                if (is_array($toName) && isset($toName[$key])) {
                    $addrName = $toName[$key];
                } else {
                    $addrName = $toName;
                }

                $addrName = (($addrName == null || $addrName == $addr || !Validate::isGenericName($addrName)) ? '' : self::mimeEncode($addrName));
                $message->addTo($addr, $addrName);
            }
            $toPlugin = $to[0];
        } else {
            /* Simple recipient, one address */
            $toPlugin = $to;
            $toName = (($toName == null || $toName == $to) ? '' : self::mimeEncode($toName));
            $message->addTo($to, $toName);
        }

        if (isset($bcc) && is_array($bcc)) {
            foreach ($bcc as $addr) {
                $addr = trim($addr);
                if (!Validate::isEmail($addr)) {
                    Tools::dieOrLog(Context::getContext()->getTranslator()->trans('Error: invalid e-mail address', array(), 'Admin.Advparameters.Notification'), $die);
                    return false;
                }
                $message->addBcc($addr);
            }
        } elseif (isset($bcc)) {
            $message->addBcc($bcc);
        }

        try {
            /* Connect with the appropriate configuration */
            if ($configuration['PS_MAIL_METHOD'] == 2) {
                if (empty($configuration['PS_MAIL_SERVER']) || empty($configuration['PS_MAIL_SMTP_PORT'])) {
                    Tools::dieOrLog(Context::getContext()->getTranslator()->trans('Error: invalid SMTP server or SMTP port', array(), 'Admin.Advparameters.Notification'), $die);

                    return false;
                }

                $connection = \Swift_SmtpTransport::newInstance($configuration['PS_MAIL_SERVER'], $configuration['PS_MAIL_SMTP_PORT'], $configuration['PS_MAIL_SMTP_ENCRYPTION'])
                    ->setUsername($configuration['PS_MAIL_USER'])
                    ->setPassword($configuration['PS_MAIL_PASSWD']);
            } else {
                $connection = \Swift_MailTransport::newInstance();
            }

            if (!$connection) {
                return false;
            }
            $swift = \Swift_Mailer::newInstance($connection);
            /* Get templates content */
            $iso = Language::getIsoById((int) $idLang);
            $isoDefault = Language::getIsoById((int) Configuration::get('PS_LANG_DEFAULT'));
            $isoArray = array();
            if ($iso) {
                $isoArray[] = $iso;
            }
            if ($isoDefault && $iso !== $isoDefault) {
                $isoArray[] = $isoDefault;
            }
            if (!in_array('en', $isoArray)) {
                $isoArray[] = 'en';
            }

            $moduleName = false;

            // get templatePath
            if (preg_match('#'.$shop->physical_uri.'modules/#', str_replace(DIRECTORY_SEPARATOR, '/', $templatePath)) && preg_match('#modules/([a-z0-9_-]+)/#ui', str_replace(DIRECTORY_SEPARATOR, '/', $templatePath), $res)) {
                $moduleName = $res[1];
            }
            foreach ($isoArray as $isoCode) {
                $isoTemplate = $isoCode.'/'.$template;
                $templatePath = self::getTemplateBasePath($isoTemplate, $moduleName, $shop->theme);

                if (!file_exists($templatePath.$isoTemplate.'.txt') && ($configuration['PS_MAIL_TYPE'] == Mail::TYPE_BOTH || $configuration['PS_MAIL_TYPE'] == Mail::TYPE_TEXT)) {
                    PrestaShopLogger::addLog(Context::getContext()->getTranslator()->trans('Error - The following e-mail template is missing: %s', array($templatePath.$isoTemplate.'.txt'), 'Admin.Advparameters.Notification'));
                } elseif (!file_exists($templatePath.$isoTemplate.'.html') && ($configuration['PS_MAIL_TYPE'] == Mail::TYPE_BOTH || $configuration['PS_MAIL_TYPE'] == Mail::TYPE_HTML)) {
                    PrestaShopLogger::addLog(Context::getContext()->getTranslator()->trans('Error - The following e-mail template is missing: %s', array($templatePath.$isoTemplate.'.html'), 'Admin.Advparameters.Notification'));
                } else {
                    $templatePathExists = true;
                    break;
                }
            }

            if (empty($templatePathExists)) {
                Tools::dieOrLog(Context::getContext()->getTranslator()->trans('Error - The following e-mail template is missing: %s', array($template), 'Admin.Advparameters.Notification'), $die);

                return false;
            }

            $templateHtml = '';
            $templateTxt = '';
            Hook::exec(
                'actionEmailAddBeforeContent',
                array(
                    'template' => $template,
                    'template_html' => &$templateHtml,
                    'template_txt' => &$templateTxt,
                    'id_lang' => (int) $idLang,
                ),
                null,
                true
            );
            $templateHtml .= Tools::file_get_contents($templatePath.$isoTemplate.'.html');
            $templateTxt .= strip_tags(html_entity_decode(Tools::file_get_contents($templatePath.$isoTemplate.'.txt'), null, 'utf-8'));
            Hook::exec(
                'actionEmailAddAfterContent',
                array(
                    'template' => $template,
                    'template_html' => &$templateHtml,
                    'template_txt' => &$templateTxt,
                    'id_lang' => (int) $idLang,
                ),
                null,
                true
            );

            /* Create mail and attach differents parts */
            $subject = '['.Configuration::get('PS_SHOP_NAME', null, null, $idShop).'] '.$subject;
            $message->setSubject($subject);

            $message->setCharset('utf-8');

            /* Set Message-ID - getmypid() is blocked on some hosting */
            $message->setId(Mail::generateId());

            if (!($replyTo && Validate::isEmail($replyTo))) {
                $replyTo = $from;
            }

            if (isset($replyTo) && $replyTo) {
                $message->setReplyTo($replyTo, ($replyToName !== '' ? $replyToName : null));
            }

            $templateVars = array_map(array('Tools', 'htmlentitiesDecodeUTF8'), $templateVars);
            $templateVars = array_map(array('Tools', 'stripslashes'), $templateVars);

            if (Configuration::get('PS_LOGO_MAIL') !== false && file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO_MAIL', null, null, $idShop))) {
                $logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO_MAIL', null, null, $idShop);
            } else {
                if (file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO', null, null, $idShop))) {
                    $logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO', null, null, $idShop);
                } else {
                    $templateVars['{shop_logo}'] = '';
                }
            }
            ShopUrl::cacheMainDomainForShop((int) $idShop);
            /* don't attach the logo as */
            if (isset($logo)) {
                $templateVars['{shop_logo}'] = $message->embed(\Swift_Image::fromPath($logo));
            }

            if ((Context::getContext()->link instanceof Link) === false) {
                Context::getContext()->link = new Link();
            }

            if (!is_null(Context::getContext()->cart) && !is_null(Context::getContext()->cart->id_lang)) {
                $templateLangId = Context::getContext()->cart->id_lang;
            } else {
                $templateLangId = Context::getContext()->language->id;
            }

            $templateVars['{shop_name}'] = Tools::safeOutput(Configuration::get('PS_SHOP_NAME', null, null, $idShop));
            $templateVars['{shop_url}'] = Context::getContext()->link->getPageLink('index', true, $templateLangId, null, false, $idShop);
            $templateVars['{my_account_url}'] = Context::getContext()->link->getPageLink('my-account', true, $templateLangId, null, false, $idShop);
            $templateVars['{guest_tracking_url}'] = Context::getContext()->link->getPageLink('guest-tracking', true, $templateLangId, null, false, $idShop);
            $templateVars['{history_url}'] = Context::getContext()->link->getPageLink('history', true, $templateLangId, null, false, $idShop);
            $templateVars['{color}'] = Tools::safeOutput(Configuration::get('PS_MAIL_COLOR', null, null, $idShop));
            // Get extra template_vars
            $extraTemplateVars = array();
            Hook::exec('actionGetExtraMailTemplateVars', array(
                'template' => $template,
                'template_vars' => $templateVars,
                'extra_template_vars' => &$extraTemplateVars,
                'id_lang' => (int) $idLang
            ), null, true);
            $templateVars = array_merge($templateVars, $extraTemplateVars);
            $swift->registerPlugin(new \Swift_Plugins_DecoratorPlugin(array($toPlugin => $templateVars)));
            if ($configuration['PS_MAIL_TYPE'] == Mail::TYPE_BOTH || $configuration['PS_MAIL_TYPE'] == Mail::TYPE_TEXT) {
                $message->addPart($templateTxt, 'text/plain', 'utf-8');
            }
            if ($configuration['PS_MAIL_TYPE'] == Mail::TYPE_BOTH || $configuration['PS_MAIL_TYPE'] == Mail::TYPE_HTML) {
                $message->addPart($templateHtml, 'text/html', 'utf-8');
            }
            if ($fileAttachment && !empty($fileAttachment)) {
                // Multiple attachments?
                if (!is_array(current($fileAttachment))) {
                    $fileAttachment = array($fileAttachment);
                }

                foreach ($fileAttachment as $attachment) {
                    if (isset($attachment['content']) && isset($attachment['name']) && isset($attachment['mime'])) {
                        $message->attach(\Swift_Attachment::newInstance()->setFilename($attachment['name'])->setContentType($attachment['mime'])->setBody($attachment['content']));
                    }
                }
            }
            /* Send mail */
            $message->setFrom(array($from => $fromName));
            $send = $swift->send($message);

            ShopUrl::resetMainDomainCache();

            if ($send && Configuration::get('PS_LOG_EMAILS')) {
                $mail = new Mail();
                $mail->template = Tools::substr($template, 0, 62);
                $mail->subject = Tools::substr($subject, 0, 254);
                $mail->id_lang = (int)$idLang;
                $recipientsTo = $message->getTo();
                $recipientsCc = $message->getCc();
                $recipientsBcc = $message->getBcc();
                if (!is_array($recipientsTo)) {
                    $recipientsTo = array();
                }
                if (!is_array($recipientsCc)) {
                    $recipientsCc = array();
                }
                if (!is_array($recipientsBcc)) {
                    $recipientsBcc = array();
                }
                foreach (array_merge($recipientsTo, $recipientsCc, $recipientsBcc) as $email => $recipient_name) {
                    /** @var Swift_Address $recipient */
                    $mail->id = null;
                    $mail->recipient = Tools::substr($email, 0, 126);
                    $mail->add();
                }
            }

            return $send;
        } catch (\Swift_SwiftException $e) {
            PrestaShopLogger::addLog(
                'Swift Error: '.$e->getMessage(),
                3,
                null,
                'Swift_Message'
            );

            return false;
        }
    }
		
	
	}

?>

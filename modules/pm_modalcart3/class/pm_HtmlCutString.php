<?php
/**
* @author original by prajwala <m.prajwala@gmail.com> - http://cut-html-string.googlecode.com/svn/php/cutstring.php
* @edited by Presta-Module.com <support@presta-module.com>
* @copyright Presta-Module 2014
*
*       _______  ____    ____
*      |_   __ \|_   \  /   _|
*        | |__) | |   \/   |
*        |  ___/  | |\  /| |
*       _| |_    _| |_\/_| |_
*      |_____|  |_____||_____|
*
**************************************
**   http://www.presta-module.com    *
**************************************
*/

class pm_HtmlCutString
{
    private $tempDiv;
    private $newDiv;
    public function __construct($string)
    {
        $this->tempDiv = new DomDocument();
        $this->tempDiv->loadXML('<div>'.html_entity_decode($string, ENT_COMPAT, 'utf-8').'</div>');
    }
    public function cut($limit, $suffix = '...')
    {
        $this->newDiv = new DomDocument();
        $this->searchEnd($this->tempDiv->documentElement, $this->newDiv, $limit - Tools::strlen($suffix));
        if (isset($this->newDiv->lastChild)) {
            $this->newDiv->lastChild->appendChild(new DOMText($suffix));
            if (isset($this->newDiv->lastChild->nodeValue) && !preg_match('#'.preg_quote($suffix).'#', $this->newDiv->lastChild->nodeValue)) {
                $this->newDiv->lastChild->nodeValue = $this->newDiv->lastChild->nodeValue.$suffix;
            }
        } else {
            $this->newDiv->appendChild(new DOMText($suffix));
        }
        $newhtml = $this->newDiv->saveHTML();
        return $newhtml;
    }
    private function deleteChildren($node)
    {
        while (isset($node->firstChild)) {
            $this->deleteChildren($node->firstChild);
            $node->removeChild($node->firstChild);
        }
    }
    private function searchEnd($parseDiv, $newParent, $limit, $charCount = 0)
    {
        foreach ($parseDiv->childNodes as $ele) {
            if ($ele->nodeType != 3) {
                $newEle = $this->newDiv->importNode($ele, true);
                if (count($ele->childNodes) === 0) {
                    $newParent->appendChild($newEle);
                    continue;
                }
                $this->deleteChildren($newEle);
                $newParent->appendChild($newEle);
                $res = $this->searchEnd($ele, $newEle, $limit, $charCount);
                if ($res) {
                    return $res;
                } else {
                    continue;
                }
            }
            if (mb_strlen($ele->nodeValue, 'UTF-8') + $charCount >= $limit) {
                $newEle = $this->newDiv->importNode($ele);
                $newEle->nodeValue = Tools::substr($newEle->nodeValue, 0, $limit - $charCount);
                $newParent->appendChild($newEle);
                return true;
            }
            $newEle = $this->newDiv->importNode($ele);
            $newParent->appendChild($newEle);
            $charCount += mb_strlen($newEle->nodeValue, 'UTF-8');
        }
        return false;
    }
}

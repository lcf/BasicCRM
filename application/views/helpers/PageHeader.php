<?php
class View_Helper_PageHeader extends Zend_View_Helper_Abstract
{
    public function pageHeader($text)
    {
        return "<div class=\"page-header\"><h1>$text</h1></div>";
    }
}
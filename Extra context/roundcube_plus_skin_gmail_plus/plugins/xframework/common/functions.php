<?php
require_once __DIR__ . '/Data.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Input.php';
require_once __DIR__ . '/Ajax.php';
require_once __DIR__ . '/Format.php';
require_once __DIR__ . '/Html.php';

/**
 * In web-based Roundcube requests this returns rcmail; in CalDAV requests it returns rcube. We avoid rcmail in CalDAV
 * because its startup initializes the full webmail application, session, plugins, GUI, etc.
 */
if (!function_exists('xrc')) {
    function xrc(): rcube
    {
        return rcube::get_instance();
    }
}

if (!function_exists('xdata')) {
    function xdata(): \XFramework\Data
    {
        return \XFramework\Data::instance();
    }
}

if (!function_exists('xdb')) {
    function xdb($provider = null): \XFramework\DatabaseGeneric
    {
        try {
            return \XFramework\Database::instance($provider);
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }
}

if (!function_exists('xformat')) {
    function xformat(): \XFramework\Format
    {
        return \XFramework\Format::instance();
    }
}

if (!function_exists('xhtml')) {
    function xhtml(): \XFramework\Html
    {
        return \XFramework\Html::instance();
    }
}

if (!function_exists('xinput')) {
    function xinput(): \XFramework\Input
    {
        return \XFramework\Input::instance();
    }
}

if (!function_exists('xajax')) {
    function xajax(): \XFramework\Ajax
    {
        return \XFramework\Ajax::instance();
    }
}

if (!function_exists('xget')) {
    function xget(string $key, bool $skipTokenCheck = false)
    {
        return \XFramework\Input::instance()->get($key, $skipTokenCheck);
    }
}

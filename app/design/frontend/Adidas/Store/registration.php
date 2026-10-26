<?php
/**
 * Registers the theme with Magento's component registrar.
 * Magento scans this file to find the theme in app/design/frontend/Codilar/starter_theme.
 */
use \Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::THEME,
    'frontend/Adidas/Store',
    __DIR__
);
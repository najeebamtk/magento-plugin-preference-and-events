<?php

use Magento\Framework\Component\ComponentRegistrar;

// Tells Magento that this folder is a module called Codilar_School
ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Codilar_School',
    __DIR__
);

<?php

declare(strict_types=1);

/**
 * @var \Duyler\Config\FileConfig $config
 */

return [
    'value' => 'from_b',
    'reference' => $config->get('config_a', 'value', 'default_from_a'),
];

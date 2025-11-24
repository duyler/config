<?php

declare(strict_types=1);

/**
 * @var \Duyler\Config\FileConfig $config
 */

return [
    'value' => 'from_a',
    'reference' => $config->get('config_b', 'value', 'default_from_b'),
];

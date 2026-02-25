<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Disposable Email',
    'description' => 'A service extension to compile lists of disposable and free mail providers, offering a simple way to validate email addresses against them.',
    'category' => 'misc',
    'author' => 'Andreas Sommer',
    'author_email' => 'sommer@belsignum.com',
    'author_company' => 'www.belsignum.com',
    'state' => 'stable',
    'version' => '10.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-12.4.99'
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];

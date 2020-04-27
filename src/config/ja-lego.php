<?php

use JA\Lego\Foundation\QS;
use JA\Lego\Foundation\Asset;
use JA\Lego\Widget\Grid\Batch;

return [
    'cache' => [
        'key-prefix' => 'ja-lego:'
    ],

    'session' => [
        'key-prefix' => 'ja-lego:',
    ],

    'assets' => [
        'dependencies' => [
            'style' => [
                'layui' => [
                    'path' => public_path(Asset::PATH . '/vendor/layui/dist/css/layui.css'),
                    'load' => true,
                ]
            ],
            'script' => [
                'jquery' => [
                    'path' => public_path(Asset::PATH . '/vendor/jquery/dist/jquery.min.js'),
                    'load' => true,
                ],
                'layui' => [
                    'path' => public_path(Asset::PATH . '/vendor/layui/dist/layui.all.js'),
                    'load' => true,
                ]
            ],
        ],
    ],

    'fields' => [
        'attributes' => [
            'class' => 'form-control',
        ],

        /*
         * Purifier Config
         * Doc：http://htmlpurifier.org/live/configdoc/plain.html
         */
        'purifier' => [
            'HTML.Allowed' => '',
            'AutoFormat.RemoveEmpty' => true,
            'AutoFormat.AutoParagraph' => false,
        ],

        'fields' => [
            /*
            \JA\Lego\Field\Fields\Checkboxes::class => [
                'separator' => '|',
            ],
            \JA\Lego\Field\Fields\RichText::class => [
                'purifier' => 'default',
            ],
            */
        ],

        'defined' => [],
    ],

    'widgets' => [
        'form' => [
            'default-view' => 'ja-lego::default.form.horizontal',
        ],
        'filter' => [
            'default-view' => 'ja-lego::default.filter.inline',
        ],
        'grid' => [
            'responsive' => true,
            'default-view' => 'ja-lego::default.grid.table',
            'default-view-mobile' => 'ja-lego::default.grid.table-mobile',
            'batch' => [
                'default-target' => Batch::BATCH_OPEN_TARGET_SELF,
            ],
            'pipes' => [
                /*
                \Lego\Widget\Grid\Pipes4Datetime::class,
                \Lego\Widget\Grid\Pipes4String::class,
                */
            ],
        ],
    ],

    'button' => [
        'default-view' => 'ja-lego::default.button',
        'default-attributes' => [
            'class' => ['layui-btn'],
        ],
    ],

    'views' => [
        'message' => [
            'default-view' => 'ja-lego::default.message',
        ],
        'styles' => [
            'default-view' => 'ja-lego::default.styles',
        ],
        'scripts' => [
            'default-view' => 'ja-lego::default.scripts',
        ],
    ],

    'paginator' => [
        'per-page'  => 100,
        'page-name' => 'page',
    ],

    'qs' => [
        QS::TYPE_QUERY => [],
        QS::TYPE_STORE => [],
    ],

    'response' => [
        'keys' => [
            'code' => 'code',
            'msg' => 'msg',
            'data' => 'data',
        ],
        'code-success' => 0,
        'msg-failed' => '服务器内部异常，请稍后再试',
    ],
];

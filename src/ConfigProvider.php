<?php

namespace PhlyBlog;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'blog'            => $this->getBlogConfig(),
            'laminas-cli'     => $this->getCliConfig(),
            'router'          => [
                'routes' => [
                    'phly-blog' => $this->getRoutes(),
                ],
            ],
            'service_manager' => $this->getDependencyConfig(),
            'view_manager'    => $this->getViewConfig(),
        ];
    }

    public function getBlogConfig(): array
    {
        return [
            'options'        => [
                'author_feed_filename_template' => 'public/blog/author/%s-%s.xml',
                'author_feed_title_template'    => 'Author: %s',
                'by_author_filename_template'   => 'public/blog/author/%s-p%d.html',
                'by_day_filename_template'      => 'public/blog/day/%s-p%d.html',
                'by_month_filename_template'    => 'public/blog/month/%s-p%d.html',
                'by_tag_filename_template'      => 'public/blog/tag/%s-p%d.html',
                'by_year_filename_template'     => 'public/blog/year/%s-p%d.html',
                'entries_filename_template'     => 'public/blog-p%d.html',
                'entries_template'              => 'phly-blog/list',
                'entry_filename_template'       => 'public/blog/%s.html',
                'entry_link_template'           => '/blog/%s.html',
                'entry_template'                => 'phly-blog/entry',
                'feed_author_email'             => 'you@your.tld',
                'feed_author_name'              => "Your name here",
                'feed_author_uri'               => 'http://your.tld',
                'feed_filename'                 => 'public/blog-%s.xml',
                'feed_hostname'                 => 'http://your.tld',
                'feed_title'                    => 'Blog Entries',
                'tag_feed_filename_template'    => 'public/blog/tag/%s-%s.xml',
                'tag_feed_title_template'       => 'Tag: %s',
                'tag_cloud_options'             => ['tagDecorator' => [
                    'decorator' => 'html_tag',
                    'options'   => [
                        'fontSizeUnit' => '%',
                        'minFontSize'  => 80,
                        'maxFontSize'  => 300,
                    ],
                ]],
            ],
            'posts_path'     => 'data/blog/',
            'cloud_callback' => false,
        ];
    }

    public function getCliConfig(): array
    {
        return [
            'commands' => [
                'phly-blog:compile' => Console\CompileCommand::class,
            ],
        ];
    }

    public function getRoutes(): array
    {
        return [
            'type'          => 'Literal',
            'options'       => [
                'route' => '/blog',
            ],
            'may_terminate' => false,
            'child_routes'  => [
                'index'     => [
                    'type'    => 'Literal',
                    'options' => [
                        'route' => '.html',
                    ],
                ],
                'feed-atom' => [
                    'type'    => 'Literal',
                    'options' => [
                        'route' => '-atom.xml',
                    ],
                ],
                'feed-rss'  => [
                    'type'    => 'Literal',
                    'options' => [
                        'route' => '-rss.xml',
                    ],
                ],
                'entry'     => [
                    'type'    => 'Regex',
                    'options' => [
                        'regex' => '/(?<id>[^/]+)\.html',
                        'spec'  => '/%id%.html',
                    ],
                ],
                'author'    => [
                    'type'          => 'Regex',
                    'options'       => [
                        'regex'    => '/author/(?<author>[^/]+)',
                        'defaults' => [
                            'action' => 'author',
                        ],
                        'spec'     => '/author/%author%',
                    ],
                    'may_terminate' => false,
                    'child_routes'  => [
                        'page'      => [
                            'type'    => 'Literal',
                            'options' => [
                                'route' => '.html',
                            ],
                        ],
                        'feed-atom' => [
                            'type'    => 'Literal',
                            'options' => [
                                'route' => '-atom.xml',
                            ],
                        ],
                        'feed-rss'  => [
                            'type'    => 'Literal',
                            'options' => [
                                'route' => '-rss.xml',
                            ],
                        ],
                    ],
                ],
                'tag'       => [
                    'type'          => 'Regex',
                    'options'       => [
                        'regex'    => '/tag/(?<tag>[^/.-]+)',
                        'defaults' => [
                            'action' => 'tag',
                        ],
                        'spec'     => '/tag/%tag%',
                    ],
                    'may_terminate' => false,
                    'child_routes'  => [
                        'page'      => [
                            'type'    => 'Literal',
                            'options' => [
                                'route' => '.html',
                            ],
                        ],
                        'feed-atom' => [
                            'type'    => 'Literal',
                            'options' => [
                                'route' => '-atom.xml',
                            ],
                        ],
                        'feed-rss'  => [
                            'type'    => 'Literal',
                            'options' => [
                                'route' => '-rss.xml',
                            ],
                        ],
                    ],
                ],
                'year'      => [
                    'type'    => 'Segment',
                    'options' => [
                        'route'       => '/year/:year.html',
                        'constraints' => [
                            'year' => '\d{4}',
                        ],
                        'defaults'    => [
                            'action' => 'year',
                        ],
                    ],
                ],
                'month'     => [
                    'type'    => 'Segment',
                    'options' => [
                        'route'       => '/month/:year/:month.html',
                        'constraints' => [
                            'year'  => '\d{4}',
                            'month' => '\d{2}',
                        ],
                        'defaults'    => [
                            'action' => 'month',
                        ],
                    ],
                ],
                'day'       => [
                    'type'    => 'Segment',
                    'options' => [
                        'route'       => '/day/:year/:month/:day.html',
                        'constraints' => [
                            'year'  => '\d{4}',
                            'month' => '\d{2}',
                            'day'   => '\d{2}',
                        ],
                        'defaults'    => [
                            'action' => 'day',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getDependencyConfig(): array
    {
        return [
            'factories' => [
                Compiler::class               => CompilerFactory::class,
                Console\CompileCommand::class => Console\CompileCommandFactory::class,
                Console\View::class           => Console\ViewFactory::class,
            ],
        ];
    }

    public function getViewConfig(): array
    {
        return [
            'template_map'        => [
                'phly-blog/entry-short' => __DIR__ . '/../view/phly-blog/entry-short.phtml',
                'phly-blog/entry'       => __DIR__ . '/../view/phly-blog/entry.phtml',
                'phly-blog/list'        => __DIR__ . '/../view/phly-blog/list.phtml',
                'phly-blog/paginator'   => __DIR__ . '/../view/phly-blog/paginator.phtml',
                'phly-blog/tags'        => __DIR__ . '/../view/phly-blog/tags.phtml',
            ],
            'template_path_stack' => [
                'phly-blog' => __DIR__ . '/../view',
            ],
        ];
    }
}

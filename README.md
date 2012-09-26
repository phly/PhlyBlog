PhlyBlog: Static Blog Generator
===============================

This module is a tool for generating a static blog.

Blog posts are simply PHP files that create and return `PhlyBlog\EntryEntity`
objects. You point the compiler at a directory, and it creates a tree of files
representing your blog and its feeds. These can either be consumed by your
application, or they can be plain old HTML markup files that you serve
directly.

Requirements
------------

- PHP >= 5.3.3
- Zend Framework 2 >= 2.0.0beta4, specifically: 
  - Zend\View\View, used to render and write generated files
  - Zend\Mvc and Zend\ModuleManager, as this implements a module, and the
    compiler script depends on it and an Application instance. As such, it also
    has a dependency on Zend\Loader, Zend\ServiceManager, and Zend\EventManager.
  - Zend\Feed\Writer
  - Zend\Tag\Cloud
- PhlyCommon (for Entity and Filter interfaces)

Installation
------------

You can add this module as a git submodule to your repository. Alternately, use
composer. To do so, add the following `composer.json` configuration in your
project:

```js
{
    "minimum-stability": "dev",
    "require": {
        "phly/phly-blog": "dev-master"
    }
}
```

and then execute:

```bash
php composer.phar install
```

Writing Entries
---------------

Find a location in your repository for entries, preferably outside your document
root; I recommend either `data/blog/` or `posts/`.

Post files are simply PHP files that return a `PhlyBlog\EntryEntity` instance.
A sample is provided in `misc/sample-post.php`. This post can be copied as a
template.

Important things to note:

- Set the created and/or updated timestamps. Alternately, use `DateTime` or
  `date()` to generate a timestamp based on a date/time string.
- Entries marked as "drafts" (i.e., `setDraft(true)`) will not be published.
- Entries marked as private (i.e., `setPublic(false)`) will be published, but
  will not be aggregated in paginated views or feeds. As such, you need to hand
  the URL to somebody in order for them to see it.
- You can set an array of tags. Tags can have whitespace, which will be
  translated to "+" characters.

Usage
=====

This module is ZF2 Console-aware. Once installed and active in your
application, you should be able to run:

```bash
% php public/index.php
```

and see usage for the module. Currently, it defines a "blog compile" action
that can generate the following artifacts:

- A file per entry
- Paginated entry files
- Paginated entry files by year
- Paginated entry files by month
- Paginated entry files by day
- Paginated entry files by tag
- Atom and/or RSS feeds for recent entries
- Atom and/or RSS feeds for recent entries by tag
- Optionally, a tag cloud

You will want to setup local configuration; I recommend putting it in
`config/autoload/module.blog.config.global.php`. As a sample:

    ```php
    <?php
    return array(
    'blog' => array(
        'options' => array(
            // The following indicate where to write files. Note that this
            // configuration writes to the "public/" directory, which would
            // create a blog made from static files. For the various
            // paginated views, "%d" is the current page number; "%s" is
            // typically a date string (see below for more information) or tag.
            'by_day_filename_template'   => 'public/blog/day/%s-p%d.html',
            'by_month_filename_template' => 'public/blog/month/%s-p%d.html',
            'by_tag_filename_template'   => 'public/blog/tag/%s-p%d.html',
            'by_year_filename_template'  => 'public/blog/year/%s-p%d.html',
            'entries_filename_template'  => 'public/blog-p%d.html',

            // In this case, the "%s" is the entry ID.
            'entry_filename_template'    => 'public/blog/%s.html',

            // For feeds, the final "%s" is the feed type -- "atom" or "rss". In
            // the case of the tag feed, the initial "%s" is the current tag.
            'feed_filename'              => 'public/blog-%s.xml',
            'tag_feed_filename_template' => 'public/blog/tag/%s-%s.xml',
             
            // This is the link to a blog post
            'entry_link_template'        => '/blog/%s.html',

            // These are the various URL templates for "paginated" views. The
            // "%d" in each is the current page number.
            'entries_url_template'       => '/blog-p%d.html',
            // For the year/month/day paginated views, "%s" is a string
            // representing the date. By default, this will be "YYYY",
            // "YYYY/MM", and "YYYY/MM/DD", respectively.
            'by_year_url_template'       => '/blog/year/%s-p%d.html',
            'by_month_url_template'      => '/blog/month/%s-p%d.html',
            'by_day_url_template'        => '/blog/day/%s-p%d.html',

            // These are the primary templates you will use -- the first is for
            // paginated lists of entries, the second for individual entries.
            // There are of course more templates, but these are the only ones 
            // that will be directly referenced and rendered by the compiler.
            'entries_template'           => 'phly-blog/list',
            'entry_template'             => 'phly-blog/entry',

            // The feed author information is default information to use when
            // the author of a post is unknown, or is not an AuthorEntity
            // object (and hence does not contain this information).
            'feed_author_email'          => 'you@your.tld',
            'feed_author_name'           => "Your Name Here",
            'feed_author_uri'            => 'http://your.tld',
            'feed_hostname'              => 'http://your.tld',
            'feed_title'                 => 'Blog Entries :: Your Blog Name',
            'tag_feed_title_template'    => 'Tag: %s :: Your Blog Name',

            // If generating a tag cloud, you can specify options for
            // Zend\Tag\Cloud. The following sets up percentage sizing from
            // 80-300%
            'tag_cloud_options'          => array('tagDecorator' => array(
                'decorator' => 'html_tag',
                'options'   => array(
                    'fontSizeUnit' => '%',
                    'minFontSize'  => 80,
                    'maxFontSize'  => 300,
                ),
            )),
        ),
        
        // This is the location where you are keeping your post files (the PHP
        // files returning `PhlyBlog\EntryEntity` objects).
        'posts_path'     => 'data/posts/',

        // You can provide your own callback to setup renderer and response
        // strategies. This is useful, for instance, for injecting your 
        // rendered contents into a layout.
        // The callback will receive a View instance, application configuration
        // (as an array), and the application's Locator instance.
        'view_callback'  => array('Application\Module', 'prepareCompilerView'),

        // Tag cloud generation is possible, but you likely need to capture
        // the rendered cloud to inject elsewhere. You can do this with a
        // callback.
        // The callback will receive a Zend\Tag\Cloud instance, the View
        // instance, application configuration // (as an array), and the
        // application's Locator instance.
        'cloud_callback' => array('Application\Module', 'handleTagCloud'),
    ),
    'di' => array('instance' => array(
        // You will likely want to customize the templates provided. Do so by
        // creating your own in your own module, and make sure you alter the
        // resolvers so that they point to the override locations. Below, I'm
        // putting my overrides in my Application module.
        'Zend\View\Resolver\TemplateMapResolver' => array('parameters' => array(
            'map' => array(
                'phly-blog/entry-short'  => 'module/Application/view/phly-blog/entry-short.phtml',
                'phly-blog/entry'        => 'module/Application/view/phly-blog/entry.phtml',
                'phly-blog/list'         => 'module/Application/view/phly-blog/list.phtml',
                'phly-blog/paginator'    => 'module/Application/view/phly-blog/paginator.phtml',
                'phly-blog/tags'         => 'module/Application/view/phly-blog/tags.phtml',
            ),
        )),

        'Zend\View\Resolver\TemplatePathStack' => array('parameters' => array(
            'paths' => array(
                'phly-blog' => 'module/Application/view',
            ),
        )),
    ));
    ```

When you run the command line tool, it will generate files in the locations you
specify in your configuration.

License
-------

Copyright (c) 2012, Matthew Weier O'Phinney
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this
  list of conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice, this
  list of conditions and the following disclaimer in the documentation and/or
  other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

<?php
use PhlyBlog\AuthorEntity;
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();
$author = new AuthorEntity();
$author->fromArray(array (
  'id' => 'crazyhorse',
  'name' => 'Crazy Horse',
  'email' => 'crazyhorse@siouxnation.org',
  'url' => 'http://crazyhorse.siouxnation.org',
));

$entry->setId('4f8706cf661ca-post-name');
$entry->setTitle('4f8706cf661ca Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2008:05:14 15:03:03'));
$entry->setUpdated(new DateTime('2008:05:14 15:03:03'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'holiday',
  1 => 'personal',
  2 => 'thoughts',
  3 => 'war',
  4 => 'children',
  5 => 'php',
));

$body =<<<'EOT'
This is it!
EOT;
$entry->setBody($body);

$extended =<<<'EOT'
This is the extended portion of the entry.
EOT;
$entry->setExtended($extended);

return $entry;

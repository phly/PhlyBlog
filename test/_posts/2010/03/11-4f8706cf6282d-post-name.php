<?php
use PhlyBlog\AuthorEntity;
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();
$author = new AuthorEntity();
$author->fromArray(array (
  'id' => 'custer',
  'name' => 'George Armstrong Custer',
  'email' => 'me@gacuster.com',
  'url' => 'http://www.gacuster.com',
));

$entry->setId('4f8706cf6282d-post-name');
$entry->setTitle('4f8706cf6282d Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2010:03:11 21:59:59'));
$entry->setUpdated(new DateTime('2010:03:11 21:59:59'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'holiday',
  1 => 'personal',
  2 => 'programming',
  3 => 'war',
  4 => 'literature',
  5 => 'children',
  6 => 'draft',
  7 => 'conferences',
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

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

$entry->setId('4f8706cf6535d-post-name');
$entry->setTitle('4f8706cf6535d Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2011:02:18 03:43:43'));
$entry->setUpdated(new DateTime('2011:02:18 03:43:43'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'personal',
  1 => 'war',
  2 => 'literature',
  3 => 'conferences',
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

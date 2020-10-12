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

$entry->setId('4f8706cf65a64-post-name');
$entry->setTitle('4f8706cf65a64 Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2004:10:09 23:58:58'));
$entry->setUpdated(new DateTime('2004:10:09 23:58:58'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'holiday',
  1 => 'war',
  2 => 'literature',
  3 => 'children',
  4 => 'draft',
  5 => 'conferences',
  6 => 'php',
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

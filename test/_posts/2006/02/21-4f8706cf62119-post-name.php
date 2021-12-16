<?php
use PhlyBlog\AuthorEntity;
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();
$author = new AuthorEntity();
$author->fromArray(array (
  'id' => 'aamilne',
  'name' => 'A.A. Milne',
  'email' => 'a.a@milne.com',
  'url' => 'http://milne.com',
));

$entry->setId('4f8706cf62119-post-name');
$entry->setTitle('4f8706cf62119 Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2006:02:21 19:06:06'));
$entry->setUpdated(new DateTime('2006:02:21 19:06:06'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'holiday',
  1 => 'personal',
  2 => 'thoughts',
  3 => 'literature',
  4 => 'children',
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

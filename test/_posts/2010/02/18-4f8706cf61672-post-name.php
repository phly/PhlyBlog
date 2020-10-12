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

$entry->setId('4f8706cf61672-post-name');
$entry->setTitle('4f8706cf61672 Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(false);
$entry->setCreated(new DateTime('2010:01:18 16:33:33'));
$entry->setUpdated(new DateTime('2010:02:18 16:33:33'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'personal',
  1 => 'programming',
  2 => 'thoughts',
  3 => 'war',
  4 => 'literature',
  5 => 'children',
  6 => 'conferences',
  7 => 'php',
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

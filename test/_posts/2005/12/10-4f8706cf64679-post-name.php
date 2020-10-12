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

$entry->setId('4f8706cf64679-post-name');
$entry->setTitle('4f8706cf64679 Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2005:12:10 18:25:25'));
$entry->setUpdated(new DateTime('2005:12:10 18:25:25'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'programming',
  1 => 'thoughts',
  2 => 'war',
  3 => 'literature',
  4 => 'children',
  5 => 'draft',
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

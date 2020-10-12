<?php
use PhlyBlog\AuthorEntity;
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();
$author = new AuthorEntity();
$author->fromArray(array (
  'id' => 'jdoe',
  'name' => 'John Doe',
  'email' => 'john@doe.com',
  'url' => 'http://john.doe.com',
));

$entry->setId('4f8706cf6201d-post-name');
$entry->setTitle('4f8706cf6201d Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2011:01:05 17:47:47'));
$entry->setUpdated(new DateTime('2011:01:05 17:47:47'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'holiday',
  1 => 'personal',
  2 => 'programming',
  3 => 'literature',
  4 => 'children',
  5 => 'draft',
  6 => 'conferences',
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

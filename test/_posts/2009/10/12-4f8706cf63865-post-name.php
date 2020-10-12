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

$entry->setId('4f8706cf63865-post-name');
$entry->setTitle('4f8706cf63865 Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2009:10:12 08:36:36'));
$entry->setUpdated(new DateTime('2009:10:12 08:36:36'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'holiday',
  1 => 'personal',
  2 => 'programming',
  3 => 'thoughts',
  4 => 'war',
  5 => 'literature',
  6 => 'children',
  7 => 'conferences',
  8 => 'php',
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

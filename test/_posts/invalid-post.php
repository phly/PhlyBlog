<?php
use PhlyBlog\AuthorEntity;
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();
$author = new AuthorEntity();

$entry->setId(''); // invalid id
$entry->setTitle('Invalid Post');
$entry->setAuthor('custer');
$entry->setDraft('foo'); // invalid flag
$entry->setPublic('bar'); // invalid flag
$entry->setCreated(new DateTime('2004:01:24 11:45:45'));
$entry->setUpdated(new DateTime('2004:01:24 11:45:45'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'holiday',
  1 => 'personal',
  2 => 'programming',
  3 => 'thoughts',
  4 => 'war',
  5 => 'literature',
  6 => 'children',
  7 => 'draft',
  8 => 'conferences',
  9 => 'php',
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

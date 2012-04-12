<?php
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();

$entry->setId('4f8706cf63ebc-post-name');
$entry->setTitle('4f8706cf63ebc Post');
$entry->setAuthor('aamilne');
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2005:10:15 21:47:47'));
$entry->setUpdated(new DateTime('2005:10:15 21:47:47'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'holiday',
  1 => 'thoughts',
  2 => 'war',
  3 => 'literature',
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


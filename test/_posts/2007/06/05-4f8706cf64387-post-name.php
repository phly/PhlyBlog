<?php
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();

$entry->setId('4f8706cf64387-post-name');
$entry->setTitle('4f8706cf64387 Post');
$entry->setAuthor('crazyhorse');
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2007:06:05 10:47:47'));
$entry->setUpdated(new DateTime('2007:06:05 10:47:47'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'personal',
  1 => 'thoughts',
  2 => 'war',
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


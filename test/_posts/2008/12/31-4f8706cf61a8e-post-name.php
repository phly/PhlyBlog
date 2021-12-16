<?php
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();

$entry->setId('4f8706cf61a8e-post-name');
$entry->setTitle('4f8706cf61a8e Post');
$entry->setAuthor('jdoe');
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2008:12:31 05:55:55'));
$entry->setUpdated(new DateTime('2008:12:31 05:55:55'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'holiday',
  1 => 'personal',
  2 => 'programming',
  3 => 'thoughts',
  4 => 'literature',
  5 => 'children',
  6 => 'draft',
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


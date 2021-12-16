<?php
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();

$entry->setId('4f8706cf634a3-post-name');
$entry->setTitle('4f8706cf634a3 Post');
$entry->setAuthor('custer');
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2008:02:14 07:03:03'));
$entry->setUpdated(new DateTime('2008:02:14 07:03:03'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'holiday',
  1 => 'personal',
  2 => 'programming',
  3 => 'thoughts',
  4 => 'war',
  5 => 'children',
  6 => 'draft',
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


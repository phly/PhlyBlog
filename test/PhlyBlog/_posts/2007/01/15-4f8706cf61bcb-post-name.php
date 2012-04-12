<?php
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();

$entry->setId('4f8706cf61bcb-post-name');
$entry->setTitle('4f8706cf61bcb Post');
$entry->setAuthor('jdoe');
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2007:01:15 16:29:29'));
$entry->setUpdated(new DateTime('2007:01:15 16:29:29'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'holiday',
  1 => 'programming',
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


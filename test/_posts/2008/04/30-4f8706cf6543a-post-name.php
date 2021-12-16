<?php
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();

$entry->setId('4f8706cf6543a-post-name');
$entry->setTitle('4f8706cf6543a Post');
$entry->setAuthor('aamilne');
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2008:04:30 16:11:11'));
$entry->setUpdated(new DateTime('2008:04:30 16:11:11'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'holiday',
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


<?php
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();

$entry->setId('4f8706cf64f50-post-name');
$entry->setTitle('4f8706cf64f50 Post');
$entry->setAuthor('aamilne');
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2004:11:23 22:37:37'));
$entry->setUpdated(new DateTime('2004:11:23 22:37:37'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'war',
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


<?php
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();

$entry->setId('4f8706cf65caf-post-name');
$entry->setTitle('4f8706cf65caf Post');
$entry->setAuthor('crazyhorse');
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2006:04:11 02:19:19'));
$entry->setUpdated(new DateTime('2006:04:11 02:19:19'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'holiday',
  1 => 'programming',
  2 => 'thoughts',
  3 => 'literature',
  4 => 'draft',
  5 => 'conferences',
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


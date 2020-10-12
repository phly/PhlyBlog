<?php
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();

$entry->setId('4f8706cf657a4-post-name');
$entry->setTitle('4f8706cf657a4 Post');
$entry->setAuthor('custer');
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2008:06:09 15:20:20'));
$entry->setUpdated(new DateTime('2008:06:09 15:20:20'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'programming',
  1 => 'thoughts',
  2 => 'children',
  3 => 'conferences',
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


<?php
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();

$entry->setId('4f8706cf65c18-post-name');
$entry->setTitle('4f8706cf65c18 Post');
$entry->setAuthor('custer');
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2010:07:01 05:31:31'));
$entry->setUpdated(new DateTime('2010:07:01 05:31:31'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'programming',
  1 => 'literature',
  2 => 'children',
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


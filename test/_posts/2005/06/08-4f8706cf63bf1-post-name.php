<?php
use PhlyBlog\AuthorEntity;
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();
$author = new AuthorEntity();
$author->fromArray(array (
  'id' => 'crazyhorse',
  'name' => 'Crazy Horse',
  'email' => 'crazyhorse@siouxnation.org',
  'url' => 'http://crazyhorse.siouxnation.org',
));

$entry->setId('4f8706cf63bf1-post-name');
$entry->setTitle('4f8706cf63bf1 Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2005:06:08 03:42:42'));
$entry->setUpdated(new DateTime('2005:06:08 03:42:42'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'programming',
  1 => 'thoughts',
  2 => 'draft',
  3 => 'php',
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

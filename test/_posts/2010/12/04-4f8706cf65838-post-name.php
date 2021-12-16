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

$entry->setId('4f8706cf65838-post-name');
$entry->setTitle('4f8706cf65838 Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2010:12:04 12:27:27'));
$entry->setUpdated(new DateTime('2010:12:04 12:27:27'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'children',
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

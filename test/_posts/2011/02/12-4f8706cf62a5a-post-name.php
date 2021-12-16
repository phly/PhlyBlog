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

$entry->setId('4f8706cf62a5a-post-name');
$entry->setTitle('4f8706cf62a5a Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2011:02:12 16:20:20'));
$entry->setUpdated(new DateTime('2011:02:12 16:20:20'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'holiday',
  1 => 'war',
  2 => 'children',
  3 => 'conferences',
  4 => 'php',
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

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

$entry->setId('4f8706cf65d49-post-name');
$entry->setTitle('4f8706cf65d49 Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2011:12:09 09:40:40'));
$entry->setUpdated(new DateTime('2011:12:09 09:40:40'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'personal',
  1 => 'programming',
  2 => 'war',
  3 => 'children',
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

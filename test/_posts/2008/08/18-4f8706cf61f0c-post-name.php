<?php
use PhlyBlog\AuthorEntity;
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();
$author = new AuthorEntity();
$author->fromArray(array (
  'id' => 'custer',
  'name' => 'George Armstrong Custer',
  'email' => 'me@gacuster.com',
  'url' => 'http://www.gacuster.com',
));

$entry->setId('4f8706cf61f0c-post-name');
$entry->setTitle('4f8706cf61f0c Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2008:08:18 11:38:38'));
$entry->setUpdated(new DateTime('2008:08:18 11:38:38'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'personal',
  1 => 'thoughts',
  2 => 'conferences',
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

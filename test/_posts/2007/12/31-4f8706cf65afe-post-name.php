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

$entry->setId('4f8706cf65afe-post-name');
$entry->setTitle('4f8706cf65afe Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2007:12:31 23:08:08'));
$entry->setUpdated(new DateTime('2007:12:31 23:08:08'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'holiday',
  1 => 'draft',
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

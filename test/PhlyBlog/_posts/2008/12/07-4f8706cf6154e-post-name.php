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

$entry->setId('4f8706cf6154e-post-name');
$entry->setTitle('4f8706cf6154e Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(false);
$entry->setCreated(new DateTime('2008:12:07 15:36:36'));
$entry->setUpdated(new DateTime('2008:12:07 15:36:36'));
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

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

$entry->setId('4f8706cf6307b-post-name');
$entry->setTitle('4f8706cf6307b Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2004:05:22 09:38:38'));
$entry->setUpdated(new DateTime('2004:05:22 09:38:38'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'thoughts',
  1 => 'literature',
  2 => 'children',
  3 => 'draft',
  4 => 'conferences',
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

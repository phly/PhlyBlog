<?php
use PhlyBlog\AuthorEntity;
use PhlyBlog\EntryEntity;

$entry  = new EntryEntity();
$author = new AuthorEntity();
$author->fromArray(array (
  'id' => 'aamilne',
  'name' => 'A.A. Milne',
  'email' => 'a.a@milne.com',
  'url' => 'http://milne.com',
));

$entry->setId('4f8706cf64157-post-name');
$entry->setTitle('4f8706cf64157 Post');
$entry->setAuthor($author);
$entry->setDraft(false);
$entry->setPublic(true);
$entry->setCreated(new DateTime('2004:08:09 20:03:03'));
$entry->setUpdated(new DateTime('2004:08:09 20:03:03'));
$entry->setTimezone('America/Chicago');
$entry->setTags(array (
  0 => 'personal',
  1 => 'war',
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

<?php

namespace PhlyBlogTest;

use DateTime;
use PhlyBlog\EntryEntity;
use PhlyBlog\Filter\EntryFilter;
use PHPUnit\Framework\TestCase;

use function array_values;
use function count;
use function strtotime;
use function var_export;

class EntryEntityTest extends TestCase
{
    /** @var EntryEntity */
    private $entry;

    protected function setUp(): void
    {
        $this->entry = new EntryEntity();
    }

    public function testUsesEntryFilterAsDefaultFilter(): void
    {
        $filter = $this->entry->getInputFilter();
        self::assertInstanceOf(EntryFilter::class, $filter);
    }

    public function testSettingTitleSetsId(): void
    {
        $this->entry->setTitle('Foo Bar');
        self::assertEquals('foo-bar', $this->entry->getId());
    }

    public function testAcceptsStringsForCreatedTimestamps(): void
    {
        $this->entry->setCreated('today');
        $expected = strtotime('today');
        self::assertEquals($expected, $this->entry->getCreated());
    }

    public function testAcceptsIntegersForCreatedTimestamps(): void
    {
        $expected = strtotime('2010-12-29 15:39Z-0500');
        $this->entry->setCreated($expected);
        self::assertEquals($expected, $this->entry->getCreated());
    }

    public function testAcceptsDateTimeForCreatedTimestamps(): void
    {
        $date = new DateTime('today');
        $this->entry->setCreated($date);
        self::assertEquals($date->getTimestamp(), $this->entry->getCreated());
    }

    public function testAcceptsStringsForUpdatedTimestamps(): void
    {
        $this->entry->setUpdated('today');
        $expected = strtotime('today');
        self::assertEquals($expected, $this->entry->getUpdated());
    }

    public function testAcceptsIntegersForUpdatedTimestamps(): void
    {
        $expected = strtotime('2010-12-29 15:39Z-0500');
        $this->entry->setUpdated($expected);
        self::assertEquals($expected, $this->entry->getUpdated());
    }

    public function testAcceptsDateTimeForUpdatedTimestamps(): void
    {
        $date = new DateTime('today');
        $this->entry->setUpdated($date);
        self::assertEquals($date->getTimestamp(), $this->entry->getUpdated());
    }

    public function testAmericaNewYorkIsDefaultTimezone(): void
    {
        self::assertEquals('America/New_York', $this->entry->getTimezone());
    }

    public function testIsDraftByDefault(): void
    {
        self::assertTrue($this->entry->isDraft());
    }

    public function testIsPublicByDefault(): void
    {
        self::assertTrue($this->entry->isPublic());
    }

    public function testNoTagsByDefault(): void
    {
        self::assertEquals([], $this->entry->getTags());
    }

    public function testCanAddManyTagsAtOnce(): void
    {
        $this->entry->setTags(['foo', 'bar', 'baz']);
        self::assertEquals(['foo', 'bar', 'baz'], $this->entry->getTags());
    }

    public function testCallingSetTagsMultipleTimesOverwrites(): void
    {
        $this->entry->setTags(['foo', 'bar', 'baz']);
        $this->entry->setTags(['oof', 'rab', 'zab']);
        self::assertEquals(['oof', 'rab', 'zab'], $this->entry->getTags());
    }

    public function testCanAddTagsOneAtATime(): void
    {
        $this->entry->setTags(['foo'])
            ->addTag('baz')
            ->addTag('bar');
        self::assertEquals(['foo', 'baz', 'bar'], $this->entry->getTags());
    }

    public function testCanRemoveSingleTags(): void
    {
        $this->entry->setTags(['foo', 'bar', 'baz']);
        $this->entry->removeTag('bar');
        self::assertEquals(
            ['foo', 'baz'],
            array_values($this->entry->getTags())
        );
    }

    public function testCanPopulateFromArray(): void
    {
        $this->loadFromArray();
        self::assertEquals('foo-bar', $this->entry->getId());
        self::assertEquals('Foo Bar', $this->entry->getTitle());
        self::assertEquals('Foo bar. Baz. Bat bedat.', $this->entry->getBody());
        self::assertEquals('matthew', $this->entry->getAuthor());
        self::assertTrue($this->entry->isDraft());
        self::assertFalse($this->entry->isPublic());
        self::assertEquals(strtotime('today'), $this->entry->getCreated());
        self::assertEquals(strtotime('today'), $this->entry->getUpdated());
        self::assertEquals('America/Chicago', $this->entry->getTimezone());
        self::assertEquals(['foo', 'bar'], $this->entry->getTags());
    }

    public function testCanSerializeToArray(): void
    {
        $this->loadFromArray();
        $values   = $this->entry->toArray();
        $expected = [
            'id'        => 'foo-bar',
            'title'     => 'Foo Bar',
            'body'      => 'Foo bar. Baz. Bat bedat.',
            'author'    => 'matthew',
            'is_draft'  => true,
            'is_public' => false,
            'created'   => strtotime('today'),
            'updated'   => strtotime('today'),
            'timezone'  => 'America/Chicago',
            'tags'      => ['foo', 'bar'],
        ];
        foreach ($expected as $key => $value) {
            self::assertEquals($value, $values[$key]);
        }
    }

    public function testOverloadingOfProperties(): void
    {
        $this->loadFromArray();
        self::assertTrue(isset($this->entry->id));
        self::assertTrue(isset($this->entry->title));
        self::assertTrue(isset($this->entry->body));
        self::assertTrue(isset($this->entry->author));
        self::assertTrue(isset($this->entry->isDraft));
        self::assertTrue(isset($this->entry->isPublic));
        self::assertTrue(isset($this->entry->created));
        self::assertTrue(isset($this->entry->updated));
        self::assertTrue(isset($this->entry->timezone));
        self::assertTrue(isset($this->entry->tags));
        self::assertEquals('foo-bar', $this->entry->id);
        self::assertEquals('Foo Bar', $this->entry->title);
        self::assertEquals('Foo bar. Baz. Bat bedat.', $this->entry->body);
        self::assertEquals('matthew', $this->entry->author);
        self::assertTrue($this->entry->isDraft);
        self::assertFalse($this->entry->isPublic);
        self::assertEquals(strtotime('today'), $this->entry->created);
        self::assertEquals(strtotime('today'), $this->entry->updated);
        self::assertEquals('America/Chicago', $this->entry->timezone);
        self::assertEquals(['foo', 'bar'], $this->entry->tags);
    }

    public function testValidationFailsInitially(): void
    {
        self::assertFalse($this->entry->isValid());
    }

    public function testValidEntryValidates(): void
    {
        $this->loadFromArray();
        $valid    = $this->entry->isValid();
        $messages = $this->entry->getInputFilter()->getMessages();
        self::assertTrue($valid, var_export($messages, 1));
    }

    public function testInputFilterOverwritesValuesWithFilteredVersions(): void
    {
        $this->loadFromArray();
        $this->entry->setTitle('foo & bar')
            ->setId('foo-bar')
            ->setDraft(0)
            ->setPublic('')
            ->setBody('  Foo Bar. ')
            ->setAuthor(' matthew ');
        self::assertTrue($this->entry->isValid());
        self::assertEquals('foo & bar', $this->entry->getTitle());
        self::assertFalse($this->entry->isDraft());
        self::assertFalse($this->entry->isPublic());
        self::assertEquals('Foo Bar.', $this->entry->getBody());
        self::assertEquals('matthew', $this->entry->getAuthor());
    }

    public function testVersionIs2ByDefault(): void
    {
        self::assertEquals(2, $this->entry->getVersion());
    }

    public function testSerializingVersion1EntryIncludesComments(): void
    {
        $this->loadFromArray();
        $this->entry->setVersion(1);
        $data = $this->entry->toArray();
        self::assertArrayHasKey('comments', $data);
        self::assertEquals(2, count($data['comments']));
        foreach ($data['comments'] as $comment) {
            self::assertIsArray($comment);
        }
    }

    public function loadFromArray(): void
    {
        $this->entry->fromArray(
            [
                'id'        => 'foo-bar',
                'title'     => 'Foo Bar',
                'body'      => 'Foo bar. Baz. Bat bedat.',
                'author'    => 'matthew',
                'is_draft'  => true,
                'is_public' => false,
                'created'   => strtotime('today'),
                'updated'   => strtotime('today'),
                'timezone'  => 'America/Chicago',
                'tags'      => ['foo', 'bar'],
                'comments'  => [
                    [
                        'created'  => strtotime('today'),
                        'timezone' => 'America/Chicago',
                        'title'    => 'comment',
                        'author'   => 'somebody',
                        'type'     => 'comment',
                    ],
                    [
                        'created'  => strtotime('today'),
                        'timezone' => 'America/Chicago',
                        'title'    => 'trackback',
                        'type'     => 'trackback',
                        'url'      => 'http://example.com/foo',
                    ],
                ],
            ]
        );
    }
}

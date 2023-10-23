<?php

namespace Dontdrinkandroot\ActivityPubOrmBundle\Tests\TestApp\DataFixtures\LocalObject;

use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Dontdrinkandroot\ActivityPubCoreBundle\Model\Type\Extended\Object\Note;
use Dontdrinkandroot\ActivityPubCoreBundle\Model\Type\Linkable\LinkableObjectsCollection;
use Dontdrinkandroot\ActivityPubCoreBundle\Model\Type\Property\Source;
use Dontdrinkandroot\ActivityPubCoreBundle\Model\Type\Property\Uri;
use Dontdrinkandroot\ActivityPubCoreBundle\Serializer\ActivityStreamEncoder;
use Dontdrinkandroot\ActivityPubOrmBundle\Entity\RawType;
use Dontdrinkandroot\ActivityPubOrmBundle\Tests\TestApp\DataFixtures\LocalActor\Person;
use Dontdrinkandroot\ActivityPubOrmBundle\Tests\TestApp\Entity\LocalActor;
use Dontdrinkandroot\ActivityPubOrmBundle\Tests\TestApp\Entity\LocalNote;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

class PersonNote1 extends Fixture implements DependentFixtureInterface
{
    public const UUID = 'a971cbac-fb77-4192-821f-cc08b706c86e';
    public const URI = 'http://localhost/notes/a971cbac-fb77-4192-821f-cc08b706c86e';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly SerializerInterface $serializer,
    )
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [Person::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        $localActor = $this->getReference(Person::class, LocalActor::class);

        $sourceContent = 'This is the content';
        $source = new Source(
            content: $sourceContent,
            mediaType: 'text/markdown'
        );

        $activityPubNote = new Note();
        $activityPubNote->id = Uri::fromString(self::URI);
        $activityPubNote->published = new DateTime('2001-02-03 04:05:06');
        $activityPubNote->attributedTo = LinkableObjectsCollection::singleLinkFromUri(
            Uri::fromString('http://localhost/@person')
        );
        $activityPubNote->to = LinkableObjectsCollection::singleLinkFromUri(
            Uri::fromString('https://www.w3.org/ns/activitystreams#Public')
        );
        $activityPubNote->cc = LinkableObjectsCollection::singleLinkFromUri(
            Uri::fromString('http://localhost/@person/followers')
        );
        $activityPubNote->content = '<p>This is the content</p>';
        $activityPubNote->source = $source;

        $uri = $this->urlGenerator->generate(
            'ddr.activity_pub_orm.tests.note.get',
            ['uuid' => self::UUID],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $serialized = $this->serializer->serialize($activityPubNote, ActivityStreamEncoder::FORMAT);
        $localObject = new LocalNote(
            uri: Uri::fromString($uri),
            attributedTo: $localActor,
            uuid: Uuid::fromString(self::UUID),
            source: $sourceContent,
            raw: new RawType($serialized)
        );

        $manager->persist($localObject);
        $manager->flush();
        $this->addReference(self::class, $localObject);
    }
}

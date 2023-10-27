<?php

namespace Dontdrinkandroot\ActivityPubOrmBundle\Tests\Integration\Service\Object;

use Dontdrinkandroot\ActivityPubCoreBundle\Model\Type\Extended\Actor\Actor;
use Dontdrinkandroot\ActivityPubCoreBundle\Model\Type\Extended\Actor\Person as ActivityPubPerson;
use Dontdrinkandroot\ActivityPubCoreBundle\Model\Type\Extended\Object\Note;
use Dontdrinkandroot\ActivityPubCoreBundle\Model\Type\Linkable\LinkableObjectsCollection;
use Dontdrinkandroot\ActivityPubCoreBundle\Model\Type\Property\Uri;
use Dontdrinkandroot\ActivityPubCoreBundle\Service\Client\ActivityPubClientInterface;
use Dontdrinkandroot\ActivityPubCoreBundle\Service\Object\ObjectResolverInterface;
use Dontdrinkandroot\ActivityPubOrmBundle\Entity\StoredActor;
use Dontdrinkandroot\ActivityPubOrmBundle\Entity\StoredObject;
use Dontdrinkandroot\ActivityPubOrmBundle\Repository\StoredActorRepository;
use Dontdrinkandroot\ActivityPubOrmBundle\Repository\StoredObjectRepository;
use Dontdrinkandroot\ActivityPubOrmBundle\Tests\TestApp\DataFixtures\FixtureSetDefault;
use Dontdrinkandroot\ActivityPubOrmBundle\Tests\TestApp\DataFixtures\LocalObject\PersonNote1;
use Dontdrinkandroot\ActivityPubOrmBundle\Tests\WebTestCase;

class ObjectResolverTest extends WebTestCase
{
    public function testResolveLocalNote(): void
    {
        self::bootKernel();
        self::loadFixtures([FixtureSetDefault::class]);

        /* Client must not be called, object is resolved directly */
        $clientMock = $this->createMock(ActivityPubClientInterface::class);
        $clientMock->expects(self::never())->method('request');
        self::getContainer()->set(ActivityPubClientInterface::class, $clientMock);

        $objectResolver = self::getService(ObjectResolverInterface::class);
        $uri = Uri::fromString(PersonNote1::URI);
        $note = $objectResolver->resolveTyped($uri, Note::class);

        self::assertNotNull($note);
        self::assertTrue($uri->equals($note->getId()));
    }

    public function testResolveLocalActor(): void
    {
        self::bootKernel();
        self::loadFixtures([FixtureSetDefault::class]);

        /* Client must not be called, object is resolved directly */
        $clientMock = $this->createMock(ActivityPubClientInterface::class);
        $clientMock->expects(self::never())->method('request');
        self::getContainer()->set(ActivityPubClientInterface::class, $clientMock);

        $objectResolver = self::getService(ObjectResolverInterface::class);
        $uri = Uri::fromString('https://localhost/@person');
        $person = $objectResolver->resolveTyped($uri, Actor::class);

        self::assertNotNull($person);
        self::assertTrue($uri->equals($person->getId()));
    }

    public function testRemoteNote(): void
    {
        self::bootKernel();
        self::loadFixtures([FixtureSetDefault::class]);

        $uri = Uri::fromString('https://example.com/note/1');

        $note = new Note();
        $note->id = $uri;
        $note->content = 'RemoteNote Content';
        $note->attributedTo = LinkableObjectsCollection::singleLinkFromUri(
            Uri::fromString('https://example.com/actor/1')
        );

        $clientMock = $this->createMock(ActivityPubClientInterface::class);
        $clientMock->expects(self::once())->method('request')->willReturn($note);
        self::getContainer()->set(ActivityPubClientInterface::class, $clientMock);

        $objectResolver = self::getService(ObjectResolverInterface::class);
        $resolvedNote = $objectResolver->resolve($uri);

        self::assertNotNull($resolvedNote);
        self::assertTrue($uri->equals($resolvedNote->getId()));

        $storedObjectRepository = self::getService(StoredObjectRepository::class);
        $storedObject = $storedObjectRepository->findOneByUri($uri);
        self::assertNotNull($storedObject);
        self::assertInstanceOf(StoredObject::class, $storedObject);
    }

    public function testRemoteActor(): void
    {
        self::bootKernel();
        self::loadFixtures([FixtureSetDefault::class]);

        $uri = Uri::fromString('https://example.com/actor/test');

        $actor = new ActivityPubPerson();
        $actor->id = $uri;
        $actor->name = 'Test Actor';
        $actor->preferredUsername = 'test';

        $clientMock = $this->createMock(ActivityPubClientInterface::class);
        $clientMock->expects(self::once())->method('request')->willReturn($actor);
        self::getContainer()->set(ActivityPubClientInterface::class, $clientMock);

        $objectResolver = self::getService(ObjectResolverInterface::class);
        $resolvedActor = $objectResolver->resolve($uri);

        self::assertNotNull($resolvedActor);
        self::assertTrue($uri->equals($resolvedActor->getId()));

        $actorRepository = self::getService(StoredActorRepository::class);
        $storedActor = $actorRepository->findOneByUri($uri);
        self::assertNotNull($storedActor);
        self::assertInstanceOf(StoredActor::class, $storedActor);
    }
}

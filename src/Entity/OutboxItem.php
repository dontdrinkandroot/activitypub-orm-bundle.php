<?php

namespace Dontdrinkandroot\ActivityPubOrmBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\ActivityPubCoreBundle\Model\LocalActorInterface;
use Dontdrinkandroot\ActivityPubOrmBundle\Repository\OutboxItemRepository;

#[ORM\Entity(repositoryClass: OutboxItemRepository::class)]
#[ORM\Table('outbox')]

class OutboxItem
{
    use EntityTrait;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: LocalActorInterface::class)]
        #[ORM\JoinColumn(name: 'local_actor_id', nullable: false)]
        public readonly LocalActorInterface $localActor,

        #[ORM\ManyToOne(targetEntity: CoreObject::class)]
        #[ORM\JoinColumn(name: 'activity_id', nullable: false)]
        public readonly CoreObject $activity,

        #[ORM\Column(type: Types::BIGINT)]
        public int $created
    ) {
    }
}

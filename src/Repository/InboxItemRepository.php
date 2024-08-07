<?php

namespace Dontdrinkandroot\ActivityPubOrmBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Dontdrinkandroot\ActivityPubOrmBundle\Entity\InboxItem;

/**
 * @extends CrudServiceEntityRepository<InboxItem>
 */
class InboxItemRepository extends CrudServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InboxItem::class);
    }
}

<?php

namespace Dontdrinkandroot\ActivityPubOrmBundle\Service;

use Dontdrinkandroot\ActivityPubCoreBundle\Model\Type\Linkable\LinkableObject;
use Dontdrinkandroot\ActivityPubCoreBundle\Model\Type\Property\Uri;
use Dontdrinkandroot\ActivityPubCoreBundle\Service\Share\ShareServiceInterface;
use Dontdrinkandroot\ActivityPubOrmBundle\Entity\Share;
use Dontdrinkandroot\ActivityPubOrmBundle\Repository\ShareRepository;
use Dontdrinkandroot\ActivityPubOrmBundle\Service\LocalObject\LocalObjectEntityResolverInterface;
use RuntimeException;

class ShareService implements ShareServiceInterface
{
    public function __construct(
        private readonly ShareRepository $shareRepository,
        private readonly LocalObjectEntityResolverInterface $localObjectEntityResolver
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function shared(Uri $remoteActorId, Uri $localObjectId): void
    {
        $localObject = $this->localObjectEntityResolver->resolve($localObjectId)
            ?? throw new RuntimeException('Local object not found');
        $share = new Share($remoteActorId, $localObject);
        $this->shareRepository->create($share);
    }

    /**
     * {@inheritdoc}
     */
    public function share(Uri $localActorId, LinkableObject $remoteObject): void
    {
        // TODO: Implement share() method.
        throw new RuntimeException(__FUNCTION__ . ' not implemented');
    }
}
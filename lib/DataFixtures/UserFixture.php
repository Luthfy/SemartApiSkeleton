<?php

declare(strict_types=1);

namespace KejawenLab\ApiSkeleton\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use KejawenLab\ApiSkeleton\Entity\User;

/**
 * @author Muhamad Surya Iksanudin<surya.kejawen@gmail.com>
 */
final class UserFixture extends AbstractFixture implements DependentFixtureInterface
{
    protected function createNew(): User
    {
        return new User();
    }

    protected function getReferenceKey(): string
    {
        return 'user';
    }

    public function getDependencies(): array
    {
        return [
            GroupFixture::class,
        ];
    }
}

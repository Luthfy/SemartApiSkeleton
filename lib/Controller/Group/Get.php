<?php

declare(strict_types=1);

namespace KejawenLab\ApiSkeleton\Controller\Group;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use KejawenLab\ApiSkeleton\Entity\Group;
use KejawenLab\ApiSkeleton\Security\Annotation\Permission;
use KejawenLab\ApiSkeleton\Security\Service\GroupService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Permission(menu="GROUP", actions={Permission::VIEW})
 *
 * @author Muhamad Surya Iksanudin<surya.kejawen@gmail.com>
 */
final class Get extends AbstractFOSRestController
{
    public function __construct(private GroupService $service)
    {
    }

    /**
     * @Rest\Get("/groups/{id}")
     *
     * @OA\Tag(name="Group")
     * @OA\Response(
     *     response=200,
     *     description= "Api client detail",
     *     content={
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 ref=@Model(type=Group::class, groups={"read"})
     *             )
     *         )
     *     }
     * )
     *
     * @Security(name="Bearer")
     */
    public function __invoke(Request $request, string $id): View
    {
        return $this->view($this->service->get($id));
    }
}

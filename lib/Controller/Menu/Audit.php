<?php

declare(strict_types=1);

namespace KejawenLab\ApiSkeleton\Controller\Menu;

use DH\Auditor\Provider\Doctrine\Persistence\Reader\Reader;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use KejawenLab\ApiSkeleton\Audit\AuditService;
use KejawenLab\ApiSkeleton\Entity\Menu;
use KejawenLab\ApiSkeleton\Security\Annotation\Permission;
use KejawenLab\ApiSkeleton\Security\Service\MenuService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Permission(menu="AUDIT", actions={Permission::VIEW})
 *
 * @author Muhamad Surya Iksanudin<surya.kejawen@gmail.com>
 */
final class Audit extends AbstractFOSRestController
{
    private MenuService $service;

    private AuditService $audit;

    private Reader $reader;

    public function __construct(MenuService $service, AuditService $audit, Reader $reader)
    {
        $this->service = $service;
        $this->audit = $audit;
        $this->reader = $reader;
    }

    /**
     * @Rest\Get("/menus/{id}/audit", priority=-255)
     *
     * @Cache(expires="+17 minute", public=false)
     *
     * @OA\Tag(name="Menu")
     * @OA\Response(
     *     response=200,
     *     description= "Audit list",
     *     content={
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     properties={
     *                         @OA\Property(
     *                             property="entity",
     *                             type="object",
     *                             @OA\Schema(
     *                                 type="object",
     *                                 ref=@Model(type=Menu::class, groups={"read"})
     *                             )
     *                         ),
     *                         @OA\Property(type="string", property="type"),
     *                         @OA\Property(type="string", property="user_id"),
     *                         @OA\Property(type="string", property="username"),
     *                         @OA\Property(type="string", property="ip_address"),
     *                         @OA\Property(
     *                             type="array",
     *                             property="data",
     *                             @OA\Items(
     *                                 @OA\Property(type="string", property="new"),
     *                                 @OA\Property(type="string", property="old"),
     *                             )
     *                         )
     *                     }
     *                 )
     *             )
     *         )
     *     }
     * )
     *
     * @Security(name="Bearer")
     */
    public function __invoke(Request $request, string $id): View
    {
        if (!$entity = $this->service->get($id)) {
            throw new NotFoundHttpException();
        }

        if (!$this->reader->getProvider()->isAuditable(Menu::class)) {
            return $this->view([]);
        }

        return $this->view($this->audit->getAudits($entity, $id)->toArray());
    }
}

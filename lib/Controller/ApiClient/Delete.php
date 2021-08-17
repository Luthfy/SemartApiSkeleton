<?php

declare(strict_types=1);

namespace KejawenLab\ApiSkeleton\Controller\ApiClient;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use KejawenLab\ApiSkeleton\ApiClient\ApiClientService;
use KejawenLab\ApiSkeleton\ApiClient\Model\ApiClientInterface;
use KejawenLab\ApiSkeleton\Security\Annotation\Permission;
use KejawenLab\ApiSkeleton\Security\Service\UserService;
use KejawenLab\ApiSkeleton\Security\User;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Permission(menu="APICLIENT", actions={Permission::DELETE})
 *
 * @author Muhamad Surya Iksanudin<surya.iksanudin@gmail.com>
 */
final class Delete extends AbstractFOSRestController
{
    public function __construct(private ApiClientService $service, private UserService $userService)
    {
    }

    /**
     * @Rest\Delete("/users/{userId}/api-clients/{id}", name=Delete::class)
     *
     * @OA\Tag(name="Api Client")
     * @OA\Response(
     *     response=204,
     *     description="Api client deletec"
     * )
     *
     * @Security(name="Bearer")
     */
    public function __invoke(Request $request, string $userId, string $id): View
    {
        $user = $this->userService->get($userId);
        if (!$user instanceof User) {
            throw new NotFoundHttpException(sprintf('User ID: "%s" not found', $userId));
        }

        $client = $this->service->get($id);
        if (!$client instanceof ApiClientInterface) {
            throw new NotFoundHttpException(sprintf('Api Client ID: "%s" not found', $id));
        }

        $this->service->remove($client);

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}

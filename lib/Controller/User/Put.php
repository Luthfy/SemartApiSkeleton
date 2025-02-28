<?php

declare(strict_types=1);

namespace KejawenLab\ApiSkeleton\Controller\User;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Put as Route;
use FOS\RestBundle\View\View;
use KejawenLab\ApiSkeleton\Entity\User;
use KejawenLab\ApiSkeleton\Form\FormFactory;
use KejawenLab\ApiSkeleton\Form\UpdateUserType;
use KejawenLab\ApiSkeleton\Security\Annotation\Permission;
use KejawenLab\ApiSkeleton\Security\Model\UserInterface;
use KejawenLab\ApiSkeleton\Security\Service\UserService;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Tag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Muhamad Surya Iksanudin<surya.iksanudin@gmail.com>
 */
#[Permission(menu: 'USER', actions: [Permission::EDIT])]
#[Tag(name: 'User')]
final class Put extends AbstractFOSRestController
{
    public function __construct(
        private readonly FormFactory         $formFactory,
        private readonly UserService         $service,
        private readonly TranslatorInterface $translator,
    )
    {
    }

    #[Route(data: '/users/{id}', name: self::class)]
    #[Security(name: 'Bearer')]
    #[RequestBody(
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(ref: new Model(type: UpdateUserType::class), type: 'object'),
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'User updated',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(ref: new Model(type: User::class, groups: ['read']), type: 'object'),
        ),
    )]
    public function __invoke(Request $request, string $id): View
    {
        $user = $this->service->get($id);
        if (!$user instanceof UserInterface) {
            throw new NotFoundHttpException($this->translator->trans('sas.page.user.not_found', [], 'pages'));
        }

        $form = $this->formFactory->submitRequest(UpdateUserType::class, $request, $user);
        if (!$form->isValid()) {
            return $this->view((array)$form->getErrors(), Response::HTTP_BAD_REQUEST);
        }

        $this->service->save($user);

        return $this->view($this->service->get($user->getId()));
    }
}

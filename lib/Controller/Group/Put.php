<?php

declare(strict_types=1);

namespace KejawenLab\ApiSkeleton\Controller\Group;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Put as Route;
use FOS\RestBundle\View\View;
use KejawenLab\ApiSkeleton\Entity\Group;
use KejawenLab\ApiSkeleton\Form\FormFactory;
use KejawenLab\ApiSkeleton\Form\GroupType;
use KejawenLab\ApiSkeleton\Security\Annotation\Permission;
use KejawenLab\ApiSkeleton\Security\Model\GroupInterface;
use KejawenLab\ApiSkeleton\Security\Service\GroupService;
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
#[Permission(menu: 'GROUP', actions: [Permission::EDIT])]
#[Tag(name: 'Group')]
final class Put extends AbstractFOSRestController
{
    public function __construct(
        private readonly FormFactory         $formFactory,
        private readonly GroupService        $service,
        private readonly TranslatorInterface $translator,
    )
    {
    }

    #[Route(data: '/groups/{id}', name: self::class)]
    #[Security(name: 'Bearer')]
    #[RequestBody(
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(ref: new Model(type: GroupType::class), type: 'object'),
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'Group updated',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(ref: new Model(type: Group::class, groups: ['read']), type: 'object'),
        ),
    )]
    public function __invoke(Request $request, string $id): View
    {
        $group = $this->service->get($id);
        if (!$group instanceof GroupInterface) {
            throw new NotFoundHttpException($this->translator->trans('sas.page.group.not_found', [], 'pages'));
        }

        $form = $this->formFactory->submitRequest(GroupType::class, $request, $group);
        if (!$form->isValid()) {
            return $this->view((array)$form->getErrors(), Response::HTTP_BAD_REQUEST);
        }

        $this->service->save($group);

        return $this->view($this->service->get($group->getId()));
    }
}

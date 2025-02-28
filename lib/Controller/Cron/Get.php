<?php

declare(strict_types=1);

namespace KejawenLab\ApiSkeleton\Controller\Cron;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get as Route;
use FOS\RestBundle\View\View;
use KejawenLab\ApiSkeleton\Cron\CronService;
use KejawenLab\ApiSkeleton\Cron\Model\CronInterface;
use KejawenLab\ApiSkeleton\Entity\Cron;
use KejawenLab\ApiSkeleton\Security\Annotation\Permission;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Tag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Muhamad Surya Iksanudin<surya.iksanudin@gmail.com>
 */
#[Permission(menu: 'CRON', actions: [Permission::VIEW])]
#[Tag(name: 'Cron')]
final class Get extends AbstractFOSRestController
{
    public function __construct(private readonly CronService $service, private readonly TranslatorInterface $translator)
    {
    }

    #[Route(data: '/cronjobs/{id}', name: self::class, priority: -7)]
    #[Security(name: 'Bearer')]
    #[Response(
        response: 200,
        description: 'Cron detail',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(ref: new Model(type: Cron::class, groups: ['read']), type: 'object'),
        ),
    )]
    public function __invoke(string $id): View
    {
        $cron = $this->service->get($id);
        if (!$cron instanceof CronInterface) {
            throw new NotFoundHttpException($this->translator->trans('sas.page.cron.not_found', [], 'pages'));
        }

        return $this->view($cron);
    }
}

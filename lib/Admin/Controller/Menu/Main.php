<?php

declare(strict_types=1);

namespace KejawenLab\ApiSkeleton\Admin\Controller\Menu;

use KejawenLab\ApiSkeleton\Admin\Controller\AbstractController;
use KejawenLab\ApiSkeleton\Entity\Menu;
use KejawenLab\ApiSkeleton\Form\MenuType;
use KejawenLab\ApiSkeleton\Pagination\Paginator;
use KejawenLab\ApiSkeleton\Security\Annotation\Permission;
use KejawenLab\ApiSkeleton\Security\Service\MenuService;
use KejawenLab\ApiSkeleton\Util\CacheFactory;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Muhamad Surya Iksanudin<surya.iksanudin@gmail.com>
 */
#[Permission(menu: 'MENU', actions: [Permission::VIEW])]
final class Main extends AbstractController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly MenuService  $service,
        private readonly CacheFactory $cache,
        private readonly Paginator    $paginator,
    )
    {
        parent::__construct($this->requestStack->getCurrentRequest(), $this->service, $this->cache, $this->paginator);
    }

    #[Route(path: '/menus', name: self::class, methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $menu = new Menu();
        if ($request->isMethod(Request::METHOD_POST)) {
            $id = $request->getSession()->get('id');
            if (null !== $id) {
                $menu = $this->service->get($id);
            }
        } else {
            $flashes = $request->getSession()->getFlashBag()->get('id');
            foreach ($flashes as $flash) {
                $menu = $this->service->get($flash);
                if (null !== $menu) {
                    $request->getSession()->set('id', $menu->getId());

                    break;
                }
            }
        }

        $form = $this->createForm(MenuType::class, $menu);
        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->service->save($form->getData());
                $this->addFlash('info', 'sas.page.menu.saved');

                $form = $this->createForm(MenuType::class);
            }
        }

        return $this->renderList($form, $request, new ReflectionClass(Menu::class));
    }
}

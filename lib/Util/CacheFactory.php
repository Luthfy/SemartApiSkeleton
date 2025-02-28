<?php

declare(strict_types=1);

namespace KejawenLab\ApiSkeleton\Util;

use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use KejawenLab\ApiSkeleton\SemartApiSkeleton;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Muhamad Surya Iksanudin<surya.iksanudin@gmail.com>
 */
final class CacheFactory
{
    public function __construct(
        private readonly RequestStack           $requestStack,
        private readonly CacheItemPoolInterface $cache,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function invalidQueryCache(): void
    {
        $configuration = $this->entityManager->getConfiguration();

        $configuration->getQueryCache()->clear();
        $configuration->getResultCache()->clear();
    }

    public function isDisableQueryCache(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request->query->get(SemartApiSkeleton::DISABLE_QUERY_CACHE_QUERY_STRING)) {
            return true;
        }

        return (bool)$request->attributes->get(SemartApiSkeleton::DISABLE_QUERY_CACHE_QUERY_STRING);
    }

    /**
     * @return mixed[]
     */
    public function getCache(string $key, string $namespace): array
    {
        $deviceId = $this->getDeviceId();
        if ('' === $deviceId) {
            return [];
        }

        $poolKey = sprintf('%s_%s', $deviceId, $namespace);
        $key = sprintf('%s_%s', $poolKey, $key);
        $pool = $this->cache->getItem($poolKey);
        if (!$pool->isHit()) {
            return [];
        }

        $keys = $pool->get();
        if (!\array_key_exists($key, $keys)) {
            return [];
        }

        return [
            'content' => $this->cache->getItem($key)->get(),
            'attribute' => $keys[$key],
        ];
    }

    private function getDeviceId(): string
    {
        $deviceId = $this->requestStack->getCurrentRequest()->getSession()->get(SemartApiSkeleton::USER_DEVICE_ID, '');
        if (SemartApiSkeleton::API_CLIENT_DEVICE_ID === $deviceId) {
            return '';
        }

        return $deviceId;
    }

    public function setCache(string $key, string $namespace, string $content, string|bool $attribute, string $period): void
    {
        if ('' === $content) {
            return;
        }

        $deviceId = $this->getDeviceId();
        if ('' === $deviceId) {
            return;
        }

        $poolKey = sprintf('%s_%s', $deviceId, $namespace);
        $key = sprintf('%s_%s', $poolKey, $key);

        $pool = $this->cache->getItem($poolKey);
        $keys = [];
        if ($pool->isHit()) {
            $keys = $pool->get();
        }

        $keys = array_merge($keys, [$key => $attribute]);

        $pool->set($keys);
        $pool->expiresAfter(new DateInterval($period));
        $this->cache->save($pool);

        $item = $this->cache->getItem($key);

        $item->set($content);
        $item->expiresAfter(new DateInterval($period));
        $this->cache->save($item);
    }

    public function invalidateCache(string $namespace): void
    {
        $deviceId = $this->getDeviceId();
        if ('' === $deviceId) {
            return;
        }

        $poolKey = sprintf('%s_%s', $deviceId, $namespace);
        $pool = $this->cache->getItem($poolKey);
        if (!$pool->isHit()) {
            return;
        }

        $keys = $pool->get();
        foreach ($keys as $key => $nothing) {
            $this->cache->deleteItem($key);
        }

        $this->cache->deleteItem($deviceId);
    }

    private function getCacheKey(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $flashBag = $request->getSession()->getFlashBag();
        $flashes = $flashBag->get('id');
        $id = '';
        foreach ($flashes as $flash) {
            $id = $flash;

            break;
        }

        if ('' !== $id) {
            $flashBag->set('id', $id);

            return '';
        }

        return sprintf('%s_%s', sha1($request->getPathInfo()), sha1(serialize($request->query->all())));
    }

    public function getPageCache(): array
    {
        $cacheKey = $this->getCacheKey();
        if ('' === $cacheKey) {
            return [];
        }

        $cacheItem = $this->cache->getItem($cacheKey);
        if (!$cacheItem->isHit()) {
            return [];
        }

        return $cacheItem->get();
    }

    public function invalidPageCache(): void
    {
        $cacheKey = $this->getCacheKey();
        if ('' === $cacheKey) {
            return;
        }

        $this->cache->deleteItem($cacheKey);
    }

    public function invalidViewCache(): void
    {
        $deviceId = $this->getDeviceId();
        if ('' === $deviceId) {
            return;
        }

        $this->cache->deleteItem($deviceId);
    }


    public function setPageCache(Response $response, string $period = 'PT10M'): void
    {
        $cacheKey = $this->getCacheKey();
        if ('' === $cacheKey) {
            return;
        }

        $cacheItem = $this->cache->getItem($cacheKey);
        $cacheItem->set([
            'content' => $response->getContent(),
            'headers' => $response->headers->all(),
            'status' => $response->getStatusCode(),
        ]);
        $cacheItem->expiresAfter(new DateInterval($period));

        $this->cache->save($cacheItem);
    }
}

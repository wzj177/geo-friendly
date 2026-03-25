<?php

declare(strict_types=1);

namespace GeoFriendly\Symfony\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * Service for discovering routes from Symfony routing
 */
class RouteDiscoverer
{
    private RouterInterface $router;
    private RequestStack $requestStack;

    public function __construct(RouterInterface $router, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    /**
     * Discover all public routes
     *
     * @param array<string> $excludePatterns Regex patterns to exclude
     * @return array<string> Array of URLs
     */
    public function discoverRoutes(array $excludePatterns = ['^_.*', '^api']): array
    {
        $urls = [];
        $routes = $this->router->getRouteCollection();

        $request = $this->requestStack->getCurrentRequest();
        $baseUrl = $request ? $request->getSchemeAndHttpHost() : 'http://localhost';

        foreach ($routes as $name => $route) {
            $path = $route->getPath();

            // Skip excluded patterns
            foreach ($excludePatterns as $pattern) {
                if (preg_match('#' . $pattern . '#', $path)) {
                    continue 2;
                }
            }

            // Skip routes with placeholders that can't be filled
            if (str_contains($path, '{')) {
                continue;
            }

            // Build full URL
            $url = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
            $urls[] = $url;
        }

        return array_unique($urls);
    }
}

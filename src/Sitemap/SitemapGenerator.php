<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace jgniecki\SitemapBundle\Sitemap;

use jgniecki\SitemapBundle\Sitemap\Attribute\Sitemap;
use jgniecki\SitemapBundle\Sitemap\Interface\RouteResolverInterface;
use jgniecki\SitemapBundle\Sitemap\Interface\ImageProviderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class SitemapGenerator
{
    private array $resolverIndex = [];
    public function __construct(
        private RouterInterface $router,
        #[TaggedIterator('sitemap.resolver')]
        iterable $resolvers = []
    ) {
        foreach ($resolvers as $resolver) {
            $this->resolverIndex[$resolver::class] = $resolver;
        }
    }

    public function generate(): array
    {
        $urls = [];

        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            $controller = $route->getDefault('_controller');
            $sitemapAttr = $this->getSitemapAttribute($route, $controller);

            if ($sitemapAttr) {
                $this->processRoute($routeName, $route, $sitemapAttr, $urls);
            }
        }

        return $urls;
    }

    private function processRoute(
        string $routeName,
        Route $route,
        Sitemap $sitemapAttr,
        array &$urls
    ): void {
        $pathVariables = $this->getPathVariables($route);

        if ($sitemapAttr->resolver && isset($this->resolverIndex[$sitemapAttr->resolver])) {
            $resolver = $this->resolverIndex[$sitemapAttr->resolver];

            if ($resolver->supports($routeName, $pathVariables)) {
                $this->processResolver($routeName, $pathVariables, $resolver, $sitemapAttr, $urls);
            }

            return;
        }

        if (empty($pathVariables)) {
            $this->addStaticUrl($routeName, $sitemapAttr, $urls);
            return;
        }

    }

    private function processResolver(
        string $routeName,
        array $pathVariables,
        RouteResolverInterface $resolver,
        Sitemap $sitemapAttr,
        array &$urls
    ): void {
        foreach ($resolver->resolve($routeName, $pathVariables) as $params) {
            $url = $this->router->generate($routeName, $params, UrlGeneratorInterface::ABSOLUTE_URL);
            $urlData = $this->createUrlData($url, $sitemapAttr);

            if ($resolver instanceof ImageProviderInterface) {
                $urlData['images'] = array_merge(
                    $urlData['images'],
                    $resolver->getImages($params)
                );
            }

            $urls[] = $urlData;
        }
    }

    private function addStaticUrl(string $routeName, Sitemap $sitemapAttr, array &$urls): void
    {
        $url = $this->router->generate($routeName, [], UrlGeneratorInterface::ABSOLUTE_URL);
        $urls[] = $this->createUrlData($url, $sitemapAttr);
    }

    private function createUrlData(string $url, Sitemap $sitemapAttr): array
    {
        $changefreq = $sitemapAttr->changefreq? $sitemapAttr->changefreq->value : null;
        $lastmod = $sitemapAttr->lastmod;
        if (strtolower($lastmod) == 'now') {
            $lastmod = $this->lastmodNow();
        }
        return [
            'loc' => $url,
            'priority' => $sitemapAttr->priority,
            'changefreq' => $changefreq,
            'lastmod' => $lastmod,
            'images' => $sitemapAttr->images
        ];
    }

    private function getSitemapAttribute(Route $route, ?string $controller): ?Sitemap
    {
        if (!$controller || !str_contains($controller, '::')) {
            return null;
        }

        [$controllerClass, $method] = explode('::', $controller, 2);

        try {
            $classReflection = new \ReflectionClass($controllerClass);
            $methodReflection = $classReflection->getMethod($method);

            $methodAttr = $this->getAttributeFromReflection($methodReflection);
            $classAttr = $this->getAttributeFromReflection($classReflection);

            return $this->mergeAttributes($classAttr, $methodAttr);
        } catch (\ReflectionException) {
            return null;
        }
    }

    private function getAttributeFromReflection(\Reflector $reflection): ?Sitemap
    {
        $attributes = $reflection->getAttributes(Sitemap::class);
        return $attributes ? $attributes[0]->newInstance() : null;
    }

    private function mergeAttributes(?Sitemap $classAttr, ?Sitemap $methodAttr): ?Sitemap
    {
        if (!$classAttr && !$methodAttr) {
            return null;
        }

        $lastmod = $methodAttr?->lastmod ?? $classAttr?->lastmod;
        if (strtolower($lastmod) === 'now') {
            $lastmod = $this->lastmodNow();
        }

        $mergedAttr = new Sitemap(
            $methodAttr?->priority ?? $classAttr?->priority,
            $methodAttr?->changefreq ?? $classAttr?->changefreq,
            $lastmod,
            array_merge($classAttr?->images ?? [], $methodAttr?->images ?? []),
            $methodAttr?->resolver ?? $classAttr?->resolver
        );

        return $mergedAttr;
    }

    private function getPathVariables(Route $route): array
    {
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $route->getPath(), $matches);
        return $matches[1] ?? [];
    }

    private function lastmodNow(): string
    {
        $lastmod = (new \DateTime('now'))->format('Y-m-d H:i:s.v') . 'Z';
        $lastmod = str_replace(' ', 'T', $lastmod);
        return $lastmod;
    }
}
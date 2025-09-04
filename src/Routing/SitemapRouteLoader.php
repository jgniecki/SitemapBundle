<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <jgniecki.contact@gmail.com>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace jgniecki\SitemapBundle\Routing;

use jgniecki\SitemapBundle\Controller\SitemapController;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class SitemapRouteLoader extends Loader
{
    private bool $loaded = false;

    public function __construct(private array $hosts = [])
    {
    }

    public function load(mixed $resource, ?string $type = null)
    {
        if ($this->loaded) {
            throw new \RuntimeException('Do not add this loader twice');
        }

        $routes = new RouteCollection();

        $orderedHosts = [];
        foreach ($this->hosts as $alias => $config) {
            if (!empty($config['host'])) {
                $orderedHosts[$alias] = $config;
            }
        }
        foreach ($this->hosts as $alias => $config) {
            if (empty($config['host'])) {
                $orderedHosts[$alias] = $config;
            }
        }

        foreach ($orderedHosts as $alias => $hostConfig) {
            $pattern = $hostConfig['host'] ?? null;
            $condition = $pattern ? "request.getHost() matches '/^" . $pattern . "$/'" : null;
            $groups = $hostConfig['groups'] ?? [];
            $default = $groups['default'] ?? ['path' => '/sitemap-default.xml', 'lastmod' => null];
            unset($groups['default']);

            $hasGroups = !empty($groups);

            $routes->add('sitemap_' . $alias, new Route('/sitemap.xml', [
                '_controller' => SitemapController::class,
                'group' => null,
                'index' => $hasGroups,
            ], [], [], '', [], [], $condition));

            if ($hasGroups) {
                $path = $default['path'] ?? '/sitemap-default.xml';
                $routes->add('sitemap_' . $alias . '_default', new Route($path, [
                    '_controller' => SitemapController::class,
                    'group' => null,
                    'index' => false,
                ], [], [], '', [], [], $condition));
            }

            foreach ($groups as $name => $config) {
                $path = $config['path'] ?? ('/sitemap-' . $name . '.xml');
                $routes->add('sitemap_' . $alias . '_' . $name, new Route($path, [
                    '_controller' => SitemapController::class,
                    'group' => $name,
                ], [], [], '', [], [], $condition));
            }
        }

        $this->loaded = true;

        return $routes;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'sitemap' === $type;
    }
}

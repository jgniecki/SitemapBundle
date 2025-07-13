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

    public function __construct(private array $groups = [], private array $default = [])
    {
    }

    public function load(mixed $resource, ?string $type = null)
    {
        if ($this->loaded) {
            throw new \RuntimeException('Do not add this loader twice');
        }

        $routes = new RouteCollection();

        // Index route
        $hasGroups = !empty($this->groups);
        $routes->add('sitemap', new Route('/sitemap.xml', [
            '_controller' => SitemapController::class,
            'group' => null,
            'index' => $hasGroups,
        ]));

        if ($hasGroups) {
            $path = $this->default['path'] ?? '/sitemap-default.xml';
            $routes->add('sitemap_default', new Route($path, [
                '_controller' => SitemapController::class,
                'group' => null,
                'index' => false,
            ]));
        }

        foreach ($this->groups as $name => $config) {
            $path = $config['path'] ?? ('/sitemap-' . $name . '.xml');
            $routes->add('sitemap_' . $name, new Route($path, [
                '_controller' => SitemapController::class,
                'group' => $name,
            ]));
        }

        $this->loaded = true;

        return $routes;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'sitemap' === $type;
    }
}

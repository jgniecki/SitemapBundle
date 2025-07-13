<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace jgniecki\SitemapBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SitemapExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.yaml');

        $container->setParameter('sitemap.default_priority', $config['default_priority']);
        $container->setParameter('sitemap.default_changefreq', $config['default_changefreq']);
        $groups = $config['groups'];
        $default = $groups['default'] ?? ['path' => '/sitemap.xml', 'lastmod' => null];
        unset($groups['default']);

        $container->setParameter('sitemap.groups', $groups);
        $container->setParameter('sitemap.default', $default);
    }
}

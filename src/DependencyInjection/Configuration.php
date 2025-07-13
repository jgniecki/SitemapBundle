<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace jgniecki\SitemapBundle\DependencyInjection;

use jgniecki\SitemapBundle\Sitemap\Enum\ChangeFreqEnum;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sitemap');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('default_priority')->defaultValue(null)->end()
            ->enumNode('default_changefreq')
            ->values($this->changeFreqValues())
            ->defaultValue(null)
            ->end()
            ->arrayNode('default')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('path')->defaultValue('/sitemap.xml')->end()
                    ->scalarNode('priority')->defaultValue(null)->end()
                    ->enumNode('changefreq')
                        ->values($this->changeFreqValues())
                        ->defaultValue(null)
                    ->end()
                    ->scalarNode('lastmod')->defaultNull()->end()
                ->end()
            ->end()
            ->arrayNode('groups')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('path')->defaultNull()->end()
                        ->scalarNode('lastmod')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }

    private function changeFreqValues(): array
    {
        $values = [];
        foreach (ChangeFreqEnum::cases() as $value) {
            $values[] = $value->value;
        }
        return $values;
    }
}
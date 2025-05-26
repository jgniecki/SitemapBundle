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
            ->end();

        return $treeBuilder;
    }

    private function changeFreqValues(): array
    {
        $values = [];
        foreach (ChangeFreqEnum::cases() as $value) {
            $values = $value->value;
        }
        return $values;
    }
}
<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace jgniecki\SitemapBundle\Sitemap\Attribute;

use jgniecki\SitemapBundle\Sitemap\ChangeFreqEnum;
use jgniecki\SitemapBundle\Sitemap\Resolver\DefaultRouteResolver;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Sitemap
{
    /**
     * @param float|null $priority
     * @param ChangeFreqEnum|null $changefreq
     * @param string|null $lastmod
     * @param $images array<{loc: string, title: string, caption: string}>
     * @param string|null $resolver
     */
    public function __construct(
        public ?float $priority = null,
        public ?ChangeFreqEnum $changefreq = null,
        public ?string $lastmod = null,
        public array $images = [],
        public ?string $resolver = DefaultRouteResolver::class
    ) {
    }
}
<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <jgniecki.contact@gmail.com>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace jgniecki\SitemapBundle\Sitemap\Attribute;

use jgniecki\SitemapBundle\Sitemap\Enum\ChangeFreqEnum;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Sitemap
{
    /**
     * @param float|null $priority
     * @param ChangeFreqEnum|null $changefreq
     * @param string|null $lastmod
     * @param $images array<{loc: string, title: string, caption: string}>
     * @param string|null $resolver
     * @param string|null $group
     */
    public function __construct(
        public ?float $priority = null,
        public ?ChangeFreqEnum $changefreq = null,
        public ?string $lastmod = null,
        public array $images = [],
        public ?string $resolver = null,
        public ?string $group = null
    ) {
    }
}
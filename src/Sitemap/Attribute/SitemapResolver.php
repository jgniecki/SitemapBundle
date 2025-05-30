<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <jgniecki.contact@gmail.com>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace jgniecki\SitemapBundle\Sitemap\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class SitemapResolver
{
    public function __construct(
        public ?int $priority = null
    ) {
    }
}
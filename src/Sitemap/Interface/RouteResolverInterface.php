<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <jgniecki.contact@gmail.com>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace jgniecki\SitemapBundle\Sitemap\Interface;

interface RouteResolverInterface
{
    public function supports(string $routeName, array $pathVariables): bool;
    public function resolve(string $routeName, array $pathVariables): iterable;
}

<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <jgniecki.contact@gmail.com>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace jgniecki\SitemapBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SitemapBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }
}
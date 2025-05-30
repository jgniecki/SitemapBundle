<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <jgniecki.contact@gmail.com>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace jgniecki\SitemapBundle\DependencyInjection\CompilerPass;

use jgniecki\SitemapBundle\Sitemap\Attribute\SitemapResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AutoResolverCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $projectDir = $container->getParameter('kernel.project_dir');
        $srcDir = $projectDir . '/src';

        $classes = $this->findClassesWithAttribute($srcDir, SitemapResolver::class);

        foreach ($classes as $class => $attribute) {
            $definition = new Definition($class);
            $definition->setAutowired(true);
            $definition->setAutoconfigured(true);

            $tag = ['name' => 'sitemap.resolver'];
            if ($attribute->priority !== null) {
                $tag['priority'] = $attribute->priority;
            }

            $definition->addTag('sitemap.resolver', $tag);
            $container->setDefinition($class, $definition);
        }
    }

    private function findClassesWithAttribute(string $directory, string $attributeClass): array
    {
        $classes = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            if (strpos($content, $attributeClass) === false) {
                continue;
            }

            $namespace = $class = '';
            if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
                $namespace = $matches[1];
            }

            if (preg_match('/class\s+(\w+)/', $content, $matches)) {
                $class = $namespace . '\\' . $matches[1];
            }

            if (class_exists($class)) {
                $reflection = new \ReflectionClass($class);
                $attributes = $reflection->getAttributes($attributeClass);

                if (!empty($attributes)) {
                    $classes[$class] = $attributes[0]->newInstance();
                }
            }
        }

        return $classes;
    }
}
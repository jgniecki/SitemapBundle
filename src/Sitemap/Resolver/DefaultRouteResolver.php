<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace jgniecki\SitemapBundle\Sitemap\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use jgniecki\SitemapBundle\Sitemap\Interface\DefaultRouteResolverInterface;

class DefaultRouteResolver implements DefaultRouteResolverInterface
{
    private array $entityNamespaces;

    public function __construct(
        private readonly EntityManagerInterface $em,
        array $doctrineMappings
    ) {
        $this->entityNamespaces = $this->extractNamespaces($doctrineMappings);
    }

    public function supports(string $routeName, array $pathVariables): bool
    {
        return $this->guessEntityClass($routeName) !== null;
    }

    public function resolve(string $routeName, array $pathVariables): iterable
    {
        $entityClass = $this->guessEntityClass($routeName);
        $repo = $this->em->getRepository($entityClass);

        foreach ($repo->findAll() as $entity) {
            $params = [];
            foreach ($pathVariables as $var) {
                $getter = 'get' . ucfirst($var);
                if (method_exists($entity, $getter)) {
                    $params[$var] = $entity->$getter();
                }
            }

            if (count($params) === count($pathVariables)) {
                yield $params;
            }
        }
    }

    private function extractNamespaces(array $mappings): array
    {
        return array_unique(array_column($mappings, 'prefix'));
    }

    private function guessEntityClass(string $routeName): ?string
    {
        $parts = explode('_', $routeName);
        $entityName = ucfirst(array_shift($parts));

        foreach ($this->entityNamespaces as $namespace) {
            $className = rtrim($namespace, '\\') . '\\' . $entityName;
            if (class_exists($className)) {
                return $className;
            }
        }

        return null;
    }
}
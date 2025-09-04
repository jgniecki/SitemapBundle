<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <jgniecki.contact@gmail.com>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace jgniecki\SitemapBundle\Controller;

use jgniecki\SitemapBundle\Sitemap\SitemapGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class SitemapController extends AbstractController
{
    public function __construct(
        private readonly SitemapGenerator $generator,
        private readonly Environment      $twig
    ) {}

    public function __invoke(Request $request, ?string $group = null, bool $index = false): Response
    {
        $host = $request->getHost();
        $urls = $this->generator->generate($host, $group, $index);

        $template = $index ? '@Sitemap/sitemap_index.xml.twig' : '@Sitemap/sitemap.xml.twig';

        return new Response(
            $this->twig->render($template, ['urls' => $urls]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/xml']
        );
    }
}
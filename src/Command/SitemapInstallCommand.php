<?php declare(strict_types=1);

namespace jgniecki\SitemapBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class SitemapInstallCommand extends Command
{
    protected static $defaultName = 'sitemap:install';
    protected static $defaultDescription = 'Install default configuration for SitemapBundle';

    public function __construct(private KernelInterface $kernel)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectDir = $this->kernel->getProjectDir();
        $configDir = $projectDir . '/config';
        $filesystem = new Filesystem();

        $sourcePackages = __DIR__ . '/../Resources/config/packages/sitemap.yaml';
        $targetPackages = $configDir . '/packages/sitemap.yaml';
        $sourceRoutes = __DIR__ . '/../Resources/config/routes/sitemap.yaml';
        $targetRoutes = $configDir . '/routes/sitemap.yaml';

        $filesystem->mkdir([$configDir . '/packages', $configDir . '/routes']);

        if (!$filesystem->exists($targetPackages)) {
            $filesystem->copy($sourcePackages, $targetPackages);
            $output->writeln(sprintf('Created <info>%s</info>', $targetPackages));
        } else {
            $output->writeln(sprintf('File <info>%s</info> already exists, skipping', $targetPackages));
        }

        if (!$filesystem->exists($targetRoutes)) {
            $filesystem->copy($sourceRoutes, $targetRoutes);
            $output->writeln(sprintf('Created <info>%s</info>', $targetRoutes));
        } else {
            $output->writeln(sprintf('File <info>%s</info> already exists, skipping', $targetRoutes));
        }

        return Command::SUCCESS;
    }
}

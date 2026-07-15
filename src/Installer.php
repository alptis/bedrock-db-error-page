<?php

declare(strict_types=1);

namespace Alptis\BedrockDbErrorPage;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

final class Installer implements PluginInterface, EventSubscriberInterface
{
    private const RESOURCES_DIR = __DIR__ . '/../resources';

    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'install',
            ScriptEvents::POST_UPDATE_CMD => 'install',
        ];
    }

    public function install(Event $event): void
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $baseDir = dirname($vendorDir);
        require_once $vendorDir . '/autoload.php';
        require_once $baseDir . '/config/application.php';
        $this->copyFile($event->getIO(), self::RESOURCES_DIR . '/db-error.php', WP_CONTENT_DIR . '/db-error.php');
        if (!file_exists(WP_CONTENT_DIR . '/custom_db_error.html')) {
            $this->copyFile(
                $event->getIO(),
                self::RESOURCES_DIR . '/default_db_error.html',
                WP_CONTENT_DIR . '/custom_db_error.html'
            );
        }
    }

    private function copyFile(IOInterface $io, string $source, string $destination): void
    {
        if (!@copy($source, $destination)) {
            $error = error_get_last()['message'] ?? 'unknown error';
            $io->writeError(sprintf('<error>Unable to copy %s to %s: %s</error>', $source, $destination, $error));
        }
    }
}

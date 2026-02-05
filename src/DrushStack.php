<?php

namespace Boedah\Robo\Task\Drush;

use Robo\Common\CommandArguments;
use Robo\Task\CommandStack;

/**
 * Runs Drush commands in stack. You can use `stopOnFail()` to point that stack should be terminated on first fail.
 * You can define global options for all commands (like Drupal root and uri).
 * The option -y is always set, as it makes sense in a task runner.
 *
 * ``` php
 * $this->taskDrushStack()
 *     ->drupalRootDirectory('/var/www/html/some-site')
 *     ->uri('sub.example.com')
 *     ->maintenanceOn()
 *     ->updateDb()
 *     ->revertAllFeatures()
 *     ->maintenanceOff()
 *     ->run();
 * ```
 *
 * Example site install command:
 *
 * ``` php
 * $this->taskDrushStack()
 *   ->siteName('Site Name')
 *   ->siteMail('site-mail@example.com')
 *   ->locale('de')
 *   ->accountMail('mail@example.com')
 *   ->accountName('admin')
 *   ->accountPass('pw')
 *   ->dbPrefix('drupal_')
 *   ->sqliteDbUrl('sites/default/.ht.sqlite')
 *   ->disableUpdateStatusModule()
 *   ->siteInstall('minimal')
 *   ->run();
 * ```
 */
class DrushStack extends CommandStack
{
    use CommandArguments;

    protected string $argumentsForNextCommand = '';

    /**
     * Drush site alias.
     * We need to save this, since it needs to be the first argument.
     */
    protected string $siteAlias = '';

    protected ?string $drushVersion = null;

    public function __construct($pathToDrush = 'drush')
    {
        $this->executable = $pathToDrush;
    }

    public function drupalRootDirectory($drupalRootDirectory): static
    {
        $this->printTaskInfo('Drupal root: <info>' . $drupalRootDirectory . '</info>');
        $this->option('-r', $drupalRootDirectory);

        return $this;
    }

    public function uri($uri): static
    {
        $this->printTaskInfo('URI: <info>' . $uri . '</info>');
        $this->option('-l', $uri);

        return $this;
    }

    public function siteAlias($alias): static
    {
        $this->printTaskInfo('Site Alias: <info>' . $alias . '</info>');
        $this->siteAlias = $alias;

        return $this;
    }

    public function debug(): static
    {
        $this->option('-d');

        return $this;
    }

    public function verbose(): static
    {
        $this->option('-v');

        return $this;
    }

    public function simulate(): static
    {
        $this->option('-s');

        return $this;
    }

    public function siteName(string $siteName): static
    {
        $this->argForNextCommand('--site-name=' . escapeshellarg($siteName));

        return $this;
    }

    /**
     * Add an argument used in the next invocation of drush.
     */
    protected function argForNextCommand(string|float|int $arg): static
    {
        return $this->argsForNextCommand($arg);
    }

    /**
     * Add multiple arguments used in the next invocation of drush.
     *
     * @param array<string|float|int>|string|float|int $args can also be multiple parameters
     */
    protected function argsForNextCommand(array|string|float|int $args): static
    {
        if (!is_array($args)) {
            $args = func_get_args();
        }
        $this->argumentsForNextCommand .= ' ' . implode(' ', $args);

        return $this;
    }

    public function siteMail(string $siteMail): static
    {
        $this->argForNextCommand('--site-mail=' . $siteMail);

        return $this;
    }

    public function sitesSubdir(string $sitesSubdir): static
    {
        $this->argForNextCommand('--sites-subdir=' . $sitesSubdir);

        return $this;
    }

    public function locale(string $locale): static
    {
        $this->argForNextCommand('--locale=' . $locale);

        return $this;
    }

    public function accountMail(string $accountMail): static
    {
        $this->argForNextCommand('--account-mail=' . $accountMail);

        return $this;
    }

    public function accountName(string $accountName): static
    {
        $this->argForNextCommand('--account-name=' . escapeshellarg($accountName));

        return $this;
    }

    public function accountPass(string $accountPass): static
    {
        $this->argForNextCommand('--account-pass=' . escapeshellarg($accountPass));

        return $this;
    }

    public function dbPrefix(string $dbPrefix): static
    {
        $this->argForNextCommand('--db-prefix=' . $dbPrefix);

        return $this;
    }

    public function dbSu(string $dbSu): static
    {
        $this->argForNextCommand('--db-su=' . $dbSu);

        return $this;
    }

    public function dbSuPw(string $dbSuPw): static
    {
        $this->argForNextCommand('--db-su-pw=' . escapeshellarg($dbSuPw));

        return $this;
    }

    public function sqliteDbUrl(string $relativePath): static
    {
        return $this->dbUrl('sqlite://' . $relativePath);
    }

    public function dbUrl(string $dbUrl): static
    {
        $this->argForNextCommand('--db-url=' . escapeshellarg($dbUrl));

        return $this;
    }

    public function mysqlDbUrl(string $dsn): static
    {
        return $this->dbUrl('mysql://' . $dsn);
    }

    public function disableUpdateStatusModule(): static
    {
        $this->argForNextCommand('install_configure_form.update_status_module=0');

        return $this;
    }

    public function configDir(string $configDir): static
    {
        $this->argForNextCommand('--config-dir=' . $configDir);

        return $this;
    }

    public function existingConfig(bool $existingConfig = true): static
    {
        if ($existingConfig) {
            $this->argForNextCommand('--existing-config');
        }

        return $this;
    }

    /**
     * Executes `drush status`
     */
    public function status(): static
    {
        return $this->drush('status');
    }

    /**
     * Runs the given drush command.
     *
     * @param string|array<string> $command
     */
    public function drush(string|array $command, bool $assumeYes = true): static
    {
        if (is_array($command)) {
            $command = implode(' ', array_filter($command));
        }

        return $this->exec($this->injectArguments($command, $assumeYes));
    }

    /**
     * Prepends site-alias and appends arguments to the command.
     *
     * @return string the modified command string
     */
    protected function injectArguments(string $command, bool $assumeYes): string
    {
        $cmd =
            $this->siteAlias . ' '
            . $command
            . ($assumeYes ? ' -y' : '')
            . $this->arguments
            . $this->argumentsForNextCommand;
        $this->argumentsForNextCommand = '';

        return $cmd;
    }

    /**
     * Runs pending database updates.
     */
    public function updateDb(): static
    {
        $this->printTaskInfo('Do database updates');
        $this->drush('updb');
        $drushVersion = $this->getVersion();
        if (-1 === version_compare($drushVersion, '6.0')) {
            $this->printTaskInfo(
                'Will clear cache after db updates for drush '
                . $drushVersion
            );
            $this->clearCache();
        } else {
            $this->printTaskInfo(
                'Will not clear cache after db updates, since drush '
                . $drushVersion . ' should do it automatically'
            );
        }

        return $this;
    }

    /**
     * Returns the drush version.
     */
    public function getVersion(): string
    {
        if (empty($this->drushVersion)) {
            $isPrinted = $this->isPrinted;
            $this->isPrinted = false;
            $result = $this->executeCommand($this->executable . ' version');
            $output = $result->getMessage();
            $this->drushVersion = 'unknown';
            if (preg_match('#[0-9.]+#', $output, $matches)) {
                $this->drushVersion = $matches[0];
            }
            $this->isPrinted = $isPrinted;
        }

        return $this->drushVersion;
    }

    /**
     * Clears the given cache.
     *
     * @param string $bin cache bin to clear
     */
    public function clearCache(string $bin = 'all'): static
    {
        $this->printTaskInfo('Clear cache');

        return $this->drush('cc ' . $bin);
    }

    /**
     * @param bool $force force revert even if Features assumes components' state are default
     * @param string $excludedFeatures space-delimited list of features to exclude from being reverted
     */
    public function revertAllFeatures(bool $force = false, string $excludedFeatures = ''): static
    {
        $this->printTaskInfo('Revert all features');
        $args = $excludedFeatures . ($force ? ' --force' : '');

        return $this->drush('fra ' . $args);
    }

    /**
     * Enables the maintenance mode.
     */
    public function maintenanceOn(): static
    {
        $this->printTaskInfo('Turn maintenance mode on');

        return $this->drush('vset --exact maintenance_mode 1');
    }

    /**
     * Disables the maintenance mode.
     */
    public function maintenanceOff(): static
    {
        $this->printTaskInfo('Turn maintenance mode off');

        return $this->drush('vdel --exact maintenance_mode');
    }

    public function siteInstall(string $installationProfile): static
    {
        return $this->drush('site-install ' . $installationProfile);
    }
}

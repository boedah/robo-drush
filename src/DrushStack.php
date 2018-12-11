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

    protected $argumentsForNextCommand;

    /**
     * Drush site alias.
     * We need to save this, since it needs to be the first argument.
     *
     * @var string
     */
    protected $siteAlias;

    /**
     * @var string
     */
    protected $drushVersion;

    public function __construct($pathToDrush = 'drush')
    {
        $this->executable = $pathToDrush;
    }

    public function drupalRootDirectory($drupalRootDirectory)
    {
        $this->printTaskInfo('Drupal root: <info>' . $drupalRootDirectory . '</info>');
        $this->option('-r', $drupalRootDirectory);

        return $this;
    }

    public function uri($uri)
    {
        $this->printTaskInfo('URI: <info>' . $uri . '</info>');
        $this->option('-l', $uri);

        return $this;
    }

    public function siteAlias($alias)
    {
        $this->printTaskInfo('Site Alias: <info>' . $alias . '</info>');
        $this->siteAlias = $alias;

        return $this;
    }

    public function debug()
    {
        $this->option('-d');

        return $this;
    }

    public function verbose()
    {
        $this->option('-v');

        return $this;
    }

    public function simulate()
    {
        $this->option('-s');

        return $this;
    }

    public function siteName($siteName)
    {
        $this->argForNextCommand('--site-name=' . escapeshellarg($siteName));

        return $this;
    }

    /**
     * Add an argument used in the next invocation of drush.
     *
     * @param string $arg
     *
     * @return $this
     */
    protected function argForNextCommand($arg)
    {
        return $this->argsForNextCommand($arg);
    }

    /**
     * Add multiple arguments used in the next invocation of drush.
     *
     * @param array|string $args can also be multiple parameters
     *
     * @return $this
     */
    protected function argsForNextCommand($args)
    {
        if (!is_array($args)) {
            $args = func_get_args();
        }
        $this->argumentsForNextCommand .= ' ' . implode(' ', $args);

        return $this;
    }

    public function siteMail($siteMail)
    {
        $this->argForNextCommand('--site-mail=' . $siteMail);

        return $this;
    }

    public function sitesSubdir($sitesSubdir)
    {
        $this->argForNextCommand('--sites-subdir=' . $sitesSubdir);

        return $this;
    }

    public function locale($locale)
    {
        $this->argForNextCommand('--locale=' . $locale);

        return $this;
    }

    public function accountMail($accountMail)
    {
        $this->argForNextCommand('--account-mail=' . $accountMail);

        return $this;
    }

    public function accountName($accountName)
    {
        $this->argForNextCommand('--account-name=' . escapeshellarg($accountName));

        return $this;
    }

    public function accountPass($accountPass)
    {
        $this->argForNextCommand('--account-pass=' . escapeshellarg($accountPass));

        return $this;
    }

    public function dbPrefix($dbPrefix)
    {
        $this->argForNextCommand('--db-prefix=' . $dbPrefix);

        return $this;
    }

    public function dbSu($dbSu)
    {
        $this->argForNextCommand('--db-su=' . $dbSu);

        return $this;
    }

    public function dbSuPw($dbSuPw)
    {
        $this->argForNextCommand('--db-su-pw=' . escapeshellarg($dbSuPw));

        return $this;
    }

    public function sqliteDbUrl($relativePath)
    {
        return $this->dbUrl('sqlite://' . $relativePath);
    }

    public function dbUrl($dbUrl)
    {
        $this->argForNextCommand('--db-url=' . escapeshellarg($dbUrl));

        return $this;
    }

    public function mysqlDbUrl($dsn)
    {
        return $this->dbUrl('mysql://' . $dsn);
    }

    public function disableUpdateStatusModule()
    {
        $this->argForNextCommand('install_configure_form.update_status_module=0');

        return $this;
    }

    public function configDir($configDir)
    {
        $this->argForNextCommand('--config-dir=' . $configDir);

        return $this;
    }

    public function existingConfig($existingConfig = true)
    {
        if ($existingConfig) {
            $this->argForNextCommand('--existing-config');
        }

        return $this;
    }

    /**
     * Executes `drush status`
     *
     * @return $this
     */
    public function status()
    {
        return $this->drush('status');
    }

    /**
     * Runs the given drush command.
     *
     * @param string $command
     * @param bool $assumeYes
     *
     * @return $this
     */
    public function drush($command, $assumeYes = true)
    {
        if (is_array($command)) {
            $command = implode(' ', array_filter($command));
        }

        return $this->exec($this->injectArguments($command, $assumeYes));
    }

    /**
     * Prepends site-alias and appends arguments to the command.
     *
     * @param string $command
     * @param bool $assumeYes
     *
     * @return string the modified command string
     */
    protected function injectArguments($command, $assumeYes)
    {
        $cmd =
            $this->siteAlias . ' '
            . $command
            . ($assumeYes ? ' -y': '')
            . $this->arguments
            . $this->argumentsForNextCommand;
        $this->argumentsForNextCommand = '';

        return $cmd;
    }

    /**
     * Runs pending database updates.
     *
     * @return $this
     */
    public function updateDb()
    {
        $this->printTaskInfo('Do database updates');
        $this->drush('updb');
        $drushVersion = $this->getVersion();
        if (-1 === version_compare($drushVersion, '6.0')) {
            $this->printTaskInfo('Will clear cache after db updates for drush '
                . $drushVersion);
            $this->clearCache();
        } else {
            $this->printTaskInfo('Will not clear cache after db updates, since drush '
                . $drushVersion . ' should do it automatically');
        }

        return $this;
    }

    /**
     * Returns the drush version.
     *
     * @return string
     */
    public function getVersion()
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
     * @param string $name cache name
     *
     * @return $this
     */
    public function clearCache($name = 'all')
    {
        $this->printTaskInfo('Clear cache');

        return $this->drush('cc ' . $name);
    }

    /**
     * @param bool $force force revert even if Features assumes components' state are default
     * @param string $excludedFeatures space-delimited list of features to exclude from being reverted
     *
     * @return $this
     */
    public function revertAllFeatures($force = false, $excludedFeatures = '')
    {
        $this->printTaskInfo('Revert all features');
        $args = $excludedFeatures . ($force ? ' --force' : '');

        return $this->drush('fra ' . $args);
    }

    /**
     * Enables the maintenance mode.
     *
     * @return $this
     */
    public function maintenanceOn()
    {
        $this->printTaskInfo('Turn maintenance mode on');

        return $this->drush('vset --exact maintenance_mode 1');
    }

    /**
     * Disables the maintenance mode.
     *
     * @return $this
     */
    public function maintenanceOff()
    {
        $this->printTaskInfo('Turn maintenance mode off');

        return $this->drush('vdel --exact maintenance_mode');
    }

    /**
     * @param string $installationProfile
     *
     * @return $this
     */
    public function siteInstall($installationProfile)
    {
        return $this->drush('site-install ' . $installationProfile);
    }
}

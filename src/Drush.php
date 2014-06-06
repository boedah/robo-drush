<?php
namespace Boedah\Robo\Task;

use Robo\Output;
use Robo\Task\Shared\CommandStack;
use Robo\Task\Shared\Executable;

trait Drush
{

    protected function taskDrushStack($pathToDrush = 'drush')
    {
        return new DrushStackTask($pathToDrush);
    }

}

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
class DrushStackTask extends CommandStack
{
    use Output;
    use Executable;

    /**
     * Drush site alias.
     * We need to save this, since it needs to be the first argument.
     *
     * @var string
     */
    protected $siteAlias;

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
        $this->arg('--site-name=' . escapeshellarg($siteName));

        return $this;
    }

    public function siteMail($siteMail)
    {
        $this->arg('--site-mail=' . $siteMail);

        return $this;
    }

    public function sitesSubdir($sitesSubdir)
    {
        $this->arg('--sites-subdir=' . $sitesSubdir);

        return $this;
    }

    public function locale($locale)
    {
        $this->arg('--locale=' . $locale);

        return $this;
    }

    public function accountMail($accountMail)
    {
        $this->arg('--account-mail=' . $accountMail);

        return $this;
    }

    public function accountName($accountName)
    {
        $this->arg('--account-name=' . escapeshellarg($accountName));

        return $this;
    }

    public function accountPass($accountPass)
    {
        $this->arg('--account-pass=' . $accountPass);

        return $this;
    }

    public function dbPrefix($dbPrefix)
    {
        $this->arg('--db-prefix=' . $dbPrefix);

        return $this;
    }

    public function dbSu($dbSu)
    {
        $this->arg('--db-su=' . $dbSu);

        return $this;
    }

    public function dbSuPw($dbSuPw)
    {
        $this->arg('--db-su-pw=' . $dbSuPw);

        return $this;
    }

    public function dbUrl($dbUrl)
    {
        $this->arg('--db-url=' . escapeshellarg($dbUrl));

        return $this;
    }

    public function sqliteDbUrl($relativePath)
    {
        return $this->dbUrl('sqlite://' . $relativePath);
    }

    public function mysqlDbUrl($dsn)
    {
        return $this->dbUrl('mysql://' . $dsn);
    }

    public function disableUpdateStatusModule()
    {
        $this->arg('install_configure_form.update_status_module=0');

        return $this;
    }

    /**
     * Echoes and returns the drush version.
     *
     * @return $this
     */
    public function getVersion()
    {
        if (empty($this->drushVersion)) {
            $isPrinted = $this->isPrinted;
            $this->isPrinted = false;
            $result = $this->executeCommand($this->executable . ' --version');
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
     * Executes `drush status`
     *
     * @return $this
     */
    public function status()
    {
        return $this->exec('status');
    }

    /**
     * Clears the given cache.
     *
     * @param string $name cache name
     * @return $this
     */
    public function clearCache($name = 'all')
    {
        $this->printTaskInfo('Clear cache');

        return $this->exec('cc ' . $name);
    }

    /**
     * Runs pending database updates.
     *
     * @return $this
     */
    public function updateDb()
    {
        $this->printTaskInfo('Do database updates');
        $this->exec('updb');
        if (-1 === version_compare($this->drushVersion, '6.0')) {
            $this->printTaskInfo('Will clear cache after db updates for drush '
                . $this->drushVersion);
            $this->clearCache();
        } else {
            $this->printTaskInfo('Will not clear cache after db updates, since drush '
                . $this->drushVersion . ' should do it automatically');
        }

        return $this;
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

        return $this->exec('fra ' . $args);
    }

    /**
     * Enables the maintenance mode.
     *
     * @return $this
     */
    public function maintenanceOn()
    {
        $this->printTaskInfo('Turn maintenance mode on');

        return $this->exec('vset --exact maintenance_mode 1');
    }

    /**
     * Disables the maintenance mode.
     *
     * @return $this
     */
    public function maintenanceOff()
    {
        $this->printTaskInfo('Turn maintenance mode off');

        return $this->exec('vdel --exact maintenance_mode');
    }

    /**
     * @param string $installationProfile
     * @return $this
     */
    public function siteInstall($installationProfile)
    {
        return $this->exec('site-install ' . $installationProfile);
    }

    /**
     * Runs the given drush command.
     *
     * @param string $command
     * @return $this
     */
    public function exec($command)
    {
        if (is_array($command)) {
            $command = implode(' ', array_filter($command));
        }

        return parent::exec($this->injectArguments($command));
    }

    /**
     * Prepends site-alias and appends arguments to the command.
     *
     * @param string $command
     * @return string the modified command string
     */
    protected function injectArguments($command)
    {
        return $this->siteAlias . ' ' . $command . ' -y' . $this->arguments;
    }

}

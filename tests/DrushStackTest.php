<?php

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;

class DrushStackTest extends \PHPUnit_Framework_TestCase implements ContainerAwareInterface
{
    use \Boedah\Robo\Task\Drush\loadTasks;
    use TaskAccessor;
    use ContainerAwareTrait;

    /**
     * @var string
     */
    protected $tmpReleaseTag;

    // Set up the Robo container so that we can create tasks in our tests.
    function setup()
    {
        $container = Robo::createDefaultContainer(null, new NullOutput());
        $this->setContainer($container);
    }

    // Scaffold the collection builder
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks;
        return $this->getContainer()->get('collectionBuilder', [$emptyRobofile]);
    }

    public function testYesIsAssumed()
    {
        $command = $this->taskDrushStack()
            ->drush('command')
            ->getCommand();
        $this->assertEquals('drush command -y', $command);
    }

    public function testAbsenceofYes()
    {
        $command = $this->taskDrushStack()
            ->drush('command', false)
            ->getCommand();
        $this->assertEquals('drush command', $command);
    }

    public function testOptionsArePrependedBeforeEachCommand()
    {
        $command = $this->taskDrushStack()
            ->drupalRootDirectory('/var/www/html/app')
            ->drush('command-1')
            ->drush('command-2')
            ->getCommand();
        $this->assertEquals(2, preg_match_all('#-r /var/www/html/app#', $command));
    }

    public function testSiteInstallCommand()
    {
        $command = $this->taskDrushStack()
            ->siteName('Site Name')
            ->siteMail('site-mail@example.com')
            ->locale('de')
            ->accountMail('mail@example.com')
            ->accountName('admin')
            ->accountPass('pw')
            ->dbPrefix('drupal_')
            ->sqliteDbUrl('sit"es/default/.ht.sqlite')
            ->disableUpdateStatusModule()
            ->siteInstall('minimal')
            ->getCommand();
        $expected = 'drush site-install minimal -y --site-name=' . escapeshellarg('Site Name')
            . ' --site-mail=site-mail@example.com'
            . ' --locale=de --account-mail=mail@example.com --account-name=' . escapeshellarg('admin')
            . ' --account-pass=pw'
            . ' --db-prefix=drupal_ --db-url=' . escapeshellarg('sqlite://sit"es/default/.ht.sqlite')
            . ' install_configure_form.update_status_module=0';
        $this->assertEquals($expected, $command);
    }

    public function testSiteAliasIsFirstOption()
    {
        $command = $this->taskDrushStack()
            ->drupalRootDirectory('/var/www/html/app')
            ->siteAlias('@qa')
            ->drush('command-1')
            ->drush('command-2')
            ->getCommand();
        $this->assertEquals(2, preg_match_all('#drush @qa comm#', $command));
    }

    public function testDrushStatus()
    {
        $result = $this->taskDrushStack(__DIR__ . '/../vendor/bin/drush')
            ->printed(false)
            ->status()
            ->run();
        $this->assertTrue($result->wasSuccessful(), 'Exit code was: ' . $result->getExitCode());
    }

    public function testDrushVersion()
    {
        $this->writeTestReleaseTag();
        foreach (['8.1.12', '9.0.0'] as $version) {
            passthru(escapeshellcmd('rm composer.lock'), $exit_code);
            $this->composer('require --update-with-dependencies drush/drush:"' . $version .'"');
            $version2 = $this->taskDrushStack(__DIR__ . '/../vendor/bin/drush')
              ->getVersion();
            $this->assertEquals($version, $version2);
        }
        $this->git(sprintf('tag -d "%s"', $this->tmpReleaseTag));
    }

    /**
     * Writes a tag for the current commit, so we can reference it directly in the
     * composer.json.
     */
    protected function writeTestReleaseTag() {
        // Tag the current state.
        $this->tmpReleaseTag = '999.0.' . time();
        $this->git(sprintf('tag -a "%s" -m "%s"', $this->tmpReleaseTag, 'Tag for testing this exact commit'));
    }

    /**
     * Wrapper for the composer command.
     *
     * @param string $command
     *   Composer command name, arguments and/or options
     */
    protected function composer($command) {
        passthru(escapeshellcmd('composer -q ' . $command), $exit_code);
        if ($exit_code !== 0) {
            throw new \Exception('Composer returned a non-zero exit code.');
        }
    }

    /**
     * Wrapper for git command in the root directory.
     *
     * @param $command
     *   Git command name, arguments and/or options.
     */
    protected function git($command) {
        passthru(escapeshellcmd('git ' . $command), $exit_code);
        if ($exit_code !== 0) {
            throw new \Exception('Git returned a non-zero exit code');
        }
    }

}

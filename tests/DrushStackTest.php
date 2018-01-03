<?php

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;
use Symfony\Component\Filesystem\Filesystem;

class DrushStackTest extends \PHPUnit_Framework_TestCase implements ContainerAwareInterface
{
    use \Boedah\Robo\Task\Drush\loadTasks;
    use TaskAccessor;
    use ContainerAwareTrait;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $fs;

    /**
     * @var string
     */
    protected $tmpDir;

    /**
     * @var string
     */
    protected $tmpReleaseTag;

    // Set up the Robo container so that we can create tasks in our tests.
    public function setUp()
    {
        $container = Robo::createDefaultContainer(null, new NullOutput());
        $this->setContainer($container);

        // Prepare temp directory.
        $this->fs = new Filesystem();
        $this->tmpDir = realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR . 'robo-drush';
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
        foreach (['8.1.15' => '8.1.15', '9.0.0-rc1' => '9.0.0'] as $version => $version_string) {
            $this->ensureDirectoryExistsAndClear($this->tmpDir);
            $this->writeComposerJSON();
            $this->composer('require --update-with-dependencies drush/drush:"' . $version .'" -vvv');
            $version2 = $this->taskDrushStack($this->tmpDir . '/vendor/bin/drush')
              ->getVersion();
            $this->assertEquals($version_string, $version2);
        }
    }

    /**
     * Writes the default composer json to the temp direcoty.
     */
    protected function writeComposerJSON() {
      $json = json_encode($this->composerJSONDefaults(), JSON_PRETTY_PRINT);
      // Write composer.json.
      file_put_contents($this->tmpDir . '/composer.json', $json);
    }

    /**
     * Provides the default composer.json data.
     *
     * @return array
     */
    protected function composerJSONDefaults() {
      return array(
        'require' => array(
          'drush/drush' => '^8.0 | ^9.0'
        ),
        'minimum-stability' => 'beta'
      );
    }

    /**
     * Wrapper for the composer command.
     *
     * @param string $command
     *   Composer command name, arguments and/or options
     */
    protected function composer($command) {
        chdir($this->tmpDir);
        exec(escapeshellcmd('composer -q ' . $command), $output, $exit_code);
        if ($exit_code !== 0) {
            throw new \Exception('Composer returned a non-zero exit code.' . $output);
        }
    }

    /**
     * Makes sure the given directory exists and has no content.
     *
     * @param string $directory
     */
    protected function ensureDirectoryExistsAndClear($directory) {
      if (is_dir($directory)) {
        $this->fs->remove($directory);
      }
      $this->fs->mkdir($directory, 0777);
    }

}

<?php

use Boedah\Robo\Task\Drush\loadTasks;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Robo;
use Robo\TaskAccessor;
use Robo\Tasks;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;

class DrushStackTest extends \PHPUnit_Framework_TestCase implements ContainerAwareInterface
{
    use loadTasks;
    use TaskAccessor;
    use ContainerAwareTrait;

    protected Filesystem $fs;

    protected string $tmpDir;

    // Set up the Robo container so that we can create tasks in our tests.
    public function setUp(): void
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
        $emptyRobofile = new Tasks;

        return $this->getContainer()->get('collectionBuilder', [$emptyRobofile]);
    }

    public function testYesIsAssumed(): void
    {
        $command = $this->taskDrushStack()
            ->drush('command')
            ->getCommand();
        $this->assertEquals('drush command -y', $command);
    }

    public function testAbsenceOfYes(): void
    {
        $command = $this->taskDrushStack()
            ->drush('command', false)
            ->getCommand();
        $this->assertEquals('drush command', $command);
    }

    public function testOptionsArePrependedBeforeEachCommand(): void
    {
        $command = $this->taskDrushStack()
            ->drupalRootDirectory('/var/www/html/app')
            ->drush('command-1')
            ->drush('command-2')
            ->getCommand();
        $this->assertEquals(2, preg_match_all('#-r /var/www/html/app#', $command));
    }

    public function testSiteInstallCommand(): void
    {
        $pw = 'p"|&w';
        $command = $this->taskDrushStack()
            ->siteName('Site Name')
            ->siteMail('site-mail@example.com')
            ->locale('de')
            ->accountMail('mail@example.com')
            ->accountName('admin')
            ->accountPass($pw)
            ->dbPrefix('drupal_')
            ->dbSu('su_account')
            ->dbSuPw($pw)
            ->sqliteDbUrl('sit"es/default/.ht.sqlite')
            ->disableUpdateStatusModule()
            ->siteInstall('minimal')
            ->getCommand();
        $expected = 'drush site-install minimal -y --site-name=' . escapeshellarg('Site Name')
            . ' --site-mail=site-mail@example.com'
            . ' --locale=de --account-mail=mail@example.com --account-name=' . escapeshellarg('admin')
            . ' --account-pass=' . escapeshellarg($pw)
            . ' --db-prefix=drupal_ --db-su=su_account --db-su-pw=' . escapeshellarg(
                $pw
            ) . ' --db-url=' . escapeshellarg('sqlite://sit"es/default/.ht.sqlite')
            . ' install_configure_form.update_status_module=0';
        $this->assertEquals($expected, $command);
    }

    public function testExistingConfigDefaultsToTrue(): void
    {
        $command = $this->taskDrushStack()
            ->existingConfig()
            ->siteInstall('minimal')
            ->getCommand();
        $expected = 'drush site-install minimal -y --existing-config';
        $this->assertEquals($expected, $command);
    }

    /**
     * @dataProvider existingConfigWithBooleanParamIsRespectedProvider
     */
    public function testExistingConfigWithBooleanParamIsRespected(
        mixed $existingConfigParam,
        string $commandParam = ' --existing-config'
    ): void {
        $command = $this->taskDrushStack()
            ->existingConfig($existingConfigParam)
            ->siteInstall('minimal')
            ->getCommand();
        $expected = 'drush site-install minimal -y' . $commandParam;
        $this->assertEquals($expected, $command);
    }

    public function existingConfigWithBooleanParamIsRespectedProvider()
    {
        return [
            // trueish
            'true' => [true],
            '1' => [1],
            '"1"' => ['1'],
            // falsish
            'false' => [false, ''],
            '0' => [0, ''],
            '"0"' => ['0', ''],
            'null' => [null, ''],
            'empty string' => ['', ''],
        ];
    }

    public function testSiteAliasIsFirstOption(): void
    {
        $command = $this->taskDrushStack()
            ->drupalRootDirectory('/var/www/html/app')
            ->siteAlias('@qa')
            ->drush('command-1')
            ->drush('command-2')
            ->getCommand();
        $this->assertEquals(2, preg_match_all('#drush @qa comm#', $command));
    }

    public function testDrushStatus(): void
    {
        $result = $this->taskDrushStack(__DIR__ . '/../vendor/bin/drush')
            ->printed(false)
            ->status()
            ->run();
        $this->assertTrue($result->wasSuccessful(), 'Exit code was: ' . $result->getExitCode());
    }

    /**
     * @dataProvider drushVersionProvider
     *
     * @param string $composerDrushVersion version to require with composer (can be different e.g. for RC versions)
     * @param string|null $expectedVersion version to compare
     */
    public function testDrushVersion(string $composerDrushVersion, string $expectedVersion = null): void
    {
        if (null === $expectedVersion) {
            $expectedVersion = $composerDrushVersion;
        }
        if (version_compare('5.6', phpversion()) > 0 && version_compare($expectedVersion, '9.0') > 0) {
            $this->markTestSkipped(phpversion() . ' too low for drush ' . $expectedVersion);
        }

        $cwd = getcwd();
        $this->ensureDirectoryExistsAndClear($this->tmpDir);
        chdir($this->tmpDir);
        $this->writeComposerJSON();
        $this->composer(
            'require --no-progress --no-suggest --update-with-dependencies drush/drush:"' . $composerDrushVersion . '"'
        );
        $actualVersion = $this->taskDrushStack($this->tmpDir . '/vendor/bin/drush')
            ->getVersion();
        $this->assertEquals($expectedVersion, $actualVersion);
        chdir($cwd);
    }

    /**
     * Should return an array of arrays with the following values:
     * 0: $composerDrushVersion (can be different e.g. for RC versions)
     * 1: $expectedVersion
     */
    public function drushVersionProvider(): array
    {
        return [
            '8' => ['8.1.15'],
            '9-rc1' => ['9.0.0-rc1', '9.0.0'],
            '9' => ['9.4.0'],
        ];
    }

    /**
     * Writes the default composer.json to the temp directory.
     */
    protected function writeComposerJSON(): void
    {
        $json = json_encode($this->composerJSONDefaults(), JSON_PRETTY_PRINT);
        file_put_contents($this->tmpDir . '/composer.json', $json);
    }

    /**
     * Provides the default composer.json data.
     */
    protected function composerJSONDefaults(): array
    {
        return [
            'minimum-stability' => 'beta',
        ];
    }

    /**
     * Wrapper for the composer command.
     *
     * @param string $command composer command name, arguments and/or options
     *
     * @throws RuntimeException
     */
    protected function composer(string $command): void
    {
        exec(escapeshellcmd('composer -q ' . $command), $output, $exitCode);
        if ($exitCode !== 0) {
            throw new RuntimeException('Composer returned a non-zero exit code.');
        }
    }

    /**
     * Makes sure the given directory exists and has no content.
     */
    protected function ensureDirectoryExistsAndClear(string $directory): void
    {
        if (is_dir($directory)) {
            $this->fs->remove($directory);
        }
        $this->fs->mkdir($directory, 0777);
    }
}

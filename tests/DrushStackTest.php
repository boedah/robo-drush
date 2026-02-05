<?php

declare(strict_types=1);

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Robo\Collection\CollectionBuilder;
use Robo\Robo;
use Robo\TaskAccessor;
use Robo\Tasks;
use Symfony\Component\Filesystem\Filesystem;

final class DrushStackTest extends TestCase implements ContainerAwareInterface
{
    use \Boedah\Robo\Task\Drush\Tasks;
    use TaskAccessor;
    use ContainerAwareTrait;

    protected Filesystem $fs;

    protected string $tmpDir;

    // Set up the Robo container so that we can create tasks in our tests.
    protected function setUp(): void
    {
        $container = Robo::createContainer();
        $this->setContainer($container);

        // set empty collection builder
        $emptyRoboFile = new Tasks;
        $container->addShared('collectionBuilder');
        $this->getContainer()->extend('collectionBuilder')->setConcrete(
            new CollectionBuilder($emptyRoboFile)
        );

        // Prepare temp directory.
        $this->fs = new Filesystem();
        $this->tmpDir = realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR . 'robo-drush';
    }

    protected function collectionBuilder()
    {
        return $this->getContainer()->get('collectionBuilder');
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
        $this->assertSame(2, preg_match_all('#-r /var/www/html/app#', (string)$command));
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

    #[TestWith([true, ' --existing-config'], 'true')]
    #[TestWith([false, ''], 'false')]
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

    public function testSiteAliasIsFirstOption(): void
    {
        $command = $this->taskDrushStack()
            ->drupalRootDirectory('/var/www/html/app')
            ->siteAlias('@qa')
            ->drush('command-1')
            ->drush('command-2')
            ->getCommand();
        $this->assertSame(2, preg_match_all('#drush @qa comm#', (string)$command));
    }

    public function testDrushStatus(): void
    {
        $this->writeFakeDrupalAutoload();
        $result = $this->taskDrushStack(__DIR__ . '/../vendor/bin/drush')
            ->printOutput(false)
            ->status()
            ->run();
        $this->assertTrue($result->wasSuccessful(), 'Exit code was: ' . $result->getExitCode());
    }

    /**
     * @param string $composerDrushVersion version to require with composer (can be different e.g. for RC versions)
     * @param string|null $expectedVersion version to compare
     */
    #[TestWith(['12.5.3', '12.5.3.0'], '12.5.3')]
    #[TestWith(['13.7.1', '13.7.1.0'], '13.7.1')]
    #[TestWith(['13.7.0-rc1', '13.7.0.0'], '13.7.0.0-rc1')]
    public function testDrushVersion(
        string $composerDrushVersion,
        string $expectedVersion = null
    ): void {
        if (null === $expectedVersion) {
            $expectedVersion = $composerDrushVersion;
        }

        // check for incompatible PHP versions: skip if PHP < 8.3 and drush >= 13.0
        if (version_compare('8.3', phpversion()) === 1 && version_compare($expectedVersion, '13.0') === 1) {
            $this->markTestSkipped(phpversion() . ' too low for drush ' . $expectedVersion);
        }

        // set up the directory
        $cwd = getcwd();
        $this->ensureDirectoryExistsAndClear($this->tmpDir);
        chdir($this->tmpDir);

        // composer require
        $this->writeComposerJSON();
        $composerRequireFlags = '--no-plugins --no-progress --no-suggest --update-with-dependencies';
        $this->composer("require $composerRequireFlags drupal/core drush/drush:$composerDrushVersion");

        $this->writeFakeDrupalAutoload();

        // assert
        $actualVersion = $this->taskDrushStack('vendor/bin/drush')
            ->getVersion();
        $this->assertEquals($expectedVersion, $actualVersion);

        // change back
        chdir($cwd);
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
     * Drush 13+ checks for the presence of vendor/drupal/autoload.php,
     * so we write an empty file.
     *
     * @see DrupalBoot8::validRoot
     */
    protected function writeFakeDrupalAutoload(): void
    {
        touch('vendor/drupal/autoload.php');
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

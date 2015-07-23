<?php

class DrushStackTest extends \PHPUnit_Framework_TestCase
{
    use Boedah\Robo\Task\Drush\loadTasks;

    public function testYesIsAssumed()
    {
        $command = $this->taskDrushStack()
            ->exec('command')
            ->getCommand();
        $this->assertEquals('drush command -y', $command);
    }

    public function testAbsenceofYes()
    {
        $command = $this->taskDrushStack()
                        ->exec('command', false)
                        ->getCommand();
        $this->assertEquals('drush command', $command);
    }

    public function testOptionsArePrependedBeforeEachCommand()
    {
        $command = $this->taskDrushStack()
            ->drupalRootDirectory('/var/www/html/app')
            ->exec('command-1')
            ->exec('command-2')
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
            ->exec('command-1')
            ->exec('command-2')
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

}

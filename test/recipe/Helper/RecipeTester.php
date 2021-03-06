<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer\Helper;

use Deployer\Deployer;
use Deployer\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;

abstract class RecipeTester extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApplicationTester
     */
    private $tester;

    /**
     * @var Deployer
     */
    protected $deployer;

    /**
     * @var string
     */
    protected static $deployPath;

    public static function setUpBeforeClass()
    {
        // Prepare FS
        self::$deployPath = __DIR__ . '/../../localhost';
        self::cleanUp();
        mkdir(self::$deployPath);
        self::$deployPath = realpath(self::$deployPath);
    }

    public function setUp()
    {
        // Create App tester.
        $console = new Application();
        $console->setAutoExit(false);
        $console->setCatchExceptions(false);
        $this->tester = new ApplicationTester($console);

        // Prepare Deployer
        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $this->deployer = new Deployer($console, $input, $output);

        // Load recipe
        localServer('localhost')
            ->env('deploy_path', self::$deployPath);
        $this->loadRecipe();

        // Init Deployer
        $this->deployer->addConsoleCommands();
    }


    public static function tearDownAfterClass()
    {
        self::cleanUp();
    }

    /**
     *  Remove deploy directory from file system.
     */
    protected static function cleanUp()
    {
        if (is_dir(self::$deployPath)) {
            exec('rm -rf ' . self::$deployPath);
        }
    }

    /**
     * Execute command with tester.
     *
     * @param string $command
     */
    protected function exec($command)
    {
        $this->tester->run(['command' => $command]);
    }

    /**
     * Load or describe recipe.
     *
     * @return void
     */
    abstract protected function loadRecipe();

    /**
     * @param string $name
     * @return string
     */
    protected function getEnv($name)
    {
        return $this->deployer->environments->get('localhost')->get($name);
    }
}

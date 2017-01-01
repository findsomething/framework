<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 16/12/20
 * Time: 17:24
 */
namespace FSth\Framework\Command;

use Symfony\Component\Console\Command\Command;

abstract class BaseCommand extends Command
{
    protected $kernel;

    public function __construct($kernel)
    {
        parent::__construct();
        $this->kernel = $kernel;
    }
}
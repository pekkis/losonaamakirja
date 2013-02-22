<?php

namespace Losofacebook\Command;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Losofacebook\Service\AsseticService;

class AssetizeCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('deploy:assetize')
            ->setDescription('Assetizes assets');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectDir = realpath($this->getProjectDirectory());
        
        $options = array(
            'javaPath' => '/usr/bin/java',
            'closureCompilerPath' => $projectDir . "/app/dev/compiler.jar",
            'nodePath' => '/usr/bin/node',
            'nodePaths' => array($projectDir . '/node_modules'),
            'optiPngPath' => '/usr/bin/optipng',
            'jpegOptimPath' => '/usr/bin/jpegoptim',

            'collections' => array(
                'essentialjs' => array(
                    'write' => array('combined' => true, 'leaves' => false),
                    'cache' => false,
                    'options' => array(
                        'debug' => false,
                        'name' => 'essential',
                        'output' => 'assets/*',
                    ),
                    'filters' => '?closure',
                    'inputs' => array(

                    )
                ),
                'css' => array(
                    'write' => array('combined' => true, 'leaves' => false),
                    'cache' => false,
                    'options' => array(
                        'debug' => false,
                        'name' => 'common',
                        'output' => 'assets/*',
                    ),
                    'filters' => 'less',
                    'inputs' => array(
                    )
                )


            ),
            
            /*
            'parser' => array(
                'lus' => array(
                    'debug' => false,
                    'directory' => APPLICATION_PATH . '/assets',
                    'blacklist' => array(),
                    'files' => array(
                        'jpg' => array(
                            'pattern' => "/\.jpg$/",
                            'filters' => array('?jpegoptim'),
                            'output' => 'assets/*.jpg',
                        ),
                        'png' => array(
                            'pattern' => "/\.png$/",
                            'filters' => array('?optipng'),
                            'output' => 'assets/*.png',
                        ),
                        'ttf' => array(
                            'pattern' => "/\.ttf$/",
                            'filters' => array(),
                            'output' => 'assets/*.ttf',
                        ),


                    ),

                )

            ),
             */
        );

        $asseticService = new AsseticService($projectDir, $options);
        $asseticService->init();


    }
}




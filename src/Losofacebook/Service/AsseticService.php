<?php

namespace Losofacebook\Service;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use Assetic\AssetManager;
use Assetic\FilterManager;
use Assetic\Filter\LessFilter;
use Assetic\Filter\OptiPngFilter;
use Assetic\Filter\JpegoptimFilter;
use Assetic\Cache\FilesystemCache;
use Assetic\Filter\GoogleClosure\CompilerJarFilter;
use Assetic\Factory\AssetFactory;
use Assetic\Asset\FileAsset;
use Assetic\AssetWriter;


class AsseticService
{
   
    private $projectDir;
    
    private $options = [];

    private $assetManager;

    private $filterManager;

    private $assetFactory;

    private $assetCache;

    public function __construct($projectDir, $options)
    {
        $this->projectDir = $projectDir;
        $this->options = $options;
    }


    public function getOptions()
    {
        return $this->options;
    }

    public function getAssetCache()
    {
        if(!$this->assetCache) {
            $this->assetCache = new FilesystemCache($this->projectDir . '/app/data/cache'));
        }
        return $this->assetCache;
    }


    /**
     * Returns asset manager
     *
     * @return AssetManager
     */
    public function getAssetManager()
    {
        if(!$this->assetManager) {
            $this->assetManager = new AssetManager();
        }
        return $this->assetManager;
    }

    /**
     * Returns filter manager
     *
     * @return FilterManager
     */
    public function getFilterManager()
    {
        if(!$this->filterManager) {

            $options = $this->getOptions();

            $this->filterManager = new FilterManager();

            $lessFilter = new LessFilter($options['nodePath'], $options['nodePaths']);
            $lessFilter->setCompress(true);

            $this->filterManager->set('less', $lessFilter);

            $this->filterManager->set('closure', new CompilerJarFilter($options['closureCompilerPath'], $options['javaPath']));

            $jpegOptimFilter = new JpegoptimFilter($options['jpegOptimPath']);
            $jpegOptimFilter->setStripAll(true);

            $this->filterManager->set('jpegoptim', $jpegOptimFilter);

            $optiPngFilter = new OptiPngFilter($options['optiPngPath']);
            $optiPngFilter->setLevel(2);

            $this->filterManager->set('optipng', $optiPngFilter);

            // $this->filterManager->set('closure', new \Assetic\Filter\UglifyJsFilter($options['uglifyPath']));
        }

        return $this->filterManager;
    }

    /**
     * Returns asset factory
     *
     * @return AssetFactory
     */
    public function getAssetFactory()
    {
        if(!$this->assetFactory) {
            $this->assetFactory = new AssetFactory($this->projectDir . '/web');
            $this->assetFactory->setAssetManager($this->getAssetManager());
            $this->assetFactory->setFilterManager($this->getFilterManager());
        }
        return $this->assetFactory;
    }


    public function init()
    {
        $options = $this->getOptions();
        $this->initAssets($options);
        return $this;
    }



    public function initAssets()
    {
        $options = $this->getOptions();

        $defaultWoptions = array(
            'combined' => true,
            'leaves' => false,
        );
        $woptions = array();

        $am = $this->getAssetManager();

        $fm = $this->getFilterManager();

        $fassets = array();

        if (isset($options['parser'])) {

            foreach ($options['parser'] as $parser) {

                $filters = array();
                foreach ($parser['files'] as $key => $file) {

                    $filters[$key] = array();

                    if(isset($file['filters'])) {
                        foreach($file['filters'] as $f) {

                            if (substr($f, 0, 1) == '?') {
                                $fn = substr($f, 1);
                                $init = (bool) !$parser['debug'];
                            } else {
                                $fn = $f;
                                $init = true;
                            }

                            if($init) {

                                if($fm->has($fn)) {
                                    $filters[$key][] = $fm->get($fn);
                                }
                            }
                        }
                    }

                }

                $diterator = new \RecursiveDirectoryIterator($parser['directory']);
                $riterator = new RecursiveIteratorIterator($diterator, \RecursiveIteratorIterator::SELF_FIRST);

                foreach ($riterator as $file) {

                    $skip = false;

                    if(isset($parser['blacklist'])) {
                        foreach($parser['blacklist'] as $bl) {
                            if (preg_match($bl, $file->getPathName())) {
                                $skip = true;
                            }
                        }
                    }

                    if ($skip) {
                        continue;
                    }

                    foreach($parser['files'] as $key => $fopts) {

                        $ppinfo = pathinfo($fopts['output']);

                        if ($file->isFile() && preg_match($fopts['pattern'], $file->getFilename())) {

                            $pinfo = pathinfo($file);

                            $pinfo['dirname'] = str_ireplace($parser['directory'], $ppinfo['dirname'], $pinfo['dirname']);
                            $pinfo['extension'] = $ppinfo['extension'];

                            $fasset = new FileAsset($file->getPathName(), $filters[$key]);
                            $fasset->setTargetPath($pinfo['dirname'] . '/' . $pinfo['filename'] . '.' . $pinfo['extension']);

                            $fassets[] = $fasset;

                        }

                    }
                }
            }
        }

        $f = $this->getAssetFactory();
        if(isset($options['collections'])) {
            foreach($options['collections'] as $name => $coll) {

                $woptions[$name] = isset($coll['write']) ? $coll['write'] : array();
                $woptions[$name] = array_merge($defaultWoptions, $woptions[$name]);

                // $coll['options']['name'] = str_ireplace("{APPLICATION_REVISION}", APPLICATION_REVISION, $coll['options']['name']);
                $asset = $f->createAsset($coll['inputs'], $coll['filters'], $coll['options']);
                if($coll['cache']) {
                    $asset = new AssetCache($asset, $this->getAssetCache());
                }
                $am->set($name, $asset);
            }
        }

        if(isset($options['fileAssets'])) {
            foreach($options['fileAssets'] as $name => $fileAsset) {

                $asset = new \Assetic\Asset\FileAsset($fileAsset['path']);
                $asset->setTargetPath($fileAsset->getTargetPath());

                if(isset($fileAsset['filters'])) {
                    foreach($fileAsset['filters'] as $filter) {
                        $f = $this->getFilterManager()->get($filter);
                        $asset->ensureFilter($f);
                    }
                }

                $am->set($name, $asset);

            }

        }



        $am = $this->getAssetManager();

        $writer = new AssetWriter(APPLICATION_PATH . '/public');

        foreach ($fassets as $fasset) {

            if(file_exists($this->projectDir . '/web' . '/' . $fasset->getTargetPath())) {

                $amod = filemtime($this->projectDir . '/web' . '/' . $fasset->getTargetPath());

                if ($fasset->getLastModified() <= $amod) {
                    continue;
                }



            }
            $writer->writeAsset($fasset);

        }

        foreach ($am->getNames() as $name) {

            $asset = $am->get($name);
            if(file_exists($this->projectDir . '/web' . '/' . $asset->getTargetPath())) {
                $amod = filemtime($this->projectDir . '/web' . '/' . $asset->getTargetPath());
                if ($asset->getLastModified() <= $amod) {
                    continue;
                }
            }


            $writeOptions = $woptions[$name];

            if($writeOptions['combined']) {
                $writer->writeAsset($asset);
            }

            if($writeOptions['leaves']) {
                foreach($asset as $leaf) {
                    $writer->writeAsset($leaf);
                }
            }
        }

    }










}

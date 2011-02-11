<?php

namespace Knplabs\Symfony2BundlesBundle;

class Symfony2Detector
{
    protected $dir;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * Tries to guess if this dir contains a SF2 project.
     * This is made quite hard by SF2 flexibility.
     *
     * @return bool
     */
    public function isProject()
    {
        if($this->hasSymfonySubmodule()) {
            return true;
        }

        if($this->hasSymfonyKernel()) {
            return true;
        }

        return false;
    }

    protected function hasSymfonySubmodule()
    {
        $gitmodules = $this->dir.'/.gitmodules';

        return file_exists($gitmodules) && preg_match('/symfony/is', file_get_contents($gitmodules));
    }

    protected function hasSymfonyKernel()
    {
        $command = sprintf('find "%s" -mindepth 2 -maxdepth 2 -iname "*Kernel.php" 2>/dev/null', $this->dir);
        $kernelCandidate = exec($command, $kernelCandidates);

        foreach($kernelCandidates as $kernelCandidate) {
            if(preg_match('/public function registerBundles/s', file_get_contents($kernelCandidate))) {
                return true;
            }
        }

        return false;
    }
}

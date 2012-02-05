<?php

namespace Knp\Bundle\KnpBundlesBundle\Badge;

use Imagine\Image\Point;
use Imagine\Image\Color;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

class BadgeGenerator
{
    /**
     * Instace of Imagine lib acсording to image lib 
     */
    protected $imagine; 

    /**
     * Get app root dir
     */
    protected $rootDir;

    /**
     * Get default font
     */
    protected $font = 'arial.ttf';

    public function __construct($imagine)
    {
        $this->imagine = $imagine;
    }

    /**
     * Generate Badge images
     *
     * @param Bundle $bundle
     * @param string $env environment
     */
    public function generate(Bundle $bundle, $env)
    {
        $bundleName = $this->shorten($bundle->getName(), 23);
        $score = $bundle->getScore();
        $recommenders = $bundle->getNbRecommenders();

        // Open bg badge image        
        $image = $this->imagine->open($this->getResourceDir().'/images/badge.png');

        // Bundle Title
        $image->draw()->text($bundleName, $this->setFont($this->imagine, $this->font, 15), new Point(67, 12));

        // Score
        if ($score) {
            // Center score position
            $y = 23;
            switch (strlen($score)) {
                case 1:
                    $x = 23;
                    break;

                case 2:
                    $x = 17;
                    break;

                case 3:
                    $x = 11;
                    break;

                default:
                    $x = 11;
                    break;
            }

            $image->draw()->text($score, $this->setFont($this->imagine, $this->font, 18), new Point($x, $y));
        }

        // Recommend
        if ($recommenders) {
            $recommendationsText = 'by '.$recommenders.' developers';
        } else {
            $recommendationsText = 'No recommendations';
        }
        $image->draw()->text(
            $recommendationsText, 
            $this->setFont($this->imagine, $this->font, 8), 
            new Point(98, 40)
        );

        // Check or create dir for generated badges
        $this->createBadgesDir($env);

        // Remove existing badge
        $this->removeIfExist($this->getBadgeFile($bundle, $env));

        // Save badge
        $image->save($this->getBadgeFile($bundle, $env));
    }

    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Return full font path
     *
     * @param Imagine $imagine
     * @param string $font
     * @param integer $size
     * @param string $color
     * @return string
     */
    protected function setFont($imagine, $font, $size, $color = '000')
    {
        return $imagine->font($this->getResourceDir().'/fonts/'.$font, $size, new Color($color));
    }

    /**
     * Get badge image full path
     *
     * @param Bundle $bundle
     * @param string $env
     * @return string
     */
    protected function getBadgeFile(Bundle $bundle, $env)
    {
        return $this->rootDir.'/cache/'.$env.'/badges/'.$bundle->getUsername().'-'.$bundle->getName().'.png';
    }

    protected function getResourceDir()
    {
        return __DIR__.'/../Resources/badge';
    }

    /**
     * Trim long bundle name
     *
     * @param string $name
     * @param integer $lenght symbol count from the end
     * @return string
     */
    protected function shorten($name, $lenght)
    {
        if ($lenght < strlen($name)) {
            $name = '...'.substr($name, -1 * $lenght);
        }

        return $name;
    }

    /**
     * Check and create a dir for uploaded badges
     * 
     * @param string $env
     * @return void
     */
    protected function createBadgesDir($env)
    {
        $dir = $this->rootDir.'/cache/'.'/'.$env.'/badges';
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755)) {
                throw new \Exception('Can\'t create a "badges" folder under the cache/'.$env);
            }
        }
    }

    /**
     * Remove previously generated badge
     * 
     * @param string $file
     * @return void
     */
    protected function removeIfExist($file)
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
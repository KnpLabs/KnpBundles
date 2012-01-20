<?php

namespace Knp\Bundle\KnpBundlesBundle\Badge;

use Imagine\Gd\Imagine;
use Imagine\Image\Point;
use Imagine\Image\Color;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

class BadgeGenerator
{
    /**
     * Get app root dir
     */
    protected $rootDir;

    /**
     * Get generated bages upload dir
     */
    protected $uploadDir;

    /**
     * Get default font
     */
    protected $font = 'arial.ttf';

    /**
     * Generate Badge images
     *
     * @param string $bundle
     */
    public function generate(Bundle $bundle)
    {
        $bundleName = $this->shotify($bundle->getName(), 23);
        $score = $bundle->getScore();
        $recommenders = $bundle->getNbRecommenders();

        // Init imagine lib
        $imagine = new Imagine();

        // Open bg badge image        
        $image = $imagine->open($this->getResourceDir().'/images/badge.png');

        // Bundle Title
        $image->draw()->text($bundleName, $this->setFont($imagine, $this->font, 15), new Point(67, 12));

        // Score
        if ($score) {
            // Center score position
            switch (strlen($score)) {
                case 1:
                    $x = 23;
                    $y = 23;
                    break;

                case 2:
                    $x = 17;
                    $y = 23;
                    break;

                case 3:
                    $x = 11;
                    $y = 23;
                    break;

                default:
                    $x = 11;
                    $y = 23;
                    break;
            }

            $image->draw()->text($score, $this->setFont($imagine, $this->font, 18), new Point($x, $y));
        }

        // Recommend
        if ($recommenders) {
            $recommendationsText = 'by '.$recommenders.' developers';
        } else {
            $recommendationsText = 'No recommendations';
        }
        $image->draw()->text(
            $recommendationsText, 
            $this->setFont($imagine, $this->font, 8), 
            new Point(98, 40)
        );

        // Remove existing badge
        $this->removeIfExist($this->getBadgeFile($bundle));

        // Save badge
        $image->save($this->getBadgeFile($bundle));
    }

    public function setUploadDir($uploadDir)
    {
        $this->uploadDir = $uploadDir;
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
     * @return string
     */
    protected function getBadgeFile(Bundle $bundle)
    {
        return $this->uploadDir.'/'.$bundle->getUsername().'-'.$bundle->getName().'.png';
    }

    protected function getResourceDir()
    {
        return $this->rootDir.'/../web/bundles/knpbundles';
    }

    /**
     * Trim long bundle name
     *
     * @param string $name
     * @param integer $lenght symbol count from the end
     */
    protected function shotify($name, $lenght)
    {
        if ($lenght < strlen($name)) {
            $name = '...'.substr($name, -1 * $lenght);
        }

        return $name;
    }

    protected function removeIfExist($file)
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}

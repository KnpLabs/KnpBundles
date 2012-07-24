<?php

namespace Knp\Bundle\KnpBundlesBundle\Badge;

use Imagine\Image\Point;
use Imagine\Image\Color;
use Imagine\Image\ImagineInterface;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

class BadgeGenerator
{
    // Badge types
    const LONG  = 'long';
    const SHORT = 'short';

    /**
     * Instace of Imagine lib acсording to image lib
     *
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * Get app cache dir
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * Get default font
     *
     * @var string
     */
    protected $font = 'arial.ttf';

    /**
     * Get badge type
     *
     * @var array
     */
    protected $type = array(
        self::LONG  => 'badge-mock.png',
        self::SHORT => 'badge-line-mock.png'
    );

    /**
     * Determine score points position
     *
     * @var array
     */
    protected $position = array(
        self::LONG => array(
            // n => x:y
            // n+1 - score count number
            '29:16',
            '23:16',
            '15:16',
            '10:16'
        ),
        self::SHORT => array(
            '28:5',
            '22:5',
            '14:5',
            '6:5'
        )
    );

    /**
     * Constructor
     *
     * @param ImagineInterface $imagine
     */
    public function __construct(ImagineInterface $imagine)
    {
        $this->imagine = $imagine;
    }

    /**
     * Generate Badge images
     *
     * @param Bundle $bundle
     */
    public function generate(Bundle $bundle)
    {
        $bundleName = $this->shorten($bundle->getName(), 15);
        $score = $bundle->getScore() ?: 'N/A';
        $recommenders = $bundle->getNbRecommenders();

        // Open bg badge image
        $image = $this->imagine->open($this->getResourceDir().'/images/'.$this->getImageMockByType(self::LONG));
        $imageShort = $this->imagine->open($this->getResourceDir().'/images/'.$this->getImageMockByType(self::SHORT));

        // Bundle Title
        $image->draw()->text($bundleName, $this->setFont($this->imagine, $this->font, 14), new Point(77, 10));

        // Score points
        $image->draw()->text($score, $this->setFont($this->imagine, $this->font, 18), $this->getPositionByType($score, self::LONG));
        $imageShort->draw()->text($score, $this->setFont($this->imagine, $this->font, 18), $this->getPositionByType($score, self::SHORT));


        // Recommend
        if ($recommenders) {
            $recommendationsText = 'by '.$recommenders.' developers';
        } else {
            $recommendationsText = 'No recommendations';
        }
        $image->draw()->text(
            $recommendationsText,
            $this->setFont($this->imagine, $this->font, 8),
            new Point(98, 34)
        );

        // Check or create dir for generated badges
        $this->createBadgesDir();

        // Remove existing badge
        $this->removeIfExist($this->getBadgeFile($bundle));
        $this->removeIfExist($this->getBadgeFile($bundle, self::SHORT));

        // Save badge
        $image->save($this->getBadgeFile($bundle));
        $imageShort->save($this->getBadgeFile($bundle, self::SHORT));
    }

    /**
     * @param string $cacheDir
     */
    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * Return full font path
     *
     * @param ImagineInterface $imagine
     * @param string $font
     * @param integer $size
     * @param string $color
     *
     * @return string
     */
    protected function setFont(ImagineInterface $imagine, $font, $size, $color = '8c96a0')
    {
        return $imagine->font($this->getResourceDir().'/fonts/'.$font, $size, new Color($color));
    }

    /**
     * Get badge image full path
     *
     * @param Bundle $bundle
     * @param string $type
     *
     * @return string
     */
    protected function getBadgeFile(Bundle $bundle, $type = self::LONG)
    {
        return $this->cacheDir.'/badges/'.$type.'/'.$bundle->getUsername().'-'.$bundle->getName().'.png';
    }

    /**
     * @return string
     */
    protected function getResourceDir()
    {
        return __DIR__.'/../Resources/badge';
    }

    /**
     * Trim long bundle name
     *
     * @param string $name
     * @param integer $lenght symbol count from the end
     *
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
     * @throws \RuntimeException
     */
    protected function createBadgesDir()
    {
        $dir = $this->cacheDir.'/badges';

        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755)) {
                throw new \RuntimeException("Can't create the 'badges' folder in ".$this->cacheDir);
            }
        }

        // Create badge types folder
        foreach ($this->type as $type => $image) {
            if (!is_dir($dir.'/'.$type) && !@mkdir($dir.'/'.$type, 0755)) {
                throw new \RuntimeException(sprintf("Can't create the '%s' folder in %s", $type, $dir));
            }
        }
    }

    /**
     * Remove previously generated badge
     *
     * @param string $file
     */
    protected function removeIfExist($file)
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Get background image
     *
     * @param string $type
     *
     * @return string
     */
    protected function getImageMockByType($type)
    {
        return $this->type[$type];
    }

    /**
     * Get score points position x:y
     *
     * @param string $type
     * @param integer|string $score
     *
     * @return Point
     */
    protected function getPositionByType($score, $type)
    {
        // Count scores numbers
        $n = (strlen($score) - 1) ?: 0;

        $coordinates = explode(':', ($this->position[$type][$n]));

        return new Point($coordinates[0], $coordinates[1]);
    }
}

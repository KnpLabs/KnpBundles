<?php

namespace Knp\Bundle\KnpBundlesBundle\Badge;

use Imagine\Image\Point;
use Imagine\Image\Color;
use Imagine\Image\ImagineInterface;
use Symfony\Component\Filesystem\Filesystem;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

class BadgeGenerator
{
    // Badge types
    const LONG  = 'long';
    const SHORT = 'short';

    /**
     * Get app cache dir
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * Get app root dir
     *
     * @var string
     */
    protected $rootDir;

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
            '29:10',
            '23:10',
            '15:10',
            '10:10'
        ),
        self::SHORT => array(
            '23:5',
            '17:5',
            '9:5',
            '3:5'
        )
    );

    /**
     * Instance of Imagine lib according to image lib
     *
     * @var ImagineInterface
     */
    private $imagine;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Constructor
     *
     * @param ImagineInterface $imagine
     * @param null|Filesystem  $filesystem
     */
    public function __construct(ImagineInterface $imagine, $filesystem = null)
    {
        $this->imagine    = $imagine;
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    /**
     * @param Bundle  $bundle
     * @param string  $type
     * @param boolean $regenerate
     *
     * @return string
     */
    public function show(Bundle $bundle, $type = 'long', $regenerate = false)
    {
        $relativePath = $this->filesystem->makePathRelative(
            $this->cacheDir,
            $this->rootDir
        );

        $filename = sprintf('%s/badges/%s/%s-%s.png', rtrim($relativePath, '/'), $type, $bundle->getOwnerName(), $bundle->getName());
        if (false !== $regenerate || !$this->filesystem->exists($filename)) {
            $this->generate($bundle);
        }

        return $filename;
    }

    /**
     * Generate Badge images
     *
     * @param Bundle $bundle
     */
    public function generate(Bundle $bundle)
    {
        // Open bg badge image
        $image      = $this->imagine->open($this->getResourceDir().'/images/'.$this->type[self::LONG]);
        $imageShort = $this->imagine->open($this->getResourceDir().'/images/'.$this->type[self::SHORT]);

        // Bundle Title
        $bundleName = $this->shorten($bundle->getName(), 16);
        $image->draw()->text($bundleName, $this->setFont($this->imagine, $this->font, 14, '085066'), new Point(75, 10));

        // Score points
        $score = $bundle->getScore() ?: 'N/A';
        $image->draw()->text($score, $this->setFont($this->imagine, $this->font, 18), $this->getPositionByType($score, self::LONG));
        $imageShort->draw()->text($score, $this->setFont($this->imagine, $this->font, 16), $this->getPositionByType($score, self::SHORT));


        // Recommend
        $recommenders = $bundle->getNbRecommenders();
        if ($recommenders) {
            $recommendationsText = $recommenders.' recommendations';
        } else {
            $recommendationsText = 'No recommendations';
        }
        $image->draw()->text(
            $recommendationsText,
            $this->setFont($this->imagine, $this->font, 9),
            new Point(92, 33)
        );

        // Check or create dir for generated badges
        $this->createBadgesDir();

        // Remove existing badge
        $this->filesystem->remove($this->getBadgeFile($bundle));
        $this->filesystem->remove($this->getBadgeFile($bundle, self::SHORT));

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
     * @param string $rootDir
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Return full font path
     *
     * @param ImagineInterface $imagine
     * @param string           $font
     * @param integer          $size
     * @param string           $color
     *
     * @return string
     */
    protected function setFont(ImagineInterface $imagine, $font, $size, $color = 'ffffff')
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
        return $this->cacheDir.'/badges/'.$type.'/'.$bundle->getOwnerName().'-'.$bundle->getName().'.png';
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
     * @param string  $name
     * @param integer $length symbol count from the end
     *
     * @return string
     */
    protected function shorten($name, $length)
    {
        if ($length < strlen($name)) {
            $name = '...'.substr($name, -1 * $length);
        }

        return $name;
    }

    /**
     * Check and create a dir for uploaded badges
     */
    protected function createBadgesDir()
    {
        $dir = $this->cacheDir.'/badges';

        $this->filesystem->mkdir($dir, 0755);

        // Create badge types folder
        foreach ($this->type as $type => $image) {
            $this->filesystem->mkdir($dir.'/'.$type, 0755);
        }
    }

    /**
     * Get score points position x:y
     *
     * @param integer|string $score
     * @param string         $type
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

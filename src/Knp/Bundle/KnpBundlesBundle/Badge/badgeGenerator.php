<?php

namespace Knp\Bundle\KnpBundlesBundle\Badge;

use Imagine;

class BadgeGenerator
{
	protected $bundleName;

	protected $score;
		
    protected $recommend;

    protected $container;

    public function __construct($container) 
    {
		$this->container = $container;
	}

    /**
     * Generate Badge images 
     *
     * @param string $bundleName
     * @param integer $score
     * @param integer $recommend
     */
	public function generate($bundleName, $score = null, $recommend = null)
	{
		$this->bundleName = $bundleName; 
		$this->score = $score;
		$this->recommend = $recommend;

        // Init imagine lib
		$imagine = new Imagine\GD\Imagine();

        // Open bg badge image        
		$image = $imagine->open($this->getBadgeDir() . '/Image/badge.png');
        
        // Bundle Title
        $image->draw()->text($this->bundleName, $this->setFont($imagine, 15), new Imagine\Image\Point(67, 12));

        // Score
        if ($this->score) {
        	// Center score position
        	switch (strlen($this->score)) {
        		case 1:
        			$x = 23; $y = 23;
        			break;

        		case 2:
        			$x = 17; $y = 23;
        			break;	

        		case 3:
        			$x = 11; $y = 23;
        			break;	
        		
        		default:
        			$x = 11; $y = 23;
        			break;
        	}
        	
            $image->draw()->text($this->score, $this->setFont($imagine, 18), new Imagine\Image\Point($x, $y));
        }

        // Recommend
        if ($this->recommend) {
            $image->draw()
                ->text('by ' . $this->recommend . ' developers', $this->setFont($imagine, 8), new Imagine\Image\Point(98, 40));
        }

        // Remove existing badge
        $this->removeIfExist($this->getBadgeFile());

        // Save badge
		$image->save($this->getBadgeFile());
	}

    /**
     * Return full font path
     *
     * @param Imagine $imagine
     * @param integer $size
     * @param string $color
     */
	protected function setFont($imagine, $size, $color = '000')
	{
		return $imagine->font($this->getBadgeDir() . '/Fonts/Arial.ttf', $size, new Imagine\Image\Color($color));
	}

	protected function getBadgeFile()
	{
		return $this->getUploadDir() . '/' . $this->bundleName . '.png';
	}

    protected function removeIfExist($file)
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }

    protected function getUploadDir()
    {
        return $this->container->getParameter('knp_bundles.badges_upload_dir');
    }

    protected function getBadgeDir()
    {
    	return $this->container->getParameter('kernel.root_dir') . '/../src/Knp/Bundle/KnpBundlesBundle/Badge';
    }
}

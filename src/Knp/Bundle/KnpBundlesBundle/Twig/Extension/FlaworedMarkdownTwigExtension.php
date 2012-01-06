<?php

namespace Knp\Bundle\KnpBundlesBundle\Twig\Extension;

class FlaworedMarkdownTwigExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            'github_md_2_md' => new \Twig_Filter_Method($this, 'githubMd2Md'),
        );
    }

    /**
     * Transform @githubFlaworedMarkdown into markdown
     *
     * @param string $githubFlaworedMarkdown
     * @return string
     */
    public function githubMd2Md($githubFlaworedMarkdown)
    {
        $types = array();
        $markdown = preg_replace_callback("@```[ ]*([^\n]*)(.+?)```@smi", function ($m) use (&$types) {
            $types[] = trim($m[1]);
            return str_replace("\n", "\n    ", $m[2]);
        }, $githubFlaworedMarkdown);
        // if need to know a block type, theres a list $types
        return $markdown;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'flawored_markdown';
    }
}

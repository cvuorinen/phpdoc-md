<?php

namespace PHPDocMD;

use Twig_Environment;
use Twig_Filter_Function;
use Twig_Loader_String;

/**
 * This class takes the output from 'parser', and generate the markdown
 * templates.
 *
 * @copyright Copyright (C) 2007-2012 Rooftop Solutions. All rights reserved.
 * @author    Evert Pot (http://www.rooftopsolutions.nl/)
 * @license   Mit
 */
class Generator
{
    /**
     * Output directory.
     *
     * @var string
     */
    protected $outputDir;

    /**
     * The list of classes and interfaces.
     *
     * @var array
     */
    protected $classDefinitions;

    /**
     * Directory containing the twig templates.
     *
     * @var string
     */
    protected $templateDir;

    /**
     * A simple template for generating links.
     *
     * @var string
     */
    protected $linkTemplate;

    /**
     * @var string
     */
    private $title;

    /**
     * @param array  $classDefinitions
     * @param string $outputDir
     * @param string $templateDir
     * @param string $linkTemplate
     * @param string $title
     * @param bool   $singleFile
     */
    public function __construct(
        array $classDefinitions,
        $outputDir,
        $templateDir,
        $linkTemplate = '%c.md',
        $title = 'API Index',
        $singleFile = true
    ) {
        $this->classDefinitions = $classDefinitions;
        $this->outputDir = $outputDir;
        $this->templateDir = $templateDir;
        $this->linkTemplate = $linkTemplate;
        $this->title = $title;
        $this->singleFile = $singleFile;
    }

    /**
     * Starts the generator.
     */
    public function run()
    {
        $content = '';
        $loader = new Twig_Loader_String();
        $twig = new Twig_Environment($loader);

        $GLOBALS['PHPDocMD_classDefinitions'] = $this->classDefinitions;
        $GLOBALS['PHPDocMD_linkTemplate'] = $this->linkTemplate;

        $twig->addFilter('classLink', new Twig_Filter_Function('PHPDocMd\Generator::classLink'));
        $twig->addFilter('stripPTags', new Twig_Filter_Function('PHPDocMd\\Generator::stripOuterParagraphTags'));

        foreach ($this->classDefinitions as $classInfo) {
            // skip all abstract classes and interfaces
            if ($classInfo['abstract'] || $classInfo['isInterface']) {
                continue;
            }

            $output = $twig->render(
                file_get_contents($this->templateDir . '/class.twig'),
                $classInfo
            );

            if ($this->singleFile) {
                $content .= $output;
            } else {
                file_put_contents($this->outputDir . '/' . $classInfo['fileName'], $output);
            }
        }

        $index = $this->createIndex();

        $content = $twig->render(
            file_get_contents($this->templateDir . '/index.twig'),
            array(
                'title' => $this->title,
                'index' => $index,
            )
        ) . $content;

        file_put_contents($this->outputDir . '/README.md', $content);
    }

    /**
     * Creates an index (table of contents) of classes and methods.
     *
     * I'm generating the actual markdown output here, which isn't great...But it will have to do.
     * If I don't want to make things too complicated.
     *
     * @return string
     */
    protected function createIndex()
    {
        $output = '';
        $links = array();

        foreach ($this->classDefinitions as $classInfo) {
            // skip all abstract classes and interfaces
            if ($classInfo['abstract'] || $classInfo['isInterface']) {
                continue;
            }

            $output .= $this->createLink(
                $classInfo['className'],
                $classInfo['shortClass'],
                $links
            );

            foreach ($classInfo['methods'] as $method) {
                // skip all but public methods
                if ($method['visibility'] !== 'public') {
                    continue;
                }

                $output .= $this->createLink(
                    $classInfo['className'],
                    $method['name'],
                    $links,
                    1
                );
            }
        }

        return $output;
    }

    /**
     * @param string $className
     * @param string $label
     * @param array  $links
     * @param int    $depth
     *
     * @return string
     */
    private function createLink($className, $label, array &$links, $depth = 0)
    {
        $anchor = strtolower($label);

        if ($this->singleFile) {
            // Check if we already have link to an anchor with the same name, and add count suffix
            $linkCounts = array_count_values($links);
            $anchorSuffix = array_key_exists($anchor, $linkCounts)
                ? '-' . $linkCounts[$anchor] : '';
            array_push($links, $anchor);

            $linkString =  sprintf("[%s](%s)", $label, '#' . $anchor . $anchorSuffix);
        } else {
            $linkString = self::classLink($className, $label, $anchor);
        }

        return str_repeat(' ', $depth * 4)  . '* ' . $linkString . "\n";
    }

    /**
     * This is a twig template function.
     *
     * This function allows us to easily link classes to their existing pages.
     *
     * Due to the unfortunate way twig works, this must be static, and we must use a global to
     * achieve our goal.
     *
     * @param string      $className
     * @param null|string $label
     * @param null|string $anchor
     *
     * @return string
     */
    public static function classLink($className, $label = null, $anchor = null)
    {
        $classDefinitions = $GLOBALS['PHPDocMD_classDefinitions'];
        $linkTemplate = $GLOBALS['PHPDocMD_linkTemplate'];

        $returnedClasses = array();

        foreach (explode('|', $className) as $oneClass) {
            $oneClass = trim($oneClass, '\\ ');

            if (!$label) {
                $label = $oneClass;
            }

            if (!isset($classDefinitions[$oneClass])) {
                $returnedClasses[] = $oneClass;
            } else {
                $link = str_replace('\\', '-', $oneClass);
                $link = strtr($linkTemplate, array('%c' => $link));
                $link .= ($anchor) ? '#' . $anchor : '';

                $returnedClasses[] = sprintf("[%s](%s)", $label, $link);
            }
        }

        return implode('|', $returnedClasses);
    }

    /**
     * This is a twig template function.
     *
     * This function just strips the outer <p> tags from an argument's description.
     * (Inner ```<p></p>``` tags are fine.)
     * @param  string $text
     * @return string
     */
    public static function stripOuterParagraphTags($text)
    {
        return preg_replace('/(^<p>|<\/p>$)/', null, $text);
    }
}

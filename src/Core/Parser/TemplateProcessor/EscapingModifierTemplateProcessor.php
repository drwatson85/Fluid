<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor;

/*
 * This file is part of the Neos.FluidAdaptor package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Preprocessor to detect the "escapingEnabled" inline flag in a template.
 */
class EscapingModifierTemplateProcessor implements TemplateProcessorInterface
{
    /**
     * @var RenderingContextInterface
     */
    protected $renderingContext;

    const SCAN_PATTERN_ESCAPINGMODIFIER = '/{(escaping|escapingEnabled)\s*=*\s*(true|false|on|off)\s*}/i';

    /**
     * @param RenderingContextInterface $renderingContext
     */
    public function setRenderingContext(RenderingContextInterface $renderingContext): void
    {
        $this->renderingContext = $renderingContext;
    }

    /**
     * Pre-process the template source before it is
     * returned to the TemplateParser or passed to
     * the next TemplateProcessorInterface instance.
     *
     * @param string $templateSource
     * @return string
     */
    public function preProcessSource(string $templateSource): string
    {
        if (strpos($templateSource, '{escaping') === false) {
            // No escaping modifier detected - early return to skip preg processing
            return $templateSource;
        }
        $matches = [];
        preg_match_all(static::SCAN_PATTERN_ESCAPINGMODIFIER, $templateSource, $matches, PREG_SET_ORDER);
        if (count($matches) > 1) {
            throw new Exception(
                'There is more than one escaping modifier defined. There can only be one {escapingEnabled=...} per template.',
                1407331080
            );
        } elseif ($matches === []) {
            return $templateSource;
        }
        $this->renderingContext->getTemplateParser()->getConfiguration()->setFeatureState(
            Configuration::FEATURE_ESCAPING,
            !(strtolower($matches[0][2]) === 'false' || strtolower($matches[0][2]) === 'off')
        );

        return str_replace($matches[0][0], '', $templateSource);
    }
}

<?php
namespace Flownative\Neos\Tideways;

/*
 * This file is part of the Flownative.Neos.Tideways package.
 *
 * (c) Flownative GmbH - https://www.flownative.com/
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Tideways\Profiler;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Package\Package as BasePackage;

/**
 * The Flownative.Neos.Tideways Package class adds additional instrumentation calls.
 */
class Package extends BasePackage
{
    /**
     * Invokes custom PHP code directly after the package manager has been initialized.
     *
     * @param Bootstrap $bootstrap The current bootstrap
     * @return void
     */
    public function boot(Bootstrap $bootstrap)
    {
        if (!class_exists(Profiler::class)) {
            return;
        }

        Profiler::watchCallback(
            'TYPO3\TypoScript\Core\Runtime_Original::render',
            function ($context) {
                $span = Profiler::createSpan('fusion-runtime');

                // 0 = $typoScriptPath
                $typoScriptPath = $context['args'][0];
                $span->annotate(['title' => 'Fusion render()', 'typoScriptPath' => $typoScriptPath]);

                return $span->getId();
            }
        );

        Profiler::watchCallback(
            'TYPO3\TypoScript\Core\Parser_Original::parse',
            function ($context) {
                $span = Profiler::createSpan('fusion-parser');

                // 0 = $sourceCode, 1 = $contextPathAndFilename
                $sourceCode = isset($context['args'][1]) ? $context['args'][1] : substr($context['args'][0], 0, 20);
                $span->annotate(['title' => 'Fusion parse()', 'sourceCode' => $sourceCode]);

                return $span->getId();
            }
        );
    }
}

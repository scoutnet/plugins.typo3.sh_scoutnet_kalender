<?php

namespace ScoutNet\ShScoutnetKalender\ViewHelpers\Format;

use Closure;
use DateTime;
use Exception;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

class StrftimeViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('date', 'mixed', 'either a DateTime object or a string (UNIX-Timestamp)', false, null);
        $this->registerArgument('format', 'string', 'Format String which is taken to format the Date/Time', false, '%A, %d. %B %Y');
    }

    /**
     * Render the supplied DateTime object as a formatted date using strftime.
     *
     * @param array                                                      $arguments
     * @param Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string Formatted date
     * @throws Exception
     */
    public static function renderStatic(array $arguments, Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        $format = $arguments['format'];
        $date = $arguments['date'];

        if ($date === null) {
            $date = $renderChildrenClosure();
            if ($date === null) {
                return '';
            }
        }

        if ($date instanceof DateTime) {
            try {
                return strftime($format, $date->getTimestamp());
            } catch (Exception $exception) {
                throw new Exception('"' . $date . '" was DateTime and could not be converted to UNIX-Timestamp by DateTime.', 200000001);
            }
        }
        return strftime($format, (int)$date);
    }
}

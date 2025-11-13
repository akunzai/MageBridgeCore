<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Logs;

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

class HtmlView extends \MageBridge\Component\MageBridge\Administrator\View\BaseHtmlView
{
    /**
     * Helper-method to return a list of log-types.
     *
     * @return array
     */
    private function getTypes()
    {
        $types = [
            'Trace'    => 1,
            'Notice'   => 2,
            'Warning'  => 3,
            'Error'    => 4,
            'Feedback' => 5,
            'Profiler' => 6,
        ];

        return $types;
    }

    /**
     * Helper-method to return the title for a specific log-type.
     *
     * @param string $type
     *
     * @return string
     */
    public function printType($type)
    {
        $types = $this->getTypes();
        foreach ($types as $name => $value) {
            if ($type == $value) {
                return Text::_($name);
            }
        }

        return '';
    }
}

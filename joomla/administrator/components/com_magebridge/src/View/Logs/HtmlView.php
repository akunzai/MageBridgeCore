<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Logs;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use Yireo\View\ViewList;

class HtmlView extends ViewList
{
    /**
     * Disable edit/copy/new buttons for logs view.
     *
     * @var bool
     */
    protected $loadToolbarEdit = false;

    /**
     * Disable delete button for logs view.
     *
     * @var bool
     */
    protected $loadToolbarDelete = false;

    public function display($tpl = null)
    {
        // Add CSS files like BaseHtmlView
        $this->addCss('backend.css', 'media/com_magebridge/css/');
        $this->addCss('backend-j35.css', 'media/com_magebridge/css/');

        $bridge = BridgeModel::getInstance();
        $debug  = DebugModel::getInstance();

        if ($bridge->getApiState() !== null) {
            $message = null;

            switch (strtoupper($bridge->getApiState())) {
                case 'EMPTY METADATA':
                    $message = Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_EMPTY_METADATA');
                    break;

                case 'AUTHENTICATION FAILED':
                    $message = Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_AUTHENTICATION_FAILED');
                    break;

                case 'INTERNAL ERROR':
                    $message = Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_INTERNAL_ERROR');
                    break;

                case 'FAILED LOAD':
                    $message = Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_FAILED_LOAD');
                    break;

                default:
                    $message = sprintf(Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_GENERIC'), (string) $bridge->getApiState());
                    break;
            }

            if ($message !== null) {
                $debug->feedback($message);
            }
        }

        $input = $this->app->getInput();

        if (
            (int) ConfigModel::load('debug') === 1
            && $input->getCmd('tmpl') !== 'component'
            && in_array($input->getCmd('view'), ['config', 'home'], true)
        ) {
            $debug->feedback(Text::_('COM_MAGEBRIDGE_VIEW_API_DEBUGGING_ENABLED'));
        }

        // Initialize filter lists
        $this->initFilterLists();

        parent::display($tpl);
    }

    /**
     * Initialize filter dropdown lists for the logs view.
     */
    private function initFilterLists(): void
    {
        // Build remote_addr filter dropdown
        $this->lists['remote_addr'] = $this->buildRemoteAddrFilter();

        // Build origin filter dropdown
        $this->lists['origin'] = $this->buildOriginFilter();

        // Build type filter dropdown
        $this->lists['type'] = $this->buildTypeFilter();
    }

    /**
     * Build remote address filter dropdown.
     */
    private function buildRemoteAddrFilter(): string
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('remote_addr'))
            ->from($db->quoteName('#__magebridge_log'))
            ->where($db->quoteName('remote_addr') . ' != ' . $db->quote(''))
            ->order($db->quoteName('remote_addr'));
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $options = [HTMLHelper::_('select.option', '', Text::_('COM_MAGEBRIDGE_VIEW_LOGS_SELECT_ADDRESS'))];
        foreach ($rows as $row) {
            $options[] = HTMLHelper::_('select.option', $row->remote_addr, $row->remote_addr);
        }

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            'filter_remote_addr',
            'class="form-select w-auto" onchange="document.adminForm.submit();"',
            'value',
            'text',
            $this->getFilter('remote_addr')
        );
    }

    /**
     * Build origin filter dropdown.
     */
    private function buildOriginFilter(): string
    {
        $options = [
            HTMLHelper::_('select.option', '', Text::_('COM_MAGEBRIDGE_VIEW_LOGS_SELECT_ORIGIN')),
            HTMLHelper::_('select.option', 'joomla', Text::_('Joomla')),
            HTMLHelper::_('select.option', 'magento', Text::_('Magento')),
        ];

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            'filter_origin',
            'class="form-select w-auto" onchange="document.adminForm.submit();"',
            'value',
            'text',
            $this->getFilter('origin')
        );
    }

    /**
     * Build type filter dropdown.
     */
    private function buildTypeFilter(): string
    {
        $options = [HTMLHelper::_('select.option', '', Text::_('COM_MAGEBRIDGE_VIEW_LOGS_SELECT_TYPE'))];
        foreach ($this->getTypes() as $name => $value) {
            $options[] = HTMLHelper::_('select.option', (string) $value, Text::_($name));
        }

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            'filter_type',
            'class="form-select w-auto" onchange="document.adminForm.submit();"',
            'value',
            'text',
            $this->getFilter('type')
        );
    }

    /**
     * Helper-method to return a list of log-types.
     *
     * @return array<string, int>
     */
    private function getTypes(): array
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
     */
    public function printType(int $type): string
    {
        $types = $this->getTypes();
        foreach ($types as $name => $value) {
            if ($type === $value) {
                return Text::_($name);
            }
        }

        return '';
    }
}

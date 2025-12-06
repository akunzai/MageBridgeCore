<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Logs;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

class CsvView extends HtmlView
{
    public function display($tpl = null)
    {
        $filename = 'magebridge-debug.csv';

        header('Expires: 0');
        header('Cache-control: private');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-disposition: attachment; filename=' . $filename);

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery('SELECT * FROM #__magebridge_log WHERE 1=1');
        $rows = $db->loadObjectList();

        $body = '';

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $data = [
                    $row->id,
                    $row->message,
                    $this->printType($row->type),
                    $row->remote_addr,
                    $row->origin,
                ];

                foreach ($data as $index => $value) {
                    $data[$index] = '"' . str_replace('"', '`', (string) $value) . '"';
                }

                $body .= implode(',', $data) . "\r\n";
            }
        }

        echo $body;
    }

    /**
     * Helper-method to return a list of log-types.
     *
     * @return array
     */
    private function getTypes()
    {
        return [
            'Trace' => 1,
            'Notice' => 2,
            'Warning' => 3,
            'Error' => 4,
        ];
    }

    /**
     * Helper-method to return the title for a specific log-type.
     */
    public function printType(int $type): string
    {
        foreach ($this->getTypes() as $name => $value) {
            if ($type === $value) {
                return Text::_($name);
            }
        }

        return '';
    }
}

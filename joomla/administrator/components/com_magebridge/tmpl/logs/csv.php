<?php

/**
 * Joomla! component MageBridge.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use MageBridge\Component\MageBridge\Administrator\View\Logs\HtmlView;

defined('_JEXEC') or die('Restricted access');

/** @var HtmlView $this */

// Set CSV headers
/** @var CMSApplication $app */
$app = Factory::getApplication();
$app->setHeader('Content-Type', 'text/csv; charset=utf-8', true);
$app->setHeader('Content-Disposition', 'attachment; filename="magebridge-logs-' . date('Y-m-d-H-i-s') . '.csv"', true);
$app->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
$app->setHeader('Pragma', 'public', true);
$app->sendHeaders();

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV header
fputcsv($output, ['ID', 'Message', 'Type', 'Origin', 'IP', 'Debug Session', 'Time']);

// Write data rows
if (!empty($this->items)) {
    foreach ($this->items as $item) {
        fputcsv($output, [
            $item->id ?? '',
            $item->message ?? '',
            $this->printType((int) ($item->type ?? 0)),
            $item->origin ?? '',
            $item->remote_addr ?? '',
            $item->session ?? '',
            $item->timestamp ?? '',
        ]);
    }
}

fclose($output);

// Exit to prevent Joomla from adding extra output
$app->close();

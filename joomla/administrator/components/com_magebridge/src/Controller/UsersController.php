<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\UserModel;

class UsersController extends BaseController
{
    /**
     * Constructor.
     *
     * @param array $config controller configuration
     * @param MVCFactoryInterface|null $factory MVC factory instance
     * @param CMSApplicationInterface|null $app application object
     * @param Input|null $input input object
     */
    public function __construct(array $config = [], ?MVCFactoryInterface $factory = null, ?CMSApplicationInterface $app = null, ?Input $input = null)
    {
        parent::__construct($config, $factory, $app, $input);
    }

    /**
     * Shows the import layout.
     */
    public function import(): void
    {
        // Set layout in input for Joomla's routing
        $this->input->set('layout', 'import');

        // Display using standard controller display mechanism
        parent::display();
    }

    /**
     * Exports users to CSV.
     */
    public function export(): bool
    {
        if (!$this->validateRequest()) {
            return false;
        }

        $users      = $this->getUserList();
        $websiteId  = ConfigModel::load('users_website_id');
        $groupId    = ConfigModel::load('users_group_id');

        if (empty($users)) {
            $this->setRedirect('index.php?option=com_magebridge&view=users', Text::_('No users found'), 'error');

            return false;
        }

        if (empty($websiteId)) {
            $this->setRedirect('index.php?option=com_magebridge&view=users', Text::_('Website not configured in export parameters'), 'error');

            return false;
        }

        if (empty($groupId)) {
            $this->setRedirect('index.php?option=com_magebridge&view=users', Text::_('Customer Group not configured in export parameters'), 'error');

            return false;
        }

        $date     = date('Ymd');
        $filename = 'magebridge-export-joomla-users_' . $date . '.csv';
        $output   = $this->getOutput($users, $websiteId, $groupId);

        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Length: ' . strlen((string) $output));
        header('Content-type: text/x-csv');
        header('Content-Disposition: attachment; filename=' . $filename);
        echo $output;

        $this->app->close();

        return true;
    }

    /**
     * Handles CSV upload for user import.
     */
    public function upload(): bool
    {
        if (!$this->validateRequest()) {
            return false;
        }

        $upload = $this->input->get('csv', null, 'files');

        if (!$this->isValidUpload($upload)) {
            $this->setRedirect('index.php?option=com_magebridge&view=users&task=import', Text::_('File upload failed on system level'), 'error');

            return false;
        }

        $csv = @file_get_contents($upload['tmp_name']);

        if (empty($csv)) {
            $this->setRedirect('index.php?option=com_magebridge&view=users&task=import', Text::_('Empty file upload'), 'error');

            return false;
        }

        $lines = explode("\n", $csv);

        if (empty($lines)) {
            $this->setRedirect('index.php?option=com_magebridge&view=users&task=import', Text::_('Empty file upload'), 'error');

            return false;
        }

        $header    = $this->parseLine(array_shift($lines));
        $email     = array_search('email', $header, true);
        $firstname = array_search('firstname', $header, true);
        $lastname  = array_search('lastname', $header, true);

        $userRecordsOk   = 0;
        $userRecordsFail = 0;

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            $fields = $this->parseLine($line);

            if (!isset($fields[$email], $fields[$firstname], $fields[$lastname])) {
                $userRecordsFail++;
                continue;
            }

            $user = [
                'email'     => $fields[$email],
                'firstname' => $fields[$firstname],
                'lastname'  => $fields[$lastname],
            ];

            $user = \MageBridge\Component\MageBridge\Site\Helper\UserHelper::convert($user);
            $result = UserModel::getInstance()->create($user, true);

            if ($result) {
                $userRecordsOk++;
            } else {
                $userRecordsFail++;
            }
        }

        $this->setRedirect(
            'index.php?option=com_magebridge&view=users',
            sprintf('Imported %d users successfully, %d users failed', $userRecordsOk, $userRecordsFail)
        );

        return true;
    }

    /**
     * Validates upload structure.
     *
     * @param array|null $upload uploaded file array
     */
    private function isValidUpload($upload): bool
    {
        if (empty($upload)) {
            return false;
        }

        return !empty($upload['name']) && !empty($upload['tmp_name']) && !empty($upload['size']);
    }

    /**
     * Fetches all Joomla users.
     */
    private function getUserList(): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery('SELECT u.* FROM #__users AS u');

        return $db->loadObjectList() ?: [];
    }

    /**
     * Drafts CSV output for users.
     *
     * @param array $users user list
     * @param string $websiteId magento website identifier
     * @param string $groupId magento group identifier
     */
    private function getOutput(array $users, string $websiteId, string $groupId): string
    {
        $output = '"group_id","website","firstname","lastname","email"' . "\n";

        foreach ($users as $user) {
            $user = \MageBridge\Component\MageBridge\Site\Helper\UserHelper::convert($user);

            $values = [
                $groupId,
                $websiteId,
                $user->firstname,
                $user->lastname,
                $user->email,
            ];

            foreach ($values as $index => $value) {
                $values[$index] = '"' . str_replace('"', '""', trim((string) $value)) . '"';
            }

            $output .= implode(',', $values) . "\n";
        }

        return $output;
    }

    /**
     * Parses a CSV line.
     *
     * @param string $line CSV line
     */
    private function parseLine(string $line): array
    {
        $fields = explode(',', $line);

        foreach ($fields as $index => $field) {
            $field        = (string) preg_replace('/^"/', '', $field);
            $field        = (string) preg_replace('/"$/', '', $field);
            $fields[$index] = str_replace('""', '"', $field);
        }

        return $fields;
    }

    /**
     * Validates request security.
     */
    private function validateRequest(): bool
    {
        if (Session::checkToken('post') === false && Session::checkToken('get') === false) {
            $this->setRedirect('index.php?option=com_magebridge&view=users', Text::_('JINVALID_TOKEN'), 'error');

            return false;
        }

        if (\MageBridge\Component\MageBridge\Administrator\Helper\Acl::isDemo()) {
            $this->setRedirect('index.php?option=com_magebridge&view=users', Text::_('LIB_YIREO_CONTROLLER_DEMO_NO_ACTION'), 'warning');

            return false;
        }

        return true;
    }
}

<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper;
use MageBridge\Component\MageBridge\Site\Model\Proxy\Adapter\CurlAdapter;

final class ProxyHelper
{
    private CMSApplication $app;

    public function __construct(CMSApplication $app)
    {
        $this->app = $app;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function upload(): array
    {
        if ($this->app->getInput()->getCmd('option') !== 'com_magebridge') {
            return [];
        }

        /** @var array<string, array<string, mixed>> */
        $tmpFiles = [];

        if (!empty($_FILES)) {
            /** @var array<string, array<string, mixed>> */
            $files = $_FILES;

            foreach ($files as $name => $file) {
                if (!is_array($file) || empty($file['tmp_name']) || empty($file['name'])) {
                    continue;
                }

                $errorMessage = $this->detectUploadError($file);

                if ($errorMessage === null) {
                    if (!is_string($file['tmp_name']) || !is_string($file['name'])) {
                        continue;
                    }

                    $tmpName = $file['tmp_name'];
                    $fileName = $file['name'];

                    if (is_readable($tmpName)) {
                        $tmpFile = $this->getUploadPath() . '/' . $fileName;
                        File::move($tmpName, $tmpFile);

                        if (!is_file($tmpFile) || !is_readable($tmpFile)) {
                            $errorMessage = Text::_('COM_MAGEBRIDGE_UNABLE_TO_READ_UPLOADED_FILE') . ': ' . $tmpFile;
                        } elseif (!(filesize($tmpFile) > 0)) {
                            $errorMessage = Text::_('COM_MAGEBRIDGE_UPLOADED_FILE_IS_EMPTY') . ': ' . $tmpFile;
                        } else {
                            $file['tmp_name']  = $tmpFile;
                            $tmpFiles[$name] = $file;
                            continue;
                        }
                    } else {
                        $errorMessage = Text::_('COM_MAGEBRIDGE_UPLOADED_FILE_NOT_READABLE') . ': ' . $tmpName;
                    }
                }

                $this->handleUploadError($errorMessage, $tmpFiles);
            }
        }

        return $tmpFiles;
    }

    public function getUploadPath(): string
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $tmpPath = $app->getConfig()->get('tmp_path');

        if (!is_string($tmpPath)) {
            return sys_get_temp_dir();
        }

        return $tmpPath;
    }

    /**
     * @param array<string, array<string, mixed>> $tmpFiles
     */
    public function cleanup(array $tmpFiles): bool
    {
        foreach ($tmpFiles as $tmpFile) {
            if (is_array($tmpFile) && !empty($tmpFile['tmp_name']) && is_string($tmpFile['tmp_name'])) {
                $tmpName = $tmpFile['tmp_name'];
                if (is_file($tmpName)) {
                    unlink($tmpName);
                }
            } elseif (is_string($tmpFile) && is_file($tmpFile)) {
                unlink($tmpFile);
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $file
     */
    private function detectUploadError(array $file): ?string
    {
        if (!isset($file['error'])) {
            return null;
        }

        $fileName = (isset($file['name']) && is_string($file['name'])) ? $file['name'] : 'unknown';
        $errorCode = is_int($file['error']) ? $file['error'] : 0;

        if (in_array($errorCode, [1, 2], true)) {
            return Text::_('COM_MAGEBRIDGE_UPLOAD_EXCEEDED_MAX_SIZE') . ': ' . $fileName . ' (error: ' . $errorCode . ')';
        }

        if (in_array($errorCode, [3, 4, 6, 7, 8], true)) {
            return Text::_('COM_MAGEBRIDGE_UPLOAD_ERROR') . ': ' . $fileName . ' (error: ' . $errorCode . ')';
        }

        return null;
    }

    /**
     * @param array<string, array<string, mixed>> &$tmpFiles
     */
    private function handleUploadError(string $errorMessage, array &$tmpFiles): void
    {
        $request = $this->app->getInput()->getString('request');

        if (preg_match('/\/uenc\/([a-zA-Z0-9,_-]+)/', $request, $uenc)) {
            $page = EncryptionHelper::base64_decode($uenc[1]);

            if (!empty($page)) {
                $this->cleanup($tmpFiles);
                $this->app->enqueueMessage($errorMessage, 'error');
                $this->app->redirect($page);
                $this->app->close();
            }
        }

        $this->app->enqueueMessage($errorMessage, 'error');
    }

    /**
     * Get proxy adapter for HTTP communication.
     */
    public function getAdapter(): ?CurlAdapter
    {
        // Check if curl is available
        if (!function_exists('curl_init')) {
            return null;
        }

        return new CurlAdapter();
    }
}

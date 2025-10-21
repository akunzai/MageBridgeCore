<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Config;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

final class Value
{
    private ?int $id = null;

    private ?string $name = null;

    private mixed $value = null;

    private int $isOriginal = 0;

    private string $description = '';

    private CMSApplicationInterface $app;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $this->app = Factory::getApplication();

        $this->hydrate($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function hydrate(array $data): void
    {
        foreach ($data as $name => $value) {
            switch ($name) {
                case 'id':
                    $this->id = $value === null ? null : (int) $value;
                    break;
                case 'name':
                    $this->name = $value === null ? null : (string) $value;
                    break;
                case 'value':
                    $this->value = $value;
                    break;
                case 'isOriginal':
                case 'core':
                    $this->isOriginal = (int) $value;
                    break;
                case 'description':
                    $this->description = (string) $value;
                    break;
            }
        }
    }

    private function getDescription(): string
    {
        if ($this->description !== '') {
            return $this->description;
        }

        if ($this->app->isClient('administrator') && $this->name !== null) {
            return Text::_(strtoupper($this->name) . '_DESCRIPTION');
        }

        return '';
    }

    /**
     * @return array{id: ?int, name: ?string, value: mixed, core: int, description: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'value' => $this->value,
            'core' => $this->isOriginal,
            'description' => $this->getDescription(),
        ];
    }
}

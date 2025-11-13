<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Root;

defined('_JEXEC') or die;

class RawView extends HtmlView
{
    protected string $block = '';

    public function display($tpl = null)
    {
        \MageBridge\Component\MageBridge\Site\Helper\UrlHelper::setRequest($this->input->get('request', 'admin'));
        $this->setBlock('root');
        $block = $this->build();
        echo $block;
        $this->app->close();
    }

    protected function setBlock(string $block): void
    {
        $this->block = $block;
    }

    protected function build(): string
    {
        // Implementation for building the block
        return $this->block;
    }
}

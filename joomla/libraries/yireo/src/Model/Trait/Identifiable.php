<?php

declare(strict_types=1);

namespace Yireo\Model\Trait;

defined('_JEXEC') or die();

/**
 * Yireo Model Trait: Identifiable - allows models to have an ID.
 */
trait Identifiable
{
    /**
     * Unique id.
     *
     * @var int
     */
    protected $id = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @param int $id
     * @param bool $reInitialize
     *
     * @return $this
     */
    public function setId($id, $reInitialize = true)
    {
        $this->id = $id;

        if ($reInitialize && isset($this->data)) {
            $this->data = [];
        }

        return $this;
    }
}

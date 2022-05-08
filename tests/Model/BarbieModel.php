<?php

declare(strict_types=1);

namespace YaPro\SymfonySerializerUnderstanding\Tests\Model;

class BarbieModel extends DollModel
{
    private KenModel $husband;

    public function __construct(string $id)
    {
        parent::__construct($id);
    }

    public function getHusband(): KenModel
    {
        return $this->husband;
    }

    /**
     * @param KenModel $husband
     *
     * @return BarbieModel
     */
    public function setHusband(KenModel $husband): self
    {
        $this->husband = $husband;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace YaPro\SymfonySerializerUnderstanding\Tests\Model;

use Symfony\Component\Validator\Constraints as Assert;

class KenModel extends DollModel
{
    /**
     * @Assert\Valid
     */
    private DollModel $wife;

    /**
     * @Assert\Valid
     *
     * @var DollModel[]
     */
    private array $kids;

    private string $city;

    public function __construct(string $id, DollModel $wife, array $kids, string $city = '')
    {
        parent::__construct($id);
        $this->wife = $wife;
        $this->kids = $kids;
        $this->city = $city;
    }

    public function getWife(): DollModel
    {
        return $this->wife;
    }

    public function getKids(): array
    {
        return $this->kids;
    }

    public function getCity(): string
    {
        return $this->city;
    }
}

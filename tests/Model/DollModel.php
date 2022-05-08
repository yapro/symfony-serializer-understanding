<?php

declare(strict_types=1);

namespace YaPro\SymfonySerializerUnderstanding\Tests\Model;

use Symfony\Component\Validator\Constraints as Assert;

class DollModel
{
    /**
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="Id must be at least {{ limit }} characters long",
     *     maxMessage="Id cannot be longer than {{ limit }} characters"
     * )
     */
    protected string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}

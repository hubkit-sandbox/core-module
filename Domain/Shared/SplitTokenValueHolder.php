<?php

declare(strict_types=1);

/*
 * Copyright (c) the Contributors as noted in the AUTHORS file.
 *
 * This file is part of the Park-Manager project.
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Domain\Shared;

/**
 * SplitToken keeps SplitToken information for storage.
 *
 * * The selector is used to identify a token, this is a unique random
 *   URI-safe string with a fixed length of {@see SplitToken::SELECTOR_BYTES} bytes.
 *
 * * The verifierHash holds a password hash of a variable
 *   length and is to be validated by a verifier callback.
 *
 * Additionally a SplitTokenValueHolder optionally holds an
 * expiration timestamp and metadata to perform the operation
 * or collect auditing information.
 *
 * The original token is not stored with this value-object.
 */
final class SplitTokenValueHolder
{
    private $selector;
    private $verifierHash;
    private $expiresAt;
    private $metadata = [];

    /**
     * THIS MUST NOT BE STORED!
     *
     * @var SplitToken
     */
    private $token;

    public function __construct(string $selector, string $verifierHash, ?\DateTimeImmutable $expiresAt = null, array $metadata = [], ?SplitToken $token = null)
    {
        $this->selector     = $selector;
        $this->verifierHash = $verifierHash;
        $this->expiresAt    = $expiresAt;
        $this->metadata     = $metadata;
        $this->token        = $token;
    }

    public static function isEmpty(?self $valueHolder): bool
    {
        if ($valueHolder === null) {
            return true;
        }

        return $valueHolder->selector === null;
    }

    public function selector(): string
    {
        return $this->selector;
    }

    public function verifierHash(): string
    {
        return $this->verifierHash;
    }

    public function withMetadata(array $metadata): self
    {
        return new self($this->selector, $this->verifierHash, $this->expiresAt, $metadata);
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function isExpired(?\DateTimeImmutable $datetime = null): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        return $this->expiresAt->getTimestamp() < ($datetime ?? new \DateTimeImmutable())->getTimestamp();
    }

    public function expiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getToken(): ?SplitToken
    {
        return $this->token;
    }
}

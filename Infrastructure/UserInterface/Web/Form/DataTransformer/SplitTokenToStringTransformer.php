<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\DataTransformer;

use Exception;
use Rollerworks\Component\SplitToken\SplitToken;
use Rollerworks\Component\SplitToken\SplitTokenFactory;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use function is_string;

final class SplitTokenToStringTransformer implements DataTransformerInterface
{
    /** @var SplitTokenFactory */
    private $splitTokenFactory;

    public function __construct(SplitTokenFactory $splitTokenFactory)
    {
        $this->splitTokenFactory = $splitTokenFactory;
    }

    /**
     * @param SplitToken|string|null $token
     */
    public function transform($token): string
    {
        // If a string was passed assume transformation in the Form failed
        if ($token === null || is_string($token)) {
            return '';
        }

        if (! $token instanceof SplitToken) {
            throw new TransformationFailedException('Expected a SplitToken object.');
        }

        return $token->token()->getString();
    }

    /**
     * @param string $token
     */
    public function reverseTransform($token): ?SplitToken
    {
        if (! is_string($token)) {
            throw new TransformationFailedException('Expected a string.');
        }

        if ($token === '') {
            return null;
        }

        try {
            return $this->splitTokenFactory->fromString($token);
        } catch (Exception $e) {
            throw new TransformationFailedException('Invalid SplitToken provided.', 0, $e);
        }
    }
}

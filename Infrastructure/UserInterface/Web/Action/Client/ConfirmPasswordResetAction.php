<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Action\Client;

use ParkManager\Module\CoreModule\Application\Command\Client\ConfirmPasswordReset;
use ParkManager\Module\CoreModule\Infrastructure\Security\ClientUser;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Common\Form\Handler\ServiceBusFormFactory;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Common\TwigResponse;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security\ConfirmPasswordResetType;
use Rollerworks\Bundle\RouteAutofillBundle\Response\RouteRedirectResponse;
use Rollerworks\Component\SplitToken\SplitToken;
use Symfony\Component\HttpFoundation\Request;

final class ConfirmPasswordResetAction
{
    /**
     * @return TwigResponse|RouteRedirectResponse
     */
    public function __invoke(Request $request, string $token, ServiceBusFormFactory $formFactory)
    {
        $handler = $formFactory->createForCommand(ConfirmPasswordResetType::class, ['reset_token' => $token], [
            'user_class' => ClientUser::class,
            'command_builder' => static function (SplitToken $splitToken, string $password) {
                return new ConfirmPasswordReset($splitToken, $password);
            },
        ]);
        $handler->handleRequest($request);

        if ($handler->isReady()) {
            return new RouteRedirectResponse('park_manager.client.security_login');
        }

        $response = new TwigResponse('@ParkManagerCore/client/security/password_reset_confirm.html.twig', $handler);
        $response->setPrivate();
        $response->setMaxAge(1);

        return $response;
    }
}

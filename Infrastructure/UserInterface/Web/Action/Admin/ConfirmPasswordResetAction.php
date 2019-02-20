<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Action\Admin;

use ParkManager\Module\CoreModule\Application\Command\Administrator\ConfirmPasswordReset;
use ParkManager\Module\CoreModule\Infrastructure\Security\AdministratorUser;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Common\TwigResponse;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security\ConfirmPasswordResetType;
use Rollerworks\Bundle\RouteAutofillBundle\Response\RouteRedirectResponse;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

final class ConfirmPasswordResetAction
{
    /**
     * @return TwigResponse|RouteRedirectResponse
     */
    public function __invoke(Request $request, string $token, FormFactoryInterface $formFactory)
    {
        $form = $formFactory->create(ConfirmPasswordResetType::class, ['reset_token' => $token], [
            'user_class' => AdministratorUser::class,
            'command_message_factory' => static function (array $data) {
                return new ConfirmPasswordReset($data['reset_token'], $data['password']);
            },
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return new RouteRedirectResponse('park_manager.admin.security_login');
        }

        $response = new TwigResponse('@ParkManagerCore/admin/security/password_reset_confirm.html.twig', $form);
        $response->setPrivate();
        $response->setMaxAge(1);

        return $response;
    }
}

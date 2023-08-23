<?php

namespace nvs\api\v1\Controllers;

use nvs\api\v1\Models as UserModel;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class User
{
    public function register(Request $request, Application $app): Response
    {
        $postData = $request->request->all();

        $validator = $app['services.validator']
            ->validate($app['services.user.constraints']->getConstraints(), $postData);

        if (!$validator->isValid()) {
            return Response::create(json_encode(['error' => $validator->getErrors()]), Response::HTTP_BAD_REQUEST);
        }

        $activeUser = $app['services.user']->getActiveUser($postData);

        if ($activeUser) {
            return Response::create(json_encode(['error' => 'User already exists']), Response::HTTP_CONFLICT);
        }

        /** @var UserModel\User $user */
        $user = $app['services.user']->register($postData);

        if (!$user) {
            return Response::create(json_encode(['error' => 'Failed to create user']), Response::HTTP_BAD_REQUEST);
        }

        return Response::create(json_encode($user->getValueMap()));
    }

    public function update(Request $request, Application $app): Response
    {
        $postData = $request->request->all();

        $validator = $app['services.validator']
            ->validate($app['services.user.constraints']->getConstraints(), $postData);

        if (!$validator->isValid()) {
            return Response::create(json_encode(['error' => $validator->getErrors()]), Response::HTTP_BAD_REQUEST);
        }

        $activeUser = $app['services.user']->getActiveUser($postData);

        if (!$activeUser) {
            return Response::create(json_encode([]), Response::HTTP_NOT_FOUND);
        }

        /** @var UserModel\User $user */
        $user = $app['services.user']->update($postData);

        if (!$user) {
            return Response::create(json_encode(['error' => 'Failed to update user']), Response::HTTP_BAD_REQUEST);
        }

        return Response::create(json_encode($user->getValueMap()));
    }

    public function confirm(Request $request, Application $app): Response
    {
        $postData = $request->request->all();

        $validator = $app['services.validator']
            ->validate($app['services.confirm.constraints']->getConstraints(), $postData);

        if (!$validator->isValid()) {
            return Response::create(json_encode(['error' => $validator->getErrors()]), Response::HTTP_BAD_REQUEST);
        }

        $fullUser = $app['services.user']->getUserAllFields($postData);

        if (!$fullUser) {
            return Response::create(json_encode([]), Response::HTTP_NOT_FOUND);
        }

        /** @var UserModel\User $user */
        $user = $app['services.user']->confirm($fullUser);

        if (!$user) {
            return Response::create(json_encode(['error' => 'Failed to confirm user']), Response::HTTP_BAD_REQUEST);
        }

        $app['services.sms']->userNotify($user);

        return Response::create(json_encode($user->getValueMap()));
    }

    public function get(Request $request, Application $app): Response
    {
        $postData = $request->request->all();

        $validator = $app['services.validator']
            ->validate($app['services.email.constraints']->getConstraints(), $postData);

        if (!$validator->isValid()) {
            return Response::create(json_encode(['error' => $validator->getErrors()]), Response::HTTP_BAD_REQUEST);
        }

        /** @var UserModel\User $user */
        $user = $app['services.user']->get($postData);

        if (!$user) {
            return Response::create(json_encode([]), Response::HTTP_NOT_FOUND);
        }

        return Response::create(json_encode($user->getValueMap()));
    }
}

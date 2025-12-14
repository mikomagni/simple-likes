<?php

namespace Mikomagni\SimpleLikes\Traits;

use Statamic\Facades\User as StatamicUser;

trait ResolvesUsers
{
    protected function findUser($userId)
    {
        try {
            $user = StatamicUser::find($userId);
            if ($user) {
                return $user;
            }
        } catch (\Exception $e) {
        }

        $userModelPaths = [
            '\App\Models\User',
            '\App\User',
        ];

        foreach ($userModelPaths as $modelClass) {
            try {
                if (class_exists($modelClass)) {
                    $user = $modelClass::find($userId);
                    if ($user) {
                        return $user;
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    protected function getUserDisplayName($user): string
    {
        if (!$user) {
            return 'Unknown User';
        }

        try {
            if (method_exists($user, 'get')) {
                $name = $user->get('name') ?: $user->get('first_name') ?: $user->get('username');
                if ($name) {
                    return $name;
                }
            }
        } catch (\Exception $e) {
        }

        $nameFields = ['name', 'first_name', 'username'];

        foreach ($nameFields as $field) {
            try {
                if (method_exists($user, $field)) {
                    $value = $user->{$field}();
                    if ($value) {
                        return $value;
                    }
                }

                if (isset($user->{$field}) && $user->{$field}) {
                    return $user->{$field};
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        try {
            $email = $this->getUserEmail($user);
            if ($email) {
                return explode('@', $email)[0];
            }
        } catch (\Exception $e) {
        }

        return 'User';
    }

    protected function getUserEmail($user): ?string
    {
        if (!$user) {
            return null;
        }

        try {
            if (method_exists($user, 'get')) {
                $email = $user->get('email');
                if ($email) {
                    return $email;
                }
            }
        } catch (\Exception $e) {
        }

        try {
            if (method_exists($user, 'email')) {
                $email = $user->email();
                if ($email) {
                    return $email;
                }
            }
        } catch (\Exception $e) {
        }

        try {
            if (isset($user->email) && $user->email) {
                return $user->email;
            }
        } catch (\Exception $e) {
        }

        if (is_array($user) && isset($user['email'])) {
            return $user['email'];
        }

        return null;
    }

    protected function getUserAvatarUrl($user): ?string
    {
        if (!$user || !method_exists($user, 'augmentedValue')) {
            return null;
        }

        try {
            $avatarValue = $user->augmentedValue('avatar');
            if (!$avatarValue) {
                return null;
            }

            $asset = $avatarValue->value();
            if ($asset && method_exists($asset, 'url')) {
                return $asset->url();
            }
        } catch (\Exception $e) {
        }

        return null;
    }

    protected function getUserEditUrl($userId): ?string
    {
        try {
            $currentUser = StatamicUser::current();

            if (!$currentUser) {
                return null;
            }

            $canViewUsers = $currentUser->hasPermission('view users') || $currentUser->isSuper();

            if (!$canViewUsers) {
                return null;
            }

            return "/cp/users/{$userId}/edit";
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function canViewUsers(): bool
    {
        try {
            $currentUser = StatamicUser::current();

            if (!$currentUser) {
                return false;
            }

            return $currentUser->hasPermission('view users') || $currentUser->isSuper();
        } catch (\Exception $e) {
            return false;
        }
    }
}

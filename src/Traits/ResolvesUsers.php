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

    /**
     * Get user avatar URL with fallback priority:
     * 1. Uploaded avatar image
     * 2. Gravatar (if email exists)
     * 3. null (Vue will show initial)
     */
    protected function getUserAvatarUrl($user): ?string
    {
        if (!$user) {
            return null;
        }

        // 1. Check for uploaded avatar
        if (method_exists($user, 'augmentedValue')) {
            try {
                $avatarValue = $user->augmentedValue('avatar');
                if ($avatarValue) {
                    $asset = $avatarValue->value();
                    if ($asset && method_exists($asset, 'url')) {
                        return $asset->url();
                    }
                }
            } catch (\Exception $e) {
            }
        }

        // 2. Check for Gravatar
        $email = $this->getUserEmail($user);
        if ($email) {
            $gravatarUrl = $this->getGravatarUrl($email);
            if ($gravatarUrl) {
                return $gravatarUrl;
            }
        }

        // 3. Return null - Vue component will show initial
        return null;
    }

    /**
     * Get Gravatar URL for email, returns null if no Gravatar exists
     */
    protected function getGravatarUrl(string $email, int $size = 80): ?string
    {
        $hash = md5(strtolower(trim($email)));

        // Use d=404 to return 404 if no Gravatar, so we can detect it
        // But for simplicity, we'll use d=mp (mystery person) as default
        // If you want to check if Gravatar exists, use d=404 and make HTTP request

        // For now, always return Gravatar URL with default avatar
        // This means users without Gravatar get the mystery person icon
        // To only show Gravatar when it exists, we'd need to make an HTTP check

        // Option: Use 'd=blank' and check if image loads in frontend
        // For better UX, let's check if Gravatar exists server-side

        $checkUrl = "https://www.gravatar.com/avatar/{$hash}?d=404&s=1";

        try {
            $headers = @get_headers($checkUrl);
            if ($headers && strpos($headers[0], '200') !== false) {
                return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=mp";
            }
        } catch (\Exception $e) {
        }

        return null;
    }

    /**
     * Get avatar initial with fallback priority:
     * 1. First letter of name
     * 2. First letter of email
     * 3. 'U' as default
     */
    protected function getUserAvatarInitial($user): string
    {
        if (!$user) {
            return 'U';
        }

        // Try to get name first
        $name = $this->getUserDisplayName($user);
        if ($name && $name !== 'Unknown User' && $name !== 'User') {
            return strtoupper(substr($name, 0, 1));
        }

        // Fall back to email
        $email = $this->getUserEmail($user);
        if ($email) {
            return strtoupper(substr($email, 0, 1));
        }

        return 'U';
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

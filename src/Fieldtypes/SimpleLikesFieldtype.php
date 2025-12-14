<?php

namespace Mikomagni\SimpleLikes\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Mikomagni\SimpleLikes\Models\SimpleLike;

class SimpleLikesFieldtype extends Fieldtype
{
    protected static $handle = 'simple_likes';
    protected $icon = 'heart';


    public function configFieldItems(): array
    {
        return [
            'allow_guest_likes' => [
                'display' => 'Allow Guest Likes',
                'instructions' => 'Allow non-authenticated users to like entries using this field. Can be overridden per entry using the simple_likes_guest_allowed field.',
                'type' => 'toggle',
                'default' => false
            ]
        ];
    }

    public function preload()
    {
        $entryId = $this->field?->parent()?->id();

        $realLikes = 0;
        if ($entryId) {
            $realLikes = SimpleLike::getTotalLikesForEntry($entryId);
        }

        return [
            'realLikes' => $realLikes,
        ];
    }

    public function preProcess($data)
    {
        if (is_array($data)) {
            return (int) ($data['count'] ?? 0);
        }

        return (int) ($data ?? 0);
    }

    public function process($data)
    {
        return (int) ($data ?? 0);
    }

    public function preProcessIndex($data)
    {
        $boostCount = (int) ($data ?? 0);
        $entryId = $this->field?->parent()?->id();

        $realLikes = 0;
        if ($entryId) {
            $realLikes = SimpleLike::getTotalLikesForEntry($entryId);
        }

        $total = $boostCount + $realLikes;

        if ($boostCount > 0 && $realLikes > 0) {
            return "{$total} ({$boostCount}+{$realLikes})";
        }

        return $total;
    }
}

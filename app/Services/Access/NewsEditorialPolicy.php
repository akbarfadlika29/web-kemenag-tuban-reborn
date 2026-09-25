<?php

namespace App\Services\Access;

use App\Models\News;
use App\Models\User;

class NewsEditorialPolicy
{
    public function __construct(private readonly AccessService $access) {}

    public function editor(User $actor, News $news): bool
    {
        return $actor->is_active && (
            $this->access->allowsUnit($actor, 'news.review', $news->unit_id)
            || $this->access->allowsUnit($actor, 'news.publish', $news->unit_id)
        );
    }

    public function canWrite(
        User $actor,
        News $news,
        string $action = 'update'
    ): bool {
        if (
            !in_array($action, ['update', 'delete'], true)
            || !$actor->is_active
            || !$this->access->allowsUnit($actor, 'news.'.$action, $news->unit_id)
        ) {
            return false;
        }

        if ($news->status === 'published') {
            return $this->access->allowsUnit(
                $actor,
                $action === 'delete' ? 'news.unpublish' : 'news.publish',
                $news->unit_id
            );
        }

        if ($this->editor($actor, $news)) {
            return true;
        }

        return (string) $news->author_id === (string) $actor->id
            && $news->status === 'draft'
            && in_array($news->editorial_state, ['draft', 'rejected'], true);
    }

    public function canSubmit(User $actor, News $news): bool
    {
        return $actor->is_active
            && $this->access->allowsUnit($actor, 'news.submit', $news->unit_id)
            && $news->status === 'draft'
            && in_array($news->editorial_state, ['draft', 'rejected'], true)
            && !$news->revision_required
            && (
                (string) $news->author_id === (string) $actor->id
                || $this->editor($actor, $news)
            );
    }

    public function canReject(User $actor, News $news): bool
    {
        return $actor->is_active
            && $this->access->allowsUnit($actor, 'news.review', $news->unit_id)
            && $news->status === 'draft'
            && $news->editorial_state === 'submitted';
    }

    public function canPublish(User $actor, News $news): bool
    {
        return $actor->is_active
            && $this->access->allowsUnit($actor, 'news.publish', $news->unit_id)
            && $news->status === 'draft'
            && in_array(
                $news->editorial_state,
                ['draft', 'submitted', 'rejected'],
                true
            )
            && !$news->revision_required;
    }
}
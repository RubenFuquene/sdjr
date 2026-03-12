<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
use App\Models\CommerceComment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 * Service for CommerceComment business logic
 */
class CommerceCommentService
{
    /**
     * Get comments by commerce
     * - Supports filtering by creator, priority, and color
     * - Results are paginated and ordered by creation date (newest first) and priority
     */
    public function getCommentsByCommerce(int $commerceId, array $filters, int $perPage): LengthAwarePaginator
    {
        $comments = CommerceComment::where(['commerce_id' => $commerceId, 'status' => Constant::STATUS_ACTIVE]);

        if ($filters['created_by'] ?? null) {
            $comments->where('created_by', $filters['created_by']);
        }

        if ($filters['priority_type_id'] ?? null) {
            $comments->where('priority_type_id', $filters['priority_type_id']);
        }

        if ($filters['color'] ?? null) {
            $comments->where('color', $filters['color']);
        }

        return $comments->orderByDesc('created_at')->orderBy('priority_type_id')->paginate($perPage);
    }

    /**
     * Create a new comment
     */
    public function createComment(int $commerceId, array $data): CommerceComment
    {
        $data['commerce_id'] = $commerceId;
        if (! isset($data['created_by'])) {
            $data['created_by'] = auth()->id();
        }

        return CommerceComment::create($data);
    }

    /**
     * Get a specific comment
     *
     * @throws ModelNotFoundException
     */
    public function getComment(int $commerceId, int $id): CommerceComment
    {
        return CommerceComment::where('commerce_id', $commerceId)
            ->where('id', $id)
            ->firstOrFail();
    }

    /**
     * Update a comment
     */
    public function updateComment(int $commerceId, int $id, array $data): CommerceComment
    {
        $comment = $this->getComment($commerceId, $id);

        Log::info('Updating comment', ['comment' => $comment, 'comment_id' => $id, 'data' => $data]);
        $comment->update($data);

        return $comment;
    }

    /**
     * Delete a comment
     */
    public function deleteComment(int $commerceId, int $id): void
    {
        $comment = $this->getComment($commerceId, $id);
        $comment->delete();
    }
}

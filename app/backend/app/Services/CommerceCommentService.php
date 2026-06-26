<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
use App\Models\CommerceComment;
use App\Models\PriorityType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use RuntimeException;

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
    public function getCommentsByCommerce(int $commerceId, array $filters, int $perPage, ?array $restrictToTypes = null): LengthAwarePaginator
    {
        $comments = CommerceComment::with('priorityType')
            ->where(['commerce_id' => $commerceId, 'status' => Constant::STATUS_ACTIVE]);

        if ($restrictToTypes !== null) {
            $comments->whereIn('comment_type', $restrictToTypes);
        }

        if ($filters['created_by'] ?? null) {
            $comments->where('created_by', $filters['created_by']);
        }

        if ($filters['priority_type_id'] ?? null) {
            $comments->where('priority_type_id', $filters['priority_type_id']);
        }

        if ($filters['color'] ?? null) {
            $comments->where('color', $filters['color']);
        }

        if ($filters['comment_type'] ?? null) {
            $comments->where('comment_type', $filters['comment_type']);
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
     * Create a rejection (RJ) comment to persist a commerce rejection observation.
     *
     * Used by the verification flow so the rejection reason becomes traceable and
     * readable by the owning provider (SCRUM-297).
     *
     * @throws RuntimeException when no priority type is configured
     */
    public function createRejectionComment(int $commerceId, string $message, ?int $createdBy = null): CommerceComment
    {
        $priorityTypeId = PriorityType::where('code', Constant::COMMENT_PRIORITY_HIGH)->value('id');

        if ($priorityTypeId === null) {
            throw new RuntimeException('No priority type configured for rejection comments.');
        }

        return $this->createComment($commerceId, [
            'comment' => $message,
            'comment_type' => Constant::COMMENT_TYPE_REJECTION,
            'priority_type_id' => $priorityTypeId,
            'created_by' => $createdBy ?? auth()->id(),
            'status' => (string) Constant::STATUS_ACTIVE,
        ]);
    }

    /**
     * Get a specific comment
     *
     * @throws ModelNotFoundException
     */
    public function getComment(int $commerceId, int $id, ?array $restrictToTypes = null): CommerceComment
    {
        $query = CommerceComment::where('commerce_id', $commerceId)
            ->where('id', $id);

        if ($restrictToTypes !== null) {
            $query->whereIn('comment_type', $restrictToTypes);
        }

        return $query->firstOrFail();
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

<?php

namespace SyncExample\Models;

use Merlin\Mvc\Model;

/**
 * Run `php console.php sync model Models/Comment.php --apply` to populate properties.
 */
class Comment extends Model
{
    public int $id;
    public int $post_id;
    public int $user_id;
    public string $body;
    public string $created_at;

    // Properties will be added automatically by the sync task.
}

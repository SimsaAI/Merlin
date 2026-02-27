<?php

namespace App\Models;

use Merlin\Mvc\Model;

/**
 * Sync: php console.php sync model Models/Comment.php --apply
 * With accessors: php console.php sync model Models/Comment.php --apply --generate-accessors --field-visibility=protected
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

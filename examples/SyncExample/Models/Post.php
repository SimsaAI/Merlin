<?php

namespace SyncExample\Models;

use Merlin\Mvc\Model;

/**
 * Run `php console.php sync model Models/Post.php --apply` to populate properties.
 */
class Post extends Model
{
    public int $id;
    public int $user_id;
    public string $title;
    public ?string $content;
    public int $published;
    public string $created_at;

    // Properties will be added automatically by the sync task.
}

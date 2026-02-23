<?php

namespace SyncExample\Models;

use Merlin\Mvc\Model;

/**
 * Run `php console.php sync model Models/User.php --apply` to populate properties.
 */
class User extends Model
{
    public int $id;
    public string $email;
    public ?string $name;
    public string $status;
    public string $created_at;
    public ?string $updated_at;
    public ?string $avatar_url;

    // Properties will be added automatically by the sync task.
}

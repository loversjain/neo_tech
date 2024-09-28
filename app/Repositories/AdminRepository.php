<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\AdminRepositoryInterface;
use Illuminate\Support\Facades\Log;

class AdminRepository implements AdminRepositoryInterface
{
    /**
     * change the active/inactive status of a user.
     *
     * @param int $id The ID of the user to manage.
     * @return void
     */
    public function manageUser(int $id): void
    {
        try {
            $user = $this->getById($id);
            if ($user) {
                // change the user's active status
                $user->is_active = !$user->is_active;
                $user->save();

                Log::info('User status updated successfully.', [
                    'user_id' => $id,
                    'new_status' => $user->is_active ? 'active' : 'inactive'
                ]);
            } else {
                Log::warning('Attempted to manage a non-existing user.', ['user_id' => $id]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to manage user: ' . $e->getMessage(), ['user_id' => $id]);
        }
    }

    /**
     * Retrieve a user by their ID.
     *
     * @param int $id The ID of the user to retrieve.
     * @return User|null The user model or null if not found.
     */
    public function getById(int $id): ?User
    {
        return User::find($id);
    }
}

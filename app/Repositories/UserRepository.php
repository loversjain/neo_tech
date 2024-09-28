<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Create a new user.
     *
     * @param array $data The data to create a user.
     * @return User The created user instance.
     * @throws Exception If there is an error during user creation.
     */
    public function create(array $data): User
    {
        try {
            return User::create($data);
        } catch (Exception $e) {
            Log::error('Failed to create user.', ['error' => $e->getMessage(), 'data' => $data]);
            throw new Exception('Error creating user.');
        }
    }

    /**
     * Update an existing user by ID.
     *
     * @param int $id The ID of the user to update.
     * @param array $data The data to update the user.
     * @return bool True if the user was updated, false otherwise.
     * @throws ModelNotFoundException If the user is not found.
     * @throws Exception If there is an error during user update.
     */
    public function update(int $id, array $data): bool
    {
        try {
            $user = User::findOrFail($id);
            return $user->update($data);
        } catch (ModelNotFoundException $e) {
            Log::error('User not found for update.', ['user_id' => $id]);
            throw $e;
        } catch (Exception $e) {
            Log::error('Failed to update user.', ['user_id' => $id, 'error' => $e->getMessage()]);
            throw new Exception('Error updating user.');
        }
    }

    /**
     * change the active/inactive status of a user.
     *
     * @param int $id The ID of the user to manage.
     * @return void
     * @throws ModelNotFoundException If the user is not found.
     * @throws Exception If there is an error during user management.
     */
    public function manageUser(int $id): void
    {
        try {
            $user = $this->getById($id);
            if ($user) {
                $user->is_active = !$user->is_active;
                $user->save();
                Log::info('User active/inactive status changes.', ['user_id' => $id, 'is_active' => $user->is_active]);
            }
        } catch (ModelNotFoundException $e) {
            Log::error('User not found for management.', ['user_id' => $id]);
            throw $e;
        } catch (Exception $e) {
            Log::error('Failed to manage user.', ['user_id' => $id, 'error' => $e->getMessage()]);
            throw new Exception('Error managing user.');
        }
    }

    /**
     * Retrieve all users.
     *
     * @return array An array of all users.
     */
    public function getAll(): array
    {
        return User::all()->toArray();
    }

    /**
     * Find a user by ID.
     *
     * @param int $id The ID of the user to find.
     * @return User|null The found user instance or null if not found.
     * @throws ModelNotFoundException If the user is not found.
     */
    public function getById(int $id): ?User
    {
        $user = User::find($id);
        if (!$user) {
            Log::error('User not found.', ['user_id' => $id]);
            throw new ModelNotFoundException("User with ID $id not found.");
        }
        return $user;
    }
}

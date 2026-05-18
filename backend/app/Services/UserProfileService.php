<?php
namespace App\Services;

use App\Models\Database;

class UserProfileService {

    public static function updateProfilePicture($user_id, $file) {
        // Upload new file
        $upload = FileUploadService::uploadFile($file, 'profiles');

        if (!$upload['success']) {
            return $upload;
        }

        // Get current profile picture
        $result = Database::query(
            "SELECT profile_picture FROM users WHERE id = ?",
            "i",
            [$user_id]
        );

        if ($row = mysqli_fetch_assoc($result)) {
            $oldPicture = $row['profile_picture'];

            // Delete old picture if it exists
            if ($oldPicture && file_exists(__DIR__ . '/../../..' . $oldPicture)) {
                unlink(__DIR__ . '/../../..' . $oldPicture);
            }
        }

        // Update database
        $updated = Database::execute(
            "UPDATE users SET profile_picture = ? WHERE id = ?",
            "si",
            [$upload['path'], $user_id]
        );

        if ($updated) {
            return [
                'success' => true,
                'message' => 'Profile picture updated successfully',
                'path' => $upload['path']
            ];
        }

        // Clean up uploaded file if database update failed
        if (file_exists($upload['filepath'])) {
            unlink($upload['filepath']);
        }

        return ['success' => false, 'error' => 'Failed to update profile picture'];
    }

    public static function getUserProfilePicture($user_id) {
        $result = Database::query(
            "SELECT profile_picture FROM users WHERE id = ?",
            "i",
            [$user_id]
        );

        if ($row = mysqli_fetch_assoc($result)) {
            return $row['profile_picture'];
        }

        return null;
    }
}
?>

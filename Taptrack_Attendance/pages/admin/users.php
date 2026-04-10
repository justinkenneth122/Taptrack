<?php
/**
 * Admin User Management Page
 * 
 * Allows admins to:
 * - View all users
 * - Create new Organizer accounts
 * - Edit user details
 * - Delete user accounts
 */

// Include RBAC system
require_once __DIR__ . '/../../includes/RoleBasedAccessControl.php';

// Initialize classes
$auth = new UserAuthentication($pdo);
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;

// Check if user has permission to manage users
if ($userRole !== 'admin') {
    echo '<div class="card"><div class="card-content text-center text-muted">❌ <p>Access Denied: Only admins can manage users.</p></div></div>';
    return;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $currentUserId = $_SESSION['user_id'] ?? null;

    if ($action === 'create_user') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $email = $_POST['email'] ?? '';
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $roleId = $_POST['role_id'] ?? '2'; // Default to Organizer

        // Validate password match
        if ($password !== $confirmPassword) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Passwords do not match. Please try again.'];
        } else if (!$currentUserId) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'User session not found. Please log in again.'];
        } else {
            $result = $auth->createUser($username, $password, $email, $firstName, $lastName, $roleId, $currentUserId);
            $_SESSION['flash'] = ['type' => $result['success'] ? 'success' : 'error', 'msg' => $result['message']];
            
            // Redirect on success, or fallthrough to show error message
            if ($result['success']) {
                header('Location: ?page=admin_users');
                exit;
            }
        }
    }

    if ($action === 'delete_user') {
        $userId = $_POST['user_id'] ?? '';
        
        // Prevention: Don't allow deleting the only admin
        if ($userId == $currentUserId) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Cannot delete your own account'];
        } else {
            $result = $auth->deleteUser($userId);
            $_SESSION['flash'] = ['type' => $result['success'] ? 'success' : 'error', 'msg' => $result['message']];
            
            // Redirect to refresh page
            if ($result['success']) {
                header('Location: ?page=admin_users');
                exit;
            }
        }
    }

    if ($action === 'update_user') {
        $userId = $_POST['user_id'] ?? '';
        $email = $_POST['email'] ?? '';
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';

        $result = $auth->updateUser($userId, $email, $firstName, $lastName);
        $_SESSION['flash'] = ['type' => $result['success'] ? 'success' : 'error', 'msg' => $result['message']];
        
        // Redirect to refresh page
        if ($result['success']) {
            header('Location: ?page=admin_users');
            exit;
        }
    }
}

// Get all users
$users = $auth->getAllUsers();

// Get all roles
try {
    $roles = $pdo->query("SELECT id, name, description FROM roles")->fetchAll();
} catch (Exception $e) {
    error_log("Error fetching roles: " . $e->getMessage());
    $roles = [];
}
?>

<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex justify-between items-center">
        <h2 style="font-size:1.5rem;" class="font-bold">👥 User Management</h2>
        <button class="btn btn-primary" onclick="document.getElementById('create-user-modal').classList.add('open')">➕ Create User</button>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="card" style="background:<?= $_SESSION['flash']['type'] === 'success' ? '#dcfce7' : '#fee2e2' ?>; border-left:4px solid <?= $_SESSION['flash']['type'] === 'success' ? '#22c55e' : '#ef4444' ?>;">
            <div class="card-content">
                <p style="color:<?= $_SESSION['flash']['type'] === 'success' ? '#16a34a' : '#dc2626' ?>;">
                    <?= $_SESSION['flash']['type'] === 'success' ? '✓' : '✕' ?> <?= e($_SESSION['flash']['msg']) ?>
                </p>
            </div>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Users (<?= count($users) ?>)</div>
            <p class="card-desc">Manage system users and their roles</p>
        </div>
        <div class="card-content">
            <?php if (empty($users)): ?>
                <p class="text-center text-muted py-8">No users found</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="font-medium"><?= e($user['username']) ?></td>
                                <td><?= e($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                <td class="text-sm text-muted"><?= e($user['email']) ?></td>
                                <td>
                                    <span class="badge" style="background:<?= $user['role_name'] === 'admin' ? '#dbeafe' : '#fef3c7' ?>; color:<?= $user['role_name'] === 'admin' ? '#1e40af' : '#92400e' ?>;">
                                        <?= ucfirst($user['role_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="color:<?= $user['is_active'] ? '#22c55e' : '#ef4444' ?>;">
                                        <?= $user['is_active'] ? '🟢 Active' : '🔴 Inactive' ?>
                                    </span>
                                </td>
                                <td class="text-sm text-muted"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                <td class="text-right">
                                    <button class="btn btn-sm btn-outline" onclick="editUser(<?= e(json_encode($user)) ?>)">✏️ Edit</button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user? Attendance records will be preserved.')">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= e($user['id']) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline" style="color:#ef4444;">🗑️ Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div id="create-user-modal" class="modal">
    <div class="modal-content" style="max-width:500px;">
        <div class="modal-header">
            <h3 class="modal-title">Create New User</h3>
            <button class="modal-close" onclick="document.getElementById('create-user-modal').classList.remove('open')">✕</button>
        </div>
        <form method="POST" class="modal-body space-y-4">
            <input type="hidden" name="action" value="create_user">
            
            <div>
                <label class="label required">Username</label>
                <input type="text" name="username" class="input" placeholder="Enter username" required>
            </div>

            <div>
                <label class="label required">Email</label>
                <input type="email" name="email" class="input" placeholder="Enter email" required>
            </div>

            <div>
                <label class="label">First Name</label>
                <input type="text" name="first_name" class="input" placeholder="Enter first name">
            </div>

            <div>
                <label class="label">Last Name</label>
                <input type="text" name="last_name" class="input" placeholder="Enter last name">
            </div>

            <div>
                <label class="label required">Password</label>
                <input type="password" name="password" class="input" id="password" placeholder="Enter password" required minlength="6">
                <small style="color: #666; font-size: 12px;">Minimum 6 characters</small>
            </div>

            <div>
                <label class="label required">Confirm Password</label>
                <input type="password" name="confirm_password" class="input" id="confirm_password" placeholder="Confirm password" required>
            </div>

            <div>
                <label class="label required">Role</label>
                <select name="role_id" class="input" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= e($role['id']) ?>"><?= ucfirst($role['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('create-user-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="edit-user-modal" class="modal">
    <div class="modal-content" style="max-width:500px;">
        <div class="modal-header">
            <h3 class="modal-title">Edit User</h3>
            <button class="modal-close" onclick="document.getElementById('edit-user-modal').classList.remove('open')">✕</button>
        </div>
        <form method="POST" class="modal-body space-y-4">
            <input type="hidden" name="action" value="update_user">
            <input type="hidden" name="user_id" id="edit_user_id">
            
            <div>
                <label class="label">Username</label>
                <div class="input" style="background:#f3f4f6;" id="edit_username"></div>
            </div>

            <div>
                <label class="label required">Email</label>
                <input type="email" name="email" class="input" id="edit_email" required>
            </div>

            <div>
                <label class="label">First Name</label>
                <input type="text" name="first_name" class="input" id="edit_first_name">
            </div>

            <div>
                <label class="label">Last Name</label>
                <input type="text" name="last_name" class="input" id="edit_last_name">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('edit-user-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script>
    function editUser(user) {
        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('edit_username').textContent = user.username;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_first_name').value = user.first_name;
        document.getElementById('edit_last_name').value = user.last_name;
        document.getElementById('edit-user-modal').classList.add('open');
    }

    // Form validation for create user
    document.addEventListener('DOMContentLoaded', function() {
        const createUserForm = document.querySelector('#create-user-modal form');
        if (createUserForm) {
            createUserForm.addEventListener('submit', function(e) {
                const username = document.querySelector('#create-user-modal input[name="username"]').value.trim();
                const email = document.querySelector('#create-user-modal input[name="email"]').value.trim();
                const password = document.querySelector('#create-user-modal input[name="password"]').value;
                const confirmPassword = document.querySelector('#create-user-modal input[name="confirm_password"]').value;

                // Validation checks
                if (!username || !email || !password || !confirmPassword) {
                    e.preventDefault();
                    alert('Please fill in all required fields');
                    return false;
                }

                if (password.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters long');
                    return false;
                }

                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match. Please try again.');
                    return false;
                }

                // Show loading state
                const submitBtn = createUserForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = '⏳ Creating...';
            });
        }
    });

    // Real-time password confirmation check
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (passwordInput && confirmPasswordInput) {
        const checkPasswordMatch = function() {
            if (confirmPasswordInput.value === '') return; // Don't show error if empty
            
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.style.borderColor = '#ef4444';
                confirmPasswordInput.style.backgroundColor = '#fee2e2';
            } else {
                confirmPasswordInput.style.borderColor = '';
                confirmPasswordInput.style.backgroundColor = '';
            }
        };
        
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        passwordInput.addEventListener('input', checkPasswordMatch);
    }

    // Close modals when clicking outside
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('open');
            }
        });
    });
</script>

<style>
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
        display: none !important;
        align-items: center;
        justify-content: center;
    }

    .modal.open {
        display: flex !important;
    }

    .modal-content {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        width: 90%;
        max-width: 500px;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #6b7280;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding: 1.5rem;
        border-top: 1px solid #e5e7eb;
    }

    .space-y-4 > * + * {
        margin-top: 1rem;
    }
</style>

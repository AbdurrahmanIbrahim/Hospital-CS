<?php
include '../functions.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'profile';



  $user_id = getId();
  $sql = "SELECT * FROM users WHERE id = '$user_id'";
  $run = $db->query($sql);
  $user = $run->fetch_assoc();
  $pic = (empty($user['pic'])) ? 'default.jpg' : $user['pic'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> | Profile</title>
    <link rel="stylesheet" type="text/css" href="../styles/styles.css">
    <style type="text/css">

    </style>
</head>

<body>

<!-- Sidebar -->
<?php include '../includes/side_nav.php'; ?>

<!-- Main Content -->
<main class="main-content">

<?php include '../includes/header.php'; ?>

<div class="content-scroll">
 <style>
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --primary-light: #7c4dff;
    --primary-dark: #5a35b5;
    --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --accent-color: #ff6b6b;
    --light-bg: #f8f9fa;
    --card-bg: #ffffff;
    --text-dark: #2d3748;
    --text-light: #718096;
    --border-color: #e2e8f0;
    --success-color: #48bb78;
    --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
    --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
    --radius: 12px;
    --radius-sm: 8px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Container Styling */
.profile-container {
    min-height: calc(100vh - 80px);
    background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
    padding: 30px 0;
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Profile Card */
.profile-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-color);
    overflow: hidden;
    transition: var(--transition);
    height: 100%;
}

.profile-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
}

/* Card Header */
.card-header-profile {
    background: var(--primary-gradient);
    padding: 25px 30px;
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.card-header-profile::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 30px 30px;
    opacity: 0.3;
    animation: float 20s linear infinite;
}

@keyframes float {
    0% { transform: translate(0, 0) rotate(0deg); }
    100% { transform: translate(30px, 30px) rotate(360deg); }
}

.header-content {
    position: relative;
    z-index: 2;
}

.card-header-profile .title {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
}

.card-header-profile .subtitle {
    font-size: 14px;
    opacity: 0.9;
    font-weight: 300;
}

/* Profile Picture Section */
.profile-picture-container {
    position: relative;
    width: 180px;
    height: 180px;
    margin: 0 auto;
    cursor: pointer;
    transition: var(--transition);
}

.profile-picture-container:hover {
    transform: scale(1.05);
}

.profile-picture-container:hover .profile-picture {
    box-shadow: 0 10px 25px rgba(118, 75, 162, 0.3);
}

.profile-picture {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    transition: var(--transition);
}

.upload-overlay {
    position: absolute;
    bottom: 0;
    right: 0;
    background: var(--accent-color);
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
    transition: var(--transition);
}

.upload-overlay:hover {
    transform: scale(1.1);
    background: #ff5252;
}

.upload-overlay i {
    font-size: 18px;
}

/* Profile Form */
.profile-form {
    padding: 30px;
}

.form-group-profile {
    margin-bottom: 24px;
    position: relative;
}

.form-group-profile label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group-profile label::before {
    font-size: 14px;
}

.name-label::before { content: "👤"; }
.email-label::before { content: "✉️"; }
.phone-label::before { content: "📱"; }

.form-input {
    width: 100%;
    padding: 14px 16px;
    font-size: 15px;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-sm);
    background: var(--light-bg);
    transition: var(--transition);
    color: var(--text-dark);
    font-weight: 500;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(124, 77, 255, 0.1);
    background: white;
}

.form-input.name-input {
    font-size: 20px;
    font-weight: 700;
    padding: 16px;
    border-color: transparent;
    border-bottom: 2px solid var(--border-color);
    background: transparent;
    border-radius: 0;
}

.form-input.name-input:focus {
    border-bottom-color: var(--primary-light);
    background: rgba(124, 77, 255, 0.03);
}

.form-input.email-input {
    color: var(--text-light);
    font-weight: 400;
}

.form-input.phone-input {
    font-size: 18px;
    font-weight: 600;
}

/* Update Button */
.update-btn {
    background: var(--primary-gradient);
    color: white;
    border: none;
    padding: 14px 35px;
    font-size: 15px;
    font-weight: 600;
    border-radius: 50px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin: 25px auto 0;
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
    transition: var(--transition);
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.update-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    background: linear-gradient(135deg, #5a6fd8 0%, #653d94 100%);
}

.update-btn::before {
    content: "💾";
    font-size: 16px;
}

.update-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

/* Security Card */
.security-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-color);
    overflow: hidden;
    transition: var(--transition);
    height: 100%;
}

.security-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
}

.security-header {
    background: var(--secondary-gradient);
    padding: 25px 30px;
    color: white;
    position: relative;
    overflow: hidden;
}

.security-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.security-header .title {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
}

.security-header .subtitle {
    font-size: 14px;
    opacity: 0.9;
    font-weight: 300;
}

.security-content {
    padding: 30px;
}

.security-section-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-dark);
    margin: 25px 0 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 10px;
}

.security-section-title::before {
    content: "🔒";
    font-size: 20px;
}

.security-form-group {
    margin-bottom: 22px;
    position: relative;
}

.security-form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.security-form-group label::before {
    font-size: 14px;
}

.old-pass-label::before { content: "🔑"; }
.new-pass-label::before { content: "✨"; }
.confirm-pass-label::before { content: "✅"; }

.password-input {
    width: 100%;
    padding: 14px 16px;
    font-size: 15px;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-sm);
    background: var(--light-bg);
    transition: var(--transition);
    color: var(--text-dark);
    position: relative;
    padding-right: 45px;
}

.password-input:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
    background: white;
}

.toggle-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-light);
    cursor: pointer;
    font-size: 18px;
    padding: 5px;
    transition: var(--transition);
}

.toggle-password:hover {
    color: var(--primary-light);
}

.password-strength {
    height: 4px;
    background: var(--border-color);
    border-radius: 2px;
    margin-top: 8px;
    overflow: hidden;
}

.strength-meter {
    height: 100%;
    width: 0%;
    background: var(--accent-color);
    border-radius: 2px;
    transition: var(--transition);
}

/* Change Password Button */
.change-password-btn {
    background: var(--secondary-gradient);
    color: white;
    border: none;
    padding: 14px 35px;
    font-size: 15px;
    font-weight: 600;
    border-radius: 50px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin: 30px auto 0;
    box-shadow: 0 6px 20px rgba(240, 147, 251, 0.3);
    transition: var(--transition);
    letter-spacing: 0.5px;
    text-transform: uppercase;
    width: 100%;
}

.change-password-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(240, 147, 251, 0.4);
    background: linear-gradient(135deg, #e585f7 0%, #f4475e 100%);
}

.change-password-btn::before {
    content: "🔄";
    font-size: 16px;
}

/* Success Message */
.success-message {
    position: fixed;
    top: 20px;
    right: 20px;
    background: var(--success-color);
    color: white;
    padding: 15px 25px;
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow-lg);
    display: none;
    align-items: center;
    gap: 12px;
    z-index: 1000;
    animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.success-message::before {
    content: "🎉";
    font-size: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .profile-container {
        padding: 15px;
    }
    
    .profile-card, .security-card {
        margin-bottom: 20px;
    }
    
    .profile-picture-container {
        width: 150px;
        height: 150px;
    }
    
    .card-header-profile, .security-header {
        padding: 20px;
    }
    
    .profile-form, .security-content {
        padding: 20px;
    }
    
    .update-btn, .change-password-btn {
        width: 100%;
        padding: 14px 20px;
    }
}

/* Loading State */
.loading {
    position: relative;
    pointer-events: none;
    opacity: 0.7;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
    border: 2px solid var(--border-color);
    border-top-color: var(--primary-light);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: translate(-50%, -50%) rotate(360deg); }
}
</style>

<div class="page-content-wrapper profile-container">
    <div class="container">
        <div class="pt-3">
            <div class="row">
                <!-- User Profile Card -->
                <div class="col-md-6 mb-4">
                    <div class="profile-card">
                        <div class="card-header-profile">
                            <div class="header-content">
                                <div class="title">User Profile</div>
                                <div class="subtitle">Click on any detail to modify it</div>
                            </div>
                        </div>

                        <div class="profile-form">
                            <div class="text-center mb-4">
                                <div class="profile-picture-container">
                                    <form method="POST" action="upload_picture.php" id="upload_form" enctype="multipart/form-data">
                                        <label for="user_pic" style="cursor: pointer;">
                                            <img class="profile-picture" 
                                                 src="<?=ROOT_URL?>images/dp/<?=$pic?>" 
                                                 alt="User Avatar" 
                                                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=667eea&color=fff&size=150'">
                                            <div class="upload-overlay">
                                                <i>📷</i>
                                            </div>
                                            <input type="file" 
                                                   name="user_pic" 
                                                   id="user_pic" 
                                                   onchange="upload_picture()" 
                                                   style="display:none;" 
                                                   accept="image/*">
                                        </label>
                                    </form>
                                </div>
                            </div>

                            <form action="handler.php" class="profile-details-form" method="post">
                                <div class="form-group-profile">
                                    <label class="name-label">Full Name</label>
                                    <input type="text" 
                                           name="full_name" 
                                           value="<?= htmlspecialchars($user['name']) ?>" 
                                           default_value="<?= htmlspecialchars($user['name']) ?>" 
                                           onkeyup="change_profile()" 
                                           id="profile_input" 
                                           class="form-input name-input">
                                </div>

                                <div class="form-group-profile">
                                    <label class="email-label">Email Address</label>
                                    <input type="email" 
                                           name="email" 
                                           value="<?= htmlspecialchars($user['email']) ?>" 
                                           email_default_value="<?= htmlspecialchars($user['email']) ?>" 
                                           onkeyup="change_profile()" 
                                           id="email_input" 
                                           class="form-input email-input">
                                </div>

                                <div class="form-group-profile">
                                    <label class="phone-label">Phone Number</label>
                                    <input type="text" 
                                           name="phone" 
                                           value="<?= htmlspecialchars($user['phone']) ?>" 
                                           phone_default_value="<?= htmlspecialchars($user['phone']) ?>" 
                                           onkeyup="change_profile()" 
                                           id="phone_input" 
                                           class="form-input phone-input">
                                </div>

                                <button type="submit" 
                                        id="profile_button" 
                                        class="update-btn update_profile_name_btn"
                                        disabled>
                                    Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security Card -->
                <div class="col-md-6 mb-4">
                    <div class="security-card">
                        <div class="security-header">
                            <div class="title">Privacy & Security</div>
                            <div class="subtitle">Modify your security settings below</div>
                        </div>

                        <div class="security-content">
                            <div class="security-section-title">
                                Change Password
                            </div>

                            <form method="POST" action="change_password.php" class="security-form">
                                <div class="security-form-group">
                                    <label class="old-pass-label">Current Password</label>
                                    <div style="position: relative;">
                                        <input type="password" 
                                               name="old_password" 
                                               class="password-input" 
                                               placeholder="Enter your current password"
                                               required>
                                        <button type="button" class="toggle-password" onclick="togglePassword(this)">👁️</button>
                                    </div>
                                </div>

                                <div class="security-form-group">
                                    <label class="new-pass-label">New Password</label>
                                    <div style="position: relative;">
                                        <input type="password" 
                                               name="new_password" 
                                               class="password-input" 
                                               placeholder="Create a strong password"
                                               required
                                               onkeyup="checkPasswordStrength(this.value)">
                                        <button type="button" class="toggle-password" onclick="togglePassword(this)">👁️</button>
                                        <div class="password-strength">
                                            <div class="strength-meter" id="passwordStrength"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="security-form-group">
                                    <label class="confirm-pass-label">Confirm Password</label>
                                    <div style="position: relative;">
                                        <input type="password" 
                                               name="confirm_password" 
                                               class="password-input" 
                                               placeholder="Re-enter your new password"
                                               required>
                                        <button type="button" class="toggle-password" onclick="togglePassword(this)">👁️</button>
                                    </div>
                                </div>

                                <button type="submit" 
                                        id="profile_button_2" 
                                        class="change-password-btn">
                                    Update Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Message Toast -->
<div class="success-message" id="successToast">
    Profile updated successfully!
</div>

<script>
// Toggle password visibility
function togglePassword(button) {
    const input = button.previousElementSibling;
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
    button.textContent = type === 'password' ? '👁️' : '🙈';
}

// Check password strength
function checkPasswordStrength(password) {
    const meter = document.getElementById('passwordStrength');
    let strength = 0;
    
    if (password.length >= 8) strength += 25;
    if (/[A-Z]/.test(password)) strength += 25;
    if (/[0-9]/.test(password)) strength += 25;
    if (/[^A-Za-z0-9]/.test(password)) strength += 25;
    
    meter.style.width = strength + '%';
    
    if (strength < 50) {
        meter.style.background = '#ff6b6b';
    } else if (strength < 75) {
        meter.style.background = '#ffa726';
    } else {
        meter.style.background = '#48bb78';
    }
}

// Show success message
function showSuccess(message) {
    const toast = document.getElementById('successToast');
    if (message) toast.textContent = message;
    toast.style.display = 'flex';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

// Enable save button when form changes
function change_profile() {
    const button = document.getElementById('profile_button');
    button.disabled = false;
    button.classList.remove('disabled');
}

// Handle picture upload
function upload_picture() {
    const form = document.getElementById('upload_form');
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector('.profile-picture').src = data.new_path + '?t=' + new Date().getTime();
            showSuccess('Profile picture updated!');
        } else {
            alert('Error uploading picture: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error uploading picture');
    });
}

// Add loading state to buttons on form submit
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const button = this.querySelector('button[type="submit"]');
        button.classList.add('loading');
        button.innerHTML = '<span>Saving...</span>';
        
        // Re-enable button after 3 seconds (in case of error)
        setTimeout(() => {
            button.classList.remove('loading');
            button.innerHTML = button.getAttribute('data-original') || button.textContent;
        }, 3000);
    });
});

// Initialize password strength meter
document.addEventListener('DOMContentLoaded', function() {
    const newPasswordInput = document.querySelector('input[name="new_password"]');
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });
    }
});

     function upload_picture(){
            document.querySelector('#upload_form').submit();
         }

</script>

</div>

<?php include '../includes/footer.php'; ?>
</main>

</body>
</html>

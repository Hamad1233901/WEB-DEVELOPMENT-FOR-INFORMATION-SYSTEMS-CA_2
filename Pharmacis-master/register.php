<?php
// Filename: register.php
// Yeh file naye user ko register karne ke liye hai.

session_start();
include_once 'config/database.php';
include_once 'includes/functions.php';

// Agar user pehle se login hai, to dashboard par bhejo
if(isset($_SESSION['user_id'])){ header("Location: index.php"); }

$message = "";
$error = "";

if($_POST){
    $database = new Database();
    $db = $database->getConnection();

    $username = cleanInput($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = cleanInput($_POST['role']); // 'admin' ya 'pharmacist'

    // 1. Validation: Kya passwords match karte hain?
    if($password !== $confirm_password){
        $error = "Passwords match nahi kar rahe!";
    } 
    // 2. Validation: Password length
    elseif(strlen($password) < 6){
        $error = "Password kam az kam 6 characters ka hona chahiye.";
    } 
    else {
        // 3. Check: Kya username pehle se majood hai?
        $checkQuery = "SELECT id FROM users WHERE username = :username";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':username', $username);
        $checkStmt->execute();

        if($checkStmt->rowCount() > 0){
            $error = "Yeh Username pehle se liya ja chuka hai.";
        } else {
            // 4. Create User (Insert)
            // Note: Password ko hamesha HASH karke save karein (Security)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':role', $role);

            if($stmt->execute()){
                $message = "Account ban gaya! Ab aap Login kar sakte hain.";
            } else {
                $error = "Account banane mein masla aa gaya.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - PharmaCIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-gray-200 h-screen flex justify-center items-center">
    <div class="bg-white p-8 rounded auth-box w-96">
        <h2 class="text-2xl font-bold mb-4 text-center text-blue-600 logo-text">Create Account</h2>
        
        <!-- Error Message -->
        <?php if($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4 text-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Success Message -->
        <?php if($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4 text-sm">
                <?php echo $message; ?> 
                <a href="login.php" class="font-bold underline">Login Here</a>
            </div>
        <?php endif; ?>

        <?php if(empty($message)): // Form tab hi dikhao jab success na ho ?>
        <form method="POST">
            <div class="mb-3">
                <label class="block text-gray-700 text-sm font-bold mb-1">Username</label>
                <input type="text" name="username" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <div class="mb-3">
                <label class="block text-gray-700 text-sm font-bold mb-1">Role</label>
                <select name="role" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="pharmacist">Pharmacist (Staff)</option>
                    <option value="admin">Admin (Manager)</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="block text-gray-700 text-sm font-bold mb-1">Password</label>
                <input type="password" name="password" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-1">Confirm Password</label>
                <input type="password" name="confirm_password" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <button type="submit" class="w-full bg-green-600 text-white p-2 rounded hover:bg-green-700 transition font-bold">Register</button>
        </form>
        <?php endif; ?>

        <div class="text-center mt-4 border-t pt-4">
            <p class="text-sm text-gray-600">Already have an account?</p>
            <a href="login.php" class="text-blue-500 font-bold hover:underline">Login here</a>
        </div>
    </div>
</body>
</html>
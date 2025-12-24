<?php
// Filename: login.php
session_start();
include_once 'config/database.php';
include_once 'includes/functions.php';

if(isset($_SESSION['user_id'])){ 
    // Agar pehle se login hai to role check karo
    if($_SESSION['role'] == 'admin'){
        header("Location: index.php");
    } else {
        header("Location: pos.php");
    }
    exit;
}

if($_POST){
    $database = new Database();
    $db = $database->getConnection();
    
    $username = cleanInput($_POST['username']);
    $password = $_POST['password']; 

    $query = "SELECT id, username, password, role FROM users WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if($stmt->rowCount() > 0){
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if(password_verify($password, $row['password'])){
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            
            // --- YAHAN CHANGE KIYA HAI ---
            // Role ke hisab se alag page par bhejo
            if($row['role'] == 'admin') {
                header("Location: index.php"); // Admin -> Dashboard
            } else {
                header("Location: pos.php");   // Staff -> POS directly
            }
            exit;
            // -----------------------------

        } else {
            $error = "Wrong Password!";
        }
    } else {
        $error = "User Not find.";
    }
}
?>
<!-- HTML part same rahega, copy from previous login.php if needed or keep existing -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - PharmaCIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-gray-200 h-screen flex justify-center items-center">
    <div class="bg-white p-8 rounded auth-box w-96">
        <h2 class="text-2xl font-bold mb-6 text-center text-blue-600 logo-text">PharmaCIS Login</h2>
        <?php if(isset($error)) echo "<p class='bg-red-100 text-red-600 p-2 rounded text-sm mb-4 text-center'>$error</p>"; ?>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" name="username" class="w-full border p-2 rounded" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" class="w-full border p-2 rounded" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700 font-bold">Sign In</button>
        </form>
        <div class="text-center mt-6 border-t pt-4">
            <a href="register.php" class="text-green-600 font-bold hover:underline">Create New Account</a>
        </div>
    </div>
</body>
</html>
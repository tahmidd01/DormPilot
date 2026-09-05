<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
.sidebar {
    width:260px;
    background: linear-gradient(to bottom, #e6d4ff, #cfe7ff);
    display:flex;
    flex-direction:column;
    align-items:center;
    padding:20px;
    border-right:2px solid #ddd;
    position:fixed;
    left:0;
    top:0;
    height:100vh;
    overflow-y:auto;
    overflow-x:hidden;
    -ms-overflow-style:none;
    scrollbar-width:none;
}
.sidebar::-webkit-scrollbar {
    width:0px;
    display:none;
}
.sidebar img.logo {
    width:200px;
    height:auto;
    margin-bottom:20px;
}
.sidebar .menu {
    width:100%;
}
.sidebar .menu a {
    display:flex; 
    align-items:center;
    padding:14px 20px; 
    color:#333;
    text-decoration:none;
    font-weight:bold;
    font-size:18px; 
    margin:6px 0;
    border-radius:6px;
    transition:0.3s;
}
.sidebar .menu a:hover {
    background:#ad276a;
    color:#fff;
}
.sidebar .menu a img.menu-icon {
    width:28px;  
    height:28px;
    margin-right:10px;
}
body.with-supervisor-sidebar {
    margin-left:260px;
}
</style>

<div class="sidebar">
    <img src="logo.png" class="logo" alt="Logo">
    <div class="menu">
        <a href="supervisor_dashboard.php" class="<?php echo $current_page == 'supervisor_dashboard.php' ? 'active' : ''; ?>"><img src="icons/dashboard.png" class="menu-icon">Dashboard</a>
        <a href="supervisor_students.php" class="<?php echo $current_page == 'supervisor_students.php' ? 'active' : ''; ?>"><img src="icons/profile.png" class="menu-icon">Students</a>
        <a href="supervisor_complaints.php" class="<?php echo $current_page == 'supervisor_complaints.php' ? 'active' : ''; ?>"><img src="icons/complain.png" class="menu-icon">Complaints</a>
        <a href="supervisor_room_changes.php" class="<?php echo $current_page == 'supervisor_room_changes.php' ? 'active' : ''; ?>"><img src="icons/room.png" class="menu-icon">Room Changes</a>
        <a href="supervisor_announcements.php" class="<?php echo $current_page == 'supervisor_announcements.php' ? 'active' : ''; ?>"><img src="icons/announcement.png" class="menu-icon">Announcements</a>
        <a href="supervisor_tasks.php" class="<?php echo $current_page == 'supervisor_tasks.php' ? 'active' : ''; ?>"><img src="icons/complain.png" class="menu-icon">Tasks</a>
        <a href="supervisor_chat.php" class="<?php echo $current_page == 'supervisor_chat.php' ? 'active' : ''; ?>"><img src="icons/supervisor.png" class="menu-icon">Chat</a>
    </div>
</div>


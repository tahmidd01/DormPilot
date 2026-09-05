<?php
// Student Sidebar - Include this in all student pages
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
.sidebar {
    width:250px;
    background:linear-gradient(to bottom, #ff6b6b, #ee5a6f);
    color:white;
    min-height:100vh;
    padding:20px 0;
    position:fixed;
    left:0;
    top:0;
    z-index:1000;
}
.sidebar-header {
    padding:20px;
    border-bottom:1px solid rgba(255,255,255,0.2);
    margin-bottom:20px;
}
.sidebar-header h2 {
    font-size:20px;
    margin-bottom:5px;
}
.sidebar-header p {
    font-size:12px;
    opacity:0.9;
}
.sidebar-menu {
    list-style:none;
}
.sidebar-menu li {
    margin:5px 0;
}
.sidebar-menu a {
    display:block;
    padding:12px 20px;
    color:white;
    text-decoration:none;
    transition:all 0.3s;
    border-left:3px solid transparent;
}
.sidebar-menu a:hover, .sidebar-menu a.active {
    background:rgba(255,255,255,0.1);
    border-left-color:white;
}
.sidebar-menu a strong {
    display:block;
    font-size:14px;
}
.sidebar-menu a span {
    font-size:11px;
    opacity:0.8;
}
body.with-sidebar {
    margin-left:250px;
}
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>DormPilot</h2>
        <p>Student Portal</p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="student_dashboard.php" class="<?php echo $current_page == 'student_dashboard.php' ? 'active' : ''; ?>">
            <strong>Dashboard</strong>
            <span>Overview & Statistics</span>
        </a></li>
        <li><a href="student_admission.php" class="<?php echo $current_page == 'student_admission.php' ? 'active' : ''; ?>">
            <strong>Request Admission</strong>
            <span>Submit hostel admission</span>
        </a></li>
        <li><a href="student_complaints.php" class="<?php echo $current_page == 'student_complaints.php' ? 'active' : ''; ?>">
            <strong>Submit Complaint</strong>
            <span>Report issues</span>
        </a></li>
        <li><a href="student_room_change.php" class="<?php echo $current_page == 'student_room_change.php' ? 'active' : ''; ?>">
            <strong>Room Change</strong>
            <span>Request room change</span>
        </a></li>
        <li><a href="student_announcements.php" class="<?php echo $current_page == 'student_announcements.php' ? 'active' : ''; ?>">
            <strong>Announcements</strong>
            <span>View notices</span>
        </a></li>
        <li><a href="student_chat.php" class="<?php echo $current_page == 'student_chat.php' ? 'active' : ''; ?>">
            <strong>Chat</strong>
            <span>Message supervisor</span>
        </a></li>
        <li><a href="student_roommates.php" class="<?php echo $current_page == 'student_roommates.php' ? 'active' : ''; ?>">
            <strong>Room & Roommates</strong>
            <span>View room details</span>
        </a></li>
        <li><a href="logout.php" style="border-top:1px solid rgba(255,255,255,0.2); margin-top:20px; padding-top:20px;">
            <strong>Logout</strong>
            <span>Sign out</span>
        </a></li>
    </ul>
</div>

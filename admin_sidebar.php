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
body.with-admin-sidebar {
    margin-left:260px;
}
</style>

<div class="sidebar">
    <img src="logo.png" class="logo" alt="Logo">
    <div class="menu">
        <a href="admin_dashboard.php" class="<?php echo $current_page == 'admin_dashboard.php' ? 'active' : ''; ?>"><img src="icons/dashboard.png" class="menu-icon">Dashboard</a>
        <a href="admin_supervisors.php" class="<?php echo $current_page == 'admin_supervisors.php' ? 'active' : ''; ?>"><img src="icons/supervisor.png" class="menu-icon">Supervisors</a>
        <a href="admin_rooms.php" class="<?php echo $current_page == 'admin_rooms.php' ? 'active' : ''; ?>"><img src="icons/room.png" class="menu-icon">Rooms</a>
        <a href="admin_assign_students.php" class="<?php echo $current_page == 'admin_assign_students.php' ? 'active' : ''; ?>"><img src="icons/admission.png" class="menu-icon">Assign Students</a>
        <a href="admin_assign_tasks.php" class="<?php echo $current_page == 'admin_assign_tasks.php' ? 'active' : ''; ?>"><img src="icons/complain.png" class="menu-icon">Assign Tasks</a>
        <a href="admin_admissions.php" class="<?php echo $current_page == 'admin_admissions.php' ? 'active' : ''; ?>"><img src="icons/admission.png" class="menu-icon">Admissions</a>
        <a href="admin_reports.php" class="<?php echo $current_page == 'admin_reports.php' ? 'active' : ''; ?>"><img src="icons/profile.png" class="menu-icon">Reports</a>
        <a href="admin_repair_costs.php" class="<?php echo $current_page == 'admin_repair_costs.php' ? 'active' : ''; ?>"><img src="icons/complain.png" class="menu-icon">Repair Costs</a>
        <a href="admin_pdf_reports.php" class="<?php echo $current_page == 'admin_pdf_reports.php' ? 'active' : ''; ?>"><img src="icons/announcement.png" class="menu-icon">PDF Reports</a>
    </div>
</div>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DormPilot | Hostel Management System</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family: Arial, sans-serif;
}


.navbar{
    height:80px; 
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 40px;
    background:linear-gradient(to right,#e6d4ff,#cfe7ff);
}


.navbar .logo{
    display:flex;
    align-items:center;
    height:100%;
}

.navbar .logo img{
    height:150px;   
    width:auto;
    padding:5px;
}


.navbar ul{
    list-style:none;
    display:flex;
    gap:25px;
}

.navbar ul li a{
    text-decoration:none;
    font-size:18px;
    color:#333;
    font-weight:bold;
}

.navbar ul li a:hover{
    color:#e24a84;
}


.hero{
    height:75vh;
    background:url("hostel.jpg") center/cover no-repeat;
    display:flex;
    justify-content:center;
    align-items:center;
    text-align:center;
    position:relative;
    height: 750px;
}

.hero::after{
    content:"";
    position:absolute;
    inset:0;
    background:rgba(0,0,0,0.45);
}

.hero-content{
    position:relative;
    color:#fff;
    max-width:800px;
}

.hero-content h1{
    font-size:50px;
    margin-bottom:15px;
    color: #070707ff; 
    
}

.hero-content p{
    font-size:40px;
    margin-bottom:30px;
      color: #0d0d0eff;
      
}


footer{
    background:#333;
    color:#fff;
    text-align:center;
    padding:18px;
    font-size:16px;
}
</style>
</head>

<body>


<div class="navbar">
    <div class="logo">
        <img src="logo.png" alt="DormPilot Logo">
    </div>

    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="guest.php">Guest</a></li>
    </ul>
</div>


<div class="hero">
    <div class="hero-content">
        <h1>Welcome to DormPilot</h1>
        <p>Smart Hostel Management System</p>
    </div>
</div>


<footer>
    © <?php echo date("Y"); ?> DormPilot | All Rights Reserved
</footer>

</body>
</html>

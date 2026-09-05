<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Guest Information - DormPilot</title>
<style>
* {
    margin:0;
    padding:0;
    box-sizing:border-box;
}
body {
    font-family: Arial, sans-serif;
    background:#f5f5f5;
}
.navbar {
    height:80px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 40px;
    background:linear-gradient(to right, #e6d4ff, #cfe7ff);
}
.navbar .logo img {
    height:150px;
    width:auto;
    padding:5px;
}
.navbar ul {
    list-style:none;
    display:flex;
    gap:25px;
}
.navbar ul li a {
    text-decoration:none;
    font-size:18px;
    color:#333;
    font-weight:bold;
}
.navbar ul li a:hover {
    color:#e24a84;
}
.container {
    max-width:1200px;
    margin:40px auto;
    padding:0 20px;
}
.section {
    background:white;
    padding:30px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    margin-bottom:30px;
}
.section h2 {
    color:#333;
    margin-bottom:20px;
    padding-bottom:10px;
    border-bottom:2px solid #4a90e2;
}
.section h3 {
    color:#555;
    margin:20px 0 10px 0;
}
.section p, .section ul {
    color:#666;
    line-height:1.8;
    margin-bottom:15px;
}
.section ul {
    margin-left:30px;
}
.room-types {
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));
    gap:20px;
    margin-top:20px;
}
.room-card {
    padding:20px;
    background:#f8f9fa;
    border-radius:5px;
    border:1px solid #ddd;
}
.room-card h4 {
    color:#4a90e2;
    margin-bottom:10px;
}
.contact-info {
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));
    gap:15px;
    margin-top:20px;
}
.contact-item {
    padding:15px;
    background:#f8f9fa;
    border-radius:5px;
}
.contact-item strong {
    display:block;
    color:#333;
    margin-bottom:5px;
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
    </ul>
</div>

<div class="container">
    <div class="section">
        <h2>Hostel Facilities</h2>
        <p>Our hostel provides a comfortable and safe living environment for all residents. We offer:</p>
        <ul>
            <li>24/7 Security and CCTV surveillance</li>
            <li>High-speed Wi-Fi internet connection</li>
            <li>Common study areas and lounges</li>
            <li>Laundry facilities</li>
            <li>Dining hall with meal services</li>
            <li>Recreation room with TV and games</li>
            <li>Gym and fitness center</li>
            <li>Library and reading room</li>
            <li>Medical assistance and first aid</li>
            <li>Regular maintenance and cleaning services</li>
        </ul>
    </div>

    <div class="section">
        <h2>Room Types</h2>
        <div class="room-types">
            <div class="room-card">
                <h4>Single Room</h4>
                <p>Private room with single bed, study desk, wardrobe, and attached bathroom. Perfect for students who prefer privacy.</p>
            </div>
            <div class="room-card">
                <h4>Double Room</h4>
                <p>Shared room with two beds, two study desks, shared wardrobe, and attached bathroom. Great for making friends.</p>
            </div>
            <div class="room-card">
                <h4>Triple Room</h4>
                <p>Spacious room with three beds, three study desks, shared facilities, and attached bathroom. Economical option.</p>
            </div>
            <div class="room-card">
                <h4>Quad Room</h4>
                <p>Large room accommodating four students with individual study spaces and shared bathroom facilities.</p>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Hostel Rules & Regulations</h2>
        <h3>General Rules</h3>
        <ul>
            <li>All residents must maintain cleanliness in their rooms and common areas</li>
            <li>Quiet hours are from 10 PM to 7 AM</li>
            <li>Visitors are allowed only during designated hours (2 PM - 8 PM)</li>
            <li>Smoking and alcohol consumption are strictly prohibited</li>
            <li>Residents must follow all safety and fire regulations</li>
        </ul>
        
        <h3>Room Maintenance</h3>
        <ul>
            <li>Residents are responsible for keeping their rooms clean</li>
            <li>Any damages must be reported immediately</li>
            <li>Room inspections will be conducted monthly</li>
            <li>Personal belongings should be properly secured</li>
        </ul>
        
        <h3>Common Areas</h3>
        <ul>
            <li>Common areas must be kept clean after use</li>
            <li>Respect other residents' privacy and space</li>
            <li>Report any issues or complaints to supervisors</li>
            <li>Follow the schedule for shared facilities</li>
        </ul>
    </div>

    <div class="section">
        <h2>Contact Information</h2>
        <div class="contact-info">
            <div class="contact-item">
                <strong>Hostel Office</strong>
                <p>Phone: +1 (555) 123-4567</p>
                <p>Email: hostel@dormpilot.edu</p>
            </div>
            <div class="contact-item">
                <strong>Emergency Contact</strong>
                <p>Phone: +1 (555) 999-8888</p>
                <p>Available 24/7</p>
            </div>
            <div class="contact-item">
                <strong>Maintenance</strong>
                <p>Phone: +1 (555) 777-6666</p>
                <p>Email: maintenance@dormpilot.edu</p>
            </div>
            <div class="contact-item">
                <strong>Address</strong>
                <p>123 University Avenue</p>
                <p>City, State 12345</p>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>How to Apply</h2>
        <p>To apply for hostel admission:</p>
        <ol style="margin-left:30px; color:#666; line-height:1.8;">
            <li>Create an account by clicking on "Register" in the navigation menu</li>
            <li>Login to your account</li>
            <li>Fill out the admission request form with all required information</li>
            <li>Wait for admin approval</li>
            <li>Once approved, you will be assigned to a room</li>
        </ol>
        <p style="margin-top:20px;"><a href="register.php" style="color:#4a90e2; text-decoration:none; font-weight:bold;">Register Now →</a></p>
    </div>
</div>

<footer style="background:#333; color:#fff; text-align:center; padding:20px; margin-top:40px;">
    © <?php echo date("Y"); ?> DormPilot | All Rights Reserved
</footer>

</body>
</html>


# Smart Cafe
<h3>A Web-Based Digital Menu &amp; Table Ordering Platform</h3>
<br>

<div align="center">
  <img src="https://img.shields.io/badge/Status-Production%20Ready-success" alt="Status" />
  <img src="https://img.shields.io/badge/License-Confidential-red" alt="License" />
</div>
<br>

## 🌟 Overview
- **Purpose**: To digitize the restaurant dining experience by allowing customers to order food directly from their tables using their smartphones.<br>
- **Problem Solved**: Eliminates waiting for waiters, reduces manual order-taking errors, speeds up the service &amp; provides real-time order tracking for customers.<br>
- **Main Features**:<br>
Admin Dashboard to manage foods, categories &amp; live orders.<br>
Real-time Order Tracking (Pending, Preparing, Ready).<br>
Digital Menu with Categories &amp; Veg/Non-Veg Filters.<br>
QR Code Scanning for automatic table identification.<br>
Live Cart &amp; order placement system.<br><br><br>

## ⚙️ Technologies Used
This project is built using a standard Web Development Stack (LAMP/XAMPP).<br><br>
- **HTML**: Used to create the basic structure &amp; layout of the web pages (buttons, text, images).<br>
- **CSS**: Used for styling the website (colors, fonts, margins) to make it look beautiful and premium.<br>
- **Bootstrap**: A CSS framework used to make the website mobile-responsive automatically &amp; provide pre-designed grid layouts.<br>
- **JavaScript**: Used on the browser (client-side) to make the website interactive. It handles the "Add to Cart" functions, modal popups &amp; updates the cart without refreshing the page.<br>
- **PHP**: The backend scripting language used to process form data, connect to the database &amp; handle business logic (like verifying admin login).<br>
- **MySQL**: The database management system used to store all persistent data like food items, customer orders &amp; admin credentials.<br>
- **phpMyAdmin**: A web interface used to easily view, manage &amp; create the MySQL database tables without writing raw SQL code in the terminal.<br>
- **XAMPP**: A local software package that turns our computer into a local server. It provides Apache &amp; MySQL so we can run PHP files offline.<br>
- **Apache**: The web server software included in XAMPP that listens for browser requests &amp; serves the PHP/HTML pages.<br>
- **JSON**: Used to format data when sending information between the JavaScript frontend &amp; PHP backend (especially in APIs).<br>
- **AJAX (Fetch API)**: Used in JavaScript to communicate with the PHP APIs in the background so the user doesn't have to reload the page when placing an order.<br>
- **Local Storage**: Used in the browser to temporarily save the customer's cart items. If they close the tab and reopen it, their cart is still there.<br>
- **Sessions (PHP)**: Used to keep the Admin logged in securely. It stores a temporary token on the server so the admin doesn't have to log in on every page.<br>
- **Prepared Statements**: A secure way to write SQL queries in PHP to protect the database against SQL Injection attacks.<br>
- **Bootstrap Icons**: Used to display high-quality scalable icons (like cart, user, trash bin) across the website.<br><br><br>

## 🗂️ Website Workflow (Customer Journey)
- **Customer opens website**: Usually by scanning a QR code on their table, which sets their table number automatically.<br>
- **Homepage**: Views popular items, interactive statistics &amp; a "How it Works" guide.<br>
- **Menu**: Browses the full digital menu.<br>
- **Search &amp; Filters**: Clicks categories &amp; Veg/Non-Veg toggles to filter foods instantly.<br>
- **Add to Cart**: Clicks "Add". A popup modal shows food details (image, macros). The quantity can be adjusted inline (- 1 +).<br>
- **Checkout**: Goes to the Cart page, reviews items, enters Name/Phone &amp; clicks "Place Order".<br>
- **Order Saved**: JavaScript sends the cart data via AJAX to place_order.php, which saves it securely in the MySQL database.<br>
- **Order Tracking**: Customer is redirected to ```track-order.php``` where they can see if their food is Pending, Preparing &amp; Ready.<br>
- p**Feedback**: After eating, the customer submits a rating &amp; comment on the Feedback page.<br><br><br>

## 📊 Project Flow Diagram
<br>
  
```
                                              Browser (HTML/CSS/JS)
                                                        ⬇
                                 Customer Clicks "Place Order" (AJAX/Fetch API)
                                                        ⬇
                                            Apache Web Server (XAMPP)
                                                        ⬇
                                             Backend Logic (PHP APIs)
                                                        ⬇
                                    Database Query (PDO Prepared Statements)
                                                        ⬇
                                        MySQL Database (Stores Order Data)
                                                        ⬇
                                            Success Message (JSON)
                                                        ⬇
                                            Browser Updates Screen
```
<br><br>

## 🤝 Contributing
Feel free to fork the repository &amp; submit the pull requests. If you find any bugs or want to suggest new features, please open an issue in the GitHub Repository.

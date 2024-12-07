const express = require('express');
const path = require('path');
const session = require('express-session');
const mysql = require('mysql2');
const app = express();
const port = 3000;

// Middleware to parse POST request data
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Session setup
app.use(session({
  secret: 'your_secret_key',  // Replace with a real secret key
  resave: false,
  saveUninitialized: true
}));

// MySQL Database Connection
const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'quickiejeepney'
});

// Connect to MySQL
db.connect((err) => {
  if (err) {
    console.error('MySQL connection error:', err);
    throw err;
  }
  console.log('Connected to MySQL Database');
});

// Serve static files from 'admin' folder
app.use(express.static(path.join(__dirname, 'admin')));

// Route to serve the login page
app.get('/', (req, res) => {
  if (req.session.admin) {
    return res.redirect('/admin');  // Redirect logged-in users to the dashboard
  }

  // Set headers to prevent caching
  res.setHeader('Cache-Control', 'no-store');
  res.sendFile(path.join(__dirname, 'admin', 'login.html'));
});

// Login route
app.post('/login', (req, res) => {
  const { email, password } = req.body;

  db.query('SELECT * FROM admin WHERE email = ?', [email], (err, result) => {
    if (err) {
      console.error('MySQL query error:', err);
      return res.status(500).json({ success: false, message: 'Database query error' });
    }

    if (result.length > 0) {
      const admin = result[0];

      if (password === admin.password) {
        req.session.admin = admin;  // Store admin data in session
        return res.json({ success: true });
      } else {
        return res.status(401).json({ success: false, message: 'Invalid credentials' });
      }
    } else {
      return res.status(401).json({ success: false, message: 'Invalid credentials' });
    }
  });
});

// Admin route - Only accessible if logged in as admin
app.get('/admin', (req, res) => {
  if (req.session.admin) {
    res.sendFile(path.join(__dirname, 'admin', 'admin-dashboard.html'));
  } else {
    res.status(403).json({ success: false, message: 'Forbidden - You must be logged in' });
  }
});

// Route to fetch users from the database
app.get('/api/users', (req, res) => {
  db.query('SELECT firstName, lastName, contactNumber, email, occupation FROM user', (err, result) => {
    if (err) {
      console.error('MySQL query error:', err);
      return res.status(500).json({ success: false, message: 'Database query error' });
    }
    res.json(result);  // Send the user data as JSON
  });
});

// Check if the user is logged in
app.get('/api/check-login', (req, res) => {
  if (req.session.admin) {
    res.json({ loggedIn: true });
  } else {
    res.json({ loggedIn: false });
  }
});

// Logout route (POST request)
app.post('/logout', (req, res) => {
  req.session.destroy((err) => {
    if (err) {
      console.error('Error destroying session:', err);
      return res.status(500).json({ success: false, message: 'Failed to log out' });
    }
    console.log('Session destroyed successfully');
    res.status(200).json({ success: true });
  });
});

// Start the server
app.listen(port, () => {
  console.log(`Server is running at http://localhost:${port}`);
});
